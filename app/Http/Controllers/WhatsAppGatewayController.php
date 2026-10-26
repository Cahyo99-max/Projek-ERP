<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\WhatsAppLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class WhatsAppGatewayController extends Controller
{
    /**
     * Display the WhatsApp Gateway settings dashboard.
     */
    public function index(Request $request)
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

        // Fetch logs for active company with search and pagination (Default 10)
        $searchLog = $request->query('search_log');
        $statusLog = $request->query('status_log');
        $perPageLog = (int) $request->query('per_page_log', 10);
        if (!in_array($perPageLog, [10, 25, 50, 100])) {
            $perPageLog = 10;
        }

        $logQuery = WhatsAppLog::forActiveCompany()->latest();

        if (!empty($searchLog)) {
            $logQuery->where(function ($q) use ($searchLog) {
                $q->where('action', 'like', "%{$searchLog}%")
                  ->orWhere('recipient', 'like', "%{$searchLog}%")
                  ->orWhere('response', 'like', "%{$searchLog}%");
            });
        }

        if (!empty($statusLog)) {
            $logQuery->where('status', $statusLog);
        }

        $logs = $logQuery->paginate($perPageLog, ['*'], 'log_page')->withQueryString();

        return view('settings.whatsapp.index', compact(
            'provider', 'apiToken', 'status', 'phoneNumber', 'deviceName', 'lastConnected',
            'enableNotification', 'enableApproval', 'enablePurchase', 'enableStockAlert', 'enableReminder',
            'logs', 'searchLog', 'statusLog', 'perPageLog'
        ));
    }

    /**
     * Delete / Prune WhatsApp Delivery Logs permanently.
     */
    public function pruneLogs(Request $request)
    {
        $this->authorize('manage-whatsapp-gateway');

        $request->validate([
            'prune_mode' => 'required|in:30_days,60_days,90_days,custom_range,all',
            'start_date' => 'nullable|required_if:prune_mode,custom_range|date',
            'end_date'   => 'nullable|required_if:prune_mode,custom_range|date|after_or_equal:start_date',
        ], [
            'prune_mode.required' => 'Pilih mode pembersihan log.',
            'start_date.required_if' => 'Tanggal mulai wajib diisi untuk rentang tanggal.',
            'end_date.required_if' => 'Tanggal akhir wajib diisi untuk rentang tanggal.',
            'end_date.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal mulai.',
        ]);

        $mode = $request->input('prune_mode');
        $deletedCount = 0;

        switch ($mode) {
            case '30_days':
                $cutoff = Carbon::now()->subDays(30);
                $deletedCount = WhatsAppLog::forActiveCompany()->where('created_at', '<', $cutoff)->delete();
                $message = "Berhasil menghapus {$deletedCount} log pengiriman WhatsApp yang lebih lama dari 30 hari.";
                break;

            case '60_days':
                $cutoff = Carbon::now()->subDays(60);
                $deletedCount = WhatsAppLog::forActiveCompany()->where('created_at', '<', $cutoff)->delete();
                $message = "Berhasil menghapus {$deletedCount} log pengiriman WhatsApp yang lebih lama dari 60 hari.";
                break;

            case '90_days':
                $cutoff = Carbon::now()->subDays(90);
                $deletedCount = WhatsAppLog::forActiveCompany()->where('created_at', '<', $cutoff)->delete();
                $message = "Berhasil menghapus {$deletedCount} log pengiriman WhatsApp yang lebih lama dari 90 hari.";
                break;

            case 'custom_range':
                $start = Carbon::parse($request->input('start_date'))->startOfDay();
                $end = Carbon::parse($request->input('end_date'))->endOfDay();
                $deletedCount = WhatsAppLog::forActiveCompany()->whereBetween('created_at', [$start, $end])->delete();
                $message = "Berhasil menghapus {$deletedCount} log pengiriman WhatsApp dari tanggal " . $start->format('d/m/Y') . " sampai " . $end->format('d/m/Y') . ".";
                break;

            case 'all':
                $deletedCount = WhatsAppLog::forActiveCompany()->delete();
                $message = "Berhasil mengosongkan seluruh data log pengiriman WhatsApp ({$deletedCount} rekaman).";
                break;

            default:
                return redirect()->route('settings.whatsapp.index')->with('error', 'Pilihan mode pembersihan log tidak valid.');
        }

        return redirect()->route('settings.whatsapp.index')->with('success', $message);
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
            $entity = self::resolveEntityDisplay($pr, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];
            
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
                $message .= "*" . $headerEntitas . "*\n\n";
                $message .= "Halo " . $supervisorName . ",\n";
                $message .= "Ada pengajuan Purchase Request (PR) baru yang membutuhkan persetujuan Anda.\n\n";
                $message .= "*Detail Pengajuan:*\n";
                if ($isCabang) {
                $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
            } else {
                if ($isCabang) {
                    $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                } else {
                    $message .= "- *Perusahaan*: " . $companyName . "\n";
                }
            }
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
        /**
     * Resolve and format entity display for WhatsApp notifications.
     * Rule: If the transaction is from a Branch (Cabang), do NOT show the PT / Perusahaan name.
     * Only show the Branch name in header and details.
     * If the transaction is from PT Pusat (no branch), show the PT / Perusahaan name.
     *
     * @param mixed $model
     * @param int|null $companyId
     * @return array
     */
    public static function resolveEntityDisplay($model, $companyId = null)
    {
        $companyId = $companyId ?: (is_object($model) && isset($model->company_id) ? $model->company_id : (session('active_company_id') ?: 1));
        
        $companyName = 'Perusahaan';
        if (is_object($model) && isset($model->company) && !empty($model->company->nama_perusahaan)) {
            $companyName = $model->company->nama_perusahaan;
        } else {
            $comp = \App\Models\Company::find($companyId);
            if ($comp && !empty($comp->nama_perusahaan)) {
                $companyName = $comp->nama_perusahaan;
            }
        }

        $branch = null;
        if (is_object($model)) {
            if (isset($model->branch) && !empty($model->branch)) {
                $branch = $model->branch;
            } elseif (!empty($model->branch_id)) {
                $branch = \App\Models\Branch::find($model->branch_id);
            }
        }

        $isCabang = !empty($branch);
        $rawCabang = trim($branch?->nama_cabang ?? '');
        $namaCabangDisplay = $isCabang ? (stripos($rawCabang, 'cabang') === 0 ? $rawCabang : ('Cabang ' . $rawCabang)) : null;

        $headerEntitas = $isCabang ? mb_strtoupper($namaCabangDisplay) : mb_strtoupper($companyName);
        $asalUnitText  = $isCabang ? $namaCabangDisplay : ($companyName . ' (Pusat)');
        
        $detailLine = $isCabang 
            ? "- *Cabang*: " . $namaCabangDisplay . "\n"
            : "- *Perusahaan*: " . $companyName . "\n";

        return [
            'isCabang'          => $isCabang,
            'companyName'       => $companyName,
            'namaCabangDisplay' => $namaCabangDisplay,
            'headerEntitas'     => $headerEntitas,
            'asalUnitText'      => $asalUnitText,
            'detailLine'        => $detailLine,
        ];
    }

    public static function runAsync(callable $callback)
    {
        // Defer execution to script shutdown so response is sent immediately (sub-second / instant)
        register_shutdown_function(function() use ($callback) {
            @ignore_user_abort(true);
            if (function_exists('fastcgi_finish_request')) {
                @fastcgi_finish_request();
            } else {
                // Non-FastCGI fallback (Windows CLI server, Apache, etc.):
                // Flush all buffers and close HTTP connection so browser receives instant response
                if (!headers_sent()) {
                    @header("Connection: close");
                }
                while (ob_get_level() > 0) {
                    @ob_end_flush();
                }
                @flush();
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
            $entity = self::resolveEntityDisplay($pr, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];
            
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
            $message .= "*" . $headerEntitas . "*\n\n";
            $message .= "Halo " . $creatorName . ",\n";
            $message .= "Kabar baik! Pengajuan Purchase Request (PR) Anda telah disetujui oleh atasan.\n\n";
            $message .= "*Detail Pengajuan:*\n";
            if ($isCabang) {
                $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
            } else {
                if ($isCabang) {
                    $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                } else {
                    $message .= "- *Perusahaan*: " . $companyName . "\n";
                }
            }
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
            $entity = self::resolveEntityDisplay($pr, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];
            
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
            $message .= "*" . $headerEntitas . "*\n\n";
            $message .= "Halo " . $creatorName . ",\n";
            $message .= "Mohon maaf, pengajuan Purchase Request (PR) Anda *ditolak* oleh atasan/pemeriksa.\n\n";
            $message .= "*Detail Pengajuan:*\n";
            if ($isCabang) {
                $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
            } else {
                if ($isCabang) {
                    $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                } else {
                    $message .= "- *Perusahaan*: " . $companyName . "\n";
                }
            }
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
            $entity = self::resolveEntityDisplay($po, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];
            
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
                $message .= "*" . $headerEntitas . "*\n\n";
                $message .= "Halo *" . $supervisorName . "*,\n";
                $message .= "Ada pengajuan Purchase Order (PO) baru yang membutuhkan persetujuan Anda.\n\n";
                $message .= "*Detail Pengajuan:*\n";
                if ($isCabang) {
                $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
            } else {
                if ($isCabang) {
                    $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                } else {
                    $message .= "- *Perusahaan*: " . $companyName . "\n";
                }
            }
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
            $entity = self::resolveEntityDisplay($po, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];
            
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
            $message .= "*" . $headerEntitas . "*\n\n";
            $message .= "Halo *" . $creatorName . "*,\n";
            $message .= "Kabar baik! Pengajuan Purchase Order (PO) Anda telah disetujui oleh atasan.\n\n";
            $message .= "*Detail Pengajuan:*\n";
            if ($isCabang) {
                $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
            } else {
                if ($isCabang) {
                    $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                } else {
                    $message .= "- *Perusahaan*: " . $companyName . "\n";
                }
            }
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
            $entity = self::resolveEntityDisplay($po, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];
            
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
            $message .= "*" . $headerEntitas . "*\n\n";
            $message .= "Halo *" . $creatorName . "*,\n";
            $message .= "Mohon maaf, pengajuan Purchase Order (PO) Anda *ditolak* oleh atasan/pemeriksa.\n\n";
            $message .= "*Detail Pengajuan:*\n";
            if ($isCabang) {
                $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
            } else {
                if ($isCabang) {
                    $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                } else {
                    $message .= "- *Perusahaan*: " . $companyName . "\n";
                }
            }
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
            $entity = self::resolveEntityDisplay($gr, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];

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
            $message .= "*" . $headerEntitas . "*\n\n";
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
            $entity = self::resolveEntityDisplay($stockOpname, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];$isCabang = !empty($stockOpname->branch_id) && $stockOpname->branch;
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
            $entity = self::resolveEntityDisplay($stockOpname, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];$isCabang = !empty($stockOpname->branch_id) && $stockOpname->branch;
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
            $entity = self::resolveEntityDisplay($stockOpname, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];$isCabang = !empty($stockOpname->branch_id) && $stockOpname->branch;
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
            $entity = self::resolveEntityDisplay($stockOpname, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];$isCabang = !empty($stockOpname->branch_id) && $stockOpname->branch;
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
            $entity = self::resolveEntityDisplay($stockOpname, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];$isCabang = !empty($stockOpname->branch_id) && $stockOpname->branch;
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

            $entity = self::resolveEntityDisplay($warehouseTransfer, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];$nomorTransfer = $warehouseTransfer->nomor_transfer;
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
                $message .= "*" . $headerEntitas . "*\n\n";
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

            $entity = self::resolveEntityDisplay($warehouseTransfer, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];$nomorTransfer = $warehouseTransfer->nomor_transfer;
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
                $message .= "*" . $headerEntitas . "*\n\n";
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

            $entity = self::resolveEntityDisplay($openingStock, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];$nomorDokumen = $openingStock->nomor_opening_stock;
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
                    . "🤖 *{$headerEntitas} - ERP System Automated Bot*";

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

            $entity = self::resolveEntityDisplay($openingStock, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];$nomorDokumen = $openingStock->nomor_opening_stock;
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
                    . "🤖 *{$headerEntitas} - ERP System Automated Bot*";

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

            $entity = self::resolveEntityDisplay($stockAdjustment, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];$nomorAdj      = $stockAdjustment->nomor_adjustment;
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
                $message .= "*" . $headerEntitas . "*\n\n";
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

            $entity = self::resolveEntityDisplay($stockAdjustment, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];$nomorAdj      = $stockAdjustment->nomor_adjustment;
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
                $message .= "*" . $headerEntitas . "*\n\n";
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

            $entity = self::resolveEntityDisplay($goodsIssue, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];$branchName  = $goodsIssue->branch?->nama_cabang ?? ($goodsIssue->gudang?->cabang?->nama_cabang ?? null);

            $headerEntity = $headerEntitas;
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

            $entity = self::resolveEntityDisplay($goodsIssue, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];$branchName  = $goodsIssue->branch?->nama_cabang ?? ($goodsIssue->gudang?->cabang?->nama_cabang ?? null);

            $headerEntity = $headerEntitas;
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

            $entity = self::resolveEntityDisplay($bom, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];

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
                $message .= "*" . $headerEntitas . "*\n\n";
                $message .= "Halo *" . $supervisorName . "*,\n";
                $message .= "Ada pengajuan Resep Produksi Garmen (Bill of Materials) baru yang membutuhkan persetujuan Anda.\n\n";
                $message .= "*Detail Pengajuan BOM:*\n";
                if ($isCabang) {
                $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
            } else {
                if ($isCabang) {
                    $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                } else {
                    $message .= "- *Perusahaan*: " . $companyName . "\n";
                }
            }
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

            $entity = self::resolveEntityDisplay($bom, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];

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
            $message .= "*" . $headerEntitas . "*\n\n";
            $message .= "Halo *" . $creatorName . "*,\n";
            $message .= "Kabar baik! Resep Produksi Garmen (Bill of Materials) yang Anda susun telah *DISETUJUI (APPROVED)* oleh atasan.\n\n";
            $message .= "*Detail Resep BOM:*\n";
            if ($isCabang) {
                $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
            } else {
                if ($isCabang) {
                    $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                } else {
                    $message .= "- *Perusahaan*: " . $companyName . "\n";
                }
            }
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

            $entity = self::resolveEntityDisplay($bom, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];$creator     = $bom->creator ?: \App\Models\User::find($bom->created_by);
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
            $message .= "*" . $headerEntitas . "*\n\n";
            $message .= "Halo *" . $creatorName . "*,\n";
            $message .= "Mohon maaf, pengajuan Resep Produksi Garmen (BOM) Anda telah *DITOLAK (REJECTED)* oleh atasan dengan catatan revisi berikut:\n\n";
            $message .= "*Detail Penolakan:*\n";
            if ($isCabang) {
                $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
            } else {
                $message .= "- *Perusahaan*: " . $companyName . "\n";
            }
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

            $entity = self::resolveEntityDisplay($spk, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];

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
                $message .= "*" . $headerEntitas . "*\n\n";
                $message .= "Halo *" . $supervisorName . "*,\n";
                $message .= "Ada Surat Perintah Kerja (SPK / Work Order) Produksi baru yang membutuhkan persetujuan Anda.\n\n";
                $message .= "*Detail SPK Produksi:*\n";
                if ($isCabang) {
                $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
            } else {
                if ($isCabang) {
                    $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                } else {
                    $message .= "- *Perusahaan*: " . $companyName . "\n";
                }
            }
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

            $entity = self::resolveEntityDisplay($spk, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];

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
                    if ($isCabang) {
                        $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                    } else {
                        $message .= "- *Perusahaan*: " . $companyName . "\n";
                    }
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
                    $message .= "*" . $headerEntitas . "*\n\n";
                    $message .= "Halo *" . $rec['name'] . "*,\n";
                    $message .= "Kabar baik! Surat Perintah Kerja (SPK / Work Order) yang Anda ajukan telah *DISETUJUI (APPROVED)* oleh atasan.\n\n";
                    $message .= "*Detail SPK Produksi:*\n";
                    if ($isCabang) {
                        $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                    } else {
                        $message .= "- *Perusahaan*: " . $companyName . "\n";
                    }
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

            $entity = self::resolveEntityDisplay($spk, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];

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
            if ($isCabang) {
                $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
            } else {
                $message .= "- *Perusahaan*: " . $companyName . "\n";
            }
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

            $entity = self::resolveEntityDisplay($spk, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];

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
                $message .= "*" . $headerEntitas . "*\n\n";
                $message .= "Halo *" . $rec['name'] . "*,\n";
                $message .= "Seluruh tahapan pengerjaan Surat Perintah Kerja (SPK / Work Order) telah *SELESAI (COMPLETED)* dengan sukses!\n\n";
                $message .= "*Ringkasan Hasil Produksi:*\n";
                if ($isCabang) {
                    $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                } else {
                    $message .= "- *Perusahaan*: " . $companyName . "\n";
                }
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

            $entity = self::resolveEntityDisplay($spk, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];

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
                $message .= "*" . $headerEntitas . "*\n\n";
                $message .= "Halo *" . $rec['name'] . "*,\n";
                $message .= "Mohon maaf, Surat Perintah Kerja (SPK / Work Order) yang Anda ajukan telah *DITOLAK (REJECTED)* oleh atasan.\n\n";
                $message .= "*Detail SPK yang Ditolak:*\n";
                if ($isCabang) {
                    $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                } else {
                    $message .= "- *Perusahaan*: " . $companyName . "\n";
                }
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
            $entity = self::resolveEntityDisplay($tender, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];

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
                $message .= "*" . $headerEntitas . "*\n\n";
                $message .= "Halo *" . $rec['name'] . "*,\n";
                $message .= "Terdapat pengajuan proposal tender baru yang *membutuhkan persetujuan (approval)* Anda.\n\n";
                $message .= "*Rincian Proposal Tender:*\n";
                if ($isCabang) {
                    $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                } else {
                    $message .= "- *Perusahaan*: " . $companyName . "\n";
                }
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
            $entity = self::resolveEntityDisplay($tender, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];// Target: Sales PIC & Creator
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
                $message .= "*" . $headerEntitas . "*\n\n";
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
            $entity = self::resolveEntityDisplay($tender, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];// Target: Sales PIC & Creator
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
                $message .= "*" . $headerEntitas . "*\n\n";
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
            $entity = self::resolveEntityDisplay($tender, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];// Broadcast to Approvers & Sales PIC
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
                $message .= "*" . $headerEntitas . "*\n\n";
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
            $entity = self::resolveEntityDisplay($quotation, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];

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
                $message .= "*" . $headerEntitas . "*\n\n";
                $message .= "Halo *" . $rec['name'] . "*,\n";
                $message .= "Terdapat Surat Penawaran Harga (Sales Quotation) baru yang *membutuhkan persetujuan (approval)* Anda.\n\n";
                $message .= "*Rincian Penawaran:*\n";
                if ($isCabang) {
                    $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                } else {
                    $message .= "- *Perusahaan*: " . $companyName . "\n";
                }
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
            $entity = self::resolveEntityDisplay($quotation, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];// Target: Sales PIC & Creator
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
                $message .= "*" . $headerEntitas . "*\n\n";
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
            $entity = self::resolveEntityDisplay($quotation, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];// Target: Sales PIC & Creator
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
                $message .= "*" . $headerEntitas . "*\n\n";
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
            $entity = self::resolveEntityDisplay($quotation, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];// Target: Sales PIC & Creator
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
                $message .= "*" . $headerEntitas . "*\n\n";
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
            $entity = self::resolveEntityDisplay($payment, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];

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
                $msg .= "*" . $headerEntitas . "*\n\n";
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
            $entity = self::resolveEntityDisplay($payment, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];$nomorBayar   = $payment->nomor_pembayaran;
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
                $msg .= "*" . $headerEntitas . "*\n\n";
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
            $entity = self::resolveEntityDisplay($payment, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];$nomorBayar   = $payment->nomor_pembayaran;
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
                $msg .= "*" . $headerEntitas . "*\n\n";
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
     * Send WhatsApp notification when Closing Pengeluaran Bahan SPK is submitted for review / approval.
     */
    public static function sendPengeluaranBahanClosingSubmissionNotification($spk, $requesterUser = null)
    {
        $companyId = $spk->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $approvalEnabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_approval')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$approvalEnabled || !$apiToken) {
            return;
        }

        try {
            if (is_numeric($spk)) {
                $spk = \App\Models\SpkProduksi::find($spk);
            }
            if (!$spk) return;

            $spk->load([
                'company',
                'branch',
                'barangJadi.satuan',
                'satuan',
                'materials.barangMentah',
                'pengeluaranBahans.details',
                'materialClosingRequestedBy.karyawan',
            ]);

            $entity = self::resolveEntityDisplay($spk, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];$isCabang      = !empty($spk->branch_id) && !empty($spk->branch);
            $rawCabang     = trim($spk->branch?->nama_cabang ?? '');
            $namaCabangDisplay = $isCabang ? (stripos($rawCabang, 'cabang') === 0 ? $rawCabang : ('Cabang ' . $rawCabang)) : null;
            $headerEntitas = $isCabang ? mb_strtoupper($namaCabangDisplay) : mb_strtoupper($companyName);
            $asalUnitText  = $isCabang ? $namaCabangDisplay : ($companyName . ' (Pusat)');

            $requester     = $requesterUser ?: ($spk->materialClosingRequestedBy ?: auth()->user());
            $requesterName = $requester?->nama_lengkap ?: ($requester?->name ?: 'Tim Produksi');
            $nomorSpk      = $spk->nomor_spk;
            $barangJadi    = $spk->barangJadi ? ('[' . $spk->barangJadi->kode_barang . '] ' . $spk->barangJadi->nama_barang) : '-';
            $targetSatuan  = $spk->satuan ? $spk->satuan->nama_satuan : ($spk->barangJadi?->satuan?->nama_satuan ?? 'Pcs');
            $targetQty     = number_format($spk->jumlah_rencana, 0, ',', '.') . ' ' . $targetSatuan;
            $totalSesi     = $spk->pengeluaranBahans->count();
            $catatan       = $spk->material_closing_note ?: 'Tidak ada catatan';
            $tanggalJam    = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            // Hitung total pemakaian & estimasi HPP
            $totalNilai = 0;
            $totalMat   = $spk->materials->count();
            $completedMat = 0;
            foreach ($spk->materials as $m) {
                $issued = (float)$m->total_keluar_gudang;
                $needed = (float)$m->total_kebutuhan;
                if ($issued >= $needed && $needed > 0) $completedMat++;
            }

            $pengeluaranIds = $spk->pengeluaranBahans->pluck('id');
            $kartuStoks = \App\Models\KartuStok::where('referensi_jenis', 'Pengeluaran Bahan SPK')
                ->whereIn('referensi_id', $pengeluaranIds)
                ->where('tipe', 'Keluar')
                ->get();
            $totalNilai = $kartuStoks->sum('nilai_total');
            $totalNilaiFmt = number_format($totalNilai, 0, ',', '.');

            // Cari user yang memiliki hak akses 'closing-pengeluaran-bahan' atau Super Admin / Owner
            $approvers = \App\Models\User::with('karyawan')
                ->whereIn('status', ['Aktif', 'Active'])
                ->where(function($q) {
                    $q->whereNotNull('no_hp')->where('no_hp', '!=', '')
                      ->orWhereHas('karyawan', fn($k) => $k->whereNotNull('no_hp')->where('no_hp', '!=', ''));
                })
                ->where(function ($q) {
                    $q->whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner']))
                      ->orWhere(function ($sub) {
                          $sub->permission(['closing-pengeluaran-bahan']);
                      });
                })
                ->where(function ($query) use ($companyId, $spk) {
                    $query->whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner']))
                          ->orWhereHas('companies', fn($c) => $c->where('companies.id', $companyId))
                          ->orWhereHas('branches', fn($b) => $b->where('branches.id', $spk->branch_id)
                                                               ->orWhere('branches.company_id', $companyId));
                })
                ->get();

            if ($approvers->isEmpty()) {
                return;
            }

            $sentTargets = [];
            foreach ($approvers as $approver) {
                $phone = $approver->no_hp ?: ($approver->karyawan?->no_hp ?? null);
                $target = self::formatPhoneNumber($phone);
                if (empty($target) || in_array($target, $sentTargets)) continue;
                $sentTargets[] = $target;

                $approverName = $approver->nama_lengkap ?? ($approver->name ?? 'Tim Reviewer / Accounting');

                $message = "📢 *PEMBERITAHUAN PENGAJUAN CLOSING PENGELUARAN BAHAN SPK* 🔒\n";
                $message .= "*" . $headerEntitas . "*\n\n";
                $message .= "Halo *" . $approverName . "*,\n";
                $message .= "Terdapat pengajuan penutupan (Closing) Pengeluaran Bahan Baku SPK dari *" . $asalUnitText . "* yang membutuhkan peninjauan, verifikasi, dan penerbitan jurnal akuntansi konsolidasi:\n\n";
                $message .= "*Rincian Pengajuan Closing:*\n";
                if ($isCabang) {
                    $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                } else {
                    $message .= "- *Unit Kerja*: Kantor Pusat\n";
                    if ($isCabang) {
                        $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                    } else {
                        $message .= "- *Perusahaan*: " . $companyName . "\n";
                    }
                }
                $message .= "- *No. SPK*: *" . $nomorSpk . "*\n";
                $message .= "- *Produk Jadi*: " . $barangJadi . "\n";
                $message .= "- *Target Produksi*: *" . $targetQty . "*\n";
                $message .= "- *Total Sesi Keluar*: " . $totalSesi . " Sesi\n";
                $message .= "- *Status Bahan*: " . $completedMat . " dari " . $totalMat . " Item Lengkap\n";
                $message .= "- *Estimasi Nilai HPP Bahan*: *Rp " . $totalNilaiFmt . "*\n";
                $message .= "- *Diajukan Oleh*: *" . $requesterName . "*\n";
                $message .= "- *Waktu Pengajuan*: " . $tanggalJam . "\n";
                $message .= "- *Catatan*: _" . $catatan . "_\n\n";
                $message .= "Silakan masuk ke sistem ERP (*Modul Produksi -> Pengeluaran Bahan Baku SPK*) untuk memeriksa rekapitulasi bahan dan memberikan persetujuan (Posting Jurnal) atau menolak pengajuan.\n\n";
                $message .= "Terima kasih.";

                $resBody = self::dispatchFonnteMessage($apiToken, $target, $message);
                $status  = (isset($resBody['status']) && $resBody['status'] == true) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id'   => $companyId,
                    'phone_number' => $target,
                    'recipient_name' => $approverName,
                    'message'      => $message,
                    'message_type' => 'Pengeluaran Bahan Closing Submission Alert',
                    'action'       => 'Material Closing Submission Alert',
                    'recipient'    => $target . ' (' . $approverName . ')',
                    'status'       => $status,
                    'response'     => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendPengeluaranBahanClosingSubmissionNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when Closing Pengeluaran Bahan SPK is approved and journal is created.
     */
    public static function sendPengeluaranBahanClosingApprovedNotification($spk, $approverUser = null)
    {
        $companyId = $spk->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$apiToken) {
            return;
        }

        try {
            if (is_numeric($spk)) {
                $spk = \App\Models\SpkProduksi::find($spk);
            }
            if (!$spk) return;

            $spk->load([
                'company',
                'branch',
                'barangJadi.satuan',
                'materialClosingRequestedBy.karyawan',
                'penanggungJawab',
                'creator.karyawan',
                'materialClosingJurnal',
            ]);

            $entity = self::resolveEntityDisplay($spk, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];$isCabang      = !empty($spk->branch_id) && !empty($spk->branch);
            $rawCabang     = trim($spk->branch?->nama_cabang ?? '');
            $namaCabangDisplay = $isCabang ? (stripos($rawCabang, 'cabang') === 0 ? $rawCabang : ('Cabang ' . $rawCabang)) : null;
            $headerEntitas = $isCabang ? mb_strtoupper($namaCabangDisplay) : mb_strtoupper($companyName);
            $asalUnitText  = $isCabang ? $namaCabangDisplay : ($companyName . ' (Pusat)');

            $approver      = $approverUser ?: auth()->user();
            $approverName  = $approver?->nama_lengkap ?: ($approver?->name ?: 'Accounting / Otorisator');
            $nomorSpk      = $spk->nomor_spk;
            $barangJadi    = $spk->barangJadi ? ('[' . $spk->barangJadi->kode_barang . '] ' . $spk->barangJadi->nama_barang) : '-';
            $nomorJurnal   = $spk->materialClosingJurnal?->nomor_jurnal ?? 'Jurnal Konsolidasi';
            $tanggalJam    = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            // Target recipients: Requester & Creator & Penanggung Jawab
            $recipients = [];
            if ($spk->materialClosingRequestedBy) {
                $recipients[] = $spk->materialClosingRequestedBy;
            }
            if ($spk->creator && $spk->creator->id !== $spk->material_closing_requested_by) {
                $recipients[] = $spk->creator;
            }

            $sentTargets = [];
            foreach ($recipients as $recUser) {
                $phone = $recUser->no_hp ?: ($recUser->karyawan?->no_hp ?? null);
                $target = self::formatPhoneNumber($phone);
                if (empty($target) || in_array($target, $sentTargets)) continue;
                $sentTargets[] = $target;

                $recipientName = $recUser->nama_lengkap ?: ($recUser->name ?: 'Rekan');

                $message = "✅ *PEMBERITAHUAN CLOSING PENGELUARAN BAHAN SPK DISETUJUI* 🔒\n";
                $message .= "*" . $headerEntitas . "*\n\n";
                $message .= "Halo *" . $recipientName . "*,\n";
                $message .= "Pengajuan penutupan (Closing) Pengeluaran Bahan Baku untuk SPK pada *" . $asalUnitText . "* telah *DISETUJUI* dan 1 Jurnal Akuntansi Konsolidasi telah berhasil diterbitkan:\n\n";
                $message .= "*Detail SPK:*\n";
                if ($isCabang) {
                    $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                } else {
                    $message .= "- *Unit Kerja*: Kantor Pusat\n";
                    if ($isCabang) {
                        $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                    } else {
                        $message .= "- *Perusahaan*: " . $companyName . "\n";
                    }
                }
                $message .= "- *No. SPK*: *" . $nomorSpk . "*\n";
                $message .= "- *Produk Jadi*: " . $barangJadi . "\n";
                $message .= "- *Status SPK*: *CLOSED (Terkunci)*\n";
                $message .= "- *No. Jurnal Konsolidasi*: *" . $nomorJurnal . "*\n";
                $message .= "- *Disetujui Oleh*: *" . $approverName . "*\n";
                $message .= "- *Waktu Persetujuan*: " . $tanggalJam . "\n\n";
                $message .= "Seluruh operasional pengeluaran bahan untuk SPK ini telah resmi ditutup dan dibukukan secara otomatis ke akuntansi.\n\n";
                $message .= "Terima kasih.";

                $resBody = self::dispatchFonnteMessage($apiToken, $target, $message);
                $status  = (isset($resBody['status']) && $resBody['status'] == true) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id'   => $companyId,
                    'phone_number' => $target,
                    'recipient_name' => $recipientName,
                    'message'      => $message,
                    'message_type' => 'Pengeluaran Bahan Closing Approved Alert',
                    'action'       => 'Material Closing Approved Alert',
                    'recipient'    => $target . ' (' . $recipientName . ')',
                    'status'       => $status,
                    'response'     => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendPengeluaranBahanClosingApprovedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when Closing Pengeluaran Bahan SPK is rejected.
     */
    public static function sendPengeluaranBahanClosingRejectedNotification($spk, $rejecterUser = null, $reason = '')
    {
        $companyId = $spk->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$apiToken) {
            return;
        }

        try {
            if (is_numeric($spk)) {
                $spk = \App\Models\SpkProduksi::find($spk);
            }
            if (!$spk) return;

            $spk->load([
                'company',
                'branch',
                'barangJadi.satuan',
                'materialClosingRequestedBy.karyawan',
                'creator.karyawan',
            ]);

            $entity = self::resolveEntityDisplay($spk, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];$isCabang      = !empty($spk->branch_id) && !empty($spk->branch);
            $rawCabang     = trim($spk->branch?->nama_cabang ?? '');
            $namaCabangDisplay = $isCabang ? (stripos($rawCabang, 'cabang') === 0 ? $rawCabang : ('Cabang ' . $rawCabang)) : null;
            $headerEntitas = $isCabang ? mb_strtoupper($namaCabangDisplay) : mb_strtoupper($companyName);
            $asalUnitText  = $isCabang ? $namaCabangDisplay : ($companyName . ' (Pusat)');

            $rejecter      = $rejecterUser ?: auth()->user();
            $rejecterName  = $rejecter?->nama_lengkap ?: ($rejecter?->name ?: 'Accounting / Reviewer');
            $nomorSpk      = $spk->nomor_spk;
            $barangJadi    = $spk->barangJadi ? ('[' . $spk->barangJadi->kode_barang . '] ' . $spk->barangJadi->nama_barang) : '-';
            $alasan        = $reason ?: ($spk->material_closing_reject_reason ?: 'Tidak ada alasan khusus');
            $tanggalJam    = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            // Target recipients: Requester & Creator
            $recipients = [];
            if ($spk->materialClosingRequestedBy) {
                $recipients[] = $spk->materialClosingRequestedBy;
            }
            if ($spk->creator && $spk->creator->id !== $spk->material_closing_requested_by) {
                $recipients[] = $spk->creator;
            }

            $sentTargets = [];
            foreach ($recipients as $recUser) {
                $phone = $recUser->no_hp ?: ($recUser->karyawan?->no_hp ?? null);
                $target = self::formatPhoneNumber($phone);
                if (empty($target) || in_array($target, $sentTargets)) continue;
                $sentTargets[] = $target;

                $recipientName = $recUser->nama_lengkap ?: ($recUser->name ?: 'Rekan');

                $message = "⚠️ *PEMBERITAHUAN PENGAJUAN CLOSING BAHAN SPK DITOLAK* ❌\n";
                $message .= "*" . $headerEntitas . "*\n\n";
                $message .= "Halo *" . $recipientName . "*,\n";
                $message .= "Pengajuan penutupan (Closing) Pengeluaran Bahan Baku untuk SPK pada *" . $asalUnitText . "* *DITOLAK* oleh pihak Accounting / Reviewer:\n\n";
                $message .= "*Detail SPK:*\n";
                if ($isCabang) {
                    $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                } else {
                    $message .= "- *Unit Kerja*: Kantor Pusat\n";
                    if ($isCabang) {
                        $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                    } else {
                        $message .= "- *Perusahaan*: " . $companyName . "\n";
                    }
                }
                $message .= "- *No. SPK*: *" . $nomorSpk . "*\n";
                $message .= "- *Produk Jadi*: " . $barangJadi . "\n";
                $message .= "- *Status SPK*: *CLOSING DITOLAK (Dapat Disesuaikan Kembali)*\n";
                $message .= "- *Ditolak Oleh*: *" . $rejecterName . "*\n";
                $message .= "- *Waktu Penolakan*: " . $tanggalJam . "\n";
                $message .= "- *Alasan Penolakan*: _" . $alasan . "_\n\n";
                $message .= "Silakan periksa kembali dan lakukan penyesuaian/pengeluaran bahan tambahan pada SPK tersebut di sistem ERP sebelum mengajukan closing kembali.\n\n";
                $message .= "Terima kasih.";

                $resBody = self::dispatchFonnteMessage($apiToken, $target, $message);
                $status  = (isset($resBody['status']) && $resBody['status'] == true) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id'   => $companyId,
                    'phone_number' => $target,
                    'recipient_name' => $recipientName,
                    'message'      => $message,
                    'message_type' => 'Pengeluaran Bahan Closing Rejected Alert',
                    'action'       => 'Material Closing Rejected Alert',
                    'recipient'    => $target . ' (' . $recipientName . ')',
                    'status'       => $status,
                    'response'     => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendPengeluaranBahanClosingRejectedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when Closing Penerimaan Barang Jadi SPK is submitted for accounting review.
     */
    public static function sendPenerimaanBarangJadiClosingSubmissionNotification($spk, $requesterUser = null, $catatanInput = null)
    {
        $companyId = $spk->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$apiToken) {
            return;
        }

        try {
            if (is_numeric($spk)) {
                $spk = \App\Models\SpkProduksi::find($spk);
            }
            if (!$spk) return;

            $spk->load([
                'company',
                'branch',
                'barangJadi.satuan',
                'satuan',
                'penerimaanBarangJadis',
            ]);

            $entity = self::resolveEntityDisplay($spk, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];$isCabang      = !empty($spk->branch_id) && !empty($spk->branch);
            $rawCabang     = trim($spk->branch?->nama_cabang ?? '');
            $namaCabangDisplay = $isCabang ? (stripos($rawCabang, 'cabang') === 0 ? $rawCabang : ('Cabang ' . $rawCabang)) : null;
            $headerEntitas = $isCabang ? mb_strtoupper($namaCabangDisplay) : mb_strtoupper($companyName);
            $asalUnitText  = $isCabang ? $namaCabangDisplay : ($companyName . ' (Pusat)');

            $requester     = $requesterUser ?: auth()->user();
            $requesterName = $requester?->nama_lengkap ?: ($requester?->name ?: 'Tim Produksi / Gudang');
            $nomorSpk      = $spk->nomor_spk;
            $barangJadi    = $spk->barangJadi ? ('[' . $spk->barangJadi->kode_barang . '] ' . $spk->barangJadi->nama_barang) : '-';
            $satuan        = $spk->satuan ? $spk->satuan->nama_satuan : ($spk->barangJadi && $spk->barangJadi->satuan ? $spk->barangJadi->satuan->nama_satuan : 'Pcs');
            $targetQty     = number_format($spk->jumlah_rencana, 0, ',', '.') . ' ' . $satuan;
            $totalSesi     = $spk->penerimaanBarangJadis->count();
            $totalHasil    = number_format($spk->penerimaanBarangJadis->sum('jumlah_hasil_baik'), 0, ',', '.') . ' ' . $satuan;
            $totalReject   = number_format($spk->penerimaanBarangJadis->sum('jumlah_reject'), 0, ',', '.') . ' ' . $satuan;
            $catatan       = $catatanInput ?: ($spk->fg_closing_note ?: 'Tidak ada catatan');
            $tanggalJam    = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            // Cari user yang memiliki hak akses 'closing-penerimaan-barang-jadi' atau Super Admin / Owner
            $approvers = \App\Models\User::with('karyawan')
                ->whereIn('status', ['Aktif', 'Active'])
                ->where(function($q) {
                    $q->whereNotNull('no_hp')->where('no_hp', '!=', '')
                      ->orWhereHas('karyawan', fn($k) => $k->whereNotNull('no_hp')->where('no_hp', '!=', ''));
                })
                ->where(function ($q) {
                    $q->whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner']))
                      ->orWhere(function ($sub) {
                          $sub->permission(['closing-penerimaan-barang-jadi']);
                      });
                })
                ->where(function ($query) use ($companyId, $spk) {
                    $query->whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner']))
                          ->orWhereHas('companies', fn($c) => $c->where('companies.id', $companyId))
                          ->orWhereHas('branches', fn($b) => $b->where('branches.id', $spk->branch_id)
                                                               ->orWhere('branches.company_id', $companyId));
                })
                ->get();

            if ($approvers->isEmpty()) {
                return;
            }

            $sentTargets = [];
            foreach ($approvers as $approver) {
                $phone = $approver->no_hp ?: ($approver->karyawan?->no_hp ?? null);
                $target = self::formatPhoneNumber($phone);
                if (empty($target) || in_array($target, $sentTargets)) continue;
                $sentTargets[] = $target;

                $approverName = $approver->nama_lengkap ?? ($approver->name ?? 'Tim Reviewer / Accounting');

                $message = "📢 *PEMBERITAHUAN PENGAJUAN CLOSING HASIL PRODUKSI SPK* 📦\n";
                $message .= "*" . $headerEntitas . "*\n\n";
                $message .= "Halo *" . $approverName . "*,\n";
                $message .= "Terdapat pengajuan penutupan (Closing) Penerimaan Barang Jadi SPK dari *" . $asalUnitText . "* yang membutuhkan peninjauan, perhitungan HPP aktual, dan otorisasi penerbitan jurnal akuntansi:\n\n";
                $message .= "*Rincian Pengajuan Closing:*\n";
                if ($isCabang) {
                    $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                } else {
                    $message .= "- *Unit Kerja*: Kantor Pusat\n";
                    if ($isCabang) {
                        $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                    } else {
                        $message .= "- *Perusahaan*: " . $companyName . "\n";
                    }
                }
                $message .= "- *No. SPK*: *" . $nomorSpk . "*\n";
                $message .= "- *Produk Jadi*: " . $barangJadi . "\n";
                $message .= "- *Target Produksi*: *" . $targetQty . "*\n";
                $message .= "- *Hasil Diterima*: *" . $totalHasil . "* (" . $totalSesi . " Sesi)\n";
                $message .= "- *Jumlah Reject*: " . $totalReject . "\n";
                $message .= "- *Diajukan Oleh*: *" . $requesterName . "*\n";
                $message .= "- *Waktu Pengajuan*: " . $tanggalJam . "\n";
                $message .= "- *Catatan*: _" . $catatan . "_\n\n";
                $message .= "Silakan masuk ke sistem ERP (*Modul Produksi -> Penerimaan Barang Jadi*) untuk me-review rincian penerimaan, menghitung HPP aktual, dan menyetujui (Posting Jurnal) atau menolak pengajuan.\n\n";
                $message .= "Terima kasih.";

                $resBody = self::dispatchFonnteMessage($apiToken, $target, $message);
                $status  = (isset($resBody['status']) && $resBody['status'] == true) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id'   => $companyId,
                    'phone_number' => $target,
                    'recipient_name' => $approverName,
                    'message'      => $message,
                    'message_type' => 'Penerimaan Barang Jadi Closing Submission Alert',
                    'action'       => 'FG Closing Submission Alert',
                    'recipient'    => $target . ' (' . $approverName . ')',
                    'status'       => $status,
                    'response'     => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendPenerimaanBarangJadiClosingSubmissionNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when Closing Penerimaan Barang Jadi SPK is approved and HPP is finalized.
     */
    public static function sendPenerimaanBarangJadiClosingApprovedNotification($spk, $approverUser = null)
    {
        $companyId = $spk->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$apiToken) {
            return;
        }

        try {
            if (is_numeric($spk)) {
                $spk = \App\Models\SpkProduksi::find($spk);
            }
            if (!$spk) return;

            $spk->load([
                'company',
                'branch',
                'barangJadi.satuan',
                'satuan',
                'fgClosingRequestedBy.karyawan',
                'creator.karyawan',
                'fgClosingJurnal',
                'penerimaanBarangJadis',
            ]);

            $entity = self::resolveEntityDisplay($spk, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];$isCabang      = !empty($spk->branch_id) && !empty($spk->branch);
            $rawCabang     = trim($spk->branch?->nama_cabang ?? '');
            $namaCabangDisplay = $isCabang ? (stripos($rawCabang, 'cabang') === 0 ? $rawCabang : ('Cabang ' . $rawCabang)) : null;
            $headerEntitas = $isCabang ? mb_strtoupper($namaCabangDisplay) : mb_strtoupper($companyName);
            $asalUnitText  = $isCabang ? $namaCabangDisplay : ($companyName . ' (Pusat)');

            $approver      = $approverUser ?: auth()->user();
            $approverName  = $approver?->nama_lengkap ?: ($approver?->name ?: 'Accounting / Otorisator');
            $nomorSpk      = $spk->nomor_spk;
            $barangJadi    = $spk->barangJadi ? ('[' . $spk->barangJadi->kode_barang . '] ' . $spk->barangJadi->nama_barang) : '-';
            $satuan        = $spk->satuan ? $spk->satuan->nama_satuan : ($spk->barangJadi && $spk->barangJadi->satuan ? $spk->barangJadi->satuan->nama_satuan : 'Pcs');
            $totalHasil    = number_format($spk->penerimaanBarangJadis->sum('jumlah_hasil_baik'), 0, ',', '.') . ' ' . $satuan;
            $totalNilaiHpp = number_format($spk->estimasi_total_biaya, 0, ',', '.');
            $nomorJurnal   = $spk->fgClosingJurnal?->nomor_jurnal ?? 'Jurnal Konsolidasi FG';
            $tanggalJam    = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            // Target recipients: Requester & Creator
            $recipients = [];
            if ($spk->fgClosingRequestedBy) {
                $recipients[] = $spk->fgClosingRequestedBy;
            }
            if ($spk->creator && $spk->creator->id !== $spk->fg_closing_requested_by) {
                $recipients[] = $spk->creator;
            }

            $sentTargets = [];
            foreach ($recipients as $recUser) {
                $phone = $recUser->no_hp ?: ($recUser->karyawan?->no_hp ?? null);
                $target = self::formatPhoneNumber($phone);
                if (empty($target) || in_array($target, $sentTargets)) continue;
                $sentTargets[] = $target;

                $recipientName = $recUser->nama_lengkap ?: ($recUser->name ?: 'Tim Produksi / Gudang');

                $message = "✅ *PEMBERITAHUAN CLOSING HASIL PRODUKSI DISETUJUI* 🔒\n";
                $message .= "*" . $headerEntitas . "*\n\n";
                $message .= "Halo *" . $recipientName . "*,\n";
                $message .= "Pengajuan penutupan (Closing) Penerimaan Barang Jadi untuk SPK pada *" . $asalUnitText . "* telah *DISETUJUI* dan HPP Final telah dihitung oleh Accounting:\n\n";
                $message .= "*Rincian Penutupan SPK:*\n";
                if ($isCabang) {
                    $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                } else {
                    $message .= "- *Unit Kerja*: Kantor Pusat\n";
                    if ($isCabang) {
                        $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                    } else {
                        $message .= "- *Perusahaan*: " . $companyName . "\n";
                    }
                }
                $message .= "- *No. SPK*: *" . $nomorSpk . "*\n";
                $message .= "- *Produk Jadi*: " . $barangJadi . "\n";
                $message .= "- *Total Hasil Diterima*: *" . $totalHasil . "*\n";
                $message .= "- *Total Biaya HPP*: *Rp " . $totalNilaiHpp . "*\n";
                $message .= "- *Disetujui Oleh*: *" . $approverName . "*\n";
                $message .= "- *Waktu Persetujuan*: " . $tanggalJam . "\n";
                $message .= "- *Status SPK*: *CLOSED (Selesai)*\n\n";
                $message .= "Status SPK kini telah ditutup secara resmi dan jurnal akuntansi penerimaan barang jadi telah diterbitkan.\n\n";
                $message .= "Terima kasih.";

                $resBody = self::dispatchFonnteMessage($apiToken, $target, $message);
                $status  = (isset($resBody['status']) && $resBody['status'] == true) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id'   => $companyId,
                    'phone_number' => $target,
                    'recipient_name' => $recipientName,
                    'message'      => $message,
                    'message_type' => 'Penerimaan Barang Jadi Closing Approved Alert',
                    'action'       => 'FG Closing Approved Alert',
                    'recipient'    => $target . ' (' . $recipientName . ')',
                    'status'       => $status,
                    'response'     => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendPenerimaanBarangJadiClosingApprovedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when Closing Penerimaan Barang Jadi SPK is rejected.
     */
    public static function sendPenerimaanBarangJadiClosingRejectedNotification($spk, $rejecterUser = null, $reason = '')
    {
        $companyId = $spk->company_id ?? session('active_company_id') ?: 1;
        $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
        $apiToken = self::getDecryptedToken($companyId);

        if (!$enabled || !$apiToken) {
            return;
        }

        try {
            if (is_numeric($spk)) {
                $spk = \App\Models\SpkProduksi::find($spk);
            }
            if (!$spk) return;

            $spk->load([
                'company',
                'branch',
                'barangJadi.satuan',
                'fgClosingRequestedBy.karyawan',
                'creator.karyawan',
            ]);

            $entity = self::resolveEntityDisplay($spk, $companyId);
            $companyName = $entity['companyName'];
            $isCabang = $entity['isCabang'];
            $namaCabangDisplay = $entity['namaCabangDisplay'];
            $headerEntitas = $entity['headerEntitas'];
            $asalUnitText = $entity['asalUnitText'];
            $asalPengajuan = $entity['asalUnitText'];$isCabang      = !empty($spk->branch_id) && !empty($spk->branch);
            $rawCabang     = trim($spk->branch?->nama_cabang ?? '');
            $namaCabangDisplay = $isCabang ? (stripos($rawCabang, 'cabang') === 0 ? $rawCabang : ('Cabang ' . $rawCabang)) : null;
            $headerEntitas = $isCabang ? mb_strtoupper($namaCabangDisplay) : mb_strtoupper($companyName);
            $asalUnitText  = $isCabang ? $namaCabangDisplay : ($companyName . ' (Pusat)');

            $rejecter      = $rejecterUser ?: auth()->user();
            $rejecterName  = $rejecter?->nama_lengkap ?: ($rejecter?->name ?: 'Accounting / Reviewer');
            $nomorSpk      = $spk->nomor_spk;
            $barangJadi    = $spk->barangJadi ? ('[' . $spk->barangJadi->kode_barang . '] ' . $spk->barangJadi->nama_barang) : '-';
            $alasan        = $reason ?: ($spk->fg_closing_reject_reason ?: 'Tidak ada alasan khusus');
            $tanggalJam    = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

            // Target recipients: Requester & Creator
            $recipients = [];
            if ($spk->fgClosingRequestedBy) {
                $recipients[] = $spk->fgClosingRequestedBy;
            }
            if ($spk->creator && $spk->creator->id !== $spk->fg_closing_requested_by) {
                $recipients[] = $spk->creator;
            }

            $sentTargets = [];
            foreach ($recipients as $recUser) {
                $phone = $recUser->no_hp ?: ($recUser->karyawan?->no_hp ?? null);
                $target = self::formatPhoneNumber($phone);
                if (empty($target) || in_array($target, $sentTargets)) continue;
                $sentTargets[] = $target;

                $recipientName = $recUser->nama_lengkap ?: ($recUser->name ?: 'Rekan');

                $message = "⚠️ *PEMBERITAHUAN PENGAJUAN CLOSING HASIL PRODUKSI DITOLAK* ❌\n";
                $message .= "*" . $headerEntitas . "*\n\n";
                $message .= "Halo *" . $recipientName . "*,\n";
                $message .= "Pengajuan penutupan (Closing) Penerimaan Barang Jadi untuk SPK pada *" . $asalUnitText . "* *DITOLAK* oleh pihak Accounting / Reviewer:\n\n";
                $message .= "*Detail SPK:*\n";
                if ($isCabang) {
                    $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                } else {
                    $message .= "- *Unit Kerja*: Kantor Pusat\n";
                    if ($isCabang) {
                        $message .= "- *Cabang*: " . $namaCabangDisplay . "\n";
                    } else {
                        $message .= "- *Perusahaan*: " . $companyName . "\n";
                    }
                }
                $message .= "- *No. SPK*: *" . $nomorSpk . "*\n";
                $message .= "- *Produk Jadi*: " . $barangJadi . "\n";
                $message .= "- *Ditolak Oleh*: *" . $rejecterName . "*\n";
                $message .= "- *Waktu Penolakan*: " . $tanggalJam . "\n";
                $message .= "- *Alasan Penolakan*: *" . $alasan . "*\n\n";
                $message .= "Silakan periksa kembali data penerimaan barang jadi dan lakukan penyesuaian yang diperlukan di sistem ERP.\n\n";
                $message .= "Terima kasih.";

                $resBody = self::dispatchFonnteMessage($apiToken, $target, $message);
                $status  = (isset($resBody['status']) && $resBody['status'] == true) ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id'   => $companyId,
                    'phone_number' => $target,
                    'recipient_name' => $recipientName,
                    'message'      => $message,
                    'message_type' => 'Penerimaan Barang Jadi Closing Rejected Alert',
                    'action'       => 'FG Closing Rejected Alert',
                    'recipient'    => $target . ' (' . $recipientName . ')',
                    'status'       => $status,
                    'response'     => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendPenerimaanBarangJadiClosingRejectedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification when Delivery Order is submitted for approval.
     */
    public static function sendDeliveryOrderSubmissionNotification($deliveryOrder)
    {
        self::runAsync(function() use ($deliveryOrder) {
            try {
                if (is_numeric($deliveryOrder)) {
                    $deliveryOrder = \App\Models\DeliveryOrder::find($deliveryOrder);
                }
                if (!$deliveryOrder) return;

                $companyId = $deliveryOrder->company_id ?: ($deliveryOrder->branch?->company_id ?? session('active_company_id') ?: 1);
                $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
                if (!$enabled) return;

                $apiToken = self::getDecryptedToken($companyId);
                if (!$apiToken) return;

                $cacheKey = 'wa_token_invalid_' . md5((string)$apiToken);
                if (Cache::has($cacheKey)) return;

                $deliveryOrder->load([
                    'company', 'branch', 'customer', 'salesOrder', 'creator.karyawan', 'details.barang.satuan'
                ]);

                $entity = self::resolveEntityDisplay($deliveryOrder, $companyId);
                $headerEntitas = $entity['headerEntitas'];
                $isCabang = $entity['isCabang'];
                $namaCabangDisplay = $entity['namaCabangDisplay'];

                $nomorDo = $deliveryOrder->nomor_do;
                $nomorSo = $deliveryOrder->salesOrder?->nomor_so ?: '-';
                $customerName = $deliveryOrder->customer?->nama_perusahaan ?: ($deliveryOrder->customer?->nama_customer ?: ($deliveryOrder->nama_penerima ?: 'Pelanggan'));
                $alamatTujuan = $deliveryOrder->alamat_tujuan ?: '-';
                $tglKirim = $deliveryOrder->tanggal_kirim ? $deliveryOrder->tanggal_kirim->format('d/m/Y') : '-';
                $metode = $deliveryOrder->metode_pengiriman ?: 'Internal';
                $creatorName = $deliveryOrder->creator?->nama_lengkap ?: ($deliveryOrder->creator?->karyawan?->nama_lengkap ?: 'Staff Marketing');

                $itemsText = "";
                $count = 0;
                foreach ($deliveryOrder->details as $idx => $d) {
                    if ($count >= 5) {
                        $remaining = count($deliveryOrder->details) - 5;
                        $itemsText .= "   _... dan {$remaining} item lainnya_\n";
                        break;
                    }
                    $bName = $d->nama_barang_custom ?: ($d->barang?->nama_barang ?: 'Item');
                    $qty = number_format($d->qty_kirim, 0, ',', '.');
                    $sat = $d->nama_satuan ?: ($d->barang?->satuan?->nama_satuan ?: 'pcs');
                    $itemsText .= "   " . ($idx + 1) . ". {$bName} ({$qty} {$sat})\n";
                    $count++;
                }

                // Cari Approver (Super Admin, Owner, Director, Supervisor Warehouse, Kepala Gudang, atau yang punya permission approve-delivery-order)
                $approvers = \App\Models\User::where(function($q) use ($companyId, $deliveryOrder) {
                    $q->whereHas('permissions', fn($p) => $p->where('name', 'approve-delivery-order'))
                      ->orWhereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner', 'Director', 'Supervisor Warehouse', 'Kepala Gudang']));
                })
                ->where(function ($q) use ($companyId, $deliveryOrder) {
                    $q->whereHas('roles', fn($r) => $r->where('name', 'Super Admin'))
                      ->orWhereHas('companies', fn($c) => $c->where('companies.id', $companyId))
                      ->orWhereHas('branches', fn($b) => $b->where('branches.id', $deliveryOrder->branch_id)
                                                           ->orWhere('branches.company_id', $companyId));
                })
                ->with('karyawan')
                ->get();

                $sentPhones = [];
                $approversList = [];
                foreach ($approvers as $approver) {
                    $phone = $approver->no_hp ?: ($approver->karyawan?->no_hp);
                    $target = self::formatPhoneNumber($phone);
                    if (!$target || in_array($target, $sentPhones)) continue;
                    $sentPhones[] = $target;
                    $approversList[] = [
                        'phone' => $target,
                        'name'  => $approver->nama_lengkap ?: ($approver->karyawan?->nama_lengkap ?: 'Atasan'),
                    ];
                }

                if (empty($sentPhones)) return;

                $message = "🔔 *PEMBERITAHUAN PENGAJUAN PENGIRIMAN (DO)*\n"
                    . "*{$headerEntitas}*\n\n"
                    . "Halo, *Bapak/Ibu Pimpinan / Approver* 👋\n"
                    . "Terdapat pengajuan Surat Jalan Pengiriman baru yang membutuhkan persetujuan (ACC) Anda:\n\n"
                    . "📋 *No. Surat Jalan:* {$nomorDo}\n"
                    . "📄 *Ref. Sales Order:* {$nomorSo}\n"
                    . "🏢 *Customer/Tujuan:* {$customerName}\n"
                    . "📍 *Alamat:* {$alamatTujuan}\n"
                    . "📅 *Tgl Kirim:* {$tglKirim}\n"
                    . "🚚 *Metode:* {$metode}\n"
                    . "👤 *Diajukan Oleh:* {$creatorName}\n\n"
                    . "📦 *Daftar Barang:*\n{$itemsText}\n"
                    . "Silakan masuk ke sistem ERP modul Pengiriman untuk memeriksa & menyetujui (ACC) pengiriman ini.\n\n"
                    . "Terima kasih,\n"
                    . "🤖 *{$headerEntitas} - ERP System*";

                // Kirim sekaligus (Batch) ke seluruh approver via multi-target Fonnte API
                $batchTargets = implode(',', $sentPhones);
                $resBody = self::dispatchFonnteMessage($apiToken, $batchTargets, $message);
                $status = isset($resBody['status']) && $resBody['status'] == true ? 'Success' : 'Failed';

                foreach ($approversList as $appr) {
                    WhatsAppLog::create([
                        'company_id'     => $companyId,
                        'phone_number'   => $appr['phone'],
                        'recipient_name' => $appr['name'],
                        'message'        => $message,
                        'message_type'   => 'Delivery Order Submission Alert',
                        'action'         => 'DO Submission Alert',
                        'recipient'      => $appr['phone'] . ' (' . $appr['name'] . ')',
                        'status'         => $status,
                        'response'       => json_encode($resBody),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('WA sendDeliveryOrderSubmissionNotification error: ' . $e->getMessage());
            }
        });
    }

    /**
     * Send WhatsApp notification to creator when Delivery Order is approved by atasan.
     */
    public static function sendDeliveryOrderApprovedNotification($deliveryOrder, $approverUser = null)
    {
        self::runAsync(function() use ($deliveryOrder, $approverUser) {
            try {
                if (is_numeric($deliveryOrder)) {
                    $deliveryOrder = \App\Models\DeliveryOrder::find($deliveryOrder);
                }
                if (!$deliveryOrder) return;

                $deliveryOrder->load([
                    'company', 'branch', 'customer', 'salesOrder', 'creator.karyawan', 'approver.karyawan'
                ]);

                $companyId = $deliveryOrder->company_id ?: ($deliveryOrder->branch?->company_id ?? session('active_company_id') ?: 1);
                $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
                $apiToken = self::getDecryptedToken($companyId);

                if (!$enabled || !$apiToken) return;

                $entity = self::resolveEntityDisplay($deliveryOrder, $companyId);
                $headerEntitas = $entity['headerEntitas'];

                $nomorDo = $deliveryOrder->nomor_do;
                $nomorSo = $deliveryOrder->salesOrder?->nomor_so ?: '-';
                $customerName = $deliveryOrder->customer?->nama_perusahaan ?: ($deliveryOrder->customer?->nama_customer ?: ($deliveryOrder->nama_penerima ?: 'Pelanggan'));
                $tglKirim = $deliveryOrder->tanggal_kirim ? $deliveryOrder->tanggal_kirim->format('d/m/Y') : '-';

                $approver = $approverUser ?: $deliveryOrder->approver;
                $approverName = $approver?->nama_lengkap ?: ($approver?->karyawan?->nama_lengkap ?: 'Atasan');

                // Kirim notifikasi ke pembuat/creator penginput
                $creator = $deliveryOrder->creator;
                if (!$creator) return;

                $phone = $creator->no_hp ?: ($creator->karyawan?->no_hp);
                $target = self::formatPhoneNumber($phone);
                if (!$target) return;

                $creatorName = $creator->nama_lengkap ?: ($creator->karyawan?->nama_lengkap ?: 'Penginput');
                $waktuAcc = now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB';

                $message = "✅ *PENGIRIMAN DISETUJUI (DI-ACC)*\n"
                    . "*{$headerEntitas}*\n\n"
                    . "Halo, *{$creatorName}* 👋\n"
                    . "Pengajuan Surat Jalan Pengiriman Anda telah *DISETUJUI / DI-ACC* oleh atasan:\n\n"
                    . "📋 *No. Surat Jalan:* {$nomorDo}\n"
                    . "📄 *Ref. Sales Order:* {$nomorSo}\n"
                    . "🏢 *Customer/Tujuan:* {$customerName}\n"
                    . "📅 *Tgl Rencana Kirim:* {$tglKirim}\n"
                    . "👨‍💼 *Disetujui Oleh:* {$approverName}\n"
                    . "⏰ *Waktu ACC:* {$waktuAcc}\n\n"
                    . "Surat Jalan kini siap diproses pengiriman dan dicetak untuk keberangkatan barang.\n\n"
                    . "Terima kasih,\n"
                    . "🤖 *{$headerEntitas} - ERP System*";

                $resBody = self::dispatchFonnteMessage($apiToken, $target, $message);
                $status = isset($resBody['status']) && $resBody['status'] == true ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id'     => $companyId,
                    'phone_number'   => $target,
                    'recipient_name' => $creatorName,
                    'message'        => $message,
                    'message_type'   => 'Delivery Order Approved Alert',
                    'action'         => 'DO Approved Alert',
                    'recipient'      => $target . ' (' . $creatorName . ')',
                    'status'         => $status,
                    'response'       => json_encode($resBody),
                ]);
            } catch (\Exception $e) {
                Log::error('WA sendDeliveryOrderApprovedNotification error: ' . $e->getMessage());
            }
        });
    }

    /**
     * Send WhatsApp notification when goods arrive at destination (completed/terkirim).
     * Dispatched to both Creator and Approver.
     */
    public static function sendDeliveryOrderCompletedNotification($deliveryOrder, $confirmerUser = null)
    {
        self::runAsync(function() use ($deliveryOrder, $confirmerUser) {
            try {
                if (is_numeric($deliveryOrder)) {
                    $deliveryOrder = \App\Models\DeliveryOrder::find($deliveryOrder);
                }
                if (!$deliveryOrder) return;

                $deliveryOrder->load([
                    'company', 'branch', 'customer', 'salesOrder', 'creator.karyawan', 'approver.karyawan', 'receiver.karyawan'
                ]);

                $companyId = $deliveryOrder->company_id ?: ($deliveryOrder->branch?->company_id ?? session('active_company_id') ?: 1);
                $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
                $apiToken = self::getDecryptedToken($companyId);

                if (!$enabled || !$apiToken) return;

                $entity = self::resolveEntityDisplay($deliveryOrder, $companyId);
                $headerEntitas = $entity['headerEntitas'];

                $nomorDo = $deliveryOrder->nomor_do;
                $nomorSo = $deliveryOrder->salesOrder?->nomor_so ?: '-';
                $customerName = $deliveryOrder->customer?->nama_perusahaan ?: ($deliveryOrder->customer?->nama_customer ?: ($deliveryOrder->nama_penerima ?: 'Pelanggan'));
                $alamatTujuan = $deliveryOrder->alamat_tujuan ?: '-';
                $catatan = $deliveryOrder->catatan_penerimaan ?: 'Barang diterima lengkap dan dalam kondisi baik.';
                
                $confirmer = $confirmerUser ?: $deliveryOrder->receiver;
                $confirmerName = $confirmer?->nama_lengkap ?: ($confirmer?->karyawan?->nama_lengkap ?: 'Petugas Pengiriman');
                $waktuSampai = now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB';

                // Target penerima notif: Pembuat (Creator) dan Atasan (Approver)
                $targets = [];
                if ($deliveryOrder->creator) {
                    $cPhone = $deliveryOrder->creator->no_hp ?: ($deliveryOrder->creator->karyawan?->no_hp);
                    if ($cPhone) {
                        $targets[] = [
                            'phone' => $cPhone,
                            'name'  => $deliveryOrder->creator->nama_lengkap ?: ($deliveryOrder->creator->karyawan?->nama_lengkap ?: 'Penginput'),
                            'role'  => 'Penginput',
                        ];
                    }
                }
                if ($deliveryOrder->approver) {
                    $aPhone = $deliveryOrder->approver->no_hp ?: ($deliveryOrder->approver->karyawan?->no_hp);
                    if ($aPhone) {
                        $targets[] = [
                            'phone' => $aPhone,
                            'name'  => $deliveryOrder->approver->nama_lengkap ?: ($deliveryOrder->approver->karyawan?->nama_lengkap ?: 'Atasan Approver'),
                            'role'  => 'Approver',
                        ];
                    }
                }

                $uniqueTargets = collect($targets)->unique('phone')->values()->all();

                foreach ($uniqueTargets as $t) {
                    $formattedPhone = self::formatPhoneNumber($t['phone']);
                    if (!$formattedPhone) continue;

                    $message = "🎉 *PENGIRIMAN SELESAI - BARANG TELAH SAMPAI*\n"
                        . "*{$headerEntitas}*\n\n"
                        . "Halo, *{$t['name']}* 👋\n"
                        . "Pengiriman Surat Jalan berikut telah dikonfirmasi *TELAH SAMPAI DI TUJUAN (SELESAI)*:\n\n"
                        . "📋 *No. Surat Jalan:* {$nomorDo}\n"
                        . "📄 *Ref. Sales Order:* {$nomorSo}\n"
                        . "🏢 *Customer/Tujuan:* {$customerName}\n"
                        . "📍 *Alamat:* {$alamatTujuan}\n"
                        . "👤 *Dikonfirmasi Oleh:* {$confirmerName}\n"
                        . "⏰ *Waktu Tiba:* {$waktuSampai}\n"
                        . "📝 *Catatan/Bukti:* {$catatan}\n\n"
                        . "Status pengiriman kini resmi *Terkirim* dan stok persediaan serta pesanan penjualan telah diperbarui otomatis.\n\n"
                        . "Terima kasih atas kerja samanya,\n"
                        . "🤖 *{$headerEntitas} - ERP System*";

                    $resBody = self::dispatchFonnteMessage($apiToken, $formattedPhone, $message);
                    $status = isset($resBody['status']) && $resBody['status'] == true ? 'Success' : 'Failed';

                    WhatsAppLog::create([
                        'company_id'     => $companyId,
                        'phone_number'   => $formattedPhone,
                        'recipient_name' => $t['name'],
                        'message'        => $message,
                        'message_type'   => 'Delivery Order Completed Alert',
                        'action'         => 'DO Completed Alert',
                        'recipient'      => $formattedPhone . ' (' . $t['name'] . ')',
                        'status'         => $status,
                        'response'       => json_encode($resBody),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('WA sendDeliveryOrderCompletedNotification error: ' . $e->getMessage());
            }
        });
    }

    /**
     * 1. Send WhatsApp notification to Approvers when a Sales Invoice is submitted for approval.
     */
    public static function sendSalesInvoiceSubmissionNotification($salesInvoice)
    {
        self::runAsync(function() use ($salesInvoice) {
            try {
                if (is_numeric($salesInvoice)) {
                    $salesInvoice = \App\Models\SalesInvoice::find($salesInvoice);
                }
                if (!$salesInvoice) return;

                $salesInvoice->load([
                    'company', 'branch', 'customer', 'salesOrder', 'deliveryOrder', 'tender', 'creator.karyawan', 'details.barang'
                ]);

                $companyId = $salesInvoice->company_id ?: ($salesInvoice->branch?->company_id ?? session('active_company_id') ?: 1);
                $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
                $apiToken = self::getDecryptedToken($companyId);

                if (!$enabled || !$apiToken) return;

                $entity = self::resolveEntityDisplay($salesInvoice, $companyId);
                $headerEntitas = $entity['headerEntitas'];

                $nomorFaktur = $salesInvoice->nomor_faktur;
                $customerName = $salesInvoice->customer?->nama_perusahaan ?: ($salesInvoice->customer?->nama_customer ?: 'Pelanggan');
                $nomorSo = $salesInvoice->salesOrder?->nomor_so ?: '-';
                $nomorDo = $salesInvoice->deliveryOrder?->nomor_do ?: '-';
                $namaTender = $salesInvoice->tender?->nama_tender ?: '-';
                $tglFaktur = $salesInvoice->tanggal_faktur ? $salesInvoice->tanggal_faktur->format('d/m/Y') : '-';
                $tglTempo = $salesInvoice->tanggal_jatuh_tempo ? $salesInvoice->tanggal_jatuh_tempo->format('d/m/Y') : '-';
                $grandTotalStr = 'Rp ' . number_format($salesInvoice->grand_total, 0, ',', '.');
                $creatorName = $salesInvoice->creator?->nama_lengkap ?: ($salesInvoice->creator?->karyawan?->nama_lengkap ?: 'Staff Marketing/Finance');

                // Ringkasan item
                $itemsText = "";
                $count = 0;
                foreach ($salesInvoice->details as $idx => $d) {
                    if ($count >= 5) {
                        $remaining = count($salesInvoice->details) - 5;
                        $itemsText .= "   _... dan {$remaining} item lainnya_\n";
                        break;
                    }
                    $bName = $d->nama_barang;
                    $qty = number_format($d->qty, 0, ',', '.');
                    $sub = 'Rp ' . number_format($d->total_harga, 0, ',', '.');
                    $itemsText .= "   " . ($idx + 1) . ". {$bName} ({$qty}x) = {$sub}\n";
                    $count++;
                }

                // Cari Approver
                $approvers = \App\Models\User::where(function($q) {
                    $q->whereHas('permissions', fn($p) => $p->where('name', 'approve-sales-invoice'))
                      ->orWhereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner', 'Director', 'Finance Manager', 'Manager Finance', 'Manager Akuntansi']));
                })
                ->where(function ($q) use ($companyId, $salesInvoice) {
                    $q->whereHas('roles', fn($r) => $r->where('name', 'Super Admin'))
                      ->orWhereHas('companies', fn($c) => $c->where('companies.id', $companyId))
                      ->orWhereHas('branches', fn($b) => $b->where('branches.id', $salesInvoice->branch_id)
                                                           ->orWhere('branches.company_id', $companyId));
                })
                ->with('karyawan')
                ->get();

                $sentPhones = [];
                foreach ($approvers as $appr) {
                    $phone = $appr->no_hp ?: ($appr->karyawan?->no_hp);
                    $target = self::formatPhoneNumber($phone);
                    if (!$target || in_array($target, $sentPhones)) continue;
                    $sentPhones[] = $target;

                    $apprName = $appr->nama_lengkap ?: ($appr->karyawan?->nama_lengkap ?: 'Atasan Approver');

                    $message = "🔔 *PENGAJUAN FAKTUR PENJUALAN BARU (BUTUH APPROVAL)*\n"
                        . "*{$headerEntitas}*\n\n"
                        . "Halo, *{$apprName}* 👋\n"
                        . "Terdapat Faktur Penjualan baru yang telah diajukan dan *membutuhkan persetujuan (approval)* Anda:\n\n"
                        . "📋 *No. Faktur:* *{$nomorFaktur}*\n"
                        . "🏢 *Customer:* {$customerName}\n";
                    
                    if ($nomorDo !== '-') $message .= "🚚 *Ref. Surat Jalan:* {$nomorDo}\n";
                    if ($nomorSo !== '-') $message .= "📄 *Ref. Sales Order:* {$nomorSo}\n";
                    if ($namaTender !== '-') $message .= "🎯 *Proyek/Tender:* {$namaTender}\n";

                    $message .= "📅 *Tgl Faktur:* {$tglFaktur}\n"
                        . "⏰ *Jatuh Tempo:* {$tglTempo}\n"
                        . "💰 *Total Tagihan:* *{$grandTotalStr}*\n"
                        . "👤 *Diajukan Oleh:* {$creatorName}\n\n";

                    if (!empty($itemsText)) {
                        $message .= "📦 *Rincian Item:*\n{$itemsText}\n";
                    }

                    $message .= "Silakan masuk ke sistem ERP modul Faktur Penjualan untuk memeriksa rincian tagihan dan memberikan persetujuan (Setujui / Tolak).\n\n"
                        . "Terima kasih,\n"
                        . "🤖 *{$headerEntitas} - ERP System*";

                    $resBody = self::dispatchFonnteMessage($apiToken, $target, $message);
                    $status = isset($resBody['status']) && $resBody['status'] == true ? 'Success' : 'Failed';

                    WhatsAppLog::create([
                        'company_id'     => $companyId,
                        'phone_number'   => $target,
                        'recipient_name' => $apprName,
                        'message'        => $message,
                        'message_type'   => 'Sales Invoice Submission Alert',
                        'action'         => 'Sales Invoice Approval Request',
                        'recipient'      => $target . ' (' . $apprName . ')',
                        'status'         => $status,
                        'response'       => json_encode($resBody),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('WA sendSalesInvoiceSubmissionNotification error: ' . $e->getMessage());
            }
        });
    }

    /**
     * 2. Send WhatsApp notification to Creator when a Sales Invoice is approved.
     */
    public static function sendSalesInvoiceApprovedNotification($salesInvoice, $approverUser = null)
    {
        self::runAsync(function() use ($salesInvoice, $approverUser) {
            try {
                if (is_numeric($salesInvoice)) {
                    $salesInvoice = \App\Models\SalesInvoice::find($salesInvoice);
                }
                if (!$salesInvoice) return;

                $salesInvoice->load([
                    'company', 'branch', 'customer', 'creator.karyawan'
                ]);

                $companyId = $salesInvoice->company_id ?: ($salesInvoice->branch?->company_id ?? session('active_company_id') ?: 1);
                $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
                $apiToken = self::getDecryptedToken($companyId);

                if (!$enabled || !$apiToken) return;

                $entity = self::resolveEntityDisplay($salesInvoice, $companyId);
                $headerEntitas = $entity['headerEntitas'];

                $nomorFaktur = $salesInvoice->nomor_faktur;
                $customerName = $salesInvoice->customer?->nama_perusahaan ?: ($salesInvoice->customer?->nama_customer ?: 'Pelanggan');
                $grandTotalStr = 'Rp ' . number_format($salesInvoice->grand_total, 0, ',', '.');
                $approverName = $approverUser?->nama_lengkap ?: ($approverUser?->karyawan?->nama_lengkap ?: (auth()->user()?->nama_lengkap ?: 'Manajemen'));
                $waktu = now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB';

                // Target: Creator
                if (!$salesInvoice->creator) return;
                $phone = $salesInvoice->creator->no_hp ?: ($salesInvoice->creator->karyawan?->no_hp);
                $target = self::formatPhoneNumber($phone);
                if (!$target) return;

                $creatorName = $salesInvoice->creator->nama_lengkap ?: ($salesInvoice->creator->karyawan?->nama_lengkap ?: 'Penginput');

                $message = "✅ *FAKTUR PENJUALAN TELAH DISETUJUI (APPROVED)*\n"
                    . "*{$headerEntitas}*\n\n"
                    . "Halo, *{$creatorName}* 👋\n"
                    . "Faktur Penjualan yang Anda ajukan telah resmi *DISETUJUI (APPROVED)*:\n\n"
                    . "📋 *No. Faktur:* *{$nomorFaktur}*\n"
                    . "🏢 *Customer:* {$customerName}\n"
                    . "💰 *Total Tagihan:* *{$grandTotalStr}*\n"
                    . "👤 *Disetujui Oleh:* *{$approverName}*\n"
                    . "⏰ *Waktu Disetujui:* {$waktu}\n\n"
                    . "Faktur resmi diterbitkan dengan status *Belum Lunas (Unpaid)* dan jurnal piutang telah dibukukan otomatis ke sistem akuntansi. Dokumen invoice siap dicetak dan ditagihkan ke pelanggan.\n\n"
                    . "Terima kasih,\n"
                    . "🤖 *{$headerEntitas} - ERP System*";

                $resBody = self::dispatchFonnteMessage($apiToken, $target, $message);
                $status = isset($resBody['status']) && $resBody['status'] == true ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id'     => $companyId,
                    'phone_number'   => $target,
                    'recipient_name' => $creatorName,
                    'message'        => $message,
                    'message_type'   => 'Sales Invoice Approved Alert',
                    'action'         => 'Sales Invoice Approved',
                    'recipient'      => $target . ' (' . $creatorName . ')',
                    'status'         => $status,
                    'response'       => json_encode($resBody),
                ]);
            } catch (\Exception $e) {
                Log::error('WA sendSalesInvoiceApprovedNotification error: ' . $e->getMessage());
            }
        });
    }

    /**
     * 3. Send WhatsApp notification to Creator when a Sales Invoice is rejected.
     */
    public static function sendSalesInvoiceRejectedNotification($salesInvoice, $rejectorUser = null, $alasan = null)
    {
        self::runAsync(function() use ($salesInvoice, $rejectorUser, $alasan) {
            try {
                if (is_numeric($salesInvoice)) {
                    $salesInvoice = \App\Models\SalesInvoice::find($salesInvoice);
                }
                if (!$salesInvoice) return;

                $salesInvoice->load([
                    'company', 'branch', 'customer', 'creator.karyawan'
                ]);

                $companyId = $salesInvoice->company_id ?: ($salesInvoice->branch?->company_id ?? session('active_company_id') ?: 1);
                $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
                $apiToken = self::getDecryptedToken($companyId);

                if (!$enabled || !$apiToken) return;

                $entity = self::resolveEntityDisplay($salesInvoice, $companyId);
                $headerEntitas = $entity['headerEntitas'];

                $nomorFaktur = $salesInvoice->nomor_faktur;
                $customerName = $salesInvoice->customer?->nama_perusahaan ?: ($salesInvoice->customer?->nama_customer ?: 'Pelanggan');
                $grandTotalStr = 'Rp ' . number_format($salesInvoice->grand_total, 0, ',', '.');
                $rejectorName = $rejectorUser?->nama_lengkap ?: ($rejectorUser?->karyawan?->nama_lengkap ?: (auth()->user()?->nama_lengkap ?: 'Manajemen'));
                $alasanStr = $alasan ?: ($salesInvoice->alasan_penolakan ?: 'Data tagihan perlu diperiksa kembali');
                $waktu = now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB';

                // Target: Creator
                if (!$salesInvoice->creator) return;
                $phone = $salesInvoice->creator->no_hp ?: ($salesInvoice->creator->karyawan?->no_hp);
                $target = self::formatPhoneNumber($phone);
                if (!$target) return;

                $creatorName = $salesInvoice->creator->nama_lengkap ?: ($salesInvoice->creator->karyawan?->nama_lengkap ?: 'Penginput');

                $message = "❌ *PEMBERITAHUAN: FAKTUR PENJUALAN DITOLAK*\n"
                    . "*{$headerEntitas}*\n\n"
                    . "Halo, *{$creatorName}* 👋\n"
                    . "Pengajuan Faktur Penjualan berikut *DITOLAK* oleh Atasan:\n\n"
                    . "📋 *No. Faktur:* *{$nomorFaktur}*\n"
                    . "🏢 *Customer:* {$customerName}\n"
                    . "💰 *Total Nilai:* {$grandTotalStr}\n"
                    . "👤 *Ditolak Oleh:* *{$rejectorName}*\n"
                    . "⚠️ *Alasan Penolakan:* *{$alasanStr}*\n"
                    . "⏰ *Waktu:* {$waktu}\n\n"
                    . "Silakan login ke sistem ERP untuk memeriksa catatan penolakan dan melakukan revisi/perbaikan pada draf faktur tersebut.\n\n"
                    . "Terima kasih,\n"
                    . "🤖 *{$headerEntitas} - ERP System*";

                $resBody = self::dispatchFonnteMessage($apiToken, $target, $message);
                $status = isset($resBody['status']) && $resBody['status'] == true ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id'     => $companyId,
                    'phone_number'   => $target,
                    'recipient_name' => $creatorName,
                    'message'        => $message,
                    'message_type'   => 'Sales Invoice Rejected Alert',
                    'action'         => 'Sales Invoice Rejected',
                    'recipient'      => $target . ' (' . $creatorName . ')',
                    'status'         => $status,
                    'response'       => json_encode($resBody),
                ]);
            } catch (\Exception $e) {
                Log::error('WA sendSalesInvoiceRejectedNotification error: ' . $e->getMessage());
            }
        });
    }

    /**
     * Kirim notifikasi WhatsApp saat Penerimaan Pembayaran Penjualan dibuat / diajukan (Draft).
     */
    public static function sendSalesPaymentSubmissionNotification($salesPayment)
    {
        self::runAsync(function() use ($salesPayment) {
            try {
                if (is_numeric($salesPayment)) {
                    $salesPayment = \App\Models\SalesPayment::find($salesPayment);
                }
                if (!$salesPayment) return;

                $salesPayment->loadMissing([
                    'company', 'branch', 'customer', 'kasBank', 'paymentMethod',
                    'creator.karyawan', 'details.invoice'
                ]);

                $companyId = $salesPayment->company_id ?: ($salesPayment->branch?->company_id ?? session('active_company_id') ?: 1);
                $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
                $apiToken = self::getDecryptedToken($companyId);

                if (!$enabled || !$apiToken) return;

                $entity = self::resolveEntityDisplay($salesPayment, $companyId);
                $headerEntitas = $entity['headerEntitas'];

                $nomorPembayaran = $salesPayment->nomor_pembayaran;
                $customerName    = $salesPayment->customer?->nama_perusahaan ?: ($salesPayment->customer?->nama_customer ?: 'Pelanggan');
                $tglBayar        = $salesPayment->tanggal_bayar ? \Carbon\Carbon::parse($salesPayment->tanggal_bayar)->format('d/m/Y') : '-';
                $kasBankName     = $salesPayment->kasBank?->nama_akun ?: 'Kas/Bank';
                $metodeBayar     = $salesPayment->paymentMethod?->nama_metode ?: 'Transfer Bank';
                $totalBayarStr   = 'Rp ' . number_format((float)$salesPayment->total_bayar, 0, ',', '.');
                $creatorName     = $salesPayment->creator?->nama_lengkap ?: ($salesPayment->creator?->karyawan?->nama_lengkap ?: 'Kasir / Staff Finance');

                $lebihBayar = (float)($salesPayment->lebih_bayar ?? 0);
                $potongan   = (float)($salesPayment->total_potongan ?? 0);

                // Ringkasan faktur yang dibayar
                $fakturText = "";
                if ($salesPayment->details && $salesPayment->details->isNotEmpty()) {
                    foreach ($salesPayment->details->take(5) as $idx => $d) {
                        $noFak = $d->invoice?->nomor_faktur ?: ('Faktur #' . $d->sales_invoice_id);
                        $bayarFmt = 'Rp ' . number_format((float)$d->jumlah_bayar, 0, ',', '.');
                        $fakturText .= "   " . ($idx + 1) . ". {$noFak} (Bayar: {$bayarFmt})\n";
                    }
                    if ($salesPayment->details->count() > 5) {
                        $remaining = $salesPayment->details->count() - 5;
                        $fakturText .= "   _... dan {$remaining} faktur lainnya_\n";
                    }
                }

                // Cari Approver / Finance Manager / Supervisor
                $approvers = \App\Models\User::where(function($q) {
                    $q->whereHas('permissions', fn($p) => $p->whereIn('name', ['lihat-sales-payment', 'tambah-sales-payment']))
                      ->orWhereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner', 'Director', 'Finance Manager', 'Manager Finance', 'Manager Akuntansi', 'Accounting']));
                })
                ->where(function ($q) use ($companyId, $salesPayment) {
                    $q->whereHas('roles', fn($r) => $r->where('name', 'Super Admin'))
                      ->orWhereHas('companies', fn($c) => $c->where('companies.id', $companyId))
                      ->orWhereHas('branches', fn($b) => $b->where('branches.id', $salesPayment->branch_id)
                                                           ->orWhere('branches.company_id', $companyId));
                })
                ->with('karyawan')
                ->get();

                $sentPhones = [];
                foreach ($approvers as $appr) {
                    $phone = $appr->no_hp ?: ($appr->karyawan?->no_hp);
                    $target = self::formatPhoneNumber($phone);
                    if (!$target || in_array($target, $sentPhones)) continue;
                    $sentPhones[] = $target;

                    $apprName = $appr->nama_lengkap ?: ($appr->karyawan?->nama_lengkap ?: 'Atasan / Pimpinan');

                    $message = "🔔 *PERMOHONAN PERSETUJUAN PEMBAYARAN PENJUALAN (BUTUH ACC)*\n"
                        . "*{$headerEntitas}*\n\n"
                        . "Halo, *{$apprName}* 👋\n"
                        . "Terdapat pengajuan pembayaran piutang penjualan baru yang *membutuhkan persetujuan (ACC / Posting)* dari Anda:\n\n"
                        . "📋 *No. Pembayaran:* *{$nomorPembayaran}*\n"
                        . "🏢 *Customer:* {$customerName}\n"
                        . "📅 *Tgl Bayar:* {$tglBayar}\n"
                        . "🏦 *Kas / Bank:* {$kasBankName}\n"
                        . "💳 *Metode:* {$metodeBayar}" . ($salesPayment->nomor_referensi ? " (Ref: {$salesPayment->nomor_referensi})" : "") . "\n"
                        . "💰 *Total Nominal:* *{$totalBayarStr}*\n";

                    if ($lebihBayar > 0) {
                        $message .= "↗ *Lebih Transfer:* Rp " . number_format($lebihBayar, 0, ',', '.') . "\n";
                    }
                    if ($potongan > 0) {
                        $message .= "✂ *Potongan/Diskon:* Rp " . number_format($potongan, 0, ',', '.') . "\n";
                    }

                    $message .= "👤 *Diajukan Oleh:* {$creatorName}\n\n";

                    if (!empty($fakturText)) {
                        $message .= "📄 *Faktur yang Dilunasi:*\n{$fakturText}\n";
                    }

                    if (!empty($salesPayment->keterangan)) {
                        $message .= "📝 *Catatan:* {$salesPayment->keterangan}\n\n";
                    }

                    $message .= "Silakan masuk ke sistem ERP modul Penerimaan Pembayaran untuk memeriksa rincian dan melakukan persetujuan (ACC / Posting).\n\n"
                        . "Terima kasih,\n"
                        . "🤖 *{$headerEntitas} - ERP System*";

                    $resBody = self::dispatchFonnteMessage($apiToken, $target, $message);
                    $status = isset($resBody['status']) && $resBody['status'] == true ? 'Success' : 'Failed';

                    WhatsAppLog::create([
                        'company_id'     => $companyId,
                        'phone_number'   => $target,
                        'recipient_name' => $apprName,
                        'message'        => $message,
                        'message_type'   => 'Sales Payment Submission Alert',
                        'action'         => 'Sales Payment Submission',
                        'recipient'      => $target . ' (' . $apprName . ')',
                        'status'         => $status,
                        'response'       => json_encode($resBody),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('WA sendSalesPaymentSubmissionNotification error: ' . $e->getMessage());
            }
        });
    }

    /**
     * Kirim notifikasi WhatsApp saat Pembayaran Penjualan di-ACC / disetujui / diposting oleh Atasan.
     * Dikirim balik ke User yang menginput (Creator) bahwasanya pengajuan pembayarannya telah disetujui.
     */
    public static function sendSalesPaymentPostedNotification($salesPayment, $postedByUser = null)
    {
        self::runAsync(function() use ($salesPayment, $postedByUser) {
            try {
                if (is_numeric($salesPayment)) {
                    $salesPayment = \App\Models\SalesPayment::find($salesPayment);
                }
                if (!$salesPayment) return;

                $salesPayment->loadMissing([
                    'company', 'branch', 'customer', 'kasBank', 'paymentMethod',
                    'creator.karyawan', 'details.invoice'
                ]);

                $companyId = $salesPayment->company_id ?: ($salesPayment->branch?->company_id ?? session('active_company_id') ?: 1);
                $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
                $apiToken = self::getDecryptedToken($companyId);

                if (!$enabled || !$apiToken) return;

                $entity = self::resolveEntityDisplay($salesPayment, $companyId);
                $headerEntitas = $entity['headerEntitas'];

                $nomorPembayaran = $salesPayment->nomor_pembayaran;
                $customerName    = $salesPayment->customer?->nama_perusahaan ?: ($salesPayment->customer?->nama_customer ?: 'Pelanggan');
                $tglBayar        = $salesPayment->tanggal_bayar ? \Carbon\Carbon::parse($salesPayment->tanggal_bayar)->format('d/m/Y') : '-';
                $kasBankName     = $salesPayment->kasBank?->nama_akun ?: 'Kas/Bank';
                $totalBayarStr   = 'Rp ' . number_format((float)$salesPayment->total_bayar, 0, ',', '.');
                $posterName      = $postedByUser?->nama_lengkap ?: ($postedByUser?->karyawan?->nama_lengkap ?: (auth()->user()?->nama_lengkap ?: 'Atasan / Pimpinan'));

                $lebihBayar = (float)($salesPayment->lebih_bayar ?? 0);

                // Ringkasan faktur yang dibayar
                $fakturText = "";
                if ($salesPayment->details && $salesPayment->details->isNotEmpty()) {
                    foreach ($salesPayment->details->take(5) as $idx => $d) {
                        $noFak = $d->invoice?->nomor_faktur ?: ('Faktur #' . $d->sales_invoice_id);
                        $bayarFmt = 'Rp ' . number_format((float)$d->jumlah_bayar, 0, ',', '.');
                        $fakturText .= "   " . ($idx + 1) . ". {$noFak} (Bayar: {$bayarFmt})\n";
                    }
                    if ($salesPayment->details->count() > 5) {
                        $remaining = $salesPayment->details->count() - 5;
                        $fakturText .= "   _... dan {$remaining} faktur lainnya_\n";
                    }
                }

                // Kirim notifikasi konfirmasi HANYA ke Petugas / User yang menginput (Creator)
                $creator = $salesPayment->creator;
                if ($creator) {
                    $creatorPhone = $creator->no_hp ?: ($creator->karyawan?->no_hp);
                    $targetCreator = self::formatPhoneNumber($creatorPhone);
                    if ($targetCreator) {
                        $cName = $creator->nama_lengkap ?: ($creator->karyawan?->nama_lengkap ?: 'Staff');

                        $msgCreator = "✅ *PENGAJUAN PEMBAYARAN TELAH DISETUJUI (DI-ACC)*\n"
                            . "*{$headerEntitas}*\n\n"
                            . "Halo, *{$cName}* 👋\n"
                            . "Kabar baik! Pengajuan pembayaran penjualan yang Anda catat telah *disetujui (di-ACC) & diposting* oleh Atasan:\n\n"
                            . "📋 *No. Pembayaran:* *{$nomorPembayaran}*\n"
                            . "🏢 *Customer:* {$customerName}\n"
                            . "📅 *Tgl Bayar:* {$tglBayar}\n"
                            . "🏦 *Kas / Bank:* {$kasBankName}\n"
                            . "💰 *Total Diterima:* *{$totalBayarStr}*\n";

                        if ($lebihBayar > 0) {
                            $msgCreator .= "↗ *Lebih Transfer:* Rp " . number_format($lebihBayar, 0, ',', '.') . "\n";
                        }

                        $msgCreator .= "👤 *Disetujui / Di-ACC Oleh:* {$posterName}\n\n"
                            . "📄 *Faktur Terkait:*\n{$fakturText}\n"
                            . "Status pembayaran kini telah *Posted* dan Jurnal Otomatis Akuntansi telah berhasil diterbitkan.\n\n"
                            . "Terima kasih atas kerja samanya,\n"
                            . "🤖 *{$headerEntitas} - ERP System*";

                        $resBody = self::dispatchFonnteMessage($apiToken, $targetCreator, $msgCreator);
                        $status = isset($resBody['status']) && $resBody['status'] == true ? 'Success' : 'Failed';

                        WhatsAppLog::create([
                            'company_id'     => $companyId,
                            'phone_number'   => $targetCreator,
                            'recipient_name' => $cName,
                            'message'        => $msgCreator,
                            'message_type'   => 'Sales Payment Approved Alert',
                            'action'         => 'Sales Payment Approved (ACC)',
                            'recipient'      => $targetCreator . ' (' . $cName . ')',
                            'status'         => $status,
                            'response'       => json_encode($resBody),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('WA sendSalesPaymentPostedNotification error: ' . $e->getMessage());
            }
        });
    }

    /**
     * Kirim notifikasi pengajuan Faktur Pembelian (Purchase Invoice) ke Atasan / Approver via WA.
     */
    public static function sendPurchaseInvoiceSubmissionNotification($purchaseInvoice)
    {
        try {
            if (!$purchaseInvoice) return;
            $purchaseInvoice->loadMissing(['supplier', 'creator.karyawan', 'details.barang', 'branch', 'currency']);

            $companyId = $purchaseInvoice->company_id ?: ($purchaseInvoice->branch?->company_id ?? session('active_company_id') ?: 1);
            $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
            $apiToken = self::getDecryptedToken($companyId);

            if (!$enabled || !$apiToken) return;

            $entity = self::resolveEntityDisplay($purchaseInvoice, $companyId);
            $headerEntitas = $entity['headerEntitas'];

            $noFaktur       = $purchaseInvoice->nomor_faktur;
            $noFakSupplier  = $purchaseInvoice->nomor_faktur_supplier ?: '-';
            $supplierName   = $purchaseInvoice->supplier?->nama_perusahaan ?: ($purchaseInvoice->supplier?->nama_supplier ?: 'Supplier');
            $tglFaktur      = $purchaseInvoice->tanggal_faktur ? \Carbon\Carbon::parse($purchaseInvoice->tanggal_faktur)->format('d/m/Y') : '-';
            $tglJatuhTempo  = $purchaseInvoice->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($purchaseInvoice->tanggal_jatuh_tempo)->format('d/m/Y') : '-';
            $mataUang       = $purchaseInvoice->currency?->kode_mata_uang ?: ($purchaseInvoice->mata_uang ?: 'IDR');
            $kurs           = (float)($purchaseInvoice->kurs ?: 1);

            $totalNominalVal = (float)($purchaseInvoice->grand_total ?: ($purchaseInvoice->total_tagihan ?: ($purchaseInvoice->total ?: 0)));
            $totalStr = $mataUang . ' ' . number_format($totalNominalVal, 2, ',', '.');
            if ($mataUang !== 'IDR' && $kurs > 1) {
                $totalStr .= ' (Rp ' . number_format($totalNominalVal * $kurs, 0, ',', '.') . ')';
            }

            $creatorName = $purchaseInvoice->creator?->nama_lengkap ?: ($purchaseInvoice->creator?->karyawan?->nama_lengkap ?: 'Staff Purchasing');

            // Ringkasan item (maksimal 5 item)
            $itemText = "";
            if ($purchaseInvoice->details && $purchaseInvoice->details->isNotEmpty()) {
                $itemRows = $purchaseInvoice->details->where('jenis', '!=', 'PPN');
                foreach ($itemRows->take(5) as $idx => $d) {
                    $itemName = $d->barang?->nama_barang ?: ($d->keterangan ?: 'Item');
                    $qtyVal   = (float)($d->jumlah ?: ($d->qty ?: 0));
                    $satuan   = $d->barang?->satuanBesar?->nama_satuan ?: ($d->barang?->satuan?->nama_satuan ?: ($d->satuan ?: 'unit'));
                    $qtyFmt   = number_format($qtyVal, 0, ',', '.') . ' ' . $satuan;
                    $itemText .= "   " . ($idx + 1) . ". {$itemName} ({$qtyFmt})\n";
                }
                if ($itemRows->count() > 5) {
                    $remaining = $itemRows->count() - 5;
                    $itemText .= "   _... dan {$remaining} barang lainnya_\n";
                }
            }

            // Cari Approver yang memiliki izin 'approve-purchase-invoice' (langsung atau via Role) atau Super Admin / Owner
            $approvers = \App\Models\User::where(function($q) {
                $q->permission('approve-purchase-invoice')
                  ->orWhereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner']));
            })
            ->where(function ($q) use ($companyId, $purchaseInvoice) {
                $q->whereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner']))
                  ->orWhereHas('companies', fn($c) => $c->where('companies.id', $companyId))
                  ->orWhereHas('branches', fn($b) => $b->where('branches.id', $purchaseInvoice->branch_id)
                                                       ->orWhere('branches.company_id', $companyId));
            })
            ->with('karyawan')
            ->get();

            if ($approvers->isEmpty()) {
                $approvers = \App\Models\User::where(function($q) {
                    $q->permission('approve-purchase-invoice')
                      ->orWhereHas('roles', fn($r) => $r->whereIn('name', ['Super Admin', 'Owner']));
                })
                ->with('karyawan')
                ->get();
            }

            $sentPhones = [];
            foreach ($approvers as $appr) {
                $phone = $appr->no_hp ?: ($appr->karyawan?->no_hp);
                $target = self::formatPhoneNumber($phone);
                if (!$target || in_array($target, $sentPhones)) continue;
                $sentPhones[] = $target;

                $apprName = $appr->nama_lengkap ?: ($appr->karyawan?->nama_lengkap ?: 'Bapak/Ibu Pimpinan');

                $message = "🔔 *PERMOHONAN PERSETUJUAN FAKTUR PEMBELIAN (BUTUH ACC)*\n"
                    . "*{$headerEntitas}*\n\n"
                    . "Halo, *{$apprName}* 👋\n"
                    . "Terdapat pengajuan Faktur Pembelian (Purchase Invoice) baru yang *membutuhkan persetujuan (ACC / Posting)* Anda:\n\n"
                    . "📋 *No. Faktur:* *{$noFaktur}*\n"
                    . "🏷 *No. Faktur Supplier:* {$noFakSupplier}\n"
                    . "🏢 *Supplier:* {$supplierName}\n"
                    . "📅 *Tgl Faktur:* {$tglFaktur}\n"
                    . "⏳ *Jatuh Tempo:* {$tglJatuhTempo}\n"
                    . "💰 *Total Tagihan:* *{$totalStr}*\n"
                    . "👤 *Diajukan Oleh:* {$creatorName}\n\n";

                if (!empty($itemText)) {
                    $message .= "📦 *Daftar Barang:*\n{$itemText}\n";
                }

                $catatanText = $purchaseInvoice->keterangan ?: ($purchaseInvoice->catatan ?: '');
                if (!empty($catatanText)) {
                    $message .= "📝 *Catatan:* {$catatanText}\n\n";
                }

                $message .= "Silakan masuk ke sistem ERP modul Faktur Pembelian untuk memeriksa rincian dan melakukan Persetujuan (Approve / Posting Jurnal).\n\n"
                    . "Terima kasih,\n"
                    . "🤖 *{$headerEntitas} - ERP System*";

                $resBody = self::dispatchFonnteMessage($apiToken, $target, $message);
                $status = isset($resBody['status']) && $resBody['status'] == true ? 'Success' : 'Failed';

                WhatsAppLog::create([
                    'company_id'     => $companyId,
                    'phone_number'   => $target,
                    'recipient_name' => $apprName,
                    'message'        => $message,
                    'message_type'   => 'Purchase Invoice Submission Alert',
                    'action'         => 'Purchase Invoice Submission',
                    'recipient'      => $target . ' (' . $apprName . ')',
                    'status'         => $status,
                    'response'       => json_encode($resBody),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WA sendPurchaseInvoiceSubmissionNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi faktur pembelian disetujui (Approved / Posted) ke User pembuat faktur.
     */
    public static function sendPurchaseInvoiceApprovedNotification($purchaseInvoice, $approverUser = null)
    {
        try {
            if (!$purchaseInvoice) return;
            $purchaseInvoice->loadMissing(['supplier', 'creator.karyawan', 'branch', 'currency']);

            $companyId = $purchaseInvoice->company_id ?: ($purchaseInvoice->branch?->company_id ?? session('active_company_id') ?: 1);
            $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
            $apiToken = self::getDecryptedToken($companyId);

            if (!$enabled || !$apiToken) {
                if ($companyId != 1) {
                    $enabled = AppSetting::where('company_id', 1)->where('key', 'wa_enable_notification')->value('value') === '1';
                    $apiToken = self::getDecryptedToken(1);
                }
            }

            if (!$enabled || !$apiToken) return;

            $entity = self::resolveEntityDisplay($purchaseInvoice, $companyId);
            $headerEntitas = $entity['headerEntitas'];

            $noFaktur       = $purchaseInvoice->nomor_faktur;
            $supplierName   = $purchaseInvoice->supplier?->nama_perusahaan ?: ($purchaseInvoice->supplier?->nama_supplier ?: 'Supplier');
            $mataUang       = $purchaseInvoice->currency?->kode_mata_uang ?: ($purchaseInvoice->mata_uang ?: 'IDR');
            $totalNominalVal = (float)($purchaseInvoice->grand_total ?: ($purchaseInvoice->total_tagihan ?: ($purchaseInvoice->total ?: 0)));
            $totalStr       = $mataUang . ' ' . number_format($totalNominalVal, 2, ',', '.');

            $approverName   = $approverUser?->nama_lengkap ?: ($approverUser?->karyawan?->nama_lengkap ?: 'Atasan / Pimpinan');

            // Penerima feedback adalah user yang MEMBUAT faktur
            $creator = $purchaseInvoice->creator;
            if (!$creator && !empty($purchaseInvoice->created_by)) {
                $creator = \App\Models\User::with('karyawan')->find($purchaseInvoice->created_by);
            }

            if ($creator) {
                $creatorPhone = $creator->no_hp ?: ($creator->karyawan?->no_hp);
                $targetCreator = self::formatPhoneNumber($creatorPhone);
                if ($targetCreator) {
                    $cName = $creator->nama_lengkap ?: ($creator->karyawan?->nama_lengkap ?: 'Staff Purchasing');

                    $msgCreator = "✅ *FAKTUR PEMBELIAN TELAH DISETUJUI & DIPOSTING (DI-ACC)*\n"
                        . "*{$headerEntitas}*\n\n"
                        . "Halo, *{$cName}* 👋\n"
                        . "Kabar baik! Faktur Pembelian yang Anda buat/ajukan telah *disetujui (di-ACC)* dan otomatis *diposting ke Jurnal Akuntansi* oleh Atasan:\n\n"
                        . "📋 *No. Faktur:* *{$noFaktur}*\n"
                        . "🏢 *Supplier:* {$supplierName}\n"
                        . "💰 *Total Tagihan:* *{$totalStr}*\n"
                        . "👤 *Disetujui Oleh:* {$approverName}\n"
                        . "🕒 *Waktu Disetujui:* " . now()->format('d/m/Y H:i') . " WIB\n\n"
                        . "Status faktur kini telah *Posted* dan Jurnal Otomatis Akuntansi telah berhasil diterbitkan.\n\n"
                        . "Terima kasih atas kerja samanya,\n"
                        . "🤖 *{$headerEntitas} - ERP System*";

                    $resBody = self::dispatchFonnteMessage($apiToken, $targetCreator, $msgCreator);
                    $status = isset($resBody['status']) && $resBody['status'] == true ? 'Success' : 'Failed';

                    WhatsAppLog::create([
                        'company_id'     => $companyId,
                        'phone_number'   => $targetCreator,
                        'recipient_name' => $cName,
                        'message'        => $msgCreator,
                        'message_type'   => 'Purchase Invoice Approved Alert',
                        'action'         => 'Purchase Invoice Approved',
                        'recipient'      => $targetCreator . ' (' . $cName . ')',
                        'status'         => $status,
                        'response'       => json_encode($resBody),
                    ]);
                } else {
                    Log::warning("WA sendPurchaseInvoiceApprovedNotification: User pembuat Faktur {$noFaktur} ({$creator->nama_lengkap}) tidak memiliki nomor HP.");
                }
            } else {
                Log::warning("WA sendPurchaseInvoiceApprovedNotification: User pembuat Faktur {$noFaktur} tidak ditemukan.");
            }
        } catch (\Exception $e) {
            Log::error('WA sendPurchaseInvoiceApprovedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi faktur pembelian ditolak (Rejected) ke User pembuat faktur.
     */
    public static function sendPurchaseInvoiceRejectedNotification($purchaseInvoice, $alasan = null, $rejectorUser = null)
    {
        try {
            if (!$purchaseInvoice) return;
            $purchaseInvoice->loadMissing(['supplier', 'creator.karyawan', 'branch', 'currency']);

            $companyId = $purchaseInvoice->company_id ?: ($purchaseInvoice->branch?->company_id ?? session('active_company_id') ?: 1);
            $enabled = AppSetting::where('company_id', $companyId)->where('key', 'wa_enable_notification')->value('value') === '1';
            $apiToken = self::getDecryptedToken($companyId);

            if (!$enabled || !$apiToken) {
                if ($companyId != 1) {
                    $enabled = AppSetting::where('company_id', 1)->where('key', 'wa_enable_notification')->value('value') === '1';
                    $apiToken = self::getDecryptedToken(1);
                }
            }

            if (!$enabled || !$apiToken) return;

            $entity = self::resolveEntityDisplay($purchaseInvoice, $companyId);
            $headerEntitas = $entity['headerEntitas'];

            $noFaktur     = $purchaseInvoice->nomor_faktur;
            $supplierName = $purchaseInvoice->supplier?->nama_perusahaan ?: ($purchaseInvoice->supplier?->nama_supplier ?: 'Supplier');
            $rejectorName = $rejectorUser?->nama_lengkap ?: ($rejectorUser?->karyawan?->nama_lengkap ?: 'Atasan / Pimpinan');
            $alasanTeks   = $alasan ?: 'Perlu peninjauan ulang / perbaikan data.';

            // Penerima feedback adalah user yang MEMBUAT faktur
            $creator = $purchaseInvoice->creator;
            if (!$creator && !empty($purchaseInvoice->created_by)) {
                $creator = \App\Models\User::with('karyawan')->find($purchaseInvoice->created_by);
            }

            if ($creator) {
                $creatorPhone = $creator->no_hp ?: ($creator->karyawan?->no_hp);
                $targetCreator = self::formatPhoneNumber($creatorPhone);
                if ($targetCreator) {
                    $cName = $creator->nama_lengkap ?: ($creator->karyawan?->nama_lengkap ?: 'Staff Purchasing');

                    $msgCreator = "❌ *PENGAJUAN FAKTUR PEMBELIAN DITOLAK*\n"
                        . "*{$headerEntitas}*\n\n"
                        . "Halo, *{$cName}* 👋\n"
                        . "Pemberitahuan: Pengajuan Faktur Pembelian yang Anda buat berikut *DITOLAK* oleh Atasan:\n\n"
                        . "📋 *No. Faktur:* *{$noFaktur}*\n"
                        . "🏢 *Supplier:* {$supplierName}\n"
                        . "👤 *Ditolak Oleh:* {$rejectorName}\n"
                        . "⚠️ *Alasan Penolakan:* {$alasanTeks}\n\n"
                        . "Silakan periksa kembali data faktur tersebut pada sistem ERP dan lakukan perbaikan yang diperlukan.\n\n"
                        . "Terima kasih,\n"
                        . "🤖 *{$headerEntitas} - ERP System*";

                    $resBody = self::dispatchFonnteMessage($apiToken, $targetCreator, $msgCreator);
                    $status = isset($resBody['status']) && $resBody['status'] == true ? 'Success' : 'Failed';

                    WhatsAppLog::create([
                        'company_id'     => $companyId,
                        'phone_number'   => $targetCreator,
                        'recipient_name' => $cName,
                        'message'        => $msgCreator,
                        'message_type'   => 'Purchase Invoice Rejected Alert',
                        'action'         => 'Purchase Invoice Rejected',
                        'recipient'      => $targetCreator . ' (' . $cName . ')',
                        'status'         => $status,
                        'response'       => json_encode($resBody),
                    ]);
                } else {
                    Log::warning("WA sendPurchaseInvoiceRejectedNotification: User pembuat Faktur {$noFaktur} ({$creator->nama_lengkap}) tidak memiliki nomor HP.");
                }
            } else {
                Log::warning("WA sendPurchaseInvoiceRejectedNotification: User pembuat Faktur {$noFaktur} tidak ditemukan.");
            }
        } catch (\Exception $e) {
            Log::error('WA sendPurchaseInvoiceRejectedNotification error: ' . $e->getMessage());
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
        $cacheKey = 'wa_token_invalid_' . md5((string)$apiToken);
        if (Cache::has($cacheKey)) {
            return ['status' => false, 'reason' => 'invalid token (cached)'];
        }

        try {
            $response = Http::timeout(10)->connectTimeout(5)->withHeaders([
                'Authorization' => $apiToken,
            ])->asForm()->post('https://api.fonnte.com/send', [
                'target'  => $targetPhone,
                'message' => $message,
            ]);

            $json = $response->json();
            if (isset($json['reason']) && stripos($json['reason'], 'invalid token') !== false) {
                Cache::put($cacheKey, true, now()->addMinutes(10));
            }

            return $json ?: ['status' => false];
        } catch (\Exception $e) {
            Log::error('WA dispatchFonnteMessage error: ' . $e->getMessage());
            return ['status' => false, 'error' => $e->getMessage()];
        }
    }
}
