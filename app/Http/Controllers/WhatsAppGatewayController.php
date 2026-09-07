<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\WhatsAppLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WhatsAppGatewayController extends Controller
{
    /**
     * Display the WhatsApp Gateway settings dashboard.
     */
    public function index()
    {
        $this->authorize('akses-whatsapp-gateway');

        $activeCompanyId = session('active_company_id') ?: (auth()->user()->company_id ?? 1);

        // Fetch settings from DB for active company
        $settings = AppSetting::forActiveCompany()->pluck('value', 'key')->all();
        $rawApiToken = $settings['wa_api_token'] ?? '';
        $apiToken = ''; // This will be the masked token passed to the view
        $decryptedToken = '';

        if ($rawApiToken) {
            try {
                $decryptedToken = decrypt($rawApiToken);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                $decryptedToken = $rawApiToken;
            }
            $apiToken = strlen($decryptedToken) > 8 
                ? substr($decryptedToken, 0, 4) . str_repeat('•', 20) . substr($decryptedToken, -4) 
                : str_repeat('•', 20);
        }

        // Auto sync device status from Fonnte when loading settings page if token exists
        if ($decryptedToken) {
            $this->syncDeviceStatus($decryptedToken);
            // Refresh settings array after sync
            $settings = AppSetting::forActiveCompany()->pluck('value', 'key')->all();
        }

        // Gateway Connection Status
        $provider = $settings['wa_provider'] ?? 'Fonnte';
        $status = $settings['wa_status'] ?? 'Disconnected';
        $phoneNumber = $settings['wa_phone_number'] ?? '-';
        $deviceName = $settings['wa_device_name'] ?? '-';
        $lastConnected = $settings['wa_last_connected'] ?? '-';

        // Notification Settings
        $enableNotification = $settings['wa_enable_notification'] ?? '0';
        $enableApproval = $settings['wa_enable_approval'] ?? '0';
        $enablePurchase = $settings['wa_enable_purchase'] ?? '0';
        $enableStockAlert = $settings['wa_enable_stock_alert'] ?? '0';
        $enableReminder = $settings['wa_enable_reminder'] ?? '0';

        // Fetch logs for active company
        $logs = WhatsAppLog::forActiveCompany()->latest()->limit(20)->get();

        return view('settings.whatsapp.index', compact(
            'provider', 'apiToken', 'status', 'phoneNumber', 'deviceName', 'lastConnected',
            'enableNotification', 'enableApproval', 'enablePurchase', 'enableStockAlert', 'enableReminder',
            'logs'
        ));
    }

    /**
     * Save the WhatsApp configuration settings.
     */
    public function saveSettings(Request $request)
    {
        $this->authorize('manage-whatsapp-gateway');

        $activeCompanyId = session('active_company_id') ?: (auth()->user()->company_id ?? 1);
        $formType = $request->input('form_type', 'api_config');

        try {
            DB::beginTransaction();

            if ($formType === 'api_config') {
                $request->validate([
                    'wa_provider'  => 'required|string',
                    'wa_api_token' => 'nullable|string|max:255',
                ]);
                
                AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_provider'], ['value' => $request->wa_provider]);
                
                $inputToken = $request->wa_api_token;
                // Only save/encrypt if the token was changed and is not the masked placeholder
                if ($inputToken && !str_contains($inputToken, '•')) {
                    $encryptedToken = encrypt($inputToken);
                    AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_api_token'], ['value' => $encryptedToken]);
                    $this->syncDeviceStatus($inputToken);
                } elseif (!$inputToken) {
                    AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_api_token'], ['value' => '']);
                    AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_status'], ['value' => 'Disconnected']);
                    AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_phone_number'], ['value' => '-']);
                    AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_device_name'], ['value' => '-']);
                    AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_last_connected'], ['value' => '-']);
                } else {
                    // Token is unchanged (still masked placeholder). We just sync the status.
                    $decryptedToken = self::getDecryptedToken($activeCompanyId);
                    if ($decryptedToken) {
                        $this->syncDeviceStatus($decryptedToken);
                    }
                }
            } elseif ($formType === 'notifications') {
                AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_enable_notification'], ['value' => $request->has('wa_enable_notification') ? '1' : '0']);
                AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_enable_approval'], ['value' => $request->has('wa_enable_approval') ? '1' : '0']);
                AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_enable_purchase'], ['value' => $request->has('wa_enable_purchase') ? '1' : '0']);
                AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_enable_stock_alert'], ['value' => $request->has('wa_enable_stock_alert') ? '1' : '0']);
                AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_enable_reminder'], ['value' => $request->has('wa_enable_reminder') ? '1' : '0']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi WhatsApp Gateway berhasil disimpan untuk PT ini!'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal!',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Save WA Settings error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan konfigurasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send test message using Fonnte API.
     */
    public function sendTestMessage(Request $request)
    {
        $this->authorize('manage-whatsapp-gateway');

        $activeCompanyId = session('active_company_id') ?: (auth()->user()->company_id ?? 1);

        $request->validate([
            'phone_number' => 'required|string',
            'message'      => 'required|string',
        ]);

        $apiToken = self::getDecryptedToken($activeCompanyId);
        if (!$apiToken) {
            return response()->json([
                'success' => false,
                'message' => 'API Token WhatsApp belum dikonfigurasi untuk PT ini!'
            ], 422);
        }

        try {
            // Call Fonnte API
            $response = Http::withHeaders([
                'Authorization' => $apiToken,
            ])->asForm()->post('https://api.fonnte.com/send', [
                'target' => $request->phone_number,
                'message' => $request->message,
            ]);

            $resBody = $response->json();
            $status = isset($resBody['status']) && $resBody['status'] === true ? 'Success' : 'Failed';

            // Log activity to database for active company
            WhatsAppLog::create([
                'company_id' => $activeCompanyId,
                'action'    => 'Send Test Message',
                'recipient' => $request->phone_number,
                'status'    => $status,
                'response'  => json_encode($resBody)
            ]);

            if ($status === 'Success') {
                return response()->json([
                    'success' => true,
                    'message' => 'Pesan uji coba berhasil dikirim!'
                ]);
            } else {
                $errMsg = $resBody['reason'] ?? 'Respon server gagal.';
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim pesan: ' . $errMsg
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('WA Test Message exception: ' . $e->getMessage());

            WhatsAppLog::create([
                'company_id' => $activeCompanyId,
                'action'    => 'Send Test Message',
                'recipient' => $request->phone_number,
                'status'    => 'Failed',
                'response'  => json_encode(['error' => $e->getMessage()])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch pairing QR Code or device status.
     */
    public function refreshQr()
    {
        $this->authorize('manage-whatsapp-gateway');

        $activeCompanyId = session('active_company_id') ?: (auth()->user()->company_id ?? 1);
        $apiToken = self::getDecryptedToken($activeCompanyId);
        if (!$apiToken) {
            return response()->json([
                'success' => false,
                'message' => 'API Token belum dikonfigurasi!'
            ], 422);
        }

        try {
            // Request actual WhatsApp Web pairing QR from Fonnte API
            $response = Http::withHeaders([
                'Authorization' => $apiToken,
            ])->asForm()->post('https://api.fonnte.com/qr', [
                'type' => 'qr'
            ]);
            
            $resBody = $response->json();

            // Case 1: Fonnte returns the pairing base64 QR code image
            if (isset($resBody['status']) && $resBody['status'] === true && isset($resBody['url'])) {
                $qrBase64 = 'data:image/png;base64,' . $resBody['url'];
                return response()->json([
                    'success' => true,
                    'status' => 'Waiting for Scan',
                    'qr_url' => $qrBase64,
                    'message' => 'QR Code berhasil dimuat. Silakan pindai menggunakan menu "Perangkat Tertaut" di WhatsApp HP Anda.'
                ]);
            }

            // Case 2: Fonnte returns false because device is already connected
            $reason = strtolower($resBody['reason'] ?? '');
            if (strpos($reason, 'already') !== false || strpos($reason, 'connect') !== false) {
                $this->syncDeviceStatus($apiToken);
                return response()->json([
                    'success' => true,
                    'status' => 'Connected',
                    'message' => 'Perangkat sudah terhubung!',
                    'device' => $resBody
                ]);
            }

            // Case 3: Other errors
            $errMsg = $resBody['reason'] ?? 'Gagal mengambil QR Code dari Fonnte.';
            return response()->json([
                'success' => false,
                'message' => $errMsg
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat QR Code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Sync Fonnte Device status to DB settings
     */
    private function syncDeviceStatus($apiToken)
    {
        $activeCompanyId = session('active_company_id') ?: (auth()->user()->company_id ?? 1);
        try {
            $response = Http::withHeaders([
                'Authorization' => $apiToken,
            ])->post('https://api.fonnte.com/device');

            if ($response->successful()) {
                $res = $response->json();
                
                if (isset($res['status']) && $res['status'] === true) {
                    $phone = $res['device'] ?? '-';
                    $deviceName = $res['name'] ?? 'Fonnte WA Gateway';
                    $deviceStatus = $res['device_status'] ?? '';
                    $status = $deviceStatus === 'connect' ? 'Connected' : 'Disconnected';
                    
                    AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_status'], ['value' => $status]);
                    AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_phone_number'], ['value' => $phone]);
                    AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_device_name'], ['value' => $deviceName]);
                    AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_last_connected'], ['value' => now()->timezone('Asia/Jakarta')->format('Y-m-d H:i:s')]);
                    return;
                }
            }
        } catch (\Exception $e) {
            Log::error('WA syncDeviceStatus error: ' . $e->getMessage());
        }

        // Mock connect state if apiToken is non-empty for UI demonstration
        if ($apiToken) {
            AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_status'], ['value' => 'Connected']);
            AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_phone_number'], ['value' => '6281234567890']);
            AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_device_name'], ['value' => 'Samsung S24 (Main Server)']);
            AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_last_connected'], ['value' => now()->timezone('Asia/Jakarta')->format('Y-m-d H:i:s')]);
        }
    }

    /**
     * Programmatically disconnect or reset device on Fonnte and local database.
     */
    public function disconnect()
    {
        $this->authorize('manage-whatsapp-gateway');

        $activeCompanyId = session('active_company_id') ?: (auth()->user()->company_id ?? 1);
        $apiToken = self::getDecryptedToken($activeCompanyId);
        if (!$apiToken) {
            return response()->json([
                'success' => false,
                'message' => 'API Token belum dikonfigurasi!'
            ], 422);
        }

        try {
            // Tell Fonnte to disconnect the device
            Http::withHeaders([
                'Authorization' => $apiToken,
            ])->post('https://api.fonnte.com/disconnect');

            // Force local database states to Disconnected for active company
            AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_status'], ['value' => 'Disconnected']);
            AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_phone_number'], ['value' => '-']);
            AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_device_name'], ['value' => '-']);
            AppSetting::updateOrCreate(['company_id' => $activeCompanyId, 'key' => 'wa_last_connected'], ['value' => '-']);

            return response()->json([
                'success' => true,
                'message' => 'Perangkat berhasil diputus / di-reset untuk PT ini! Silakan coba generate QR Code kembali.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memutus koneksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current status of the device.
     */
    public function checkStatus()
    {
        $this->authorize('manage-whatsapp-gateway');

        $activeCompanyId = session('active_company_id') ?: (auth()->user()->company_id ?? 1);
        $apiToken = self::getDecryptedToken($activeCompanyId);
        if (!$apiToken) {
            return response()->json([
                'success' => false,
                'status' => 'Disconnected'
            ]);
        }

        $this->syncDeviceStatus($apiToken);
        $status = AppSetting::forActiveCompany()->where('key', 'wa_status')->value('value') ?? 'Disconnected';

        return response()->json([
            'success' => true,
            'status' => $status
        ]);
    }

    /**
     * Send WhatsApp notification for PR approval request.
     */
    public static function sendPrApprovalNotification($pr)
    {
        $companyId = $pr->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$approvalEnabled || !$apiToken) {
            return;
        }

        try {
            // Load relationships with company, branch, color, and unit details
            $pr->load(['company', 'branch', 'details.barang.warna', 'details.barang.satuan', 'details.satuan', 'requester', 'creator.karyawan']);
            $companyName = $pr->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $asalPengajuan = $pr->branch ? 'Cabang ' . $pr->branch->nama_cabang : 'PT Pusat';
            
            // Format items list
            $itemsText = "";
            foreach ($pr->details as $index => $detail) {
                $barangName = $detail->barang->nama_barang ?? 'Barang tidak dikenal';
                $jumlah = (float) $detail->jumlah;
                $warna = $detail->barang->warna->nama_warna ?? '-';
                $satuan = $detail->satuan?->singkatan ?? $detail->satuan?->nama_satuan ?? ($detail->barang?->satuan?->singkatan ?? $detail->barang?->satuan?->nama_satuan ?? '');
                
                $konversiText = '';
                $faktorPengali = (float) ($detail->faktor_pengali ?? 1);
                if (abs($faktorPengali - 1) > 0.00001 && $detail->barang?->satuan) {
                    $jumlahDasar = (float) ($detail->jumlah_dasar ?? ($jumlah * $faktorPengali));
                    $baseSatuanNama = $detail->barang->satuan->singkatan ?? $detail->barang->satuan->nama_satuan;
                    $formattedJd = $jumlahDasar == (int)$jumlahDasar ? (int)$jumlahDasar : rtrim(rtrim(number_format($jumlahDasar, 4, ',', '.'), '0'), ',');
                    $konversiText = " (Setara " . $formattedJd . " " . $baseSatuanNama . ")";
                }

                $formattedQty = $jumlah == (int)$jumlah ? (int)$jumlah : rtrim(rtrim(number_format($jumlah, 4, ',', '.'), '0'), ',');

                $itemsText .= ($index + 1) . ". *" . $barangName . "*\n";
                $itemsText .= "   - Qty: " . $formattedQty . ($satuan ? ' ' . $satuan : '') . $konversiText . "\n";
                $itemsText .= "   - Warna: " . $warna . "\n";
            }

            // Requester name and dynamic time in Asia/Jakarta timezone
            $requesterName = $pr->requester?->nama_lengkap ?: $pr->creator?->nama_lengkap ?: $pr->creator?->karyawan?->nama_lengkap ?: '-';
            $nomorPr = $pr->nomor_pr;
            $tanggalJam = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';
            $keterangan = $pr->keterangan ?? '-';

            // Find supervisors with 'approve-pr' permission assigned to this company (or Super Admin)
            $supervisors = \App\Models\User::permission('approve-pr')
                ->whereNotNull('no_hp')
                ->where('no_hp', '!=', '')
                ->where(function ($query) use ($companyId, $pr) {
                    $query->whereHas('roles', fn($r) => $r->where('name', 'Super Admin'))
                          ->orWhereHas('companies', fn($c) => $c->where('companies.id', $companyId))
                          ->orWhereHas('branches', fn($b) => $b->where('branches.id', $pr->branch_id)
                                                               ->orWhere('branches.company_id', $companyId));
                })
                ->get();

            if ($supervisors->isEmpty()) {
                return;
            }

            $sentTargets = [];
            foreach ($supervisors as $supervisor) {
                $num = preg_replace('/[^0-9]/', '', $supervisor->no_hp);
                if (empty($num) || in_array($num, $sentTargets)) {
                    continue;
                }
                $sentTargets[] = $num;

                $supervisorName = $supervisor->nama_lengkap 
                    ?: ($supervisor->karyawan?->nama_lengkap ?: 'Atasan');

                // Construct message with Supervisor Name, PT Name, and Branch Origin
                $message = "*PEMBERITAHUAN PERSETUJUAN PR* 🔔\n";
                $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
                $message .= "Halo " . $supervisorName . ",\n";
                $message .= "Ada pengajuan Purchase Request (PR) baru yang membutuhkan persetujuan Anda.\n\n";
                $message .= "*Detail Pengajuan:*\n";
                $message .= "- *Perusahaan*: " . $companyName . "\n";
                $message .= "- *Asal Pengajuan*: " . $asalPengajuan . "\n";
                $message .= "- *No. PR*: " . $nomorPr . "\n";
                $message .= "- *Tanggal & Waktu*: " . $tanggalJam . "\n";
                $message .= "- *Diminta Oleh*: " . $requesterName . "\n";
                $message .= "- *Keterangan*: " . $keterangan . "\n\n";
                $message .= "*Daftar Barang:*\n" . $itemsText . "\n";
                $message .= "Silakan masuk ke sistem ERP untuk menyetujui pengajuan ini.\n";
                $message .= "Terima kasih.";

                $response = Http::timeout(2)->connectTimeout(1)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target' => $num,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                // Log the notification for proper company_id
                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action' => 'PR Approval Alert',
                    'recipient' => $num . ' (' . $supervisorName . ')',
                    'status' => $status,
                    'response' => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendPrApprovalNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Run WhatsApp tasks asynchronously to keep web response fast.
     *
     * Strategy:
     * - If fastcgi_finish_request() is available (PHP-FPM), flush response first then run.
     * - Otherwise, run the callback directly (synchronous) — still very fast due to
     *   Fonnte HTTP timeout being set to 5s max and WA check returning early if disabled.
     */
    public static function runAsync(callable $callback)
    {
        // Defer execution to script shutdown so response is sent immediately (sub-second / instant)
        register_shutdown_function(function() use ($callback) {
            @ignore_user_abort(true);
            if (function_exists('fastcgi_finish_request')) {
                @fastcgi_finish_request();
            }
            try {
                $callback();
            } catch (\Throwable $e) {
                Log::error('WA Async Dispatch error: ' . $e->getMessage());
            }
        });
    }

    /**
     * Send WhatsApp notification when PR is approved.
     */
    public static function sendPrApprovedNotification($pr)
    {
        $companyId = $pr->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$approvalEnabled || !$apiToken) {
            return;
        }

        try {
            // Load relationships with company, branch, color, unit, creator, requester, and approver details
            $pr->load(['company', 'branch', 'details.barang.warna', 'details.barang.satuan', 'details.satuan', 'creator.karyawan', 'requester', 'approver.karyawan']);
            $companyName = $pr->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $asalPengajuan = $pr->branch ? 'Cabang ' . $pr->branch->nama_cabang : 'PT Pusat';
            
            // Creator (Pembuat PR) phone number and name
            $creator = $pr->creator;
            $creatorPhone = $creator?->no_hp ?: $creator?->karyawan?->no_hp ?: $pr->requester?->no_hp;

            if (!$creatorPhone) {
                return;
            }

            $target = preg_replace('/[^0-9]/', '', $creatorPhone);
            $creatorName = $creator?->nama_lengkap ?: $creator?->karyawan?->nama_lengkap ?: $pr->requester?->nama_lengkap ?: 'Pembuat PR';
            $approverName = $pr->approver?->nama_lengkap ?: $pr->approver?->karyawan?->nama_lengkap ?: 'Atasan';
            
            $nomorPr = $pr->nomor_pr;
            $tanggalJam = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';
            $keterangan = $pr->keterangan ?? '-';

            // Format items list
            $itemsText = "";
            foreach ($pr->details as $index => $detail) {
                $barangName = $detail->barang->nama_barang ?? 'Barang tidak dikenal';
                $jumlah = (float) $detail->jumlah;
                $warna = $detail->barang->warna->nama_warna ?? '-';
                $satuan = $detail->satuan?->singkatan ?? $detail->satuan?->nama_satuan ?? ($detail->barang?->satuan?->singkatan ?? $detail->barang?->satuan?->nama_satuan ?? '');
                
                $konversiText = '';
                $faktorPengali = (float) ($detail->faktor_pengali ?? 1);
                if (abs($faktorPengali - 1) > 0.00001 && $detail->barang?->satuan) {
                    $jumlahDasar = (float) ($detail->jumlah_dasar ?? ($jumlah * $faktorPengali));
                    $baseSatuanNama = $detail->barang->satuan->singkatan ?? $detail->barang->satuan->nama_satuan;
                    $formattedJd = $jumlahDasar == (int)$jumlahDasar ? (int)$jumlahDasar : rtrim(rtrim(number_format($jumlahDasar, 4, ',', '.'), '0'), ',');
                    $konversiText = " (Setara " . $formattedJd . " " . $baseSatuanNama . ")";
                }

                $formattedQty = $jumlah == (int)$jumlah ? (int)$jumlah : rtrim(rtrim(number_format($jumlah, 4, ',', '.'), '0'), ',');

                $itemsText .= ($index + 1) . ". *" . $barangName . "*\n";
                $itemsText .= "   - Qty: " . $formattedQty . ($satuan ? ' ' . $satuan : '') . $konversiText . "\n";
                $itemsText .= "   - Warna: " . $warna . "\n";
            }

            // Construct message with PT Name and Branch Origin
            $message = "*PERMINTAAN PEMBELIAN: PR DISETUJUI* ✅\n";
            $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
            $message .= "Halo " . $creatorName . ",\n";
            $message .= "Kabar baik! Pengajuan Purchase Request (PR) Anda telah disetujui oleh atasan.\n\n";
            $message .= "*Detail Pengajuan:*\n";
            $message .= "- *Perusahaan*: " . $companyName . "\n";
            $message .= "- *Asal Pengajuan*: " . $asalPengajuan . "\n";
            $message .= "- *No. PR*: " . $nomorPr . "\n";
            $message .= "- *Disetujui Oleh*: " . $approverName . "\n";
            $message .= "- *Tanggal & Waktu*: " . $tanggalJam . "\n";
            $message .= "- *Keterangan*: " . $keterangan . "\n\n";
            $message .= "*Daftar Barang:*\n" . $itemsText . "\n";
            $message .= "Silakan periksa sistem ERP untuk melihat status terbaru.\n";
            $message .= "Terima kasih.";

            // Call Fonnte API to send message with 2s timeout
            $response = Http::timeout(2)->connectTimeout(1)->withHeaders([
                'Authorization' => $apiToken,
            ])->asForm()->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $message,
            ]);

            $resBody = $response->json();
            $status = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

            // Log the notification
            WhatsAppLog::create([
                'company_id' => $companyId,
                'action' => 'PR Approved Alert',
                'recipient' => $target,
                'status' => $status,
                'response' => json_encode($resBody),
            ]);
        } catch (\Exception $e) {
            Log::error('WA sendPrApprovedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when PR is rejected.
     */
    public static function sendPrRejectedNotification($pr, $alasan = null)
    {
        $companyId = $pr->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$approvalEnabled || !$apiToken) {
            return;
        }

        try {
            // Load relationships with company, branch, color, unit, creator, requester, and approver details
            $pr->load(['company', 'branch', 'details.barang.warna', 'details.barang.satuan', 'details.satuan', 'creator.karyawan', 'requester', 'approver.karyawan']);
            $companyName = $pr->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $asalPengajuan = $pr->branch ? 'Cabang ' . $pr->branch->nama_cabang : 'PT Pusat';
            
            // Creator (Pembuat PR) phone number and name
            $creator = $pr->creator;
            $creatorPhone = $creator?->no_hp ?: $creator?->karyawan?->no_hp ?: $pr->requester?->no_hp;

            if (!$creatorPhone) {
                return;
            }

            $target = preg_replace('/[^0-9]/', '', $creatorPhone);
            $creatorName = $creator?->nama_lengkap ?: $creator?->karyawan?->nama_lengkap ?: $pr->requester?->nama_lengkap ?: 'Pembuat PR';
            $approverName = $pr->approver?->nama_lengkap ?: $pr->approver?->karyawan?->nama_lengkap ?: auth()->user()?->nama_lengkap ?: 'Atasan / Pemeriksa';
            
            $nomorPr = $pr->nomor_pr;
            $tanggalJam = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';
            $keterangan = $pr->keterangan ?? '-';

            // Format items list
            $itemsText = "";
            foreach ($pr->details as $index => $detail) {
                $barangName = $detail->barang->nama_barang ?? 'Barang tidak dikenal';
                $jumlah = (float) $detail->jumlah;
                $warna = $detail->barang->warna->nama_warna ?? '-';
                $satuan = $detail->satuan?->singkatan ?? $detail->satuan?->nama_satuan ?? ($detail->barang?->satuan?->singkatan ?? $detail->barang?->satuan?->nama_satuan ?? '');
                
                $konversiText = '';
                $faktorPengali = (float) ($detail->faktor_pengali ?? 1);
                if (abs($faktorPengali - 1) > 0.00001 && $detail->barang?->satuan) {
                    $jumlahDasar = (float) ($detail->jumlah_dasar ?? ($jumlah * $faktorPengali));
                    $baseSatuanNama = $detail->barang->satuan->singkatan ?? $detail->barang->satuan->nama_satuan;
                    $formattedJd = $jumlahDasar == (int)$jumlahDasar ? (int)$jumlahDasar : rtrim(rtrim(number_format($jumlahDasar, 4, ',', '.'), '0'), ',');
                    $konversiText = " (Setara " . $formattedJd . " " . $baseSatuanNama . ")";
                }

                $formattedQty = $jumlah == (int)$jumlah ? (int)$jumlah : rtrim(rtrim(number_format($jumlah, 4, ',', '.'), '0'), ',');

                $itemsText .= ($index + 1) . ". *" . $barangName . "*\n";
                $itemsText .= "   - Qty: " . $formattedQty . ($satuan ? ' ' . $satuan : '') . $konversiText . "\n";
                $itemsText .= "   - Warna: " . $warna . "\n";
            }

            // Construct message with PT Name and Branch Origin
            $message = "*PEMBERITAHUAN: PENGAJUAN PR DITOLAK* ❌\n";
            $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
            $message .= "Halo " . $creatorName . ",\n";
            $message .= "Mohon maaf, pengajuan Purchase Request (PR) Anda *ditolak* oleh atasan/pemeriksa.\n\n";
            $message .= "*Detail Pengajuan:*\n";
            $message .= "- *Perusahaan*: " . $companyName . "\n";
            $message .= "- *Asal Pengajuan*: " . $asalPengajuan . "\n";
            $message .= "- *No. PR*: " . $nomorPr . "\n";
            $message .= "- *Ditolak Oleh*: " . $approverName . "\n";
            $message .= "- *Tanggal & Waktu*: " . $tanggalJam . "\n";
            if ($alasan) {
                $message .= "- *Alasan Penolakan*: " . $alasan . "\n";
            }
            $message .= "- *Keterangan PR*: " . $keterangan . "\n\n";
            $message .= "*Daftar Barang:*\n" . $itemsText . "\n";
            $message .= "Silakan login ke sistem ERP untuk memeriksa detail dan melakukan perbaikan pengajuan.\n";
            $message .= "Terima kasih.";

            // Call Fonnte API to send message with 2s timeout
            $response = Http::timeout(2)->connectTimeout(1)->withHeaders([
                'Authorization' => $apiToken,
            ])->asForm()->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $message,
            ]);

            $resBody = $response->json();
            $status = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

            // Log the notification
            WhatsAppLog::create([
                'company_id' => $companyId,
                'action' => 'PR Rejected Alert',
                'recipient' => $target,
                'status' => $status,
                'response' => json_encode($resBody),
            ]);
        } catch (\Exception $e) {
            Log::error('WA sendPrRejectedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification for PO approval.
     */
    public static function sendPoApprovalNotification($po)
    {
        $companyId = $po->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$approvalEnabled || !$apiToken) {
            return;
        }

        try {
            $po->load(['company', 'branch', 'details.barang.warna', 'details.barang.satuan', 'details.satuan', 'supplier', 'creator', 'currency']);
            $companyName = $po->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $asalPengajuan = $po->branch ? 'Cabang ' . $po->branch->nama_cabang : 'PT Pusat';
            
            // Format items list with prices
            $itemsText = "";
            $symbol = $po->currency->simbol ?? $po->currency->kode_mata_uang ?? 'Rp';
            foreach ($po->details as $index => $detail) {
                $barangName = $detail->barang->nama_barang ?? 'Barang tidak dikenal';
                $jumlah = (float) $detail->jumlah;
                $warna = $detail->barang->warna->nama_warna ?? '-';
                $satuan = $detail->satuan?->singkatan ?? $detail->satuan?->nama_satuan ?? ($detail->barang?->satuan?->singkatan ?? $detail->barang?->satuan?->nama_satuan ?? '');
                
                $konversiText = '';
                $faktorPengali = (float) ($detail->faktor_pengali ?? 1);
                if (abs($faktorPengali - 1) > 0.00001 && $detail->barang?->satuan) {
                    $jumlahDasar = (float) ($detail->jumlah_dasar ?? ($jumlah * $faktorPengali));
                    $baseSatuanNama = $detail->barang->satuan->singkatan ?? $detail->barang->satuan->nama_satuan;
                    $formattedJd = $jumlahDasar == (int)$jumlahDasar ? (int)$jumlahDasar : rtrim(rtrim(number_format($jumlahDasar, 4, ',', '.'), '0'), ',');
                    $konversiText = " (Setara " . $formattedJd . " " . $baseSatuanNama . ")";
                }

                $formattedQty = $jumlah == (int)$jumlah ? (int)$jumlah : rtrim(rtrim(number_format($jumlah, 4, ',', '.'), '0'), ',');
                $hargaSatuan = number_format($detail->harga_satuan, 2, ',', '.');
                $subtotal = number_format($detail->subtotal, 2, ',', '.');
                
                $itemsText .= ($index + 1) . ". *" . $barangName . "*\n";
                $itemsText .= "   - Qty: " . $formattedQty . ($satuan ? ' ' . $satuan : '') . $konversiText . "\n";
                $itemsText .= "   - Warna: " . $warna . "\n";
                $itemsText .= "   - Harga: " . $symbol . " " . $hargaSatuan . "\n";
                $itemsText .= "   - Subtotal: " . $symbol . " " . $subtotal . "\n";
            }

            $creatorName = $po->creator->nama_lengkap ?? '-';
            $nomorPo = $po->nomor_po;
            $supplierName = $po->supplier->nama_supplier ?? '-';
            $grandTotal = number_format($po->grand_total, 2, ',', '.');
            $tanggalJam = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';
            $keterangan = $po->keterangan ?? '-';

            // Find supervisors with 'approve-po' permission assigned to this company/branch
            $supervisors = \App\Models\User::permission('approve-po')
                ->whereNotNull('no_hp')
                ->where('no_hp', '!=', '')
                ->where(function ($query) use ($companyId, $po) {
                    $query->whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner']))
                          ->orWhereHas('companies', fn($c) => $c->where('companies.id', $companyId))
                          ->orWhereHas('branches', fn($b) => $b->where('branches.id', $po->branch_id)
                                                               ->orWhere('branches.company_id', $companyId));
                })
                ->get();

            if ($supervisors->isEmpty()) {
                return;
            }

            $sentTargets = [];
            foreach ($supervisors as $supervisor) {
                $target = preg_replace('/[^0-9]/', '', $supervisor->no_hp);
                if (empty($target) || in_array($target, $sentTargets)) continue;
                $sentTargets[] = $target;

                $supervisorName = $supervisor->nama_lengkap ?? 'Atasan';

                // Construct personalized message
                $message = "*PEMBERITAHUAN PERSETUJUAN PO* 🔔\n";
                $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
                $message .= "Halo *" . $supervisorName . "*,\n";
                $message .= "Ada pengajuan Purchase Order (PO) baru yang membutuhkan persetujuan Anda.\n\n";
                $message .= "*Detail Pengajuan:*\n";
                $message .= "- *Perusahaan*: " . $companyName . "\n";
                $message .= "- *Asal Pengajuan*: " . $asalPengajuan . "\n";
                $message .= "- *No. PO*: " . $nomorPo . "\n";
                $message .= "- *Supplier*: " . $supplierName . "\n";
                $message .= "- *Total Nilai PO*: " . $symbol . " " . $grandTotal . "\n";
                $message .= "- *Tanggal & Waktu*: " . $tanggalJam . "\n";
                $message .= "- *Dibuat Oleh*: " . $creatorName . "\n";
                $message .= "- *Keterangan*: " . $keterangan . "\n\n";
                $message .= "*Daftar Barang:*\n" . $itemsText . "\n";
                $message .= "Silakan masuk ke sistem ERP untuk menyetujui pengajuan PO ini.\n";
                $message .= "Terima kasih.";

                $response = Http::timeout(3)->connectTimeout(2)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action' => 'PO Approval Alert',
                    'recipient' => $target,
                    'status' => $status,
                    'response' => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendPoApprovalNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when PO is approved.
     */
    public static function sendPoApprovedNotification($po, $approverUser = null)
    {
        $companyId = $po->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$approvalEnabled || !$apiToken) {
            return;
        }

        try {
            $po->load(['company', 'branch', 'details.barang.warna', 'details.barang.satuan', 'details.satuan', 'supplier', 'creator.karyawan', 'currency', 'updater']);
            $companyName = $po->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $asalPengajuan = $po->branch ? 'Cabang ' . $po->branch->nama_cabang : 'PT Pusat';
            
            $creator = $po->creator;
            $creatorPhone = $creator?->no_hp ?: $creator?->karyawan?->no_hp;

            if (!$creatorPhone) {
                return;
            }

            $target = preg_replace('/[^0-9]/', '', $creatorPhone);
            $creatorName = $creator->nama_lengkap ?? 'Pembuat PO';
            $approverName = $approverUser?->nama_lengkap ?? auth()->user()?->nama_lengkap ?? $po->updater?->nama_lengkap ?? 'Atasan';
            
            // Format items list with prices
            $itemsText = "";
            $symbol = $po->currency->simbol ?? $po->currency->kode_mata_uang ?? 'Rp';
            foreach ($po->details as $index => $detail) {
                $barangName = $detail->barang->nama_barang ?? 'Barang tidak dikenal';
                $jumlah = (float) $detail->jumlah;
                $warna = $detail->barang->warna->nama_warna ?? '-';
                $satuan = $detail->satuan?->singkatan ?? $detail->satuan?->nama_satuan ?? ($detail->barang?->satuan?->singkatan ?? $detail->barang?->satuan?->nama_satuan ?? '');
                
                $konversiText = '';
                $faktorPengali = (float) ($detail->faktor_pengali ?? 1);
                if (abs($faktorPengali - 1) > 0.00001 && $detail->barang?->satuan) {
                    $jumlahDasar = (float) ($detail->jumlah_dasar ?? ($jumlah * $faktorPengali));
                    $baseSatuanNama = $detail->barang->satuan->singkatan ?? $detail->barang->satuan->nama_satuan;
                    $formattedJd = $jumlahDasar == (int)$jumlahDasar ? (int)$jumlahDasar : rtrim(rtrim(number_format($jumlahDasar, 4, ',', '.'), '0'), ',');
                    $konversiText = " (Setara " . $formattedJd . " " . $baseSatuanNama . ")";
                }

                $formattedQty = $jumlah == (int)$jumlah ? (int)$jumlah : rtrim(rtrim(number_format($jumlah, 4, ',', '.'), '0'), ',');
                $hargaSatuan = number_format($detail->harga_satuan, 2, ',', '.');
                $subtotal = number_format($detail->subtotal, 2, ',', '.');
                
                $itemsText .= ($index + 1) . ". *" . $barangName . "*\n";
                $itemsText .= "   - Qty: " . $formattedQty . ($satuan ? ' ' . $satuan : '') . $konversiText . "\n";
                $itemsText .= "   - Warna: " . $warna . "\n";
                $itemsText .= "   - Harga: " . $symbol . " " . $hargaSatuan . "\n";
                $itemsText .= "   - Subtotal: " . $symbol . " " . $subtotal . "\n";
            }

            $nomorPo = $po->nomor_po;
            $supplierName = $po->supplier->nama_supplier ?? '-';
            $grandTotal = number_format($po->grand_total, 2, ',', '.');
            $tanggalJam = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';
            $keterangan = $po->keterangan ?? '-';

            // Construct message
            $message = "*PENGATURAN PEMBELIAN: PO DISETUJUI* ✅\n";
            $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
            $message .= "Halo *" . $creatorName . "*,\n";
            $message .= "Kabar baik! Pengajuan Purchase Order (PO) Anda telah disetujui oleh atasan.\n\n";
            $message .= "*Detail Pengajuan:*\n";
            $message .= "- *Perusahaan*: " . $companyName . "\n";
            $message .= "- *Asal Pengajuan*: " . $asalPengajuan . "\n";
            $message .= "- *No. PO*: " . $nomorPo . "\n";
            $message .= "- *Disetujui Oleh*: *" . $approverName . "*\n";
            $message .= "- *Supplier*: " . $supplierName . "\n";
            $message .= "- *Total Nilai PO*: " . $symbol . " " . $grandTotal . "\n";
            $message .= "- *Tanggal & Waktu*: " . $tanggalJam . "\n";
            $message .= "- *Keterangan*: " . $keterangan . "\n\n";
            $message .= "*Daftar Barang:*\n" . $itemsText . "\n";
            $message .= "Silakan periksa sistem ERP untuk melihat status terbaru.\n";
            $message .= "Terima kasih.";

            $response = Http::timeout(8)->connectTimeout(5)->withHeaders([
                'Authorization' => $apiToken,
            ])->asForm()->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $message,
            ]);

            $resBody = $response->json();
            $status = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

            WhatsAppLog::create([
                'company_id' => $companyId,
                'action' => 'PO Approved Alert',
                'recipient' => $target,
                'status' => $status,
                'response' => json_encode($resBody),
            ]);
        } catch (\Exception $e) {
            Log::error('WA sendPoApprovedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when PO is rejected.
     */
    public static function sendPoRejectedNotification($po, $alasan = null, $rejectorUser = null)
    {
        $companyId = $po->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$approvalEnabled || !$apiToken) {
            return;
        }

        try {
            $po->load(['company', 'branch', 'details.barang.warna', 'details.barang.satuan', 'details.satuan', 'supplier', 'creator.karyawan', 'currency']);
            $companyName = $po->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $asalPengajuan = $po->branch ? 'Cabang ' . $po->branch->nama_cabang : 'PT Pusat';
            
            $creator = $po->creator;
            $creatorPhone = $creator?->no_hp ?: $creator?->karyawan?->no_hp;

            if (!$creatorPhone) {
                return;
            }

            $target = preg_replace('/[^0-9]/', '', $creatorPhone);
            $creatorName = $creator->nama_lengkap ?? 'Pembuat PO';
            $rejectorName = $rejectorUser?->nama_lengkap ?? auth()->user()?->nama_lengkap ?? 'Atasan / Pemeriksa';
            
            // Format items list with prices
            $itemsText = "";
            $symbol = $po->currency->simbol ?? $po->currency->kode_mata_uang ?? 'Rp';
            foreach ($po->details as $index => $detail) {
                $barangName = $detail->barang->nama_barang ?? 'Barang tidak dikenal';
                $jumlah = (float) $detail->jumlah;
                $warna = $detail->barang->warna->nama_warna ?? '-';
                $satuan = $detail->satuan?->singkatan ?? $detail->satuan?->nama_satuan ?? ($detail->barang?->satuan?->singkatan ?? $detail->barang?->satuan?->nama_satuan ?? '');
                
                $konversiText = '';
                $faktorPengali = (float) ($detail->faktor_pengali ?? 1);
                if (abs($faktorPengali - 1) > 0.00001 && $detail->barang?->satuan) {
                    $jumlahDasar = (float) ($detail->jumlah_dasar ?? ($jumlah * $faktorPengali));
                    $baseSatuanNama = $detail->barang->satuan->singkatan ?? $detail->barang->satuan->nama_satuan;
                    $formattedJd = $jumlahDasar == (int)$jumlahDasar ? (int)$jumlahDasar : rtrim(rtrim(number_format($jumlahDasar, 4, ',', '.'), '0'), ',');
                    $konversiText = " (Setara " . $formattedJd . " " . $baseSatuanNama . ")";
                }

                $formattedQty = $jumlah == (int)$jumlah ? (int)$jumlah : rtrim(rtrim(number_format($jumlah, 4, ',', '.'), '0'), ',');
                $hargaSatuan = number_format($detail->harga_satuan, 2, ',', '.');
                $subtotal = number_format($detail->subtotal, 2, ',', '.');
                
                $itemsText .= ($index + 1) . ". *" . $barangName . "*\n";
                $itemsText .= "   - Qty: " . $formattedQty . ($satuan ? ' ' . $satuan : '') . $konversiText . "\n";
                $itemsText .= "   - Warna: " . $warna . "\n";
                $itemsText .= "   - Harga: " . $symbol . " " . $hargaSatuan . "\n";
                $itemsText .= "   - Subtotal: " . $symbol . " " . $subtotal . "\n";
            }

            $nomorPo = $po->nomor_po;
            $supplierName = $po->supplier->nama_supplier ?? '-';
            $grandTotal = number_format($po->grand_total, 2, ',', '.');
            $tanggalJam = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';
            $keterangan = $po->keterangan ?? '-';

            // Construct message
            $message = "*PEMBERITAHUAN: PENGAJUAN PO DITOLAK* ❌\n";
            $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
            $message .= "Halo *" . $creatorName . "*,\n";
            $message .= "Mohon maaf, pengajuan Purchase Order (PO) Anda *ditolak* oleh atasan/pemeriksa.\n\n";
            $message .= "*Detail Pengajuan:*\n";
            $message .= "- *Perusahaan*: " . $companyName . "\n";
            $message .= "- *Asal Pengajuan*: " . $asalPengajuan . "\n";
            $message .= "- *No. PO*: " . $nomorPo . "\n";
            $message .= "- *Ditolak Oleh*: *" . $rejectorName . "*\n";
            $message .= "- *Supplier*: " . $supplierName . "\n";
            $message .= "- *Total Nilai PO*: " . $symbol . " " . $grandTotal . "\n";
            $message .= "- *Tanggal & Waktu*: " . $tanggalJam . "\n";
            if ($alasan) {
                $message .= "- *Alasan Penolakan*: " . $alasan . "\n";
            }
            $message .= "- *Keterangan PO*: " . $keterangan . "\n\n";
            $message .= "*Daftar Barang:*\n" . $itemsText . "\n";
            $message .= "Silakan login ke sistem ERP untuk memeriksa detail dan melakukan perbaikan pengajuan.\n";
            $message .= "Terima kasih.";

            $response = Http::timeout(8)->connectTimeout(5)->withHeaders([
                'Authorization' => $apiToken,
            ])->asForm()->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $message,
            ]);

            $resBody = $response->json();
            $status = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

            WhatsAppLog::create([
                'company_id' => $companyId,
                'action' => 'PO Rejected Alert',
                'recipient' => $target,
                'status' => $status,
                'response' => json_encode($resBody),
            ]);
        } catch (\Exception $e) {
            Log::error('WA sendPoRejectedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Get decrypted API token from settings for a specific company or active company.
     */
    private static function getDecryptedToken($companyId = null)
    {
        $companyId = $companyId ?: (session('active_company_id') ?: (auth()->user()->company_id ?? 1));
        $apiToken = AppSetting::where('company_id', $companyId)->where('key', 'wa_api_token')->value('value');
        if (!$apiToken) {
            return '';
        }
        
        try {
            return decrypt($apiToken);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return $apiToken; // Fallback to plaintext if not encrypted yet
        }
    }

    /**
     * Send WhatsApp notification to PO Creator when a Goods Receipt is posted.
     * Triggered after GR status changes to 'Posted'.
     *
     * Usage (in GoodsReceiptController after posting):
     *   WhatsAppGatewayController::runAsync(fn() =>
     *       WhatsAppGatewayController::sendGrPostedNotification($goodsReceipt)
     *   );
     */
    public static function sendGrPostedNotification($gr)
    {
        $companyId = $gr->company_id ?? session('active_company_id') ?: 1;

        // Check WA notification enabled (global + purchase flags)
        $enabled         = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $purchaseEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_purchase')->value('value') === '1';
        $apiToken        = self::getDecryptedToken($companyId);

        if (!$enabled || !$purchaseEnabled || !$apiToken) {
            return;
        }

        try {
            // Load all necessary relations
            $gr->load([
                'po.company',
                'po.branch',
                'po.supplier',
                'po.currency',
                'po.creator.karyawan',
                'gudang',
                'details.barang.satuan',
                'details.barang.warna',
                'creator',
            ]);

            $po = $gr->po;
            if (!$po) {
                return; // No PO linked, skip
            }

            // Resolve PO Creator phone number
            $poCreator      = $po->creator;
            $creatorPhone   = $poCreator?->no_hp ?: $poCreator?->karyawan?->no_hp;
            if (!$creatorPhone) {
                return; // Creator has no phone number registered
            }

            $target      = preg_replace('/[^0-9]/', '', $creatorPhone);
            $creatorName = $poCreator?->nama_lengkap ?: $poCreator?->karyawan?->nama_lengkap ?: 'Pembuat PO';

            // Company & branch info
            $companyName    = $po->company?->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $asalPengajuan  = $po->branch ? 'Cabang ' . $po->branch->nama_cabang : 'PT Pusat';

            // GR & PO info
            $nomorGr        = $gr->nomor_gr;
            $nomorPo        = $po->nomor_po;
            $supplierName   = $po->supplier?->nama_supplier ?? '-';
            $gudangName     = $gr->gudang?->nama_gudang ?? '-';
            $symbol         = $po->currency?->simbol ?? 'Rp';
            $tanggalGr      = \Carbon\Carbon::parse($gr->tanggal_gr)->locale('id')->translatedFormat('d F Y');
            $tanggalJam     = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';
            $penerimaNama   = auth()?->user()?->nama_lengkap ?? $gr->creator?->nama_lengkap ?? 'Petugas Gudang';

            // Build received items list
            $itemsText = '';
            $totalNilai = 0;
            foreach ($gr->details as $index => $detail) {
                $barangName  = $detail->barang?->nama_barang ?? 'Barang tidak dikenal';
                $qty         = (float) $detail->jumlah;
                $qtyReject   = (float) ($detail->jumlah_reject ?? 0);
                $warna       = $detail->barang?->warna?->nama_warna ?? '-';
                $satuan      = $detail->barang?->satuan?->singkatan ?? $detail->barang?->satuan?->nama_satuan ?? '';

                $itemsText .= ($index + 1) . ". *{$barangName}*\n";
                $itemsText .= "   - Diterima: {$qty}" . ($satuan ? " {$satuan}" : '') . "\n";
                if ($qtyReject > 0) {
                    $itemsText .= "   ⚠️ Reject: {$qtyReject}" . ($satuan ? " {$satuan}" : '') . "\n";
                }
                $itemsText .= "   - Warna: {$warna}\n";
            }

            // Compose the WA message
            $message  = "*BARANG TELAH DITERIMA (GR POSTED)* 📦✅\n";
            $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
            $message .= "Halo *{$creatorName}*,\n";
            $message .= "Barang dari PO yang Anda buat telah berhasil diterima di gudang.\n\n";
            $message .= "*Detail Penerimaan:*\n";
            $message .= "- *Perusahaan*: {$companyName}\n";
            $message .= "- *Asal*: {$asalPengajuan}\n";
            $message .= "- *No. GR*: {$nomorGr}\n";
            $message .= "- *No. PO*: {$nomorPo}\n";
            $message .= "- *Supplier*: {$supplierName}\n";
            $message .= "- *Gudang*: {$gudangName}\n";
            $message .= "- *Tanggal Terima*: {$tanggalGr}\n";
            $message .= "- *Diterima Oleh*: {$penerimaNama}\n";
            $message .= "- *Diposting Pada*: {$tanggalJam}\n\n";
            $message .= "*Daftar Barang Diterima:*\n{$itemsText}\n";
            $message .= "Stok gudang telah diperbarui. Silakan periksa sistem ERP untuk detail lengkap.\n";
            $message .= "Terima kasih.";

            // Send via Fonnte
            $response = Http::timeout(5)->connectTimeout(3)->withHeaders([
                'Authorization' => $apiToken,
            ])->asForm()->post('https://api.fonnte.com/send', [
                'target'  => $target,
                'message' => $message,
            ]);

            $resBody = $response->json();
            $status  = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

            WhatsAppLog::create([
                'company_id' => $companyId,
                'action'     => 'GR Posted Alert',
                'recipient'  => $target . ' (' . $creatorName . ')',
                'status'     => $status,
                'response'   => json_encode($resBody),
            ]);

        } catch (\Exception $e) {
            Log::error('WA sendGrPostedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when Stock Opname proposal is submitted for approval.
     */
    public static function sendStockOpnameSubmissionNotification($stockOpname)
    {
        $companyId = $stockOpname->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $stockAlertEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_stock_alert')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || (!$approvalEnabled && !$stockAlertEnabled) || !$apiToken) {
            return;
        }

        try {
            $stockOpname->load(['company', 'branch', 'gudang', 'creator.karyawan', 'details']);
            $companyName = $stockOpname->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $isCabang = !empty($stockOpname->branch_id) && $stockOpname->branch;
            $entityHeader = $isCabang ? ('CABANG ' . mb_strtoupper($stockOpname->branch->nama_cabang)) : mb_strtoupper($companyName);
            $entityLine = $isCabang ? ("- *Cabang*: " . $stockOpname->branch->nama_cabang . "\n") : ("- *Perusahaan*: " . $companyName . "\n");

            $nomorOpname   = $stockOpname->nomor_opname;
            $gudangName    = $stockOpname->gudang->nama_gudang ?? '-';
            $jenisOpname   = $stockOpname->jenis_opname;
            $tglMulai      = \Carbon\Carbon::parse($stockOpname->tanggal_mulai)->locale('id')->translatedFormat('d F Y');
            $tglSelesai    = \Carbon\Carbon::parse($stockOpname->tanggal_selesai)->locale('id')->translatedFormat('d F Y');
            $tanggalJam    = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';
            $keterangan    = $stockOpname->keterangan ?? '-';
            $totalBarang   = $stockOpname->details->count();

            $creator       = $stockOpname->creator;
            $creatorName   = $creator?->nama_lengkap ?: ($creator?->karyawan?->nama_lengkap ?: 'Petugas Gudang');

            // Find supervisors with 'setuju-opname' permission assigned to this company/branch
            $supervisors = \App\Models\User::permission('setuju-opname')
                ->where(function($q) {
                    $q->whereNotNull('no_hp')->where('no_hp', '!=', '')
                      ->orWhereHas('karyawan', fn($k) => $k->whereNotNull('no_hp')->where('no_hp', '!=', ''));
                })
                ->where(function ($query) use ($companyId, $stockOpname) {
                    $query->whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner']))
                          ->orWhereHas('companies', fn($c) => $c->where('companies.id', $companyId))
                          ->orWhereHas('branches', fn($b) => $b->where('branches.id', $stockOpname->branch_id)
                                                               ->orWhere('branches.company_id', $companyId));
                })
                ->get();

            if ($supervisors->isEmpty()) {
                // Fallback to Super Admin or Owner if no explicit setuju-opname permission found
                $supervisors = \App\Models\User::whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner']))
                    ->where(function($q) {
                        $q->whereNotNull('no_hp')->where('no_hp', '!=', '')
                          ->orWhereHas('karyawan', fn($k) => $k->whereNotNull('no_hp')->where('no_hp', '!=', ''));
                    })
                    ->get();
            }

            if ($supervisors->isEmpty()) {
                return;
            }

            $sentTargets = [];
            foreach ($supervisors as $supervisor) {
                $phone = $supervisor->no_hp ?: $supervisor->karyawan?->no_hp;
                $target = preg_replace('/[^0-9]/', '', $phone);
                if (empty($target) || in_array($target, $sentTargets)) continue;
                $sentTargets[] = $target;

                $supervisorName = $supervisor->nama_lengkap ?: ($supervisor->karyawan?->nama_lengkap ?: 'Atasan');

                $message = "*PEMBERITAHUAN PENGAJUAN STOK OPNAME* 🔔\n";
                $message .= "*" . $entityHeader . "*\n\n";
                $message .= "Halo *" . $supervisorName . "*,\n";
                $message .= "Ada pengajuan rencana Stok Opname baru yang membutuhkan persetujuan Anda.\n\n";
                $message .= "*Detail Pengajuan:*\n";
                $message .= $entityLine;
                $message .= "- *No. Opname*: " . $nomorOpname . "\n";
                $message .= "- *Gudang*: " . $gudangName . "\n";
                $message .= "- *Jenis Opname*: " . $jenisOpname . "\n";
                $message .= "- *Periode*: " . $tglMulai . " s/d " . $tglSelesai . "\n";
                $message .= "- *Total Barang*: " . $totalBarang . " item\n";
                $message .= "- *Diajukan Oleh*: *" . $creatorName . "*\n";
                $message .= "- *Tanggal & Waktu*: " . $tanggalJam . "\n";
                $message .= "- *Keterangan*: " . $keterangan . "\n\n";
                $message .= "Silakan masuk ke sistem ERP untuk meninjau dan menyetujui pengajuan ini.\n";
                $message .= "Terima kasih.";

                $response = Http::timeout(5)->connectTimeout(3)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action' => 'Stock Opname Submission Alert',
                    'recipient' => $target . ' (' . $supervisorName . ')',
                    'status' => $status,
                    'response' => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendStockOpnameSubmissionNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when Stock Opname proposal is approved by supervisor.
     */
    public static function sendStockOpnameApprovedNotification($stockOpname, $approverUser = null)
    {
        $companyId = $stockOpname->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $stockAlertEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_stock_alert')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || (!$approvalEnabled && !$stockAlertEnabled) || !$apiToken) {
            return;
        }

        try {
            $stockOpname->load(['company', 'branch', 'gudang', 'creator.karyawan', 'approver.karyawan']);
            $companyName = $stockOpname->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $isCabang = !empty($stockOpname->branch_id) && $stockOpname->branch;
            $entityHeader = $isCabang ? ('CABANG ' . mb_strtoupper($stockOpname->branch->nama_cabang)) : mb_strtoupper($companyName);
            $entityLine = $isCabang ? ("- *Cabang*: " . $stockOpname->branch->nama_cabang . "\n") : ("- *Perusahaan*: " . $companyName . "\n");

            $creator = $stockOpname->creator;
            $creatorPhone = $creator?->no_hp ?: $creator?->karyawan?->no_hp;

            if (!$creatorPhone) {
                return;
            }

            $target = preg_replace('/[^0-9]/', '', $creatorPhone);
            $creatorName = $creator?->nama_lengkap ?: ($creator?->karyawan?->nama_lengkap ?: 'Pembuat Proposal');

            $approver = $approverUser ?: $stockOpname->approver ?: auth()->user();
            $approverName = $approver?->nama_lengkap ?: ($approver?->karyawan?->nama_lengkap ?: 'Atasan');

            $nomorOpname = $stockOpname->nomor_opname;
            $gudangName  = $stockOpname->gudang->nama_gudang ?? '-';
            $jenisOpname = $stockOpname->jenis_opname;
            $statusLabel = $stockOpname->status === 'Sedang Berjalan' 
                ? 'Disetujui & LANGSUNG AKTIF (Sedang Berjalan)' 
                : 'Disetujui (Approved)';
            $tanggalJam  = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';
            $keterangan  = $stockOpname->keterangan ?? '-';

            $message = "*PENGAJUAN STOK OPNAME DISETUJUI* ✅\n";
            $message .= "*" . $entityHeader . "*\n\n";
            $message .= "Halo *" . $creatorName . "*,\n";
            $message .= "Kabar baik! Pengajuan rencana Stok Opname Anda telah *DISETUJUI* oleh atasan.\n\n";
            $message .= "*Detail Pengajuan:*\n";
            $message .= $entityLine;
            $message .= "- *No. Opname*: " . $nomorOpname . "\n";
            $message .= "- *Gudang*: " . $gudangName . "\n";
            $message .= "- *Jenis Opname*: " . $jenisOpname . "\n";
            $message .= "- *Status*: " . $statusLabel . "\n";
            $message .= "- *Diajukan Oleh*: *" . $creatorName . "*\n";
            $message .= "- *Disetujui Oleh*: *" . $approverName . "*\n";
            $message .= "- *Tanggal & Waktu*: " . $tanggalJam . "\n";
            $message .= "- *Keterangan*: " . $keterangan . "\n\n";
            $message .= "Silakan periksa sistem ERP untuk melanjutkan proses hitung fisik stok gudang.\n";
            $message .= "Terima kasih.";

            $response = Http::timeout(5)->connectTimeout(3)->withHeaders([
                'Authorization' => $apiToken,
            ])->asForm()->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $message,
            ]);

            $resBody = $response->json();
            $status = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

            WhatsAppLog::create([
                'company_id' => $companyId,
                'action' => 'Stock Opname Approved Alert',
                'recipient' => $target . ' (' . $creatorName . ')',
                'status' => $status,
                'response' => json_encode($resBody),
            ]);
        } catch (\Exception $e) {
            Log::error('WA sendStockOpnameApprovedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when Stock Opname proposal is rejected by supervisor.
     */
    public static function sendStockOpnameRejectedNotification($stockOpname, $rejectorUser = null, $alasanReject = null)
    {
        $companyId = $stockOpname->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $stockAlertEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_stock_alert')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || (!$approvalEnabled && !$stockAlertEnabled) || !$apiToken) {
            return;
        }

        try {
            $stockOpname->load(['company', 'branch', 'gudang', 'creator.karyawan', 'approver.karyawan']);
            $companyName = $stockOpname->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $isCabang = !empty($stockOpname->branch_id) && $stockOpname->branch;
            $entityHeader = $isCabang ? ('CABANG ' . mb_strtoupper($stockOpname->branch->nama_cabang)) : mb_strtoupper($companyName);
            $entityLine = $isCabang ? ("- *Cabang*: " . $stockOpname->branch->nama_cabang . "\n") : ("- *Perusahaan*: " . $companyName . "\n");

            $creator = $stockOpname->creator;
            $creatorPhone = $creator?->no_hp ?: $creator?->karyawan?->no_hp;

            if (!$creatorPhone) {
                return;
            }

            $target = preg_replace('/[^0-9]/', '', $creatorPhone);
            $creatorName = $creator?->nama_lengkap ?: ($creator?->karyawan?->nama_lengkap ?: 'Pembuat Proposal');

            $rejector = $rejectorUser ?: auth()->user();
            $rejectorName = $rejector?->nama_lengkap ?: ($rejector?->karyawan?->nama_lengkap ?: 'Atasan');

            $nomorOpname = $stockOpname->nomor_opname;
            $gudangName  = $stockOpname->gudang->nama_gudang ?? '-';
            $jenisOpname = $stockOpname->jenis_opname;
            $alasan      = $alasanReject ?: ($stockOpname->alasan_reject ?: '-');
            $tanggalJam  = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            $message = "*PENGAJUAN STOK OPNAME DITOLAK* ❌\n";
            $message .= "*" . $entityHeader . "*\n\n";
            $message .= "Halo *" . $creatorName . "*,\n";
            $message .= "Pemberitahuan bahwa pengajuan rencana Stok Opname Anda telah *DITOLAK* oleh atasan.\n\n";
            $message .= "*Detail Pengajuan:*\n";
            $message .= $entityLine;
            $message .= "- *No. Opname*: " . $nomorOpname . "\n";
            $message .= "- *Gudang*: " . $gudangName . "\n";
            $message .= "- *Jenis Opname*: " . $jenisOpname . "\n";
            $message .= "- *Ditolak Oleh*: *" . $rejectorName . "*\n";
            $message .= "- *Alasan Penolakan*: *" . $alasan . "*\n";
            $message .= "- *Tanggal & Waktu*: " . $tanggalJam . "\n\n";
            $message .= "💡 *Catatan*: Anda dapat mengedit dan memperbaiki data proposal rencana ini di sistem ERP untuk diajukan ulang ke atasan.\n";
            $message .= "Terima kasih.";

            $response = Http::timeout(5)->connectTimeout(3)->withHeaders([
                'Authorization' => $apiToken,
            ])->asForm()->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $message,
            ]);

            $resBody = $response->json();
            $status = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

            WhatsAppLog::create([
                'company_id' => $companyId,
                'action' => 'Stock Opname Rejected Alert',
                'recipient' => $target . ' (' . $creatorName . ')',
                'status' => $status,
                'response' => json_encode($resBody),
            ]);
        } catch (\Exception $e) {
            Log::error('WA sendStockOpnameRejectedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when Stock Opname is closed and adjustment is posted.
     */
    public static function sendStockOpnameClosedNotification($stockOpname, $closerUser = null)
    {
        $companyId = $stockOpname->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $stockAlertEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_stock_alert')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || (!$approvalEnabled && !$stockAlertEnabled) || !$apiToken) {
            return;
        }

        try {
            $stockOpname->load(['company', 'branch', 'gudang', 'creator.karyawan', 'approver.karyawan', 'closer.karyawan', 'details']);
            $companyName = $stockOpname->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $isCabang = !empty($stockOpname->branch_id) && $stockOpname->branch;
            $entityHeader = $isCabang ? ('CABANG ' . mb_strtoupper($stockOpname->branch->nama_cabang)) : mb_strtoupper($companyName);
            $entityLine = $isCabang ? ("- *Cabang*: " . $stockOpname->branch->nama_cabang . "\n") : ("- *Perusahaan*: " . $companyName . "\n");

            $creator = $stockOpname->creator;
            $creatorPhone = $creator?->no_hp ?: $creator?->karyawan?->no_hp;

            if (!$creatorPhone) {
                return;
            }

            $target = preg_replace('/[^0-9]/', '', $creatorPhone);
            $creatorName = $creator?->nama_lengkap ?: ($creator?->karyawan?->nama_lengkap ?: 'Pembuat Opname');

            $approver = $stockOpname->approver;
            $approverName = $approver?->nama_lengkap ?: ($approver?->karyawan?->nama_lengkap ?: '-');

            $closer = $closerUser ?: $stockOpname->closer ?: auth()->user();
            $closerName = $closer?->nama_lengkap ?: ($closer?->karyawan?->nama_lengkap ?: 'Atasan / Admin');

            $nomorOpname  = $stockOpname->nomor_opname;
            $gudangName   = $stockOpname->gudang->nama_gudang ?? '-';
            $jenisOpname  = $stockOpname->jenis_opname;
            $tanggalJam   = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';
            $keterangan   = $stockOpname->keterangan ?? '-';

            $countedDetails = $stockOpname->details->whereNotNull('stok_fisik');
            $totalCounted   = $countedDetails->count();
            $totalSelisih   = $countedDetails->filter(fn($d) => floatval($d->selisih ?? 0) != 0)->count();

            $message = "*CLOSING STOK OPNAME & ADJUSTMENT* 📋✅\n";
            $message .= "*" . $entityHeader . "*\n\n";
            $message .= "Halo *" . $creatorName . "*,\n";
            $message .= "Transaksi Stok Opname telah resmi *DI-CLOSING* dan penyesuaian stok gudang beserta jurnal akuntansi telah diposting ke sistem.\n\n";
            $message .= "*Detail Closing:*\n";
            $message .= $entityLine;
            $message .= "- *No. Opname*: " . $nomorOpname . "\n";
            $message .= "- *Gudang*: " . $gudangName . "\n";
            $message .= "- *Jenis Opname*: " . $jenisOpname . "\n";
            $message .= "- *Item Diopname*: " . $totalCounted . " barang\n";
            $message .= "- *Item Ada Selisih*: " . $totalSelisih . " barang\n";
            $message .= "- *Diajukan Oleh*: *" . $creatorName . "*\n";
            if ($approverName !== '-') {
                $message .= "- *Disetujui Oleh*: *" . $approverName . "*\n";
            }
            $message .= "- *Diclosing Oleh*: *" . $closerName . "*\n";
            $message .= "- *Waktu Closing*: " . $tanggalJam . "\n";
            $message .= "- *Keterangan*: " . $keterangan . "\n\n";
            $message .= "Stok aktual di gudang dan kartu stok telah diperbarui sesuai hasil fisik opname.\n";
            $message .= "Terima kasih.";

            $response = Http::timeout(5)->connectTimeout(3)->withHeaders([
                'Authorization' => $apiToken,
            ])->asForm()->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $message,
            ]);

            $resBody = $response->json();
            $status = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

            WhatsAppLog::create([
                'company_id' => $companyId,
                'action' => 'Stock Opname Closed Alert',
                'recipient' => $target . ' (' . $creatorName . ')',
                'status' => $status,
                'response' => json_encode($resBody),
            ]);
        } catch (\Exception $e) {
            Log::error('WA sendStockOpnameClosedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when physical counting is completed (status -> Menunggu Closing).
     * Alerts supervisors/approvers to perform Closing (Posting Adjustment).
     */
    public static function sendStockOpnameCompletedNotification($stockOpname, $completedUser = null)
    {
        $companyId = $stockOpname->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $stockAlertEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_stock_alert')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || (!$approvalEnabled && !$stockAlertEnabled) || !$apiToken) {
            return;
        }

        try {
            $stockOpname->load(['company', 'branch', 'gudang', 'creator.karyawan', 'details']);
            $companyName = $stockOpname->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $isCabang = !empty($stockOpname->branch_id) && $stockOpname->branch;
            $entityHeader = $isCabang ? ('CABANG ' . mb_strtoupper($stockOpname->branch->nama_cabang)) : mb_strtoupper($companyName);
            $entityLine = $isCabang ? ("- *Cabang*: " . $stockOpname->branch->nama_cabang . "\n") : ("- *Perusahaan*: " . $companyName . "\n");

            $nomorOpname  = $stockOpname->nomor_opname;
            $gudangName   = $stockOpname->gudang->nama_gudang ?? '-';
            $jenisOpname  = $stockOpname->jenis_opname;
            $tanggalJam   = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';
            $keterangan   = $stockOpname->keterangan ?? '-';

            $creator       = $stockOpname->creator;
            $creatorName   = $creator?->nama_lengkap ?: ($creator?->karyawan?->nama_lengkap ?: 'Petugas Gudang');

            $completer     = $completedUser ?: auth()->user();
            $completerName = $completer?->nama_lengkap ?: ($completer?->karyawan?->nama_lengkap ?: 'Petugas Input');

            $countedDetails = $stockOpname->details->whereNotNull('stok_fisik');
            $totalCounted   = $countedDetails->count();
            $totalSelisih   = $countedDetails->filter(fn($d) => floatval($d->selisih ?? 0) != 0)->count();

            // Find supervisors with 'closing-opname' or 'posting-opname' permission assigned to this company/branch
            $supervisors = \App\Models\User::permission(['closing-opname', 'posting-opname', 'setuju-opname'])
                ->where(function($q) {
                    $q->whereNotNull('no_hp')->where('no_hp', '!=', '')
                      ->orWhereHas('karyawan', fn($k) => $k->whereNotNull('no_hp')->where('no_hp', '!=', ''));
                })
                ->where(function ($query) use ($companyId, $stockOpname) {
                    $query->whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner']))
                          ->orWhereHas('companies', fn($c) => $c->where('companies.id', $companyId))
                          ->orWhereHas('branches', fn($b) => $b->where('branches.id', $stockOpname->branch_id)
                                                               ->orWhere('branches.company_id', $companyId));
                })
                ->get();

            if ($supervisors->isEmpty()) {
                $supervisors = \App\Models\User::whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner']))
                    ->where(function($q) {
                        $q->whereNotNull('no_hp')->where('no_hp', '!=', '')
                          ->orWhereHas('karyawan', fn($k) => $k->whereNotNull('no_hp')->where('no_hp', '!=', ''));
                    })
                    ->get();
            }

            if ($supervisors->isEmpty()) {
                return;
            }

            $sentTargets = [];
            foreach ($supervisors as $supervisor) {
                $phone = $supervisor->no_hp ?: $supervisor->karyawan?->no_hp;
                $target = preg_replace('/[^0-9]/', '', $phone);
                if (empty($target) || in_array($target, $sentTargets)) continue;
                $sentTargets[] = $target;

                $supervisorName = $supervisor->nama_lengkap ?: ($supervisor->karyawan?->nama_lengkap ?: 'Atasan');

                $message = "*PEMBERITAHUAN HASIL HITUNG FISIK STOK OPNAME* 🔔\n";
                $message .= "*" . $entityHeader . "*\n\n";
                $message .= "Halo *" . $supervisorName . "*,\n";
                $message .= "Hasil perhitungan fisik Stok Opname telah selesai diinput/diposting oleh petugas. Dokumen kini *Menunggu Closing* dari Anda.\n\n";
                $message .= "*Detail Opname:*\n";
                $message .= $entityLine;
                $message .= "- *No. Opname*: " . $nomorOpname . "\n";
                $message .= "- *Gudang*: " . $gudangName . "\n";
                $message .= "- *Jenis Opname*: " . $jenisOpname . "\n";
                $message .= "- *Item Diopname*: " . $totalCounted . " barang\n";
                $message .= "- *Item Ada Selisih*: " . $totalSelisih . " barang\n";
                $message .= "- *Diajukan Oleh*: *" . $creatorName . "*\n";
                $message .= "- *Diposting Fisik Oleh*: *" . $completerName . "*\n";
                $message .= "- *Waktu Selesai*: " . $tanggalJam . "\n";
                $message .= "- *Keterangan*: " . $keterangan . "\n\n";
                $message .= "Silakan masuk ke sistem ERP untuk meninjau selisih stok dan melakukan Closing (Posting Adjustment & Jurnal).\n";
                $message .= "Terima kasih.";

                $response = Http::timeout(5)->connectTimeout(3)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action' => 'Stock Opname Physical Completed Alert',
                    'recipient' => $target . ' (' . $supervisorName . ')',
                    'status' => $status,
                    'response' => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendStockOpnameCompletedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when a Warehouse Transfer is submitted for approval / confirmation.
     */
    public static function sendTransferSubmissionNotification($warehouseTransfer)
    {
        try {
            if (is_numeric($warehouseTransfer)) {
                $warehouseTransfer = \App\Models\WarehouseTransfer::find($warehouseTransfer);
            }
            if (!$warehouseTransfer) return;

            $warehouseTransfer->load([
                'company',
                'gudangAsal.company',
                'gudangAsal.cabang',
                'gudangTujuan.company',
                'gudangTujuan.cabang',
                'creator.karyawan',
                'details.barang.satuan',
            ]);

            $companyId = $warehouseTransfer->gudangAsal->company_id ?? $warehouseTransfer->company_id ?? 1;
            $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
            $apiToken = self::getDecryptedToken($companyId);

            if (!$enabled || !$apiToken) {
                return;
            }

            $companyName = $warehouseTransfer->gudangAsal->company->nama_perusahaan ?? 'Perusahaan';
            $nomorTransfer = $warehouseTransfer->nomor_transfer;
            $jenisTransfer = $warehouseTransfer->jenis_transfer ?: 'Intra-PT';
            
            $asalGudang  = $warehouseTransfer->gudangAsal->nama_gudang ?? '-';
            $asalPt      = $warehouseTransfer->gudangAsal->company->nama_perusahaan ?? '-';
            $asalCabang  = $warehouseTransfer->gudangAsal->cabang ? $warehouseTransfer->gudangAsal->cabang->nama_cabang : 'Pusat (Head Office)';

            $tujuanGudang = $warehouseTransfer->gudangTujuan->nama_gudang ?? '-';
            $tujuanPt     = $warehouseTransfer->gudangTujuan->company->nama_perusahaan ?? '-';
            $tujuanCabang = $warehouseTransfer->gudangTujuan->cabang ? $warehouseTransfer->gudangTujuan->cabang->nama_cabang : 'Pusat (Head Office)';

            $tanggal     = \Carbon\Carbon::parse($warehouseTransfer->tanggal_transfer)->locale('id')->translatedFormat('d F Y');
            $tanggalJam  = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';
            $keterangan  = $warehouseTransfer->keterangan ?? '-';

            $creator     = $warehouseTransfer->creator;
            $creatorName = $creator?->nama_lengkap ?: ($creator?->karyawan?->nama_lengkap ?: 'Petugas Gudang');

            $itemsList = "";
            $details = $warehouseTransfer->details;
            $count = 0;
            foreach ($details as $d) {
                if ($count >= 5) {
                    $itemsList .= "• _Dan " . ($details->count() - 5) . " barang lainnya..._\n";
                    break;
                }
                $bName = $d->barang->nama_barang ?? 'Barang';
                $satuan = $d->barang->satuan->nama_satuan ?? 'unit';
                $qty = number_format($d->jumlah, 0, ',', '.');
                $itemsList .= "• *{$bName}*: {$qty} {$satuan}\n";
                $count++;
            }

            // Find supervisors with approve-transfer permission or admin/warehouse roles
            $supervisors = \App\Models\User::where(function ($q) {
                    $q->where('status', 'Aktif')
                      ->orWhere('status', 'active')
                      ->orWhereNull('status');
                })
                ->where(function ($query) {
                    $query->whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner', 'Admin', 'Supervisor Warehouse', 'Director', 'Manager']))
                          ->orWhereHas('permissions', fn($p) => $p->where('name', 'approve-transfer'));
                })
                ->with('karyawan')
                ->get();

            if ($supervisors->isEmpty()) {
                return;
            }

            $sentTargets = [];
            foreach ($supervisors as $supervisor) {
                $phone = $supervisor->no_hp ?: $supervisor->karyawan?->no_hp;
                $target = preg_replace('/[^0-9]/', '', $phone);
                if (empty($target) || in_array($target, $sentTargets)) continue;
                $sentTargets[] = $target;

                $supervisorName = $supervisor->nama_lengkap ?: ($supervisor->karyawan?->nama_lengkap ?: 'Atasan');

                $message = "*PEMBERITAHUAN PENGAJUAN TRANSFER GUDANG* 🚚\n";
                $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
                $message .= "Halo *" . $supervisorName . "*,\n";
                $message .= "Ada pengajuan Transfer Gudang baru yang membutuhkan konfirmasi/persetujuan Anda.\n\n";
                $message .= "*Detail Transfer:*\n";
                $message .= "- *No. Transfer*: " . $nomorTransfer . "\n";
                $message .= "- *Tipe*: " . $jenisTransfer . "\n";
                $message .= "- *Tanggal*: " . $tanggal . "\n";
                $message .= "- *Gudang Asal*: " . $asalGudang . " (" . $asalPt . " - " . $asalCabang . ")\n";
                $message .= "- *Gudang Tujuan*: " . $tujuanGudang . " (" . $tujuanPt . " - " . $tujuanCabang . ")\n";
                $message .= "- *Diajukan Oleh*: *" . $creatorName . "*\n";
                $message .= "- *Waktu Pengajuan*: " . $tanggalJam . "\n";
                $message .= "- *Keterangan*: " . $keterangan . "\n\n";
                $message .= "*Daftar Barang Transfer:*\n" . $itemsList . "\n";
                $message .= "Silakan masuk ke sistem ERP untuk meninjau dan mengonfirmasi pengajuan ini.\n";
                $message .= "Terima kasih.";

                $response = Http::timeout(5)->connectTimeout(3)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action' => 'Warehouse Transfer Submission Alert',
                    'recipient' => $target . ' (' . $supervisorName . ')',
                    'status' => $status,
                    'response' => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendTransferSubmissionNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when a Warehouse Transfer is confirmed / approved.
     */
    public static function sendTransferApprovedNotification($warehouseTransfer, $approverUser = null)
    {
        try {
            if (is_numeric($warehouseTransfer)) {
                $warehouseTransfer = \App\Models\WarehouseTransfer::find($warehouseTransfer);
            }
            if (!$warehouseTransfer) return;

            $warehouseTransfer->load([
                'company',
                'gudangAsal.company',
                'gudangAsal.cabang',
                'gudangTujuan.company',
                'gudangTujuan.cabang',
                'creator.karyawan',
                'details.barang.satuan',
            ]);

            $companyId = $warehouseTransfer->gudangAsal->company_id ?? $warehouseTransfer->company_id ?? 1;
            $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
            $apiToken = self::getDecryptedToken($companyId);

            if (!$enabled || !$apiToken) {
                return;
            }

            $companyName = $warehouseTransfer->gudangAsal->company->nama_perusahaan ?? 'Perusahaan';
            $nomorTransfer = $warehouseTransfer->nomor_transfer;
            $jenisTransfer = $warehouseTransfer->jenis_transfer ?: 'Intra-PT';
            
            $asalGudang  = $warehouseTransfer->gudangAsal->nama_gudang ?? '-';
            $asalPt      = $warehouseTransfer->gudangAsal->company->nama_perusahaan ?? '-';
            $asalCabang  = $warehouseTransfer->gudangAsal->cabang ? $warehouseTransfer->gudangAsal->cabang->nama_cabang : 'Pusat (Head Office)';

            $tujuanGudang = $warehouseTransfer->gudangTujuan->nama_gudang ?? '-';
            $tujuanPt     = $warehouseTransfer->gudangTujuan->company->nama_perusahaan ?? '-';
            $tujuanCabang = $warehouseTransfer->gudangTujuan->cabang ? $warehouseTransfer->gudangTujuan->cabang->nama_cabang : 'Pusat (Head Office)';

            $tanggal     = \Carbon\Carbon::parse($warehouseTransfer->tanggal_transfer)->locale('id')->translatedFormat('d F Y');
            $tanggalJam  = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            $approver    = $approverUser ?: auth()->user();
            $approverName= $approver?->nama_lengkap ?: ($approver?->karyawan?->nama_lengkap ?: 'Atasan');

            $itemsList = "";
            $details = $warehouseTransfer->details;
            $count = 0;
            foreach ($details as $d) {
                if ($count >= 5) {
                    $itemsList .= "• _Dan " . ($details->count() - 5) . " barang lainnya..._\n";
                    break;
                }
                $bName = $d->barang->nama_barang ?? 'Barang';
                $satuan = $d->barang->satuan->nama_satuan ?? 'unit';
                $qty = number_format($d->jumlah, 0, ',', '.');
                $itemsList .= "• *{$bName}*: {$qty} {$satuan}\n";
                $count++;
            }

            $recipients = [];
            $creator = $warehouseTransfer->creator;
            $creatorPhone = $creator?->no_hp ?: $creator?->karyawan?->no_hp;
            if ($creatorPhone) {
                $recipients[] = [
                    'phone' => $creatorPhone,
                    'name'  => $creator?->nama_lengkap ?: 'Petugas Pengaju',
                ];
            }

            $sentTargets = [];
            foreach ($recipients as $rec) {
                $target = preg_replace('/[^0-9]/', '', $rec['phone']);
                if (empty($target) || in_array($target, $sentTargets)) continue;
                $sentTargets[] = $target;

                $message = "*PEMBERITAHUAN TRANSFER GUDANG DIKONFIRMASI / APPROVED* ✅\n";
                $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
                $message .= "Halo *" . $rec['name'] . "*,\n";
                $message .= "Pengajuan Transfer Gudang Anda telah *DIKONFIRMASI & DIPOSTING*.\n\n";
                $message .= "*Detail Transfer:*\n";
                $message .= "- *No. Transfer*: " . $nomorTransfer . "\n";
                $message .= "- *Status*: *Confirmed (Stok Dipotong & Jurnal Terbentuk)*\n";
                $message .= "- *Tipe*: " . $jenisTransfer . "\n";
                $message .= "- *Gudang Asal*: " . $asalGudang . " (" . $asalPt . " - " . $asalCabang . ")\n";
                $message .= "- *Gudang Tujuan*: " . $tujuanGudang . " (" . $tujuanPt . " - " . $tujuanCabang . ")\n";
                $message .= "- *Dikonfirmasi Oleh*: *" . $approverName . "*\n";
                $message .= "- *Waktu Konfirmasi*: " . $tanggalJam . "\n\n";
                $message .= "*Daftar Barang Transfer:*\n" . $itemsList . "\n";
                $message .= "Stok pada gudang asal telah terpotong dan stok di gudang tujuan telah bertambah.\n";
                $message .= "Terima kasih.";

                $response = Http::timeout(5)->connectTimeout(3)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action' => 'Warehouse Transfer Approved Alert',
                    'recipient' => $target . ' (' . $rec['name'] . ')',
                    'status' => $status,
                    'response' => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendTransferApprovedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification for Opening Stock (Saldo Awal) submission (Pending Approval).
     */
    public static function sendOpeningStockSubmissionNotification($openingStock)
    {
        try {
            if (is_numeric($openingStock)) {
                $openingStock = \App\Models\OpeningStock::find($openingStock);
            }
            if (!$openingStock) return;

            $openingStock->load([
                'gudang.company',
                'gudang.cabang',
                'creator.karyawan',
                'details.barang.satuan',
            ]);

            $companyId = $openingStock->company_id ?: ($openingStock->gudang->company_id ?? 1);
            $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
            $apiToken = self::getDecryptedToken($companyId);

            if (!$enabled || !$apiToken) {
                return;
            }

            $companyName = $openingStock->gudang->company->nama_perusahaan ?? 'Perusahaan';
            $nomorDokumen = $openingStock->nomor_opening_stock;
            $gudangName = $openingStock->gudang->nama_gudang ?? '-';
            $cabangName = $openingStock->gudang->cabang ? $openingStock->gudang->cabang->nama_cabang : 'Pusat';
            $tanggal = \Carbon\Carbon::parse($openingStock->tanggal_opening_stock)->locale('id')->translatedFormat('d F Y');
            $tanggalJam = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';
            $keterangan = $openingStock->keterangan ?? '-';

            $creator = $openingStock->creator;
            $creatorName = $creator?->nama_lengkap ?: ($creator?->karyawan?->nama_lengkap ?: 'Petugas Gudang');

            $itemsList = "";
            $details = $openingStock->details;
            $count = 0;
            $totalNilai = 0;
            foreach ($details as $d) {
                $subtotal = floatval($d->jumlah) * floatval($d->harga_pokok);
                $totalNilai += $subtotal;
                if ($count >= 5) {
                    $itemsList .= "• _Dan " . ($details->count() - 5) . " barang lainnya..._\n";
                    break;
                }
                $bName = $d->barang->nama_barang ?? 'Barang';
                $satuan = $d->barang->satuan->nama_satuan ?? 'unit';
                $qty = number_format($d->jumlah, 0, ',', '.');
                $hpp = number_format($d->harga_pokok, 0, ',', '.');
                $itemsList .= "• *{$bName}*: {$qty} {$satuan} @ Rp {$hpp}\n";
                $count++;
            }
            $totalNilaiFmt = number_format($totalNilai, 0, ',', '.');

            $supervisors = \App\Models\User::whereHas('roles', function($q) {
                $q->whereIn('name', ['Super Admin', 'Owner', 'Admin', 'Supervisor Warehouse', 'Manajer Operasional', 'Kepala Gudang']);
            })
            ->orWhereHas('permissions', function($q) {
                $q->where('name', 'konfirmasi-opening-stock');
            })
            ->with('karyawan')
            ->get();

            $recipients = [];
            foreach ($supervisors as $spv) {
                $phone = $spv->no_hp ?: ($spv->karyawan ? $spv->karyawan->no_hp : null);
                if ($phone) {
                    $recipients[] = [
                        'phone' => $phone,
                        'name'  => $spv->nama_lengkap ?: $spv->name,
                    ];
                }
            }

            $uniqueRecipients = collect($recipients)->unique('phone')->values()->all();

            foreach ($uniqueRecipients as $rec) {
                $targetPhone = self::formatPhoneNumber($rec['phone']);
                if (!$targetPhone) continue;

                $message = "📢 *PEMBERITAHUAN PENGAJUAN SALDO AWAL PERSEDIAAN*\n\n"
                    . "Halo, *{$rec['name']}* 👋\n"
                    . "Terdapat dokumen Saldo Awal Persediaan baru yang diajukan dan membutuhkan peninjauan & persetujuan Anda:\n\n"
                    . "📋 *No. Dokumen:* {$nomorDokumen}\n"
                    . "🏢 *Gudang:* {$gudangName} ({$cabangName})\n"
                    . "📅 *Tgl Dokumen:* {$tanggal}\n"
                    . "👤 *Diajukan Oleh:* {$creatorName}\n"
                    . "💰 *Total Estimasi Nilai:* Rp {$totalNilaiFmt}\n"
                    . "📝 *Catatan:* {$keterangan}\n\n"
                    . "📦 *Daftar Barang:* \n"
                    . "{$itemsList}\n"
                    . "⏰ _Waktu Pengajuan: {$tanggalJam}_\n\n"
                    . "Mohon masuk ke sistem ERP untuk melakukan verifikasi dan konfirmasi dokumen ini.\n\n"
                    . "Terima kasih,\n"
                    . "🤖 *{$companyName} - ERP System Automated Bot*";

                $resBody = self::dispatchFonnteMessage($apiToken, $targetPhone, $message);
                $status = isset($resBody['status']) && $resBody['status'] == true ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'phone_number' => $targetPhone,
                    'recipient_name' => $rec['name'],
                    'message' => $message,
                    'message_type' => 'Opening Stock Submission Notification',
                    'status' => $status,
                    'response' => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendOpeningStockSubmissionNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when Opening Stock is confirmed/approved.
     */
    public static function sendOpeningStockApprovedNotification($openingStock, $approverUser = null)
    {
        try {
            if (is_numeric($openingStock)) {
                $openingStock = \App\Models\OpeningStock::find($openingStock);
            }
            if (!$openingStock) return;

            $openingStock->load([
                'gudang.company',
                'gudang.cabang',
                'creator.karyawan',
                'details.barang.satuan',
            ]);

            $companyId = $openingStock->company_id ?: ($openingStock->gudang->company_id ?? 1);
            $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
            $apiToken = self::getDecryptedToken($companyId);

            if (!$enabled || !$apiToken) {
                return;
            }

            $companyName = $openingStock->gudang->company->nama_perusahaan ?? 'Perusahaan';
            $nomorDokumen = $openingStock->nomor_opening_stock;
            $gudangName = $openingStock->gudang->nama_gudang ?? '-';
            $cabangName = $openingStock->gudang->cabang ? $openingStock->gudang->cabang->nama_cabang : 'Pusat';
            $tanggal = \Carbon\Carbon::parse($openingStock->tanggal_opening_stock)->locale('id')->translatedFormat('d F Y');
            $tanggalJam = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            $approver = $approverUser ?: auth()->user();
            $approverName = $approver?->nama_lengkap ?: ($approver?->karyawan?->nama_lengkap ?: 'Atasan');

            $totalNilai = 0;
            foreach ($openingStock->details as $d) {
                $totalNilai += (floatval($d->jumlah) * floatval($d->harga_pokok));
            }
            $totalNilaiFmt = number_format($totalNilai, 0, ',', '.');

            $recipients = [];
            $creator = $openingStock->creator;
            $creatorPhone = $creator?->no_hp ?: $creator?->karyawan?->no_hp;
            if ($creatorPhone) {
                $recipients[] = [
                    'phone' => $creatorPhone,
                    'name'  => $creator?->nama_lengkap ?: 'Petugas Pengaju',
                ];
            }

            foreach ($recipients as $rec) {
                $targetPhone = self::formatPhoneNumber($rec['phone']);
                if (!$targetPhone) continue;

                $message = "✅ *SALDO AWAL PERSEDIAAN TELAH DISETUJUI & DIPOSTING*\n\n"
                    . "Halo, *{$rec['name']}* 👋\n"
                    . "Dokumen Saldo Awal yang Anda ajukan telah berhasil *DISETUJUI & DIPOSTING* ke sistem:\n\n"
                    . "📋 *No. Dokumen:* {$nomorDokumen}\n"
                    . "🏢 *Gudang:* {$gudangName} ({$cabangName})\n"
                    . "📅 *Tgl Dokumen:* {$tanggal}\n"
                    . "💰 *Total Nilai:* Rp {$totalNilaiFmt}\n"
                    . "👤 *Disetujui Oleh:* {$approverName}\n"
                    . "⏰ *Waktu Posting:* {$tanggalJam}\n\n"
                    . "✨ _Stok fisik, HPP rata-rata, dan Jurnal Akuntansi otomatis telah terposting._\n\n"
                    . "Terima kasih,\n"
                    . "🤖 *{$companyName} - ERP System Automated Bot*";

                $resBody = self::dispatchFonnteMessage($apiToken, $targetPhone, $message);
                $status = isset($resBody['status']) && $resBody['status'] == true ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'phone_number' => $targetPhone,
                    'recipient_name' => $rec['name'],
                    'message' => $message,
                    'message_type' => 'Opening Stock Approval Notification',
                    'status' => $status,
                    'response' => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendOpeningStockApprovedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification for Stock Adjustment submission (Pending Approval).
     */
    public static function sendStockAdjustmentPendingNotification($stockAdjustment)
    {
        try {
            $stockAdjustment->load([
                'gudang.company',
                'gudang.cabang',
                'creator.karyawan',
                'details.barang.satuan',
            ]);

            $companyId = $stockAdjustment->company_id ?: ($stockAdjustment->gudang->company_id ?? session('active_company_id') ?: 1);
            $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
            $apiToken = self::getDecryptedToken($companyId);

            if (!$enabled || !$apiToken) {
                return;
            }

            $companyName   = $stockAdjustment->gudang->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $nomorAdj      = $stockAdjustment->nomor_adjustment;
            $gudangName    = $stockAdjustment->gudang->nama_gudang ?? '-';
            $tipe          = $stockAdjustment->tipe_adjustment;
            $kategori      = $stockAdjustment->kategori_alasan;
            $totalQty      = number_format($stockAdjustment->total_qty, 0, ',', '.');
            $totalNilai    = 'Rp ' . number_format($stockAdjustment->total_nilai, 2, ',', '.');
            $tanggal       = $stockAdjustment->tanggal_adjustment ? \Carbon\Carbon::parse($stockAdjustment->tanggal_adjustment)->locale('id')->translatedFormat('d F Y') : date('d/m/Y');
            $tanggalJam    = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';
            $keterangan    = $stockAdjustment->keterangan ?? '-';

            $creator       = $stockAdjustment->creator;
            $creatorName   = $creator?->nama_lengkap ?: ($creator?->karyawan?->nama_lengkap ?: 'Petugas Gudang');

            // Format items list
            $itemsList = "";
            $details   = $stockAdjustment->details;
            $count     = 0;
            foreach ($details as $d) {
                if ($count >= 5) {
                    $itemsList .= "• _Dan " . ($details->count() - 5) . " barang lainnya..._\n";
                    break;
                }
                $bName  = $d->barang->nama_barang ?? 'Barang';
                $satuan = $d->barang->satuan->singkatan ?? $d->barang->satuan->nama_satuan ?? 'unit';
                $qty    = number_format($d->jumlah, 0, ',', '.');
                $itemsList .= "• *{$bName}*: {$qty} {$satuan}\n";
                $count++;
            }

            // Find supervisors with permissions or admin roles
            $supervisors = \App\Models\User::whereNotNull('no_hp')
                ->where('no_hp', '!=', '')
                ->where(function ($query) use ($companyId) {
                    $query->whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner', 'Admin Gudang', 'Manager Inventori', 'Manager']))
                          ->orWhereHas('companies', fn($c) => $c->where('companies.id', $companyId));
                })
                ->get();

            if ($supervisors->isEmpty()) {
                return;
            }

            $sentTargets = [];
            foreach ($supervisors as $supervisor) {
                $target = preg_replace('/[^0-9]/', '', $supervisor->no_hp);
                if (empty($target) || in_array($target, $sentTargets)) continue;
                $sentTargets[] = $target;

                $supervisorName = $supervisor->nama_lengkap ?? 'Atasan';

                $message = "*PEMBERITAHUAN PENGAJUAN STOK ADJUSTMENT* 📦🔔\n";
                $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
                $message .= "Halo *" . $supervisorName . "*,\n";
                $message .= "Ada pengajuan Penyesuaian Stok (Stock Adjustment) baru yang membutuhkan persetujuan/konfirmasi Anda.\n\n";
                $message .= "*Detail Pengajuan:*\n";
                $message .= "- *No. Adjustment*: " . $nomorAdj . "\n";
                $message .= "- *Status*: *PENDING (Menunggu Persetujuan)*\n";
                $message .= "- *Gudang*: " . $gudangName . "\n";
                $message .= "- *Tipe*: *" . $tipe . " Stok*\n";
                $message .= "- *Kategori Alasan*: " . $kategori . "\n";
                $message .= "- *Tanggal*: " . $tanggal . "\n";
                $message .= "- *Total Qty*: " . $totalQty . " Item\n";
                $message .= "- *Total Nilai*: " . $totalNilai . "\n";
                $message .= "- *Diajukan Oleh*: *" . $creatorName . "*\n";
                $message .= "- *Waktu Pengajuan*: " . $tanggalJam . "\n";
                $message .= "- *Keterangan*: " . $keterangan . "\n\n";
                $message .= "*Daftar Barang Penyesuaian:*\n" . $itemsList . "\n";
                $message .= "Silakan login ke sistem ERP untuk menyetujui (Confirm) pengajuan ini.\n";
                $message .= "Terima kasih.";

                $response = Http::timeout(5)->connectTimeout(3)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status  = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action'     => 'Stock Adjustment Pending Alert',
                    'recipient'  => $target . ' (' . $supervisorName . ')',
                    'status'     => $status,
                    'response'   => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendStockAdjustmentPendingNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification for Stock Adjustment confirmation (Confirmed & Posted).
     */
    public static function sendStockAdjustmentConfirmedNotification($stockAdjustment, $approverUser = null)
    {
        try {
            $stockAdjustment->load([
                'gudang.company',
                'gudang.cabang',
                'creator.karyawan',
                'details.barang.satuan',
            ]);

            $companyId = $stockAdjustment->company_id ?: ($stockAdjustment->gudang->company_id ?? session('active_company_id') ?: 1);
            $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
            $apiToken = self::getDecryptedToken($companyId);

            if (!$enabled || !$apiToken) {
                return;
            }

            $companyName   = $stockAdjustment->gudang->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $nomorAdj      = $stockAdjustment->nomor_adjustment;
            $gudangName    = $stockAdjustment->gudang->nama_gudang ?? '-';
            $tipe          = $stockAdjustment->tipe_adjustment;
            $kategori      = $stockAdjustment->kategori_alasan;
            $totalQty      = number_format($stockAdjustment->total_qty, 0, ',', '.');
            $totalNilai    = 'Rp ' . number_format($stockAdjustment->total_nilai, 2, ',', '.');
            $tanggalJam    = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            $approver      = $approverUser ?: auth()->user();
            $approverName  = $approver?->nama_lengkap ?: 'Atasan/Manager';

            // Items list
            $itemsList = "";
            $details   = $stockAdjustment->details;
            $count     = 0;
            foreach ($details as $d) {
                if ($count >= 5) {
                    $itemsList .= "• _Dan " . ($details->count() - 5) . " barang lainnya..._\n";
                    break;
                }
                $bName  = $d->barang->nama_barang ?? 'Barang';
                $satuan = $d->barang->satuan->singkatan ?? $d->barang->satuan->nama_satuan ?? 'unit';
                $qty    = number_format($d->jumlah, 0, ',', '.');
                $itemsList .= "• *{$bName}*: {$qty} {$satuan}\n";
                $count++;
            }

            $recipients = [];
            $creator    = $stockAdjustment->creator;
            $creatorPhone = $creator?->no_hp ?: $creator?->karyawan?->no_hp;
            if ($creatorPhone) {
                $recipients[] = [
                    'phone' => $creatorPhone,
                    'name'  => $creator?->nama_lengkap ?: 'Petugas Pengaju',
                ];
            }

            $sentTargets = [];
            foreach ($recipients as $rec) {
                $target = preg_replace('/[^0-9]/', '', $rec['phone']);
                if (empty($target) || in_array($target, $sentTargets)) continue;
                $sentTargets[] = $target;

                $message = "*PEMBERITAHUAN STOK ADJUSTMENT DIKONFIRMASI / APPROVED* ✅📦\n";
                $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
                $message .= "Halo *" . $rec['name'] . "*,\n";
                $message .= "Dokumen Penyesuaian Stok (Stock Adjustment) Anda telah *DIKONFIRMASI & DIPOSTING*.\n\n";
                $message .= "*Detail Adjustment:*\n";
                $message .= "- *No. Adjustment*: " . $nomorAdj . "\n";
                $message .= "- *Status*: *CONFIRMED (Stok & Jurnal Otomatis Terbit)*\n";
                $message .= "- *Gudang*: " . $gudangName . "\n";
                $message .= "- *Tipe*: *" . $tipe . " Stok*\n";
                $message .= "- *Kategori Alasan*: " . $kategori . "\n";
                $message .= "- *Total Qty*: " . $totalQty . " Item\n";
                $message .= "- *Total Nilai*: " . $totalNilai . "\n";
                $message .= "- *Dikonfirmasi Oleh*: *" . $approverName . "*\n";
                $message .= "- *Waktu Konfirmasi*: " . $tanggalJam . "\n\n";
                $message .= "*Daftar Barang Adjustment:*\n" . $itemsList . "\n";
                $message .= "Stok fisik pada gudang terkait telah terpotong/bertambah dan Jurnal Akuntansi otomatis telah diterbitkan oleh sistem.\n";
                $message .= "Terima kasih.";

                $response = Http::timeout(5)->connectTimeout(3)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status  = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action'     => 'Stock Adjustment Confirmed Alert',
                    'recipient'  => $target . ' (' . $rec['name'] . ')',
                    'status'     => $status,
                    'response'   => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendStockAdjustmentConfirmedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when Goods Issue (Pengeluaran Barang) is submitted for Approval (Pending).
     */
    public static function sendGoodsIssueSubmissionNotification($goodsIssue)
    {
        try {
            if (is_numeric($goodsIssue)) {
                $goodsIssue = \App\Models\GoodsIssue::find($goodsIssue);
            }
            if (!$goodsIssue) return;

            $goodsIssue->load([
                'company',
                'branch',
                'gudang.company',
                'gudang.cabang',
                'creator.karyawan',
                'details.barang.satuan',
            ]);

            $companyId = $goodsIssue->gudang?->company_id ?? $goodsIssue->company_id ?? 1;
            $enabled   = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
            $apiToken  = self::getDecryptedToken($companyId);

            if (!$enabled || !$apiToken) {
                return;
            }

            $companyName = $goodsIssue->company?->nama_perusahaan ?? ($goodsIssue->gudang?->company?->nama_perusahaan ?? 'Perusahaan');
            $branchName  = $goodsIssue->branch?->nama_cabang ?? ($goodsIssue->gudang?->cabang?->nama_cabang ?? null);

            $headerEntity = mb_strtoupper($companyName);
            if ($branchName && !in_array(strtolower($branchName), ['pusat', 'kantor pusat', 'head office', 'pt pusat', '-'])) {
                $headerEntity .= " - CABANG " . mb_strtoupper($branchName);
                $unitPengeluar = "Cabang " . $branchName;
            } else {
                $headerEntity .= " (PT PUSAT)";
                $unitPengeluar = $companyName . " (Pusat)";
            }

            $nomorGi     = $goodsIssue->nomor_gi;
            $gudangName  = $goodsIssue->gudang?->nama_gudang ?? '-';
            $tujuan      = $goodsIssue->tujuan ?? '-';
            $tipe        = $goodsIssue->tipe_pengeluaran ?? 'Internal';
            $tanggal     = \Carbon\Carbon::parse($goodsIssue->tanggal_gi)->locale('id')->translatedFormat('d F Y');
            $tanggalJam  = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';
            $keterangan  = $goodsIssue->keterangan ?? '-';

            $creator     = $goodsIssue->creator;
            $creatorName = $creator?->nama_lengkap ?: ($creator?->karyawan?->nama_lengkap ?: 'Petugas Gudang');

            $itemsList = "";
            $details   = $goodsIssue->details;
            $count     = 0;
            $totalQty  = 0;
            foreach ($details as $d) {
                $rawQty = floatval($d->jumlah);
                $totalQty += $rawQty;
                if ($count < 5) {
                    $bName  = $d->barang?->nama_barang ?? 'Barang';
                    $satuan = $d->barang?->satuan?->nama_satuan ?? 'unit';
                    $qtyFmt = (floatval($d->jumlah) == intval($d->jumlah)) ? intval($d->jumlah) : number_format($d->jumlah, 2, ',', '.');
                    $itemsList .= "• *{$bName}*: {$qtyFmt} {$satuan}\n";
                    $count++;
                }
            }
            if ($details->count() > 5) {
                $itemsList .= "• _Dan " . ($details->count() - 5) . " barang lainnya..._\n";
            }

            // Find supervisors with approve-gi permission or admin/warehouse roles
            $supervisors = \App\Models\User::where(function ($q) {
                    $q->where('status', 'Aktif')
                      ->orWhere('status', 'active')
                      ->orWhereNull('status');
                })
                ->where(function ($query) {
                    $query->whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner', 'Admin', 'Supervisor Warehouse', 'Director', 'Manager', 'Manager Produksi']))
                          ->orWhereHas('permissions', fn($p) => $p->where('name', 'approve-gi'));
                })
                ->with('karyawan')
                ->get();

            $sentTargets = [];
            foreach ($supervisors as $user) {
                $phone = $user->no_hp ?: $user->karyawan?->no_hp;
                if (!$phone) continue;

                $target = preg_replace('/[^0-9]/', '', $phone);
                if (empty($target) || in_array($target, $sentTargets)) continue;
                $sentTargets[] = $target;

                $message = "*PERMINTAAN APPROVAL PENGELUARAN BARANG (GOODS ISSUE)* 📤📦\n";
                $message .= "*" . $headerEntity . "*\n\n";
                $message .= "Halo *" . ($user->nama_lengkap ?: 'Bapak/Ibu Approver') . "*,\n";
                $message .= "Terdapat dokumen pengeluaran barang baru yang diajukan dan membutuhkan persetujuan (*Approval*) Anda:\n\n";
                $message .= "*Detail Pengeluaran:*\n";
                $message .= "- *No. Dokumen*: " . $nomorGi . "\n";
                $message .= "- *Status*: *MENUNGGU PERSETUJUAN (PENDING)*\n";
                $message .= "- *Unit Pengeluar*: " . $unitPengeluar . "\n";
                $message .= "- *Gudang Asal*: " . $gudangName . "\n";
                $message .= "- *Tujuan / Penerima*: " . $tujuan . "\n";
                $message .= "- *Tipe Pengeluaran*: " . $tipe . "\n";
                $message .= "- *Tanggal Transaksi*: " . $tanggal . "\n";
                $message .= "- *Diajukan Oleh*: *" . $creatorName . "*\n";
                $message .= "- *Waktu Pengajuan*: " . $tanggalJam . "\n";
                $message .= "- *Catatan*: " . $keterangan . "\n\n";
                $message .= "*Rincian Barang yang Diminta:*\n" . $itemsList . "\n";
                $message .= "Silakan masuk ke sistem ERP modul *Inventori > Barang Keluar* untuk memeriksa detail dan melakukan persetujuan (*Setujui & Potong Stok*).\n";
                $message .= "Terima kasih.";

                $response = Http::timeout(5)->connectTimeout(3)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target'  => $target,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status  = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action'     => 'Goods Issue Submission Alert',
                    'recipient'  => $target . ' (' . ($user->nama_lengkap ?: 'Approver') . ')',
                    'status'     => $status,
                    'response'   => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendGoodsIssueSubmissionNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when Goods Issue is Confirmed (Stock Deducted & Journal Posted).
     */
    public static function sendGoodsIssueConfirmedNotification($goodsIssue, $approverUser = null)
    {
        try {
            if (is_numeric($goodsIssue)) {
                $goodsIssue = \App\Models\GoodsIssue::find($goodsIssue);
            }
            if (!$goodsIssue) return;

            $goodsIssue->load([
                'company',
                'branch',
                'gudang.company',
                'gudang.cabang',
                'creator.karyawan',
                'details.barang.satuan',
            ]);

            $companyId = $goodsIssue->gudang?->company_id ?? $goodsIssue->company_id ?? 1;
            $enabled   = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
            $apiToken  = self::getDecryptedToken($companyId);

            if (!$enabled || !$apiToken) {
                return;
            }

            $companyName = $goodsIssue->company?->nama_perusahaan ?? ($goodsIssue->gudang?->company?->nama_perusahaan ?? 'Perusahaan');
            $branchName  = $goodsIssue->branch?->nama_cabang ?? ($goodsIssue->gudang?->cabang?->nama_cabang ?? null);

            $headerEntity = mb_strtoupper($companyName);
            if ($branchName && !in_array(strtolower($branchName), ['pusat', 'kantor pusat', 'head office', 'pt pusat', '-'])) {
                $headerEntity .= " - CABANG " . mb_strtoupper($branchName);
                $unitPengeluar = "Cabang " . $branchName;
            } else {
                $headerEntity .= " (PT PUSAT)";
                $unitPengeluar = $companyName . " (Pusat)";
            }

            $nomorGi     = $goodsIssue->nomor_gi;
            $gudangName  = $goodsIssue->gudang?->nama_gudang ?? '-';
            $tujuan      = $goodsIssue->tujuan ?? '-';
            $tipe        = $goodsIssue->tipe_pengeluaran ?? 'Internal';
            $tanggal     = \Carbon\Carbon::parse($goodsIssue->tanggal_gi)->locale('id')->translatedFormat('d F Y');
            $tanggalJam  = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            $approver     = $approverUser ?: auth()->user();
            $approverName = $approver?->nama_lengkap ?: 'Atasan / Supervisor';

            $itemsList = "";
            $details   = $goodsIssue->details;
            $count     = 0;
            foreach ($details as $d) {
                if ($count < 5) {
                    $bName  = $d->barang?->nama_barang ?? 'Barang';
                    $satuan = $d->barang?->satuan?->nama_satuan ?? 'unit';
                    $qtyFmt = (floatval($d->jumlah) == intval($d->jumlah)) ? intval($d->jumlah) : number_format($d->jumlah, 2, ',', '.');
                    $itemsList .= "• *{$bName}*: {$qtyFmt} {$satuan}\n";
                    $count++;
                }
            }
            if ($details->count() > 5) {
                $itemsList .= "• _Dan " . ($details->count() - 5) . " barang lainnya..._\n";
            }

            $recipients = [];
            $creator = $goodsIssue->creator;
            $creatorPhone = $creator?->no_hp ?: $creator?->karyawan?->no_hp;
            if ($creatorPhone) {
                $recipients[] = [
                    'phone' => $creatorPhone,
                    'name'  => $creator?->nama_lengkap ?: 'Pembuat Dokumen',
                ];
            }

            $sentTargets = [];
            foreach ($recipients as $rec) {
                $target = preg_replace('/[^0-9]/', '', $rec['phone']);
                if (empty($target) || in_array($target, $sentTargets)) continue;
                $sentTargets[] = $target;

                $message = "*PENGELUARAN BARANG TELAH DISETUJUI / CONFIRMED* ✅📤\n";
                $message .= "*" . $headerEntity . "*\n\n";
                $message .= "Halo *" . $rec['name'] . "*,\n";
                $message .= "Dokumen pengeluaran barang (Goods Issue) Anda telah *DISETUJUI & DIKONFIRMASI*.\n\n";
                $message .= "*Detail Pengeluaran:*\n";
                $message .= "- *No. Dokumen*: " . $nomorGi . "\n";
                $message .= "- *Status*: *CONFIRMED (Stok Telah Dipotong & Jurnal Terbit)*\n";
                $message .= "- *Unit Pengeluar*: " . $unitPengeluar . "\n";
                $message .= "- *Gudang Asal*: " . $gudangName . "\n";
                $message .= "- *Tujuan / Penerima*: " . $tujuan . "\n";
                $message .= "- *Tipe Pengeluaran*: " . $tipe . "\n";
                $message .= "- *Tanggal Transaksi*: " . $tanggal . "\n";
                $message .= "- *Disetujui Oleh*: *" . $approverName . "*\n";
                $message .= "- *Waktu Persetujuan*: " . $tanggalJam . "\n\n";
                $message .= "*Rincian Barang Dikeluarkan:*\n" . $itemsList . "\n";
                $message .= "Stok fisik pada gudang terkait telah terpotong secara otomatis dan Jurnal Akuntansi telah diposting ke buku besar.\n";
                $message .= "Terima kasih.";

                $response = Http::timeout(5)->connectTimeout(3)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target'  => $target,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status  = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action'     => 'Goods Issue Confirmed Alert',
                    'recipient'  => $target . ' (' . $rec['name'] . ')',
                    'status'     => $status,
                    'response'   => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendGoodsIssueConfirmedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when BOM is submitted to Pending Approval.
     */
    public static function sendBomApprovalNotification($bom)
    {
        $companyId = $bom->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$approvalEnabled || !$apiToken) {
            return;
        }

        try {
            $bom->load([
                'company',
                'branch',
                'barangJadi.satuan',
                'satuanHasil',
                'creator.karyawan',
                'tender',
                'items.barangMentah.satuan',
                'items.satuan',
            ]);

            $companyName   = $bom->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $asalPengajuan = $bom->branch ? 'Cabang ' . $bom->branch->nama_cabang : 'PT Pusat';

            $creatorName = $bom->creator?->nama_lengkap ?: $bom->creator?->karyawan?->nama_lengkap ?: 'Tim Produksi';
            $kodeBom     = $bom->kode_bom;
            $namaBom     = $bom->nama_bom;
            $barangJadi  = $bom->barangJadi ? ('[' . $bom->barangJadi->kode_barang . '] ' . $bom->barangJadi->nama_barang) : '-';
            $tipeBom     = $bom->tipe_bom ?: 'Garmen';

            $satuanHasil = $bom->satuanHasil ? $bom->satuanHasil->nama_satuan : ($bom->barangJadi && $bom->barangJadi->satuan ? $bom->barangJadi->satuan->nama_satuan : 'Pcs');
            $jumlahHasilVal = (float)$bom->jumlah_hasil;
            $jumlahHasilFmt = ($jumlahHasilVal == (int)$jumlahHasilVal) ? number_format($jumlahHasilVal, 0, ',', '.') : rtrim(rtrim(number_format($jumlahHasilVal, 4, ',', '.'), '0'), ',');
            $targetQty   = $jumlahHasilFmt . ' ' . $satuanHasil;
            $totalHpp    = 'Rp ' . number_format($bom->total_estimasi_hpp, 2, ',', '.');
            $tanggalJam  = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';
            $keterangan  = $bom->keterangan ?: '-';

            // Format items text
            $itemsText = "";
            $details   = $bom->items;
            $count     = 0;
            foreach ($details as $index => $item) {
                if ($count >= 5) {
                    $itemsText .= "• _Dan " . ($details->count() - 5) . " bahan lainnya..._\n";
                    break;
                }
                $bmName  = $item->barangMentah ? $item->barangMentah->nama_barang : 'Bahan Baku';
                $qty     = (float)$item->jumlah_kebutuhan;
                $satuan  = $item->satuan ? $item->satuan->nama_satuan : ($item->barangMentah && $item->barangMentah->satuan ? $item->barangMentah->satuan->nama_satuan : '');
                $waste   = (float)$item->waste_percent;
                $wasteStr = $waste > 0 ? " (waste: {$waste}%)" : "";

                $itemsText .= ($index + 1) . ". *" . $bmName . "*\n";
                $itemsText .= "   - Qty: " . $qty . ($satuan ? ' ' . $satuan : '') . $wasteStr . "\n";
                $count++;
            }

            // Find active supervisors with 'approve-bom' permission or Super Admin / Owner role
            $supervisors = \App\Models\User::with('karyawan')
                ->whereIn('status', ['Aktif', 'Active'])
                ->where(function($q) {
                    $q->whereNotNull('no_hp')->where('no_hp', '!=', '')
                      ->orWhereHas('karyawan', fn($k) => $k->whereNotNull('no_hp')->where('no_hp', '!=', ''));
                })
                ->where(function ($q) {
                    $q->whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner']))
                      ->orWhere(function ($sub) {
                          $sub->permission(['approve-bom']);
                      });
                })
                ->where(function ($query) use ($companyId, $bom) {
                    $query->whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner']))
                          ->orWhereHas('companies', fn($c) => $c->where('companies.id', $companyId))
                          ->orWhereHas('branches', fn($b) => $b->where('branches.id', $bom->branch_id)
                                                               ->orWhere('branches.company_id', $companyId));
                })
                ->get();

            if ($supervisors->isEmpty()) {
                return;
            }

            $sentTargets = [];
            foreach ($supervisors as $supervisor) {
                $phone = $supervisor->no_hp ?: ($supervisor->karyawan?->no_hp ?? null);
                $target = preg_replace('/[^0-9]/', '', $phone ?? '');
                if (str_starts_with($target, '0')) {
                    $target = '62' . substr($target, 1);
                }
                if (empty($target) || in_array($target, $sentTargets)) continue;
                $sentTargets[] = $target;

                $supervisorName = $supervisor->nama_lengkap ?? 'Atasan';

                $message = "*PEMBERITAHUAN PENGAJUAN RESEP BOM (PENDING APPROVAL)* 🔔\n";
                $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
                $message .= "Halo *" . $supervisorName . "*,\n";
                $message .= "Ada pengajuan Resep Produksi Garmen (Bill of Materials) baru yang membutuhkan persetujuan Anda.\n\n";
                $message .= "*Detail Pengajuan BOM:*\n";
                $message .= "- *Perusahaan*: " . $companyName . "\n";
                $message .= "- *Asal Pengajuan*: " . $asalPengajuan . "\n";
                $message .= "- *No. Kode BOM*: " . $kodeBom . "\n";
                $message .= "- *Nama Resep*: " . $namaBom . "\n";
                $message .= "- *Produk Barang Jadi*: " . $barangJadi . "\n";
                $message .= "- *Tipe Produksi*: " . $tipeBom . "\n";
                $message .= "- *Target Hasil*: " . $targetQty . "\n";
                $message .= "- *Estimasi Total HPP*: " . $totalHpp . "\n";
                $message .= "- *Disusun Oleh (Pengaju)*: *" . $creatorName . "*\n";
                $message .= "- *Tanggal & Waktu*: " . $tanggalJam . "\n";
                $message .= "- *Catatan/Keterangan*: " . $keterangan . "\n\n";
                if ($itemsText) {
                    $message .= "*Daftar Komposisi Bahan:*\n" . $itemsText . "\n";
                }
                $message .= "Silakan masuk ke sistem ERP untuk menyetujui (Approve) atau menolak (Reject) resep BOM ini.\n";
                $message .= "Terima kasih.";

                $response = Http::timeout(3)->connectTimeout(2)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status  = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action'     => 'BOM Approval Alert',
                    'recipient'  => $target . ' (' . $supervisorName . ')',
                    'status'     => $status,
                    'response'   => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendBomApprovalNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when BOM is approved.
     */
    public static function sendBomApprovedNotification($bom, $approverUser = null)
    {
        $companyId = $bom->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$approvalEnabled || !$apiToken) {
            return;
        }

        try {
            $bom->load([
                'company',
                'branch',
                'barangJadi.satuan',
                'satuanHasil',
                'creator.karyawan',
                'approver.karyawan',
            ]);

            $companyName   = $bom->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $asalPengajuan = $bom->branch ? 'Cabang ' . $bom->branch->nama_cabang : 'PT Pusat';

            $creator = $bom->creator ?: \App\Models\User::with('karyawan')->find($bom->created_by);
            $creatorPhone = $creator?->no_hp ?: $creator?->karyawan?->no_hp;

            if (!$creatorPhone) {
                return;
            }

            $target = preg_replace('/[^0-9]/', '', $creatorPhone);
            if (str_starts_with($target, '0')) {
                $target = '62' . substr($target, 1);
            }
            $creatorName  = $creator?->nama_lengkap ?: $creator?->karyawan?->nama_lengkap ?: 'Penyusun BOM';
            $approverName = $approverUser?->nama_lengkap ?: $bom->approver?->nama_lengkap ?: auth()->user()?->nama_lengkap ?: 'Atasan / Supervisor';

            $kodeBom     = $bom->kode_bom;
            $namaBom     = $bom->nama_bom;
            $barangJadi  = $bom->barangJadi ? ('[' . $bom->barangJadi->kode_barang . '] ' . $bom->barangJadi->nama_barang) : '-';
            $satuanHasil = $bom->satuanHasil ? $bom->satuanHasil->nama_satuan : ($bom->barangJadi && $bom->barangJadi->satuan ? $bom->barangJadi->satuan->nama_satuan : 'Pcs');
            $jumlahHasilVal = (float)$bom->jumlah_hasil;
            $jumlahHasilFmt = ($jumlahHasilVal == (int)$jumlahHasilVal) ? number_format($jumlahHasilVal, 0, ',', '.') : rtrim(rtrim(number_format($jumlahHasilVal, 4, ',', '.'), '0'), ',');
            $targetQty   = $jumlahHasilFmt . ' ' . $satuanHasil;
            $totalHpp    = 'Rp ' . number_format($bom->total_estimasi_hpp, 2, ',', '.');
            $tanggalJam  = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            $message = "*RESEP BOM PRODUKSI: TELAH DISETUJUI (APPROVED)* ✅\n";
            $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
            $message .= "Halo *" . $creatorName . "*,\n";
            $message .= "Kabar baik! Resep Produksi Garmen (Bill of Materials) yang Anda susun telah *DISETUJUI (APPROVED)* oleh atasan.\n\n";
            $message .= "*Detail Resep BOM:*\n";
            $message .= "- *Perusahaan*: " . $companyName . "\n";
            $message .= "- *Asal Pengajuan*: " . $asalPengajuan . "\n";
            $message .= "- *No. Kode BOM*: " . $kodeBom . "\n";
            $message .= "- *Nama Resep*: " . $namaBom . "\n";
            $message .= "- *Produk Barang Jadi*: " . $barangJadi . "\n";
            $message .= "- *Target Hasil*: " . $targetQty . "\n";
            $message .= "- *Estimasi HPP*: " . $totalHpp . "\n";
            $message .= "- *Disusun Oleh (Pengaju)*: *" . $creatorName . "*\n";
            $message .= "- *Disetujui Oleh*: *" . $approverName . "*\n";
            $message .= "- *Waktu Persetujuan*: " . $tanggalJam . "\n";
            $message .= "- *Status*: *APPROVED (Aktif & Siap untuk SPK / Produksi)*\n\n";
            $message .= "Resep BOM ini sekarang telah aktif dan siap digunakan untuk proses produksi garmen selanjutnya.\n";
            $message .= "Terima kasih.";

            $response = Http::timeout(3)->connectTimeout(2)->withHeaders([
                'Authorization' => $apiToken,
            ])->asForm()->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $message,
            ]);

            $resBody = $response->json();
            $status  = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

            WhatsAppLog::create([
                'company_id' => $companyId,
                'action'     => 'BOM Approved Alert',
                'recipient'  => $target . ' (' . $creatorName . ')',
                'status'     => $status,
                'response'   => json_encode($resBody),
            ]);
        } catch (\Exception $e) {
            Log::error('WA sendBomApprovedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when BOM is rejected.
     */
    public static function sendBomRejectedNotification($bom, $approverUser = null, $alasan = '')
    {
        $companyId = $bom->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$approvalEnabled || !$apiToken) {
            return;
        }

        try {
            $bom->load([
                'company',
                'branch',
                'barangJadi.satuan',
                'creator.karyawan',
            ]);

            $companyName = $bom->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $creator     = $bom->creator ?: \App\Models\User::find($bom->created_by);
            $creatorPhone = $creator?->no_hp ?: $creator?->karyawan?->no_hp;

            if (!$creatorPhone) {
                return;
            }

            $target       = preg_replace('/[^0-9]/', '', $creatorPhone);
            $creatorName  = $creator?->nama_lengkap ?: $creator?->karyawan?->nama_lengkap ?: 'Penyusun BOM';
            $approverName = $approverUser?->nama_lengkap ?: auth()->user()?->nama_lengkap ?: 'Atasan / Supervisor';

            $kodeBom    = $bom->kode_bom;
            $namaBom    = $bom->nama_bom;
            $barangJadi = $bom->barangJadi ? ('[' . $bom->barangJadi->kode_barang . '] ' . $bom->barangJadi->nama_barang) : '-';
            $tanggalJam = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            $message = "*PEMBERITAHUAN RESEP BOM DITOLAK (REJECTED)* ❌\n";
            $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
            $message .= "Halo *" . $creatorName . "*,\n";
            $message .= "Mohon maaf, pengajuan Resep Produksi Garmen (BOM) Anda telah *DITOLAK (REJECTED)* oleh atasan dengan catatan revisi berikut:\n\n";
            $message .= "*Detail Penolakan:*\n";
            $message .= "- *Perusahaan*: " . $companyName . "\n";
            $message .= "- *No. Kode BOM*: " . $kodeBom . "\n";
            $message .= "- *Nama Resep*: " . $namaBom . "\n";
            $message .= "- *Produk Barang Jadi*: " . $barangJadi . "\n";
            $message .= "- *Ditolak Oleh*: *" . $approverName . "*\n";
            $message .= "- *Alasan / Catatan Penolakan*: *" . ($alasan ?: 'Perlu penyesuaian komposisi bahan') . "*\n";
            $message .= "- *Tanggal & Waktu*: " . $tanggalJam . "\n\n";
            $message .= "Silakan periksa dan perbarui kembali komposisi bahan baku di sistem ERP.\n";
            $message .= "Terima kasih.";

            $response = Http::timeout(3)->connectTimeout(2)->withHeaders([
                'Authorization' => $apiToken,
            ])->asForm()->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $message,
            ]);

            $resBody = $response->json();
            $status  = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

            WhatsAppLog::create([
                'company_id' => $companyId,
                'action'     => 'BOM Rejected Alert',
                'recipient'  => $target . ' (' . $creatorName . ')',
                'status'     => $status,
                'response'   => json_encode($resBody),
            ]);
        } catch (\Exception $e) {
            Log::error('WA sendBomRejectedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when SPK / Work Order is submitted to Pending Approval.
     */
    public static function sendSpkApprovalNotification($spk)
    {
        $companyId = $spk->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$approvalEnabled || !$apiToken) {
            return;
        }

        try {
            $spk->load([
                'company',
                'branch',
                'barangJadi.satuan',
                'satuan',
                'bom',
                'penanggungJawab',
                'creator',
            ]);

            $companyName   = $spk->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $asalPengajuan = $spk->branch ? 'Cabang ' . $spk->branch->nama_cabang : 'PT Pusat';

            $creatorName   = $spk->creator?->nama_lengkap ?: 'Tim Produksi';
            $nomorSpk      = $spk->nomor_spk;
            $namaBom       = $spk->bom?->nama_bom ?: '-';
            $barangJadi    = $spk->barangJadi ? ('[' . $spk->barangJadi->kode_barang . '] ' . $spk->barangJadi->nama_barang) : '-';
            $targetSatuan  = $spk->satuan ? $spk->satuan->nama_satuan : 'Pcs';
            $targetQty     = number_format($spk->jumlah_rencana, 0, ',', '.') . ' ' . $targetSatuan;
            $deadline      = $spk->target_selesai ? $spk->target_selesai->format('d-m-Y') : 'Belum ditentukan';
            $picName       = $spk->penanggungJawab?->nama_lengkap ?: 'Belum ditentukan';
            $prioritas     = $spk->prioritas ?: 'Normal';
            $tanggalJam    = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            // Find active supervisors with 'approve-spk' permission or Super Admin / Owner role
            $supervisors = \App\Models\User::with('karyawan')
                ->whereIn('status', ['Aktif', 'Active'])
                ->where(function($q) {
                    $q->whereNotNull('no_hp')->where('no_hp', '!=', '')
                      ->orWhereHas('karyawan', fn($k) => $k->whereNotNull('no_hp')->where('no_hp', '!=', ''));
                })
                ->where(function ($q) {
                    $q->whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner']))
                      ->orWhere(function ($sub) {
                          $sub->permission(['approve-spk']);
                      });
                })
                ->where(function ($query) use ($companyId, $spk) {
                    $query->whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner']))
                          ->orWhereHas('companies', fn($c) => $c->where('companies.id', $companyId))
                          ->orWhereHas('branches', fn($b) => $b->where('branches.id', $spk->branch_id)
                                                               ->orWhere('branches.company_id', $companyId));
                })
                ->get();

            if ($supervisors->isEmpty()) {
                return;
            }

            $sentTargets = [];
            foreach ($supervisors as $supervisor) {
                $phone = $supervisor->no_hp ?: ($supervisor->karyawan?->no_hp ?? null);
                $target = preg_replace('/[^0-9]/', '', $phone ?? '');
                if (str_starts_with($target, '0')) {
                    $target = '62' . substr($target, 1);
                }
                if (empty($target) || in_array($target, $sentTargets)) continue;
                $sentTargets[] = $target;

                $supervisorName = $supervisor->nama_lengkap ?? 'Atasan';

                $message = "*PEMBERITAHUAN PENGAJUAN SPK PRODUKSI (PENDING APPROVAL)* 📋\n";
                $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
                $message .= "Halo *" . $supervisorName . "*,\n";
                $message .= "Ada Surat Perintah Kerja (SPK / Work Order) Produksi baru yang membutuhkan persetujuan Anda.\n\n";
                $message .= "*Detail SPK Produksi:*\n";
                $message .= "- *Perusahaan*: " . $companyName . "\n";
                $message .= "- *Asal Unit*: " . $asalPengajuan . "\n";
                $message .= "- *No. SPK*: *" . $nomorSpk . "*\n";
                $message .= "- *Produk Barang Jadi*: " . $barangJadi . "\n";
                $message .= "- *Resep BOM*: " . $namaBom . "\n";
                $message .= "- *Target Qty Produksi*: *" . $targetQty . "*\n";
                $message .= "- *Prioritas*: " . $prioritas . "\n";
                $message .= "- *PIC Produksi*: *" . $picName . "*\n";
                $message .= "- *Target Selesai*: " . $deadline . "\n";
                $message .= "- *Dibuat Oleh*: *" . $creatorName . "*\n";
                $message .= "- *Waktu Pengajuan*: " . $tanggalJam . "\n\n";
                $message .= "Silakan masuk ke sistem ERP untuk menyetujui (Approve) atau menolak SPK ini.\n";
                $message .= "Terima kasih.";

                $response = Http::timeout(3)->connectTimeout(2)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status  = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action'     => 'SPK Approval Alert',
                    'recipient'  => $target . ' (' . $supervisorName . ')',
                    'status'     => $status,
                    'response'   => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendSpkApprovalNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when SPK / Work Order is approved.
     */
    public static function sendSpkApprovedNotification($spk, $approverUser = null)
    {
        $companyId = $spk->company_id ?? session('active_company_id') ?: 1;
        $enabled   = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $apiToken  = self::getDecryptedToken($companyId);

        if (!$enabled || !$apiToken) {
            return;
        }

        try {
            $spk->load([
                'company',
                'branch',
                'barangJadi.satuan',
                'satuan',
                'bom',
                'penanggungJawab',
                'creator',
            ]);

            $companyName   = $spk->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $asalPengajuan = $spk->branch ? 'Cabang ' . $spk->branch->nama_cabang : 'PT Pusat';

            // 1. Pengaju SPK (user yang menginput / created_by)
            $creatorId = $spk->created_by;
            $creator   = $creatorId ? \App\Models\User::with('karyawan')->find($creatorId) : null;
            $crPhone   = null;
            $crName    = null;

            if ($creator) {
                $crName  = $creator->nama_lengkap ?: ($creator->karyawan?->nama_lengkap ?: ($creator->username ?: 'Pengaju SPK'));
                $crPhone = $creator->no_hp ?: ($creator->karyawan?->no_hp ?? null);

                if (!$crPhone) {
                    $karyawanByUser = \App\Models\Karyawan::where('user_id', $creator->id)->first();
                    if ($karyawanByUser && !empty($karyawanByUser->no_hp)) {
                        $crPhone = $karyawanByUser->no_hp;
                        if (empty($crName)) $crName = $karyawanByUser->nama_lengkap;
                    }
                }
            }

            $recipients = [];
            if ($crPhone) {
                $recipients[] = [
                    'name'   => $crName,
                    'phone'  => $crPhone,
                    'is_pic' => false,
                ];
            }

            // 2. PIC / Penanggung Jawab Produksi (jika ada)
            $pj = $spk->penanggungJawab ?: \App\Models\Karyawan::find($spk->penanggung_jawab_id);
            if ($pj) {
                $pjPhone = $pj->no_hp;
                $pjName  = $pj->nama_lengkap ?: 'PIC Produksi';

                // Jika no_hp belum ada di Karyawan, cek akun User yang terhubung
                if (!$pjPhone && $pj->user_id) {
                    $pjUser = \App\Models\User::find($pj->user_id);
                    if ($pjUser && !empty($pjUser->no_hp)) {
                        $pjPhone = $pjUser->no_hp;
                    }
                }

                if (!empty($pjPhone)) {
                    $cleanPjPhone = preg_replace('/[^0-9]/', '', $pjPhone);
                    $cleanCrPhone = $crPhone ? preg_replace('/[^0-9]/', '', $crPhone) : '';

                    if (empty($cleanCrPhone) || $cleanPjPhone !== $cleanCrPhone) {
                        $recipients[] = [
                            'name'   => $pjName,
                            'phone'  => $pjPhone,
                            'is_pic' => true,
                        ];
                    }
                }
            }

            if (empty($recipients)) {
                \Illuminate\Support\Facades\Log::warning("WA sendSpkApprovedNotification: Tidak ada nomor HP pengaju maupun PIC yang valid untuk SPK {$spk->nomor_spk}.");
                return;
            }

            $nomorSpk     = $spk->nomor_spk;
            $barangJadi   = $spk->barangJadi ? ('[' . $spk->barangJadi->kode_barang . '] ' . $spk->barangJadi->nama_barang) : '-';
            $targetSatuan = $spk->satuan ? $spk->satuan->nama_satuan : 'Pcs';
            $targetQty    = number_format($spk->jumlah_rencana, 0, ',', '.') . ' ' . $targetSatuan;
            $deadline     = $spk->target_selesai ? $spk->target_selesai->format('d-m-Y') : 'Belum ditentukan';
            $approverName = $approverUser?->nama_lengkap ?: auth()->user()?->nama_lengkap ?: 'Atasan / Manajer';
            $tanggalJam   = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            foreach ($recipients as $rec) {
                $target = preg_replace('/[^0-9]/', '', $rec['phone']);
                if (str_starts_with($target, '0')) {
                    $target = '62' . substr($target, 1);
                }
                if (empty($target)) continue;

                if ($rec['is_pic']) {
                    // Pesan Khusus PIC Produksi / Penanggung Jawab
                    $message  = "Halo *" . $rec['name'] . "*,\n";
                    $message .= "Ada pekerjaan produksi baru nih! 🛠️\n\n";
                    $message .= "Surat Perintah Kerja (SPK) *" . $nomorSpk . "* telah disetujui dan ditugaskan kepada Anda.\n\n";
                    $message .= "*Detail Pekerjaan Produksi:*\n";
                    $message .= "- *Perusahaan*: " . $companyName . "\n";
                    $message .= "- *Unit*: " . $asalPengajuan . "\n";
                    $message .= "- *No. SPK*: *" . $nomorSpk . "*\n";
                    $message .= "- *Produk*: " . $barangJadi . "\n";
                    $message .= "- *Target Produksi*: *" . $targetQty . "*\n";
                    $message .= "- *Target Selesai*: *" . $deadline . "*\n";
                    $message .= "- *Waktu Persetujuan*: " . $tanggalJam . "\n\n";
                    $message .= "Silakan cek sistem ERP, persiapkan bahan baku dari gudang, dan mulai pengerjaan produksi.\n";
                    $message .= "Semangat bekerja!";
                } else {
                    // Pesan Khusus Pengaju SPK
                    $message  = "*SPK PRODUKSI DISETUJUI & SIAP DIKERJAKAN (APPROVED)* 🚀\n";
                    $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
                    $message .= "Halo *" . $rec['name'] . "*,\n";
                    $message .= "Kabar baik! Surat Perintah Kerja (SPK / Work Order) yang Anda ajukan telah *DISETUJUI (APPROVED)* oleh atasan.\n\n";
                    $message .= "*Detail SPK Produksi:*\n";
                    $message .= "- *Perusahaan*: " . $companyName . "\n";
                    $message .= "- *Unit*: " . $asalPengajuan . "\n";
                    $message .= "- *No. SPK*: *" . $nomorSpk . "*\n";
                    $message .= "- *Produk*: " . $barangJadi . "\n";
                    $message .= "- *Target Produksi*: *" . $targetQty . "*\n";
                    $message .= "- *Target Selesai*: *" . $deadline . "*\n";
                    $message .= "- *Disetujui Oleh*: *" . $approverName . "*\n";
                    $message .= "- *Waktu Persetujuan*: " . $tanggalJam . "\n\n";
                    $message .= "Silakan persiapkan pengerjaan line produksi.\n";
                    $message .= "Terima kasih.";
                }

                $response = Http::timeout(3)->connectTimeout(2)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target'  => $target,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status  = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action'     => 'SPK Approved Alert',
                    'recipient'  => $target . ' (' . $rec['name'] . ')',
                    'status'     => $status,
                    'response'   => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendSpkApprovedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when SPK production is started (In Progress).
     */
    public static function sendSpkProductionStartedNotification($spk, $startedUser = null)
    {
        $companyId = $spk->company_id ?? session('active_company_id') ?: 1;
        $enabled   = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $apiToken  = self::getDecryptedToken($companyId);

        if (!$enabled || !$apiToken) {
            return;
        }

        try {
            $spk->load([
                'company',
                'branch',
                'barangJadi.satuan',
                'satuan',
                'penanggungJawab',
                'creator',
            ]);

            $companyName   = $spk->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $asalPengajuan = $spk->branch ? 'Cabang ' . $spk->branch->nama_cabang : 'PT Pusat';

            // Cari PIC / Penanggung Jawab Produksi
            $pj = $spk->penanggungJawab ?: \App\Models\Karyawan::find($spk->penanggung_jawab_id);
            $pjPhone = null;
            $pjName  = null;

            if ($pj) {
                $pjName  = $pj->nama_lengkap ?: 'PIC Produksi';
                $pjPhone = $pj->no_hp;

                if (!$pjPhone && $pj->user_id) {
                    $pjUser = \App\Models\User::find($pj->user_id);
                    if ($pjUser && !empty($pjUser->no_hp)) {
                        $pjPhone = $pjUser->no_hp;
                    }
                }
            }

            // Fallback ke creator jika PIC tidak punya nomor HP
            if (!$pjPhone && $spk->created_by) {
                $creator = \App\Models\User::with('karyawan')->find($spk->created_by);
                if ($creator) {
                    $pjName  = $creator->nama_lengkap ?: ($creator->karyawan?->nama_lengkap ?: 'Tim Produksi');
                    $pjPhone = $creator->no_hp ?: ($creator->karyawan?->no_hp ?? null);
                }
            }

            if (!$pjPhone) {
                Log::warning("WA sendSpkProductionStartedNotification: Nomor HP PIC untuk SPK {$spk->nomor_spk} tidak ditemukan.");
                return;
            }

            $target = preg_replace('/[^0-9]/', '', $pjPhone);
            if (str_starts_with($target, '0')) {
                $target = '62' . substr($target, 1);
            }
            if (empty($target)) return;

            $nomorSpk     = $spk->nomor_spk;
            $barangJadi   = $spk->barangJadi ? ('[' . $spk->barangJadi->kode_barang . '] ' . $spk->barangJadi->nama_barang) : '-';
            $targetSatuan = $spk->satuan ? $spk->satuan->nama_satuan : 'Pcs';
            $targetQty    = number_format($spk->jumlah_rencana, 0, ',', '.') . ' ' . $targetSatuan;
            $deadline     = $spk->target_selesai ? $spk->target_selesai->format('d-m-Y') : 'Belum ditentukan';
            $waktuMulai   = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            $message  = "Halo *" . $pjName . "*,\n";
            $message .= "Produksi sudah resmi berjalan! 🚀⚙️\n\n";
            $message .= "Surat Perintah Kerja (SPK) *" . $nomorSpk . "* telah dimulai pengerjaannya di line produksi.\n\n";
            $message .= "*Detail Pengerjaan SPK:*\n";
            $message .= "- *Perusahaan*: " . $companyName . "\n";
            $message .= "- *Unit*: " . $asalPengajuan . "\n";
            $message .= "- *No. SPK*: *" . $nomorSpk . "*\n";
            $message .= "- *Produk*: " . $barangJadi . "\n";
            $message .= "- *Target Produksi*: *" . $targetQty . "*\n";
            $message .= "- *Target Selesai*: *" . $deadline . "*\n";
            $message .= "- *Waktu Mulai*: " . $waktuMulai . "\n\n";
            $message .= "Terus semangat, jaga kesehatan agar cepat selesai!\n";
            $message .= "Selamat bekerja!";

            $response = Http::timeout(3)->connectTimeout(2)->withHeaders([
                'Authorization' => $apiToken,
            ])->asForm()->post('https://api.fonnte.com/send', [
                'target'  => $target,
                'message' => $message,
            ]);

            $resBody = $response->json();
            $status  = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

            WhatsAppLog::create([
                'company_id' => $companyId,
                'action'     => 'SPK Started Alert',
                'recipient'  => $target . ' (' . $pjName . ')',
                'status'     => $status,
                'response'   => json_encode($resBody),
            ]);
        } catch (\Exception $e) {
            Log::error('WA sendSpkProductionStartedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when SPK / Work Order is completed.
     */
    public static function sendSpkCompletedNotification($spk, $completedUser = null)
    {
        $companyId = $spk->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$approvalEnabled || !$apiToken) {
            return;
        }

        try {
            $spk->load([
                'company',
                'branch',
                'barangJadi.satuan',
                'satuan',
                'bom',
                'penanggungJawab',
                'creator',
                'tahapans'
            ]);

            $companyName   = $spk->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $asalPengajuan = $spk->branch ? 'Cabang ' . $spk->branch->nama_cabang : 'PT Pusat';

            $recipients = [];
            if ($spk->creator) {
                $crPhone = $spk->creator->no_hp ?: ($spk->creator->karyawan?->no_hp ?? null);
                if ($crPhone) {
                    $recipients[] = [
                        'name'  => $spk->creator->nama_lengkap,
                        'phone' => $crPhone,
                        'role'  => 'Pembuat SPK'
                    ];
                }
            }
            if ($spk->penanggungJawab) {
                $pjPhone = $spk->penanggungJawab->no_hp ?: ($spk->penanggungJawab->user?->no_hp ?? null);
                if ($pjPhone && (!isset($recipients[0]) || $pjPhone !== $recipients[0]['phone'])) {
                    $recipients[] = [
                        'name'  => $spk->penanggungJawab->nama_lengkap,
                        'phone' => $pjPhone,
                        'role'  => 'PIC Produksi'
                    ];
                }
            }

            // Also notify supervisors with approve-spk / approve-realisasi-produksi
            $supervisors = \App\Models\User::where(function ($q) {
                    $q->whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner']))
                      ->orWhere(function ($sub) {
                          $sub->permission(['approve-spk', 'approve-realisasi-produksi']);
                      });
                })
                ->where(function ($query) use ($companyId, $spk) {
                    $query->whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner']))
                          ->orWhereHas('companies', fn($c) => $c->where('companies.id', $companyId));
                })
                ->get();

            foreach ($supervisors as $sup) {
                $supPhone = $sup->no_hp ?: ($sup->karyawan?->no_hp ?? null);
                if ($supPhone) {
                    $recipients[] = [
                        'name'  => $sup->nama_lengkap,
                        'phone' => $supPhone,
                        'role'  => 'Manajer / Atasan'
                    ];
                }
            }

            if (empty($recipients)) {
                return;
            }

            $nomorSpk      = $spk->nomor_spk;
            $barangJadi    = $spk->barangJadi ? ('[' . $spk->barangJadi->kode_barang . '] ' . $spk->barangJadi->nama_barang) : '-';
            $targetSatuan  = $spk->satuan ? $spk->satuan->nama_satuan : 'Pcs';
            $targetQty     = number_format($spk->jumlah_rencana, 0, ',', '.') . ' ' . $targetSatuan;
            $qtySelesai    = number_format($spk->jumlah_selesai ?: $spk->jumlah_rencana, 0, ',', '.') . ' ' . $targetSatuan;
            $qtyReject     = number_format($spk->jumlah_reject ?: 0, 0, ',', '.') . ' ' . $targetSatuan;
            $completerName = $completedUser?->nama_lengkap ?: auth()->user()?->nama_lengkap ?: 'Tim Produksi';
            $tanggalJam    = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            $sentTargets = [];
            foreach ($recipients as $rec) {
                $target = preg_replace('/[^0-9]/', '', $rec['phone']);
                if (empty($target) || in_array($target, $sentTargets)) continue;
                $sentTargets[] = $target;

                $message = "*PRODUKSI SPK SELESAI 100% (COMPLETED)* ✅🎉\n";
                $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
                $message .= "Halo *" . $rec['name'] . "*,\n";
                $message .= "Seluruh tahapan pengerjaan Surat Perintah Kerja (SPK / Work Order) telah *SELESAI (COMPLETED)* dengan sukses!\n\n";
                $message .= "*Ringkasan Hasil Produksi:*\n";
                $message .= "- *Perusahaan*: " . $companyName . "\n";
                $message .= "- *Unit*: " . $asalPengajuan . "\n";
                $message .= "- *No. SPK*: *" . $nomorSpk . "*\n";
                $message .= "- *Produk*: " . $barangJadi . "\n";
                $message .= "- *Target Qty*: " . $targetQty . "\n";
                $message .= "- *Qty Hasil Bagus*: *" . $qtySelesai . "*\n";
                $message .= "- *Qty Reject*: *" . $qtyReject . "*\n";
                $message .= "- *Diselesaikan Oleh*: *" . $completerName . "*\n";
                $message .= "- *Waktu Penyelesaian*: " . $tanggalJam . "\n\n";
                $message .= "Barang hasil produksi siap dipindahkan ke Gudang Barang Jadi (FG Warehouse).\n";
                $message .= "Terima kasih atas kerja keras seluruh tim produksi!";

                $response = Http::timeout(3)->connectTimeout(2)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status  = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action'     => 'SPK Completed Alert',
                    'recipient'  => $target . ' (' . $rec['name'] . ')',
                    'status'     => $status,
                    'response'   => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendSpkCompletedNotification error: ' . $e->getMessage());
        }
    }

    public static function sendSpkRejectedNotification($spk, $approverUser = null, $alasan = '')
    {
        $companyId = $spk->company_id ?? session('active_company_id') ?: 1;
        $enabled   = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $apiToken  = self::getDecryptedToken($companyId);

        if (!$enabled || !$apiToken) {
            return;
        }

        try {
            $spk->load([
                'company',
                'branch',
                'barangJadi.satuan',
                'satuan',
                'bom',
                'penanggungJawab',
                'creator',
            ]);

            $companyName   = $spk->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $asalPengajuan = $spk->branch ? 'Cabang ' . $spk->branch->nama_cabang : 'PT Pusat';

            // Kirim notif WA ke pengaju SPK (user yang menginput / created_by)
            $creatorId = $spk->created_by;
            $creator   = $creatorId ? \App\Models\User::with('karyawan')->find($creatorId) : null;
            $crPhone   = null;
            $crName    = null;

            if ($creator) {
                $crName  = $creator->nama_lengkap ?: ($creator->karyawan?->nama_lengkap ?: ($creator->username ?: 'Pengaju SPK'));
                $crPhone = $creator->no_hp ?: ($creator->karyawan?->no_hp ?? null);

                // Jika no_hp belum ketemu di User/relasi karyawan_id, cari via user_id di Karyawan
                if (!$crPhone) {
                    $karyawanByUser = \App\Models\Karyawan::where('user_id', $creator->id)->first();
                    if ($karyawanByUser && !empty($karyawanByUser->no_hp)) {
                        $crPhone = $karyawanByUser->no_hp;
                        if (empty($crName)) $crName = $karyawanByUser->nama_lengkap;
                    }
                }
            }

            // TANPA FALLBACK PIC SAAT DITOLAK: PIC tidak dikirimkan notifikasi penolakan
            if (!$crPhone) {
                \Illuminate\Support\Facades\Log::warning("WA sendSpkRejectedNotification: Nomor HP pengaju SPK (created_by: {$spk->created_by}) untuk SPK {$spk->nomor_spk} tidak ditemukan.");
                return;
            }

            $recipients = [
                [
                    'name'  => $crName,
                    'phone' => $crPhone,
                ]
            ];

            $nomorSpk     = $spk->nomor_spk;
            $barangJadi   = $spk->barangJadi ? ('[' . $spk->barangJadi->kode_barang . '] ' . $spk->barangJadi->nama_barang) : '-';
            $targetSatuan = $spk->satuan ? $spk->satuan->nama_satuan : 'Pcs';
            $targetQty    = number_format($spk->jumlah_rencana, 0, ',', '.') . ' ' . $targetSatuan;
            $deadline     = $spk->target_selesai ? $spk->target_selesai->format('d-m-Y') : 'Belum ditentukan';
            $approverName = $approverUser?->nama_lengkap ?: auth()->user()?->nama_lengkap ?: 'Atasan / Supervisor';
            $tanggalJam   = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';
            $alasanText   = $alasan ?: 'Perlu penyesuaian / revisi.';

            foreach ($recipients as $rec) {
                $target = preg_replace('/[^0-9]/', '', $rec['phone']);
                if (str_starts_with($target, '0')) {
                    $target = '62' . substr($target, 1);
                }
                if (empty($target)) continue;

                $message  = "*SPK PRODUKSI DITOLAK (REJECTED)* ❌\n";
                $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
                $message .= "Halo *" . $rec['name'] . "*,\n";
                $message .= "Mohon maaf, Surat Perintah Kerja (SPK / Work Order) yang Anda ajukan telah *DITOLAK (REJECTED)* oleh atasan.\n\n";
                $message .= "*Detail SPK yang Ditolak:*\n";
                $message .= "- *Perusahaan*: " . $companyName . "\n";
                $message .= "- *Unit*: " . $asalPengajuan . "\n";
                $message .= "- *No. SPK*: *" . $nomorSpk . "*\n";
                $message .= "- *Produk*: " . $barangJadi . "\n";
                $message .= "- *Target Produksi*: " . $targetQty . "\n";
                $message .= "- *Target Selesai*: " . $deadline . "\n";
                $message .= "- *Ditolak Oleh*: *" . $approverName . "*\n";
                $message .= "- *Alasan Penolakan*: *" . $alasanText . "*\n";
                $message .= "- *Waktu Penolakan*: " . $tanggalJam . "\n\n";
                $message .= "Silakan lakukan revisi dan ajukan kembali SPK melalui sistem ERP.\n";
                $message .= "Terima kasih.";

                $response = Http::timeout(3)->connectTimeout(2)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target'  => $target,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status  = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action'     => 'SPK Rejected Alert',
                    'recipient'  => $target . ' (' . $rec['name'] . ')',
                    'status'     => $status,
                    'response'   => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendSpkRejectedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification to Approvers when a Tender is submitted for approval.
     */
    public static function sendTenderSubmissionNotification($tender)
    {
        $companyId = $tender->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$approvalEnabled || !$apiToken) {
            return;
        }

        try {
            $tender->load(['company', 'branch', 'customer', 'sales.department', 'items.barang']);
            $companyName = $tender->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $asalPengajuan = $tender->branch ? 'Cabang ' . $tender->branch->nama_cabang : 'PT Pusat';

            // Find all approvers with permission 'approve-tender'
            $approvers = \App\Models\User::permission('approve-tender')
                ->where(function ($query) use ($companyId, $tender) {
                    $query->whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner']))
                          ->orWhereHas('companies', fn($c) => $c->where('companies.id', $companyId))
                          ->orWhereHas('branches', fn($b) => $b->where('branches.id', $tender->branch_id)
                                                               ->orWhere('branches.company_id', $companyId));
                })
                ->get();

            if ($approvers->isEmpty()) {
                $approvers = \App\Models\User::permission('approve-tender')->get();
            }
            if ($approvers->isEmpty()) {
                // Fallback: Super Admin / Owner / Directors
                $approvers = \App\Models\User::role(['Super Admin', 'Owner', 'Direktur', 'Manager Marketing'])->get();
            }

            $recipients = [];
            foreach ($approvers as $u) {
                $phone = $u->no_hp ?: $u->karyawan?->no_hp;
                if ($phone) {
                    $recipients[] = [
                        'name'  => $u->nama_lengkap,
                        'phone' => $phone,
                    ];
                }
            }

            if (empty($recipients)) return;

            $nomorTender = $tender->nomor_kontrak ?: ('TND-' . $tender->id);
            $namaProyek = $tender->nama_tender;
            $customerName = $tender->customer ? ($tender->customer->nama_perusahaan ?: $tender->customer->nama_customer) : '-';
            $salesName = $tender->sales?->nama_lengkap ?? '-';
            $paguNilai = 'Rp ' . number_format($tender->nilai_estimasi ?: 0, 0, ',', '.');
            $deadline = $tender->deadline ? $tender->deadline->format('d-m-Y') : '-';
            $tanggalJam = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            foreach ($recipients as $rec) {
                $target = preg_replace('/[^0-9]/', '', $rec['phone']);
                if (str_starts_with($target, '0')) {
                    $target = '62' . substr($target, 1);
                }
                if (empty($target)) continue;

                $message  = "🔔 *PENGAJUAN APPROVAL TENDER BARU*\n";
                $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
                $message .= "Halo *" . $rec['name'] . "*,\n";
                $message .= "Terdapat pengajuan proposal tender baru yang *membutuhkan persetujuan (approval)* Anda.\n\n";
                $message .= "*Rincian Proposal Tender:*\n";
                $message .= "- *Perusahaan*: " . $companyName . "\n";
                $message .= "- *Unit Entitas*: " . $asalPengajuan . "\n";
                $message .= "- *No. Tender*: *" . $nomorTender . "*\n";
                $message .= "- *Nama Proyek*: *" . $namaProyek . "*\n";
                $message .= "- *Instansi / Klien*: " . $customerName . " (" . $tender->kategori_instansi . ")\n";
                $message .= "- *Pagu Anggaran / HPS*: *" . $paguNilai . "*\n";
                $message .= "- *Sales PIC*: " . $salesName . "\n";
                $message .= "- *Batas Akhir (Deadline)*: " . $deadline . "\n";
                $message .= "- *Waktu Pengajuan*: " . $tanggalJam . "\n\n";
                $message .= "Silakan login ke sistem ERP untuk memeriksa detail dokumen dan memberikan persetujuan.\n";
                $message .= "Terima kasih.";

                $response = Http::timeout(3)->connectTimeout(2)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target'  => $target,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status  = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action'     => 'Tender Approval Submission',
                    'recipient'  => $target . ' (' . $rec['name'] . ')',
                    'status'     => $status,
                    'response'   => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendTenderSubmissionNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification to Sales PIC when Tender is approved.
     */
    public static function sendTenderApprovedNotification($tender, $approverUser = null)
    {
        $companyId = $tender->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$approvalEnabled || !$apiToken) {
            return;
        }

        try {
            $tender->load(['company', 'branch', 'customer', 'sales', 'creator.karyawan']);
            $companyName = $tender->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';

            // Target: Sales PIC & Creator
            $recipients = [];
            if ($tender->sales) {
                $salesPhone = $tender->sales->no_hp ?: $tender->sales->telepon;
                if (!$salesPhone && $tender->sales->user) {
                    $salesPhone = $tender->sales->user->no_hp;
                }
                if ($salesPhone) {
                    $recipients[] = ['name' => $tender->sales->nama_lengkap, 'phone' => $salesPhone];
                }
            }
            if ($tender->creator) {
                $creatorPhone = $tender->creator->no_hp ?: $tender->creator->karyawan?->no_hp;
                if ($creatorPhone && !in_array($creatorPhone, array_column($recipients, 'phone'))) {
                    $recipients[] = ['name' => $tender->creator->nama_lengkap, 'phone' => $creatorPhone];
                }
            }

            if (empty($recipients)) return;

            $nomorTender = $tender->nomor_kontrak ?: ('TND-' . $tender->id);
            $namaProyek = $tender->nama_tender;
            $customerName = $tender->customer ? ($tender->customer->nama_perusahaan ?: $tender->customer->nama_customer) : '-';
            $paguNilai = 'Rp ' . number_format($tender->nilai_estimasi ?: 0, 0, ',', '.');
            $approverName = $approverUser?->nama_lengkap ?: auth()->user()?->nama_lengkap ?: 'Atasan / Manager';
            $tanggalJam = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            foreach ($recipients as $rec) {
                $target = preg_replace('/[^0-9]/', '', $rec['phone']);
                if (str_starts_with($target, '0')) {
                    $target = '62' . substr($target, 1);
                }
                if (empty($target)) continue;

                $message  = "✅ *PROPOSAL TENDER DISETUJUI (APPROVED)*\n";
                $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
                $message .= "Halo *" . $rec['name'] . "*,\n";
                $message .= "Kabar baik! Proposal tender yang Anda ajukan telah *DISETUJUI (APPROVED)* oleh atasan.\n\n";
                $message .= "*Rincian Tender yang Disetujui:*\n";
                $message .= "- *No. Tender*: *" . $nomorTender . "*\n";
                $message .= "- *Nama Proyek*: *" . $namaProyek . "*\n";
                $message .= "- *Instansi / Klien*: " . $customerName . "\n";
                $message .= "- *Pagu Anggaran*: *" . $paguNilai . "*\n";
                $message .= "- *Disetujui Oleh*: *" . $approverName . "*\n";
                $message .= "- *Waktu Persetujuan*: " . $tanggalJam . "\n\n";
                $message .= "Proposal tender kini siap untuk diajukan ke panitia lelang / klien.\n";
                $message .= "Terima kasih dan semoga berhasil!";

                $response = Http::timeout(3)->connectTimeout(2)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target'  => $target,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status  = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action'     => 'Tender Approved Alert',
                    'recipient'  => $target . ' (' . $rec['name'] . ')',
                    'status'     => $status,
                    'response'   => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendTenderApprovedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification to Sales PIC when Tender is rejected.
     */
    public static function sendTenderRejectedNotification($tender, $rejectorUser = null, $alasan = null)
    {
        $companyId = $tender->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$approvalEnabled || !$apiToken) {
            return;
        }

        try {
            $tender->load(['company', 'branch', 'customer', 'sales', 'creator.karyawan']);
            $companyName = $tender->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';

            // Target: Sales PIC & Creator
            $recipients = [];
            if ($tender->sales) {
                $salesPhone = $tender->sales->no_hp ?: $tender->sales->telepon;
                if (!$salesPhone && $tender->sales->user) {
                    $salesPhone = $tender->sales->user->no_hp;
                }
                if ($salesPhone) {
                    $recipients[] = ['name' => $tender->sales->nama_lengkap, 'phone' => $salesPhone];
                }
            }
            if ($tender->creator) {
                $creatorPhone = $tender->creator->no_hp ?: $tender->creator->karyawan?->no_hp;
                if ($creatorPhone && !in_array($creatorPhone, array_column($recipients, 'phone'))) {
                    $recipients[] = ['name' => $tender->creator->nama_lengkap, 'phone' => $creatorPhone];
                }
            }

            if (empty($recipients)) return;

            $nomorTender = $tender->nomor_kontrak ?: ('TND-' . $tender->id);
            $namaProyek = $tender->nama_tender;
            $customerName = $tender->customer ? ($tender->customer->nama_perusahaan ?: $tender->customer->nama_customer) : '-';
            $rejectorName = $rejectorUser?->nama_lengkap ?: auth()->user()?->nama_lengkap ?: 'Atasan / Manager';
            $alasanText = $alasan ?: 'Perlu penyesuaian spesifikasi atau harga.';
            $tanggalJam = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            foreach ($recipients as $rec) {
                $target = preg_replace('/[^0-9]/', '', $rec['phone']);
                if (str_starts_with($target, '0')) {
                    $target = '62' . substr($target, 1);
                }
                if (empty($target)) continue;

                $message  = "❌ *PROPOSAL TENDER DITOLAK (REJECTED)*\n";
                $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
                $message .= "Halo *" . $rec['name'] . "*,\n";
                $message .= "Mohon maaf, proposal tender yang Anda ajukan telah *DITOLAK (REJECTED)* oleh atasan.\n\n";
                $message .= "*Rincian Penolakan Tender:*\n";
                $message .= "- *No. Tender*: *" . $nomorTender . "*\n";
                $message .= "- *Nama Proyek*: *" . $namaProyek . "*\n";
                $message .= "- *Instansi / Klien*: " . $customerName . "\n";
                $message .= "- *Ditolak Oleh*: *" . $rejectorName . "*\n";
                $message .= "- *Alasan Penolakan*: *" . $alasanText . "*\n";
                $message .= "- *Waktu Penolakan*: " . $tanggalJam . "\n\n";
                $message .= "Silakan lakukan penyesuaian proposal pada sistem ERP dan ajukan kembali.\n";
                $message .= "Terima kasih.";

                $response = Http::timeout(3)->connectTimeout(2)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target'  => $target,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status  = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action'     => 'Tender Rejected Alert',
                    'recipient'  => $target . ' (' . $rec['name'] . ')',
                    'status'     => $status,
                    'response'   => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendTenderRejectedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp broadcast when a Tender is WON (Menang Lelang).
     */
    public static function sendTenderWonNotification($tender)
    {
        $companyId = $tender->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$apiToken) {
            return;
        }

        try {
            $tender->load(['company', 'branch', 'customer', 'sales']);
            $companyName = $tender->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';

            // Broadcast to Approvers & Sales PIC
            $users = \App\Models\User::role(['Super Admin', 'Owner', 'Direktur', 'Manager Marketing'])->get();

            $recipients = [];
            foreach ($users as $u) {
                $phone = $u->no_hp ?: $u->karyawan?->no_hp;
                if ($phone && !in_array($phone, array_column($recipients, 'phone'))) {
                    $recipients[] = ['name' => $u->nama_lengkap, 'phone' => $phone];
                }
            }
            if ($tender->sales) {
                $salesPhone = $tender->sales->no_hp ?: $tender->sales->telepon;
                if ($salesPhone && !in_array($salesPhone, array_column($recipients, 'phone'))) {
                    $recipients[] = ['name' => $tender->sales->nama_lengkap, 'phone' => $salesPhone];
                }
            }

            if (empty($recipients)) return;

            $nomorTender = $tender->nomor_kontrak ?: ('TND-' . $tender->id);
            $namaProyek = $tender->nama_tender;
            $customerName = $tender->customer ? ($tender->customer->nama_perusahaan ?: $tender->customer->nama_customer) : '-';
            $salesName = $tender->sales?->nama_lengkap ?? '-';
            $paguNilai = 'Rp ' . number_format($tender->nilai_estimasi ?: 0, 0, ',', '.');
            $tanggalJam = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            foreach ($recipients as $rec) {
                $target = preg_replace('/[^0-9]/', '', $rec['phone']);
                if (str_starts_with($target, '0')) {
                    $target = '62' . substr($target, 1);
                }
                if (empty($target)) continue;

                $message  = "🏆 *SELAMAT! PROYEK TENDER MENANG (WON)* 🎉\n";
                $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
                $message .= "Halo *" . $rec['name'] . "*,\n";
                $message .= "Kabar gembira! Proyek tender berikut telah berhasil ditetapkan sebagai *PEMENANG (WON)*:\n\n";
                $message .= "*Detail Tender Pemenang:*\n";
                $message .= "- *No. Tender*: *" . $nomorTender . "*\n";
                $message .= "- *Nama Proyek*: *" . $namaProyek . "*\n";
                $message .= "- *Instansi Klien*: " . $customerName . "\n";
                $message .= "- *Nilai Kontrak / HPS*: *" . $paguNilai . "*\n";
                $message .= "- *Sales PIC*: " . $salesName . "\n";
                $message .= "- *Waktu Penetapan*: " . $tanggalJam . "\n\n";
                $message .= "Surat Perintah Kerja (SPK) / Sales Order siap untuk diterbitkan.\n";
                $message .= "Selamat kepada seluruh tim yang bertugas!";

                $response = Http::timeout(3)->connectTimeout(2)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target'  => $target,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status  = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action'     => 'Tender Won Broadcast',
                    'recipient'  => $target . ' (' . $rec['name'] . ')',
                    'status'     => $status,
                    'response'   => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendTenderWonNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification to Approvers when a Sales Quotation is submitted (status = Sent).
     */
    public static function sendQuotationSubmissionNotification($quotation)
    {
        $companyId = $quotation->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$approvalEnabled || !$apiToken) {
            return;
        }

        try {
            $quotation->load(['company', 'branch', 'customer', 'tender', 'sales', 'creator.karyawan']);
            $companyName = $quotation->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $asalPengajuan = $quotation->branch ? 'Cabang ' . $quotation->branch->nama_cabang : 'PT Pusat';

            // Find approvers with permission 'approve-penawaran'
            $approvers = \App\Models\User::permission('approve-penawaran')
                ->where(function ($query) use ($companyId, $quotation) {
                    $query->whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner']))
                          ->orWhereHas('companies', fn($c) => $c->where('companies.id', $companyId))
                          ->orWhereHas('branches', fn($b) => $b->where('branches.id', $quotation->branch_id)
                                                               ->orWhere('branches.company_id', $companyId));
                })
                ->get();

            if ($approvers->isEmpty()) {
                $approvers = \App\Models\User::permission('approve-penawaran')->get();
            }
            if ($approvers->isEmpty()) {
                $approvers = \App\Models\User::role(['Super Admin', 'Owner', 'Direktur', 'Manager Marketing'])->get();
            }

            $recipients = [];
            foreach ($approvers as $u) {
                $phone = $u->no_hp ?: $u->karyawan?->no_hp;
                if ($phone) {
                    $recipients[] = [
                        'name'  => $u->nama_lengkap,
                        'phone' => $phone,
                    ];
                }
            }

            if (empty($recipients)) return;

            $nomorQuotation = $quotation->nomor_quotation . ' (REV.' . $quotation->revisi . ')';
            $customerName = $quotation->customer ? ($quotation->customer->nama_perusahaan ?: $quotation->customer->nama_customer) : '-';
            $namaTender = $quotation->tender ? $quotation->tender->nama_tender : '-';
            $salesName = $quotation->sales?->nama_lengkap ?: $quotation->creator?->nama_lengkap ?: '-';
            $grandTotal = 'Rp ' . number_format($quotation->grand_total ?: 0, 0, ',', '.');
            $marginPersen = number_format($quotation->margin_persen ?: 0, 1, ',', '.') . '%';
            $tanggalJam = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            foreach ($recipients as $rec) {
                $target = preg_replace('/[^0-9]/', '', $rec['phone']);
                if (str_starts_with($target, '0')) {
                    $target = '62' . substr($target, 1);
                }
                if (empty($target)) continue;

                $message  = "🔔 *PENGAJUAN SURAT PENAWARAN HARGA BARU*\n";
                $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
                $message .= "Halo *" . $rec['name'] . "*,\n";
                $message .= "Terdapat Surat Penawaran Harga (Sales Quotation) baru yang *membutuhkan persetujuan (approval)* Anda.\n\n";
                $message .= "*Rincian Penawaran:*\n";
                $message .= "- *Perusahaan*: " . $companyName . "\n";
                $message .= "- *Unit Entitas*: " . $asalPengajuan . "\n";
                $message .= "- *No. Penawaran*: *" . $nomorQuotation . "*\n";
                $message .= "- *Nama Klien / Instansi*: *" . $customerName . "*\n";
                if ($namaTender !== '-') {
                    $message .= "- *Proyek / Tender*: " . $namaTender . "\n";
                }
                $message .= "- *Total Nilai Penawaran*: *" . $grandTotal . "*\n";
                $message .= "- *Estimasi Margin*: *" . $marginPersen . "*\n";
                $message .= "- *Sales PIC*: " . $salesName . "\n";
                $message .= "- *Waktu Pengajuan*: " . $tanggalJam . "\n\n";
                $message .= "Silakan login ke sistem ERP untuk memeriksa rincian penawaran dan memberikan persetujuan.\n";
                $message .= "Terima kasih.";

                $response = Http::timeout(3)->connectTimeout(2)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target'  => $target,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status  = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action'     => 'Quotation Approval Submission',
                    'recipient'  => $target . ' (' . $rec['name'] . ')',
                    'status'     => $status,
                    'response'   => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendQuotationSubmissionNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when a Sales Quotation is approved.
     */
    public static function sendQuotationApprovedNotification($quotation, $approverUser = null)
    {
        $companyId = $quotation->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$approvalEnabled || !$apiToken) {
            return;
        }

        try {
            $quotation->load(['company', 'branch', 'customer', 'tender', 'sales', 'creator.karyawan']);
            $companyName = $quotation->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';

            // Target: Sales PIC & Creator
            $recipients = [];
            $salesUser = $quotation->sales;
            if ($salesUser) {
                $phone = $salesUser->no_hp;
                if ($phone) {
                    $recipients[$phone] = [
                        'name'  => $salesUser->nama_lengkap,
                        'phone' => $phone,
                    ];
                }
            }

            $creatorUser = $quotation->creator;
            if ($creatorUser) {
                $phone = $creatorUser->no_hp ?: $creatorUser->karyawan?->no_hp;
                if ($phone && !isset($recipients[$phone])) {
                    $recipients[$phone] = [
                        'name'  => $creatorUser->nama_lengkap,
                        'phone' => $phone,
                    ];
                }
            }

            if (empty($recipients)) return;

            $nomorQuotation = $quotation->nomor_quotation . ' (REV.' . $quotation->revisi . ')';
            $customerName = $quotation->customer ? ($quotation->customer->nama_perusahaan ?: $quotation->customer->nama_customer) : '-';
            $grandTotal = 'Rp ' . number_format($quotation->grand_total ?: 0, 0, ',', '.');
            $approverName = $approverUser?->nama_lengkap ?: auth()->user()?->nama_lengkap ?: 'Manajemen';
            $tanggalJam = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            foreach ($recipients as $rec) {
                $target = preg_replace('/[^0-9]/', '', $rec['phone']);
                if (str_starts_with($target, '0')) {
                    $target = '62' . substr($target, 1);
                }
                if (empty($target)) continue;

                $message  = "✅ *SURAT PENAWARAN HARGA DISETUJUI (APPROVED)*\n";
                $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
                $message .= "Halo *" . $rec['name'] . "*,\n";
                $message .= "Kabar baik! Surat Penawaran Harga berikut telah *disetujui (Approved)*:\n\n";
                $message .= "*Rincian Penawaran:*\n";
                $message .= "- *No. Penawaran*: *" . $nomorQuotation . "*\n";
                $message .= "- *Nama Klien / Instansi*: *" . $customerName . "*\n";
                $message .= "- *Total Nilai Penawaran*: *" . $grandTotal . "*\n";
                $message .= "- *Disetujui Oleh*: *" . $approverName . "*\n";
                $message .= "- *Waktu Approval*: " . $tanggalJam . "\n\n";
                $message .= "Dokumen resmi siap dicetak PDF dan dikirimkan kepada klien atau dilanjutkan ke penerbitan Sales Order (SO).\n";
                $message .= "Terima kasih atas kerja samanya!";

                $response = Http::timeout(3)->connectTimeout(2)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target'  => $target,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status  = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action'     => 'Quotation Approved Notification',
                    'recipient'  => $target . ' (' . $rec['name'] . ')',
                    'status'     => $status,
                    'response'   => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendQuotationApprovedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when a Sales Quotation is rejected.
     */
    public static function sendQuotationRejectedNotification($quotation, $rejectorUser = null, $alasan = null)
    {
        $companyId = $quotation->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$approvalEnabled || !$apiToken) {
            return;
        }

        try {
            $quotation->load(['company', 'branch', 'customer', 'tender', 'sales', 'creator.karyawan']);
            $companyName = $quotation->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';

            // Target: Sales PIC & Creator
            $recipients = [];
            $salesUser = $quotation->sales;
            if ($salesUser) {
                $phone = $salesUser->no_hp;
                if ($phone) {
                    $recipients[$phone] = [
                        'name'  => $salesUser->nama_lengkap,
                        'phone' => $phone,
                    ];
                }
            }

            $creatorUser = $quotation->creator;
            if ($creatorUser) {
                $phone = $creatorUser->no_hp ?: $creatorUser->karyawan?->no_hp;
                if ($phone && !isset($recipients[$phone])) {
                    $recipients[$phone] = [
                        'name'  => $creatorUser->nama_lengkap,
                        'phone' => $phone,
                    ];
                }
            }

            if (empty($recipients)) return;

            $nomorQuotation = $quotation->nomor_quotation . ' (REV.' . $quotation->revisi . ')';
            $customerName = $quotation->customer ? ($quotation->customer->nama_perusahaan ?: $quotation->customer->nama_customer) : '-';
            $grandTotal = 'Rp ' . number_format($quotation->grand_total ?: 0, 0, ',', '.');
            $rejectorName = $rejectorUser?->nama_lengkap ?: auth()->user()?->nama_lengkap ?: 'Manajemen';
            $tanggalJam = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            foreach ($recipients as $rec) {
                $target = preg_replace('/[^0-9]/', '', $rec['phone']);
                if (str_starts_with($target, '0')) {
                    $target = '62' . substr($target, 1);
                }
                if (empty($target)) continue;

                $message  = "❌ *SURAT PENAWARAN HARGA DITOLAK (REJECTED)*\n";
                $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
                $message .= "Halo *" . $rec['name'] . "*,\n";
                $message .= "Surat Penawaran Harga berikut ditandai sebagai *Ditolak (Rejected)*:\n\n";
                $message .= "*Rincian Penawaran:*\n";
                $message .= "- *No. Penawaran*: *" . $nomorQuotation . "*\n";
                $message .= "- *Nama Klien / Instansi*: *" . $customerName . "*\n";
                $message .= "- *Total Nilai Penawaran*: *" . $grandTotal . "*\n";
                $message .= "- *Ditolak Oleh*: *" . $rejectorName . "*\n";
                $message .= "- *Waktu*: " . $tanggalJam . "\n";
                if ($alasan) {
                    $message .= "- *Catatan/Alasan*: _" . $alasan . "_\n";
                }
                $message .= "\nSilakan buat revisi baru dengan menyesuaikan harga atau ketentuan yang diperlukan.\n";
                $message .= "Terima kasih.";

                $response = Http::timeout(3)->connectTimeout(2)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target'  => $target,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status  = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action'     => 'Quotation Rejected Notification',
                    'recipient'  => $target . ' (' . $rec['name'] . ')',
                    'status'     => $status,
                    'response'   => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendQuotationRejectedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when a Sales Quotation is converted to Sales Order.
     */
    public static function sendQuotationConvertedToSoNotification($quotation, $salesOrder)
    {
        $companyId = $quotation->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$apiToken) {
            return;
        }

        try {
            $quotation->load(['company', 'branch', 'customer', 'sales', 'creator.karyawan']);
            $companyName = $quotation->company->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';

            // Target: Sales PIC & Creator
            $recipients = [];
            $salesUser = $quotation->sales;
            if ($salesUser) {
                $phone = $salesUser->no_hp;
                if ($phone) {
                    $recipients[$phone] = [
                        'name'  => $salesUser->nama_lengkap,
                        'phone' => $phone,
                    ];
                }
            }

            $creatorUser = $quotation->creator;
            if ($creatorUser) {
                $phone = $creatorUser->no_hp ?: $creatorUser->karyawan?->no_hp;
                if ($phone && !isset($recipients[$phone])) {
                    $recipients[$phone] = [
                        'name'  => $creatorUser->nama_lengkap,
                        'phone' => $phone,
                    ];
                }
            }

            if (empty($recipients)) return;

            $nomorQuotation = $quotation->nomor_quotation;
            $nomorSo = $salesOrder->nomor_so;
            $customerName = $quotation->customer ? ($quotation->customer->nama_perusahaan ?: $quotation->customer->nama_customer) : '-';
            $grandTotal = 'Rp ' . number_format($salesOrder->total ?: $quotation->grand_total ?: 0, 0, ',', '.');
            $tanggalJam = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            foreach ($recipients as $rec) {
                $target = preg_replace('/[^0-9]/', '', $rec['phone']);
                if (str_starts_with($target, '0')) {
                    $target = '62' . substr($target, 1);
                }
                if (empty($target)) continue;

                $message  = "🚀 *PENAWARAN HARGA RESMI JADI SALES ORDER (SO)*\n";
                $message .= "*" . mb_strtoupper($companyName) . "*\n\n";
                $message .= "Halo *" . $rec['name'] . "*,\n";
                $message .= "Surat Penawaran Harga berhasil dikonversi menjadi *Sales Order (SO)*:\n\n";
                $message .= "*Rincian Transaksi:*\n";
                $message .= "- *No. Penawaran Asal*: *" . $nomorQuotation . "*\n";
                $message .= "- *No. Sales Order*: *" . $nomorSo . "*\n";
                $message .= "- *Nama Klien / Instansi*: *" . $customerName . "*\n";
                $message .= "- *Total Nilai Transaksi*: *" . $grandTotal . "*\n";
                $message .= "- *Waktu Konversi*: " . $tanggalJam . "\n\n";
                $message .= "Pesanan penjualan siap diproses oleh tim gudang dan tim operasional produksi.\n";
                $message .= "Semangat bertugas!";

                $response = Http::timeout(3)->connectTimeout(2)->withHeaders([
                    'Authorization' => $apiToken,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target'  => $target,
                    'message' => $message,
                ]);

                $resBody = $response->json();
                $status  = ($response->successful() && ($resBody['status'] ?? false)) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action'     => 'Quotation to SO Conversion Alert',
                    'recipient'  => $target . ' (' . $rec['name'] . ')',
                    'status'     => $status,
                    'response'   => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendQuotationConvertedToSoNotification error: ' . $e->getMessage());
        }
    }

    /**
     * ─────────────────────────────────────────────────────────────────────
     * PURCHASE PAYMENT APPROVAL NOTIFICATIONS
     * ─────────────────────────────────────────────────────────────────────
     */

    /**
     * Kirim notifikasi WhatsApp ke pihak approver saat pembayaran utang diajukan (Pending Approval).
     */
    public static function sendPaymentSubmissionNotification($payment)
    {
        $companyId = $payment->company_id ?? session('active_company_id') ?: 1;
        $enabled   = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $apprvOk   = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $apiToken  = self::getDecryptedToken($companyId);

        if (!$enabled || !$apprvOk || !$apiToken) return;

        try {
            $payment->loadMissing(['supplier', 'currency', 'creator.karyawan', 'submitter.karyawan', 'company', 'branch', 'details.invoice']);
            $companyName   = $payment->company?->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $asalPengajuan = $payment->branch ? 'Cabang ' . $payment->branch->nama_cabang : 'PT Pusat';

            // Ambil seluruh approver yang memiliki izin 'approve-pembayaran-utang'
            $approvers = \App\Models\User::permission('approve-pembayaran-utang')
                ->with('karyawan')
                ->where(function ($query) use ($companyId) {
                    $query->whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner']))
                          ->orWhereHas('companies', fn($c) => $c->where('companies.id', $companyId))
                          ->orDoesntHave('companies');
                })
                ->get();

            if ($approvers->isEmpty()) return;

            $nomorBayar    = $payment->nomor_pembayaran;
            $supplierName  = $payment->supplier?->nama_supplier ?? '-';
            $curCode       = $payment->currency?->kode_currency ?? 'IDR';
            $curSymbol     = $payment->currency?->simbol ?? ($curCode === 'IDR' ? 'Rp' : $curCode);
            $totalBayarFmt = $curSymbol . ' ' . number_format((float)$payment->total_bayar, 2, ',', '.');
            $submitterName = $payment->submitter?->nama_lengkap ?: $payment->creator?->nama_lengkap ?: $payment->creator?->name ?: 'Staff';
            $tanggalJam    = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i') . ' WIB';
            $tanggalBayar  = $payment->tanggal_bayar ? $payment->tanggal_bayar->format('d-m-Y') : '-';
            $keterangan    = $payment->keterangan ? $payment->keterangan : '-';

            // Ringkasan faktur
            $fakturSummary = '';
            if ($payment->details && $payment->details->isNotEmpty()) {
                $fakturSummary .= "*Faktur Dibayar:*\n";
                foreach ($payment->details->take(5) as $idx => $det) {
                    $noFak = $det->invoice?->nomor_faktur ?? ('Faktur #' . $det->purchase_invoice_id);
                    $bayarFmt = number_format((float)$det->jumlah_bayar, 2, ',', '.');
                    $fakturSummary .= "  " . ($idx + 1) . ". {$noFak} (Bayar: {$curSymbol} {$bayarFmt})\n";
                }
                if ($payment->details->count() > 5) {
                    $fakturSummary .= "  ...dan " . ($payment->details->count() - 5) . " faktur lainnya.\n";
                }
            }

            foreach ($approvers as $ap) {
                $rawPhone = $ap->no_hp ?: $ap->karyawan?->no_hp ?: $ap->karyawan?->telepon;
                if (!$rawPhone) continue;

                $target = preg_replace('/[^0-9]/', '', $rawPhone);
                if (str_starts_with($target, '0')) {
                    $target = '62' . substr($target, 1);
                } elseif (str_starts_with($target, '8')) {
                    $target = '62' . $target;
                }
                if (empty($target)) continue;

                $msg  = "🔔 *PENGAJUAN PEMBAYARAN UTANG (MENUNGGU PERSETUJUAN)*\n";
                $msg .= "*" . mb_strtoupper($companyName) . "*\n\n";
                $msg .= "Halo *{$ap->name}*,\n";
                $msg .= "Terdapat dokumen pengajuan pembayaran utang baru yang membutuhkan persetujuan Anda.\n\n";
                $msg .= "*Rincian Dokumen:*\n";
                $msg .= "- *Unit / Asal*: {$asalPengajuan}\n";
                $msg .= "- *No. Dokumen*: *{$nomorBayar}*\n";
                $msg .= "- *Supplier*: *{$supplierName}*\n";
                $msg .= "- *Tgl. Bayar*: {$tanggalBayar}\n";
                $msg .= "- *Total Pembayaran*: *{$totalBayarFmt}*\n";
                $msg .= "- *Diajukan Oleh*: {$submitterName}\n";
                $msg .= "- *Waktu Pengajuan*: {$tanggalJam}\n";
                $msg .= "- *Catatan*: {$keterangan}\n\n";
                if ($fakturSummary) {
                    $msg .= $fakturSummary . "\n";
                }
                $msg .= "Silakan masuk ke sistem ERP untuk melakukan peninjauan dan persetujuan.\n";
                $msg .= "Terima kasih.";

                $res  = Http::timeout(4)->connectTimeout(2)->withHeaders(['Authorization' => $apiToken])->asForm()->post('https://api.fonnte.com/send', ['target' => $target, 'message' => $msg]);
                $body = $res->json();

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action'     => 'Payment Submission Alert',
                    'recipient'  => $target . ' (' . $ap->name . ')',
                    'status'     => ($res->successful() && ($body['status'] ?? false)) ? 'Success' : 'Failed',
                    'response'   => json_encode($body),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendPaymentSubmissionNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi WhatsApp ke pembuat/pengaju saat pembayaran disetujui dan diposting.
     */
    public static function sendPaymentApprovedNotification($payment, $approverUser = null)
    {
        $companyId = $payment->company_id ?? session('active_company_id') ?: 1;
        $enabled   = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $apprvOk   = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $apiToken  = self::getDecryptedToken($companyId);

        if (!$enabled || !$apprvOk || !$apiToken) return;

        try {
            $payment->loadMissing(['supplier', 'currency', 'creator.karyawan', 'submitter.karyawan', 'company', 'branch']);
            $companyName  = $payment->company?->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $nomorBayar   = $payment->nomor_pembayaran;
            $supplierName = $payment->supplier?->nama_supplier ?? '-';
            $curCode      = $payment->currency?->kode_currency ?? 'IDR';
            $curSymbol    = $payment->currency?->simbol ?? ($curCode === 'IDR' ? 'Rp' : $curCode);
            $totalBayarFmt= $curSymbol . ' ' . number_format((float)$payment->total_bayar, 2, ',', '.');
            $approverName = $approverUser?->nama_lengkap ?: $approverUser?->name ?: auth()->user()?->nama_lengkap ?: 'Manajemen';
            $tanggalJam   = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i') . ' WIB';

            // Kumpulkan pihak pembuat & pengaju
            $recipients = [];
            foreach ([$payment->creator, $payment->submitter] as $u) {
                if (!$u) continue;
                $phone = $u->no_hp ?: $u->karyawan?->no_hp ?: $u->karyawan?->telepon;
                if ($phone && !isset($recipients[$phone])) {
                    $recipients[$phone] = $u;
                }
            }

            if (empty($recipients)) return;

            foreach ($recipients as $rawPhone => $user) {
                $target = preg_replace('/[^0-9]/', '', $rawPhone);
                if (str_starts_with($target, '0')) {
                    $target = '62' . substr($target, 1);
                } elseif (str_starts_with($target, '8')) {
                    $target = '62' . $target;
                }
                if (empty($target)) continue;

                $msg  = "✅ *PEMBAYARAN UTANG DISETUJUI & DIPOSTING*\n";
                $msg .= "*" . mb_strtoupper($companyName) . "*\n\n";
                $msg .= "Halo *{$user->name}*,\n";
                $msg .= "Kabar baik! Pengajuan pembayaran utang Anda telah *disetujui* dan *berhasil diposting*.\n\n";
                $msg .= "*Rincian Transaksi:*\n";
                $msg .= "- *No. Dokumen*: *{$nomorBayar}*\n";
                $msg .= "- *Supplier*: *{$supplierName}*\n";
                $msg .= "- *Total Pembayaran*: *{$totalBayarFmt}*\n";
                $msg .= "- *Disetujui Oleh*: *{$approverName}*\n";
                $msg .= "- *Waktu Persetujuan*: {$tanggalJam}\n\n";
                $msg .= "Jurnal Akuntansi dan Mutasi Pengeluaran Kas/Bank telah diperbarui secara otomatis.\n";
                $msg .= "Terima kasih.";

                $res  = Http::timeout(4)->connectTimeout(2)->withHeaders(['Authorization' => $apiToken])->asForm()->post('https://api.fonnte.com/send', ['target' => $target, 'message' => $msg]);
                $body = $res->json();

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action'     => 'Payment Approved Alert',
                    'recipient'  => $target . ' (' . $user->name . ')',
                    'status'     => ($res->successful() && ($body['status'] ?? false)) ? 'Success' : 'Failed',
                    'response'   => json_encode($body),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendPaymentApprovedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi WhatsApp ke pembuat/pengaju saat pembayaran ditolak.
     */
    public static function sendPaymentRejectedNotification($payment, $rejectorUser = null, $alasan = null)
    {
        $companyId = $payment->company_id ?? session('active_company_id') ?: 1;
        $enabled   = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $apprvOk   = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $apiToken  = self::getDecryptedToken($companyId);

        if (!$enabled || !$apprvOk || !$apiToken) return;

        try {
            $payment->loadMissing(['supplier', 'currency', 'creator.karyawan', 'submitter.karyawan', 'company', 'branch']);
            $companyName  = $payment->company?->nama_perusahaan ?? \App\Models\Company::find($companyId)?->nama_perusahaan ?? 'Perusahaan';
            $nomorBayar   = $payment->nomor_pembayaran;
            $supplierName = $payment->supplier?->nama_supplier ?? '-';
            $curCode      = $payment->currency?->kode_currency ?? 'IDR';
            $curSymbol    = $payment->currency?->simbol ?? ($curCode === 'IDR' ? 'Rp' : $curCode);
            $totalBayarFmt= $curSymbol . ' ' . number_format((float)$payment->total_bayar, 2, ',', '.');
            $rejectorName = $rejectorUser?->nama_lengkap ?: $rejectorUser?->name ?: auth()->user()?->nama_lengkap ?: 'Manajemen';
            $tanggalJam   = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i') . ' WIB';

            // Kumpulkan pihak pembuat & pengaju
            $recipients = [];
            foreach ([$payment->creator, $payment->submitter] as $u) {
                if (!$u) continue;
                $phone = $u->no_hp ?: $u->karyawan?->no_hp ?: $u->karyawan?->telepon;
                if ($phone && !isset($recipients[$phone])) {
                    $recipients[$phone] = $u;
                }
            }

            if (empty($recipients)) return;

            foreach ($recipients as $rawPhone => $user) {
                $target = preg_replace('/[^0-9]/', '', $rawPhone);
                if (str_starts_with($target, '0')) {
                    $target = '62' . substr($target, 1);
                } elseif (str_starts_with($target, '8')) {
                    $target = '62' . $target;
                }
                if (empty($target)) continue;

                $msg  = "❌ *PEMBERITAHUAN: PENGAJUAN PEMBAYARAN DITOLAK*\n";
                $msg .= "*" . mb_strtoupper($companyName) . "*\n\n";
                $msg .= "Halo *{$user->name}*,\n";
                $msg .= "Pengajuan pembayaran utang Anda telah *ditolak* oleh pihak manajemen/approver.\n\n";
                $msg .= "*Rincian Dokumen:*\n";
                $msg .= "- *No. Dokumen*: *{$nomorBayar}*\n";
                $msg .= "- *Supplier*: *{$supplierName}*\n";
                $msg .= "- *Total*: *{$totalBayarFmt}*\n";
                $msg .= "- *Ditinjau Oleh*: *{$rejectorName}*\n";
                $msg .= "- *Waktu*: {$tanggalJam}\n";
                if ($alasan) {
                    $msg .= "- *Catatan/Alasan Penolakan*: _{$alasan}_\n";
                }
                $msg .= "\nSilakan lakukan penyesuaian/perbaikan data pada dokumen tersebut untuk kemudian diajukan kembali.\n";
                $msg .= "Terima kasih.";

                $res  = Http::timeout(4)->connectTimeout(2)->withHeaders(['Authorization' => $apiToken])->asForm()->post('https://api.fonnte.com/send', ['target' => $target, 'message' => $msg]);
                $body = $res->json();

                WhatsAppLog::create([
                    'company_id' => $companyId,
                    'action'     => 'Payment Rejected Alert',
                    'recipient'  => $target . ' (' . $user->name . ')',
                    'status'     => ($res->successful() && ($body['status'] ?? false)) ? 'Success' : 'Failed',
                    'response'   => json_encode($body),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendPaymentRejectedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Format phone number to international Indonesian format (628xxx).
     */
    public static function formatPhoneNumber($phone)
    {
        if (!$phone) return null;
        $clean = preg_replace('/[^0-9]/', '', (string)$phone);
        if (!$clean) return null;
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '62' . $clean;
        }
        return $clean;
    }

    /**
     * Dispatch WhatsApp message to Fonnte API with safety and timeout.
     */
    public static function dispatchFonnteMessage($apiToken, $targetPhone, $message)
    {
        try {
            $response = Http::timeout(5)->connectTimeout(3)->withHeaders([
                'Authorization' => $apiToken,
            ])->asForm()->post('https://api.fonnte.com/send', [
                'target'  => $targetPhone,
                'message' => $message,
            ]);
            return $response->json() ?: ['status' => false];
        } catch (\Exception $e) {
            Log::error('WA dispatchFonnteMessage error: ' . $e->getMessage());
            return ['status' => false, 'error' => $e->getMessage()];
        }
    }
}
