<?php

namespace App\Http\Controllers;

use App\Models\MasterCustomer;
use App\Models\CustomerAddress;
use App\Models\CustomerContact;
use App\Models\PaymentTerm;
use App\Models\MasterCurrency;
use App\Models\User;
use App\Helpers\CodeGeneratorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class MasterCustomerController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-customer');

        // Combined Single SQL Pass for Summary Cards (Fast & Indexed)
        $summaryStats = MasterCustomer::forActiveCompany()
            ->selectRaw("
                COUNT(*) as count_total,
                SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as count_active,
                SUM(CASE WHEN jenis_customer = 'Distributor' THEN 1 ELSE 0 END) as count_distributor,
                SUM(CASE WHEN jenis_customer IN ('Retail', 'End User') THEN 1 ELSE 0 END) as count_retail
            ")
            ->first();

        $countTotal       = (int) ($summaryStats->count_total ?? 0);
        $countActive      = (int) ($summaryStats->count_active ?? 0);
        $countDistributor = (int) ($summaryStats->count_distributor ?? 0);
        $countRetail      = (int) ($summaryStats->count_retail ?? 0);

        // Fetch currencies, payment terms for active PT, and users (who can act as Sales)
        $currencies   = MasterCurrency::where('status', 'Active')->get();
        $paymentTerms = PaymentTerm::forActiveCompany()->active()->orderBy('jumlah_hari', 'asc')->get();
        $salesList    = User::where('status', 'Aktif')->get(); // Fetch active users

        return view('data_master.viewcustomer', compact(
            'currencies',
            'paymentTerms',
            'salesList',
            'countTotal',
            'countActive',
            'countDistributor',
            'countRetail'
        ));
    }

    public function getData(Request $request)
    {
        $this->authorize('lihat-customer');

        $search = trim($request->input('search.value', ''));
        $start = intval($request->input('start', 0));
        $length = intval($request->input('length', 10));

        $baseQuery = MasterCustomer::forActiveCompany();
        $totalRecords = $baseQuery->count();

        $query = MasterCustomer::forActiveCompany()->with(['defaultAddress', 'paymentTerm', 'sales']);

        // Global Search across customer fields
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('kode_customer', 'like', "%{$search}%")
                  ->orWhere('nama_customer', 'like', "%{$search}%")
                  ->orWhere('nama_perusahaan', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telepon', 'like', "%{$search}%")
                  ->orWhere('hp', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
            $filteredRecords = $query->count();
        } else {
            $filteredRecords = $totalRecords;
        }

        // Server-side Ordering
        $columnsMap = [
            1 => 'kode_customer',
            2 => 'nama_customer',
            3 => 'jenis_customer',
            4 => 'telepon',
            6 => 'status',
        ];
        if ($request->has('order.0.column')) {
            $colIdx = intval($request->input('order.0.column'));
            $colDir = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';
            if (isset($columnsMap[$colIdx])) {
                $query->orderBy($columnsMap[$colIdx], $colDir);
            } else {
                $query->latest();
            }
        } else {
            $query->latest();
        }

        $customers = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($customers as $c) {
            $defaultAddr = $c->defaultAddress;
            $locationInfo = $defaultAddr ? "{$defaultAddr->kota}, {$defaultAddr->provinsi}" : '-';
            
            $data[] = [
                'id' => $c->id,
                'kode_customer' => $c->kode_customer,
                'nama_customer' => $c->nama_customer,
                'nama_perusahaan' => $c->nama_perusahaan ?? '-',
                'jenis_customer' => $c->jenis_customer,
                'contact_person' => $c->contact_person ?? '-',
                'telepon' => $c->telepon ?? $c->hp ?? '-',
                'email' => $c->email ?? '-',
                'location' => $locationInfo,
                'status' => $c->status,
                'sales_name' => $c->sales->nama_lengkap ?? '-',
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('tambah-customer');

        // Filter valid addresses and contacts
        $addresses = [];
        if ($request->has('addresses')) {
            foreach ($request->addresses as $addr) {
                if (!empty($addr['alamat']) || !empty($addr['kota']) || !empty($addr['provinsi'])) {
                    $addresses[] = $addr;
                }
            }
        }

        $contacts = [];
        if ($request->has('contacts')) {
            foreach ($request->contacts as $contact) {
                if (!empty($contact['nama']) || !empty($contact['hp'])) {
                    $contacts[] = $contact;
                }
            }
        }

        if ($request->has('hp')) {
            $cleaned = preg_replace('/[^0-9]/', '', (string)$request->hp);
            $request->merge(['hp' => $cleaned !== '' ? $cleaned : null]);
        }
        if ($request->has('telepon')) {
            $cleaned = preg_replace('/[^0-9]/', '', (string)$request->telepon);
            $request->merge(['telepon' => $cleaned !== '' ? $cleaned : null]);
        }
        if ($request->has('addresses') && is_array($request->addresses)) {
            $sanitizedAddrs = $request->addresses;
            foreach ($sanitizedAddrs as $k => $v) {
                if (isset($v['telepon'])) {
                    $c = preg_replace('/[^0-9]/', '', (string)$v['telepon']);
                    $sanitizedAddrs[$k]['telepon'] = $c !== '' ? $c : null;
                }
            }
            $request->merge(['addresses' => $sanitizedAddrs]);
        }
        if ($request->has('contacts') && is_array($request->contacts)) {
            $sanitizedCons = $request->contacts;
            foreach ($sanitizedCons as $k => $v) {
                if (isset($v['hp'])) {
                    $c = preg_replace('/[^0-9]/', '', (string)$v['hp']);
                    $sanitizedCons[$k]['hp'] = $c !== '' ? $c : null;
                }
            }
            $request->merge(['contacts' => $sanitizedCons]);
        }

        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        $request->validate([
            'kode_customer'       => 'nullable|string|max:15|unique:master_customers,kode_customer',
            'nama_customer'       => 'required|string|max:60',
            'nama_perusahaan'     => 'nullable|string|max:60',
            'jenis_customer'      => 'required|in:Distributor,Agen,Retail,End User',
            'contact_person'      => 'nullable|string|max:30',
            'jabatan'             => 'nullable|string|max:30',
            'telepon'             => 'nullable|string|max:13|regex:/^[0-9]+$/',
            'hp'                  => 'nullable|string|max:13|regex:/^[0-9]+$/',
            'email'               => [
                'nullable',
                'email',
                'max:100',
                Rule::unique('master_customers', 'email')->where(function ($q) use ($activeCompanyId) {
                    return $q->where('company_id', $activeCompanyId)->whereNull('deleted_at');
                }),
            ],
            'website'             => 'nullable|string|max:40',
            'npwp'                => 'nullable|string|max:40',
            'pkp'                 => 'nullable|boolean',
            'currency'            => 'nullable|string|max:10',
            'payment_term_id'     => [
                'nullable',
                Rule::exists('payment_terms', 'id')->where(function ($query) use ($activeCompanyId) {
                    $query->where('company_id', $activeCompanyId)->orWhereNull('company_id');
                }),
            ],
            'credit_limit'        => 'nullable|numeric|min:0',
            'sales_id'            => 'nullable|exists:users,id',
            'status'              => 'required|in:Active,Inactive',
            'keterangan'          => 'nullable|string|max:40',
            'contacts.*.nama'     => 'nullable|string|max:60',
            'contacts.*.jabatan'  => 'nullable|string|max:30',
            'contacts.*.hp'       => 'nullable|string|max:13|regex:/^[0-9]+$/',
            'addresses'                 => 'required|array|min:1',
            'addresses.*.nama_penerima' => 'required|string|max:40',
            'addresses.*.telepon'       => 'nullable|string|max:13|regex:/^[0-9]+$/',
            'addresses.*.alamat'        => 'required|string|max:70',
            'addresses.*.kota'          => 'required|string|max:30',
            'addresses.*.provinsi'      => 'required|string|max:30',
            'addresses.*.kode_pos'      => 'nullable|string|max:15',
        ], [
            'nama_customer.required'             => 'Nama Customer wajib diisi!',
            'nama_customer.max'                  => 'Nama Customer maksimal 60 karakter!',
            'nama_perusahaan.max'                => 'Nama Perusahaan maksimal 60 karakter!',
            'kode_customer.max'                  => 'Kode Customer maksimal 15 karakter!',
            'kode_customer.unique'               => 'Kode Customer sudah digunakan (termasuk riwayat data yang pernah dihapus)!',
            'contact_person.max'                 => 'Contact Person Utama maksimal 30 karakter!',
            'jabatan.max'                        => 'Jabatan maksimal 30 karakter!',
            'hp.max'                             => 'Nomor Handphone / WA maksimal 13 angka!',
            'hp.regex'                           => 'Nomor Handphone / WA harus berupa angka!',
            'telepon.max'                        => 'Nomor Telepon Kantor maksimal 13 angka!',
            'telepon.regex'                      => 'Nomor Telepon Kantor harus berupa angka!',
            'website.max'                        => 'Website maksimal 40 karakter!',
            'npwp.max'                           => 'Nomor NPWP maksimal 40 karakter!',
            'keterangan.max'                     => 'Keterangan maksimal 40 karakter!',
            'contacts.*.nama.max'                => 'Nama personil kontak maksimal 60 karakter!',
            'contacts.*.jabatan.max'             => 'Jabatan personil kontak maksimal 30 karakter!',
            'contacts.*.hp.max'                  => 'Nomor HP kontak personil maksimal 13 angka!',
            'contacts.*.hp.regex'                => 'Nomor HP kontak personil harus berupa angka!',
            'addresses.required'                 => 'Alamat pengiriman wajib diisi minimal 1 alamat!',
            'addresses.min'                      => 'Alamat pengiriman wajib diisi minimal 1 alamat!',
            'addresses.*.nama_penerima.required' => 'Nama penerima pada alamat pengiriman wajib diisi!',
            'addresses.*.nama_penerima.max'      => 'Nama penerima alamat maksimal 40 karakter!',
            'addresses.*.telepon.max'            => 'Nomor telepon alamat maksimal 13 angka!',
            'addresses.*.telepon.regex'          => 'Nomor telepon alamat harus berupa angka!',
            'addresses.*.alamat.required'        => 'Alamat lengkap pengiriman wajib diisi!',
            'addresses.*.alamat.max'             => 'Alamat lengkap maksimal 70 karakter!',
            'addresses.*.kota.required'          => 'Kota / Kabupaten pengiriman wajib diisi!',
            'addresses.*.kota.max'               => 'Kota / Kabupaten maksimal 30 karakter!',
            'addresses.*.provinsi.required'      => 'Provinsi pengiriman wajib diisi!',
            'addresses.*.provinsi.max'           => 'Provinsi maksimal 30 karakter!',
            'addresses.*.kode_pos.max'           => 'Kode Pos maksimal 15 karakter!',
        ]);

        try {
            DB::beginTransaction();

            $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

            // Auto-generate kode dengan proteksi global (companyId = false) agar tidak tabrakan dengan data terhapus/antar-PT
            $manualKode = $request->filled('kode_customer') ? trim($request->kode_customer) : null;
            if ($manualKode) {
                $kode = $manualKode;
            } else {
                $kode = CodeGeneratorHelper::generate('master_customers', 'CST-', 'kode_customer', 4, false);
            }

            $customer = MasterCustomer::create([
                'company_id'      => $activeCompanyId,
                'kode_customer'   => $kode,
                'nama_customer'   => $request->nama_customer,
                'nama_perusahaan' => $request->nama_perusahaan,
                'jenis_customer'  => $request->jenis_customer,
                'contact_person'  => $request->contact_person,
                'jabatan'         => $request->jabatan,
                'telepon'         => $request->telepon,
                'hp'              => $request->hp,
                'email'           => $request->email,
                'website'         => $request->website,
                'npwp'            => $request->npwp,
                'pkp'             => $request->has('pkp'),
                'currency'        => $request->currency,
                'payment_term_id' => $request->payment_term_id,
                'credit_limit'    => $request->credit_limit ?? 0,
                'sales_id'        => $request->sales_id,
                'status'          => $request->status,
                'keterangan'      => $request->keterangan,
                'created_by'      => auth()->id(),
                'updated_by'      => auth()->id(),
            ]);

            // Save Addresses
            if (count($addresses) > 0) {
                $hasDefault = false;
                foreach ($addresses as $a) {
                    if (isset($a['is_default']) && ($a['is_default'] == '1' || $a['is_default'] == 'true')) {
                        $hasDefault = true;
                        break;
                    }
                }

                foreach ($addresses as $index => $a) {
                    $isDefault = false;
                    if ($hasDefault) {
                        $isDefault = isset($a['is_default']) && ($a['is_default'] == '1' || $a['is_default'] == 'true');
                    } else {
                        // Default to first if none was selected
                        $isDefault = ($index === 0);
                    }

                    $customer->addresses()->create([
                        'jenis_alamat'  => $a['jenis_alamat'] ?? 'Kantor',
                        'nama_penerima' => $a['nama_penerima'] ?: $customer->nama_customer,
                        'telepon'       => $a['telepon'] ?: $customer->hp,
                        'alamat'        => $a['alamat'],
                        'kota'          => $a['kota'],
                        'provinsi'      => $a['provinsi'],
                        'kode_pos'      => $a['kode_pos'] ?? null,
                        'negara'        => $a['negara'] ?? 'Indonesia',
                        'is_default'    => $isDefault,
                    ]);
                }
            }

            // Save Contacts
            if (count($contacts) > 0) {
                foreach ($contacts as $c) {
                    $customer->contacts()->create([
                        'nama'    => $c['nama'],
                        'jabatan' => $c['jabatan'] ?? null,
                        'hp'      => $c['hp'],
                        'email'   => $c['email'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Customer berhasil ditambahkan!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store customer: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menambah data customer.'], 500);
        }
    }

    public function edit(MasterCustomer $customer)
    {
        $this->authorize('edit-customer');

        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
        if ($customer->company_id && $customer->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data customer PT lain.'], 403);
        }

        // Load addresses and contacts relationships
        $customer->load(['addresses', 'contacts']);
        return response()->json(['customer' => $customer]);
    }

    public function update(Request $request, MasterCustomer $customer)
    {
        $this->authorize('edit-customer');

        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
        if ($customer->company_id && $customer->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Anda tidak memiliki akses untuk mengubah data customer PT lain.'], 403);
        }

        $addresses = [];
        if ($request->has('addresses')) {
            foreach ($request->addresses as $addr) {
                if (!empty($addr['alamat']) || !empty($addr['kota']) || !empty($addr['provinsi'])) {
                    $addresses[] = $addr;
                }
            }
        }

        $contacts = [];
        if ($request->has('contacts')) {
            foreach ($request->contacts as $contact) {
                if (!empty($contact['nama']) || !empty($contact['hp'])) {
                    $contacts[] = $contact;
                }
            }
        }

        if ($request->has('hp')) {
            $cleaned = preg_replace('/[^0-9]/', '', (string)$request->hp);
            $request->merge(['hp' => $cleaned !== '' ? $cleaned : null]);
        }
        if ($request->has('telepon')) {
            $cleaned = preg_replace('/[^0-9]/', '', (string)$request->telepon);
            $request->merge(['telepon' => $cleaned !== '' ? $cleaned : null]);
        }
        if ($request->has('addresses') && is_array($request->addresses)) {
            $sanitizedAddrs = $request->addresses;
            foreach ($sanitizedAddrs as $k => $v) {
                if (isset($v['telepon'])) {
                    $c = preg_replace('/[^0-9]/', '', (string)$v['telepon']);
                    $sanitizedAddrs[$k]['telepon'] = $c !== '' ? $c : null;
                }
            }
            $request->merge(['addresses' => $sanitizedAddrs]);
        }
        if ($request->has('contacts') && is_array($request->contacts)) {
            $sanitizedCons = $request->contacts;
            foreach ($sanitizedCons as $k => $v) {
                if (isset($v['hp'])) {
                    $c = preg_replace('/[^0-9]/', '', (string)$v['hp']);
                    $sanitizedCons[$k]['hp'] = $c !== '' ? $c : null;
                }
            }
            $request->merge(['contacts' => $sanitizedCons]);
        }

        $request->validate([
            'kode_customer'       => 'required|string|max:15|unique:master_customers,kode_customer,' . $customer->id . ',id',
            'nama_customer'       => 'required|string|max:60',
            'nama_perusahaan'     => 'nullable|string|max:60',
            'jenis_customer'      => 'required|in:Distributor,Agen,Retail,End User',
            'contact_person'      => 'nullable|string|max:30',
            'jabatan'             => 'nullable|string|max:30',
            'telepon'             => 'nullable|string|max:13|regex:/^[0-9]+$/',
            'hp'                  => 'nullable|string|max:13|regex:/^[0-9]+$/',
            'email'               => [
                'nullable',
                'email',
                'max:100',
                Rule::unique('master_customers', 'email')->where(function ($q) use ($activeCompanyId) {
                    return $q->where('company_id', $activeCompanyId)->whereNull('deleted_at');
                })->ignore($customer->id),
            ],
            'website'             => 'nullable|string|max:40',
            'npwp'                => 'nullable|string|max:40',
            'pkp'                 => 'nullable|boolean',
            'currency'            => 'nullable|string|max:10',
            'payment_term_id'     => [
                'nullable',
                Rule::exists('payment_terms', 'id')->where(function ($query) use ($activeCompanyId) {
                    $query->where('company_id', $activeCompanyId)->orWhereNull('company_id');
                }),
            ],
            'credit_limit'        => 'nullable|numeric|min:0',
            'sales_id'            => 'nullable|exists:users,id',
            'status'              => 'required|in:Active,Inactive',
            'keterangan'          => 'nullable|string|max:40',
            'contacts.*.nama'     => 'nullable|string|max:60',
            'contacts.*.jabatan'  => 'nullable|string|max:30',
            'contacts.*.hp'       => 'nullable|string|max:13|regex:/^[0-9]+$/',
            'addresses'                 => 'required|array|min:1',
            'addresses.*.nama_penerima' => 'required|string|max:40',
            'addresses.*.telepon'       => 'nullable|string|max:13|regex:/^[0-9]+$/',
            'addresses.*.alamat'        => 'required|string|max:70',
            'addresses.*.kota'          => 'required|string|max:30',
            'addresses.*.provinsi'      => 'required|string|max:30',
            'addresses.*.kode_pos'      => 'nullable|string|max:15',
        ], [
            'nama_customer.required'             => 'Nama Customer wajib diisi!',
            'nama_customer.max'                  => 'Nama Customer maksimal 60 karakter!',
            'nama_perusahaan.max'                => 'Nama Perusahaan maksimal 60 karakter!',
            'kode_customer.required'             => 'Kode Customer wajib diisi!',
            'kode_customer.max'                  => 'Kode Customer maksimal 15 karakter!',
            'kode_customer.unique'               => 'Kode Customer sudah digunakan (termasuk riwayat data yang pernah dihapus)!',
            'contact_person.max'                 => 'Contact Person Utama maksimal 30 karakter!',
            'jabatan.max'                        => 'Jabatan maksimal 30 karakter!',
            'hp.max'                             => 'Nomor Handphone / WA maksimal 13 angka!',
            'hp.regex'                           => 'Nomor Handphone / WA harus berupa angka!',
            'telepon.max'                        => 'Nomor Telepon Kantor maksimal 13 angka!',
            'telepon.regex'                      => 'Nomor Telepon Kantor harus berupa angka!',
            'website.max'                        => 'Website maksimal 40 karakter!',
            'npwp.max'                           => 'Nomor NPWP maksimal 40 karakter!',
            'keterangan.max'                     => 'Keterangan maksimal 40 karakter!',
            'contacts.*.nama.max'                => 'Nama personil kontak maksimal 60 karakter!',
            'contacts.*.jabatan.max'             => 'Jabatan personil kontak maksimal 30 karakter!',
            'contacts.*.hp.max'                  => 'Nomor HP kontak personil maksimal 13 angka!',
            'contacts.*.hp.regex'                => 'Nomor HP kontak personil harus berupa angka!',
            'addresses.required'                 => 'Alamat pengiriman wajib diisi minimal 1 alamat!',
            'addresses.min'                      => 'Alamat pengiriman wajib diisi minimal 1 alamat!',
            'addresses.*.nama_penerima.required' => 'Nama penerima pada alamat pengiriman wajib diisi!',
            'addresses.*.nama_penerima.max'      => 'Nama penerima alamat maksimal 40 karakter!',
            'addresses.*.telepon.max'            => 'Nomor telepon alamat maksimal 13 angka!',
            'addresses.*.telepon.regex'          => 'Nomor telepon alamat harus berupa angka!',
            'addresses.*.alamat.required'        => 'Alamat lengkap pengiriman wajib diisi!',
            'addresses.*.alamat.max'             => 'Alamat lengkap maksimal 70 karakter!',
            'addresses.*.kota.required'          => 'Kota / Kabupaten pengiriman wajib diisi!',
            'addresses.*.kota.max'               => 'Kota / Kabupaten maksimal 30 karakter!',
            'addresses.*.provinsi.required'      => 'Provinsi pengiriman wajib diisi!',
            'addresses.*.provinsi.max'           => 'Provinsi maksimal 30 karakter!',
            'addresses.*.kode_pos.max'           => 'Kode Pos maksimal 15 karakter!',
        ]);

        try {
            DB::beginTransaction();

            $customer->update([
                'kode_customer'   => $request->kode_customer,
                'nama_customer'   => $request->nama_customer,
                'nama_perusahaan' => $request->nama_perusahaan,
                'jenis_customer'  => $request->jenis_customer,
                'contact_person'  => $request->contact_person,
                'jabatan'         => $request->jabatan,
                'telepon'         => $request->telepon,
                'hp'              => $request->hp,
                'email'           => $request->email,
                'website'         => $request->website,
                'npwp'            => $request->npwp,
                'pkp'             => $request->has('pkp'),
                'currency'        => $request->currency,
                'payment_term_id' => $request->payment_term_id,
                'credit_limit'    => $request->credit_limit ?? 0,
                'sales_id'        => $request->sales_id,
                'status'          => $request->status,
                'keterangan'      => $request->keterangan,
                'updated_by'      => auth()->id(),
            ]);

            // Sync addresses (re-write strategy)
            $customer->addresses()->delete();
            if (count($addresses) > 0) {
                $hasDefault = false;
                foreach ($addresses as $a) {
                    if (isset($a['is_default']) && ($a['is_default'] == '1' || $a['is_default'] == 'true')) {
                        $hasDefault = true;
                        break;
                    }
                }

                foreach ($addresses as $index => $a) {
                    $isDefault = false;
                    if ($hasDefault) {
                        $isDefault = isset($a['is_default']) && ($a['is_default'] == '1' || $a['is_default'] == 'true');
                    } else {
                        $isDefault = ($index === 0);
                    }

                    $customer->addresses()->create([
                        'jenis_alamat'  => $a['jenis_alamat'] ?? 'Kantor',
                        'nama_penerima' => $a['nama_penerima'] ?: $customer->nama_customer,
                        'telepon'       => $a['telepon'] ?: $customer->hp,
                        'alamat'        => $a['alamat'],
                        'kota'          => $a['kota'],
                        'provinsi'      => $a['provinsi'],
                        'kode_pos'      => $a['kode_pos'] ?? null,
                        'negara'        => $a['negara'] ?? 'Indonesia',
                        'is_default'    => $isDefault,
                    ]);
                }
            }

            // Sync contacts (re-write strategy)
            $customer->contacts()->delete();
            if (count($contacts) > 0) {
                foreach ($contacts as $c) {
                    $customer->contacts()->create([
                        'nama'    => $c['nama'],
                        'jabatan' => $c['jabatan'] ?? null,
                        'hp'      => $c['hp'],
                        'email'   => $c['email'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Data customer berhasil diperbarui!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update customer: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat memperbarui data customer.'], 500);
        }
    }

    public function destroy(MasterCustomer $customer)
    {
        $this->authorize('hapus-customer');

        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
        if ($customer->company_id && $customer->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Anda tidak memiliki akses untuk menghapus data customer PT lain.'], 403);
        }

        try {
            $customer->delete();
            return response()->json(['message' => 'Data customer berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Error delete customer: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus data customer.'], 500);
        }
    }

    /**
     * Sanitasi nilai sel untuk mencegah CSV/Excel Formula Injection (CWE-1236)
     */
    private function sanitizeExcelCell($val)
    {
        if (is_string($val) && strlen($val) > 0) {
            $firstChar = $val[0];
            if (in_array($firstChar, ['=', '+', '-', '@', "\t", "\r"])) {
                return "'" . $val;
            }
        }
        return $val;
    }

    // =========================================================
    //  EXPORT EXCEL
    // =========================================================
    public function exportExcel()
    {
        $this->authorize('lihat-customer');

        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $customers = MasterCustomer::forActiveCompany()
            ->with(['defaultAddress', 'paymentTerm', 'sales'])
            ->latest()
            ->cursor();

        $headerStyle = '<style bgcolor="#1e3a5f" color="#ffffff" bold="1" border="thin">';

        $rows = [
            [
                $headerStyle . 'No</style><center>',
                $headerStyle . 'Kode Customer</style><center>',
                $headerStyle . 'Nama Customer</style>',
                $headerStyle . 'Nama Perusahaan</style>',
                $headerStyle . 'Jenis Customer</style><center>',
                $headerStyle . 'Contact Person</style>',
                $headerStyle . 'Jabatan</style>',
                $headerStyle . 'Telepon</style><center>',
                $headerStyle . 'HP / WA</style><center>',
                $headerStyle . 'Email</style>',
                $headerStyle . 'Website</style>',
                $headerStyle . 'NPWP</style>',
                $headerStyle . 'PKP</style><center>',
                $headerStyle . 'Currency</style><center>',
                $headerStyle . 'Credit Limit</style><right>',
                $headerStyle . 'Payment Term</style>',
                $headerStyle . 'Sales</style>',
                $headerStyle . 'Status</style><center>',
                $headerStyle . 'Keterangan</style>',
                $headerStyle . 'Alamat Utama</style>',
                $headerStyle . 'Kota</style>',
                $headerStyle . 'Provinsi</style>',
                $headerStyle . 'Kode Pos</style><center>',
            ]
        ];

        $no = 1;
        foreach ($customers as $c) {
            $addr = $c->defaultAddress;
            $statusStyle = $c->status === 'Active'
                ? '<style border="thin" color="#059669"><b>Active</b></style><center>'
                : '<style border="thin" color="#6b7280">Inactive</style><center>';
            $pkpStyle = $c->pkp
                ? '<style border="thin" color="#2563eb">PKP</style><center>'
                : '<style border="thin">Non-PKP</style><center>';

            $rows[] = [
                '<style border="thin">' . $no++ . '</style><center>',
                '<style border="thin">' . $this->sanitizeExcelCell($c->kode_customer ?? '') . '</style><center>',
                '<style border="thin">' . $this->sanitizeExcelCell($c->nama_customer ?? '') . '</style>',
                '<style border="thin">' . $this->sanitizeExcelCell($c->nama_perusahaan ?? '') . '</style>',
                '<style border="thin">' . $this->sanitizeExcelCell($c->jenis_customer ?? '') . '</style><center>',
                '<style border="thin">' . $this->sanitizeExcelCell($c->contact_person ?? '') . '</style>',
                '<style border="thin">' . $this->sanitizeExcelCell($c->jabatan ?? '') . '</style>',
                '<style border="thin">' . $this->sanitizeExcelCell($c->telepon ?? '') . '</style><center>',
                '<style border="thin">' . $this->sanitizeExcelCell($c->hp ?? '') . '</style><center>',
                '<style border="thin">' . $this->sanitizeExcelCell($c->email ?? '') . '</style>',
                '<style border="thin">' . $this->sanitizeExcelCell($c->website ?? '') . '</style>',
                '<style border="thin">' . $this->sanitizeExcelCell($c->npwp ?? '') . '</style>',
                $pkpStyle,
                '<style border="thin">' . $this->sanitizeExcelCell($c->currency ?? '') . '</style><center>',
                '<style border="thin" format="#,##0">' . ($c->credit_limit ?? 0) . '</style><right>',
                '<style border="thin">' . $this->sanitizeExcelCell($c->paymentTerm?->nama_term ?? '') . '</style>',
                '<style border="thin">' . $this->sanitizeExcelCell($c->sales?->nama_lengkap ?? '') . '</style>',
                $statusStyle,
                '<style border="thin">' . $this->sanitizeExcelCell($c->keterangan ?? '') . '</style>',
                '<style border="thin">' . $this->sanitizeExcelCell($addr?->alamat ?? '') . '</style>',
                '<style border="thin">' . $this->sanitizeExcelCell($addr?->kota ?? '') . '</style>',
                '<style border="thin">' . $this->sanitizeExcelCell($addr?->provinsi ?? '') . '</style>',
                '<style border="thin">' . $this->sanitizeExcelCell($addr?->kode_pos ?? '') . '</style><center>',
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'data_customer_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // =========================================================
    //  DOWNLOAD TEMPLATE IMPORT
    // =========================================================
    public function downloadTemplate()
    {
        $this->authorize('lihat-customer');

        $headerStyle = '<style bgcolor="#1e3a5f" color="#ffffff" bold="1">';

        $rows = [
            [
                $headerStyle . 'Kode Customer</style>',
                $headerStyle . 'Nama Customer *</style>',
                $headerStyle . 'Nama Perusahaan</style>',
                $headerStyle . 'Jenis Customer *</style>',
                $headerStyle . 'Contact Person</style>',
                $headerStyle . 'Jabatan</style>',
                $headerStyle . 'Telepon</style>',
                $headerStyle . 'HP / WA</style>',
                $headerStyle . 'Email</style>',
                $headerStyle . 'Website</style>',
                $headerStyle . 'NPWP</style>',
                $headerStyle . 'PKP (1=Ya/0=Tidak)</style>',
                $headerStyle . 'Credit Limit</style>',
                $headerStyle . 'Status *</style>',
                $headerStyle . 'Keterangan</style>',
                $headerStyle . 'Nama Penerima Alamat</style>',
                $headerStyle . 'Telepon Penerima</style>',
                $headerStyle . 'Jenis Alamat</style>',
                $headerStyle . 'Alamat Lengkap</style>',
                $headerStyle . 'Kota</style>',
                $headerStyle . 'Provinsi</style>',
                $headerStyle . 'Kode Pos</style>',
            ],
            // Contoh baris 1
            [
                'CST-001',
                'Toko Maju Jaya',
                'CV. Maju Jaya Abadi',
                'Retail',
                'Budi Santoso',
                'Manager',
                '0218765432',
                '081234567890',
                'budi@majujaya.com',
                'www.majujaya.com',
                '01.234.567.8-901.000',
                '1',
                '5000000',
                'Active',
                'Customer lama',
                'Budi Santoso',
                '081234567890',
                'Kantor',
                'Jl. Sudirman No. 12 RT 001/002 Kec. Kebayoran',
                'Jakarta Selatan',
                'DKI Jakarta',
                '12190',
            ],
            // Contoh baris 2
            [
                '',
                'PT. Sentosa Makmur',
                'PT. Sentosa Makmur Tbk',
                'Distributor',
                'Sari Dewi',
                'Direktur',
                '',
                '089876543210',
                'sari@sentosa.co.id',
                '',
                '',
                '0',
                '50000000',
                'Active',
                '',
                'Sari Dewi',
                '089876543210',
                'Gudang',
                'Jl. Industri Raya Blok A No. 5',
                'Surabaya',
                'Jawa Timur',
                '60272',
            ],
        ];

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'template_import_customer.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // =========================================================
    //  IMPORT EXCEL
    // =========================================================
    public function importExcel(Request $request)
    {
        $this->authorize('tambah-customer');

        try {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,csv,txt|max:5120',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => $e->errors(),
            ], 422);
        }

        $file = $request->file('excel_file');
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $rows      = [];
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'csv' || $extension === 'txt') {
            if (($handle = fopen($file->path(), 'r')) !== false) {
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") rewind($handle);
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    $rows[] = $data;
                }
                fclose($handle);
            }
        } else {
            if (!$xlsx = SimpleXLSX::parse($file->path())) {
                return response()->json(['message' => 'Gagal membaca file Excel: ' . SimpleXLSX::parseError()], 422);
            }
            $rows = $xlsx->rows();
        }

        if (count($rows) < 2) {
            return response()->json(['message' => 'File kosong atau tidak memiliki data (minimal 1 baris data + header)!'], 422);
        }

        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
        $now = now()->toDateTimeString();
        $userId = auth()->id();

        // Ambil kode & email existing untuk pencegahan duplikasi
        $existingKodes = DB::table('master_customers')
            ->whereNull('deleted_at')
            ->pluck('kode_customer')
            ->map(fn($k) => strtolower(trim((string)$k)))
            ->flip()
            ->toArray();

        $existingEmails = DB::table('master_customers')
            ->where('company_id', $activeCompanyId)
            ->whereNull('deleted_at')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->pluck('email')
            ->map(fn($e) => strtolower(trim((string)$e)))
            ->flip()
            ->toArray();

        // Cache payment terms & sales users untuk lookup cepat (sesuai PT aktif)
        $paymentTerms = PaymentTerm::forActiveCompany()->active()->pluck('id', 'nama_term')->toArray();
        $salesUsers = DB::table('users')->pluck('id', 'nama_lengkap')->toArray();

        // ---------------------------------------------------------
        // DYNAMIC HEADER DETECTION
        // Mendukung file dari Template maupun file hasil Export
        // ---------------------------------------------------------
        $headerRow = $rows[0] ?? [];
        $colMap = [];

        foreach ($headerRow as $colIdx => $headerVal) {
            $norm = strtolower(preg_replace('/[^a-z0-9]/i', '', strip_tags((string)$headerVal)));
            if ($norm === '') continue;

            if (str_contains($norm, 'penerima')) {
                if (str_contains($norm, 'telp') || str_contains($norm, 'telepon') || str_contains($norm, 'hp')) {
                    $colMap['telepon_penerima'] = $colIdx;
                } else {
                    $colMap['nama_penerima'] = $colIdx;
                }
            } elseif (str_contains($norm, 'jenisalamat') || $norm === 'tipealamat') {
                $colMap['jenis_alamat'] = $colIdx;
            } elseif (str_contains($norm, 'alamat')) {
                $colMap['alamat'] = $colIdx;
            } elseif ($norm === 'kota' || str_contains($norm, 'kabupaten')) {
                $colMap['kota'] = $colIdx;
            } elseif ($norm === 'provinsi') {
                $colMap['provinsi'] = $colIdx;
            } elseif (str_contains($norm, 'kodepos')) {
                $colMap['kode_pos'] = $colIdx;
            } elseif (str_contains($norm, 'kode')) {
                $colMap['kode_customer'] = $colIdx;
            } elseif (str_contains($norm, 'perusahaan')) {
                $colMap['nama_perusahaan'] = $colIdx;
            } elseif (str_contains($norm, 'customer') && str_contains($norm, 'nama')) {
                $colMap['nama_customer'] = $colIdx;
            } elseif (str_contains($norm, 'customer') && str_contains($norm, 'jenis')) {
                $colMap['jenis_customer'] = $colIdx;
            } elseif ($norm === 'nama') {
                $colMap['nama_customer'] = $colIdx;
            } elseif ($norm === 'jenis') {
                $colMap['jenis_customer'] = $colIdx;
            } elseif (str_contains($norm, 'contact') || str_contains($norm, 'pic') || str_contains($norm, 'kontak')) {
                $colMap['contact_person'] = $colIdx;
            } elseif (str_contains($norm, 'jabatan')) {
                $colMap['jabatan'] = $colIdx;
            } elseif (str_contains($norm, 'telp') || str_contains($norm, 'telepon')) {
                $colMap['telepon'] = $colIdx;
            } elseif ($norm === 'hp' || $norm === 'wa' || str_contains($norm, 'hpwa') || str_contains($norm, 'whatsapp')) {
                $colMap['hp'] = $colIdx;
            } elseif (str_contains($norm, 'email')) {
                $colMap['email'] = $colIdx;
            } elseif (str_contains($norm, 'web')) {
                $colMap['website'] = $colIdx;
            } elseif (str_contains($norm, 'npwp')) {
                $colMap['npwp'] = $colIdx;
            } elseif (str_contains($norm, 'pkp')) {
                $colMap['pkp'] = $colIdx;
            } elseif (str_contains($norm, 'currency') || str_contains($norm, 'matauang')) {
                $colMap['currency'] = $colIdx;
            } elseif (str_contains($norm, 'credit') || str_contains($norm, 'limit')) {
                $colMap['credit_limit'] = $colIdx;
            } elseif (str_contains($norm, 'term') || str_contains($norm, 'top') || str_contains($norm, 'termin')) {
                $colMap['payment_term'] = $colIdx;
            } elseif (str_contains($norm, 'sales')) {
                $colMap['sales'] = $colIdx;
            } elseif (str_contains($norm, 'status')) {
                $colMap['status'] = $colIdx;
            } elseif (str_contains($norm, 'ket') || str_contains($norm, 'catatan') || str_contains($norm, 'note')) {
                $colMap['keterangan'] = $colIdx;
            }
        }

        $validJenisCustomer = ['Distributor', 'Agen', 'Retail', 'End User'];
        $validJenisAlamat   = ['Kantor', 'Gudang', 'Toko', 'Rumah', 'Lainnya'];

        $inserted = 0;
        $skipped  = 0;
        $errors   = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            // Skip baris kosong
            if (empty(array_filter(array_map('trim', $row)))) continue;

            $val = function($key, $fallbackIdx = null) use (&$row, &$colMap) {
                if (isset($colMap[$key]) && array_key_exists($colMap[$key], $row)) {
                    return trim(strip_tags((string)$row[$colMap[$key]]));
                }
                if ($fallbackIdx !== null && array_key_exists($fallbackIdx, $row)) {
                    return trim(strip_tags((string)$row[$fallbackIdx]));
                }
                return '';
            };

            $kode           = $val('kode_customer', 0);
            $nama           = $val('nama_customer', 1);
            $namaPerusahaan = $val('nama_perusahaan', 2);
            $jenis          = $val('jenis_customer', 3);
            $contactPerson  = $val('contact_person', 4);
            $jabatan        = $val('jabatan', 5);
            $telepon        = preg_replace('/[^0-9]/', '', $val('telepon', 6));
            $hp             = preg_replace('/[^0-9]/', '', $val('hp', 7));
            $email          = strtolower($val('email', 8));
            $website        = $val('website', 9);
            $npwp           = $val('npwp', 10);
            $pkpRaw         = $val('pkp', 11);
            $currency       = $val('currency', null);
            $creditRaw      = $val('credit_limit', 12);
            $termRaw        = $val('payment_term', null);
            $salesRaw       = $val('sales', null);
            $statusRaw      = $val('status', 13);
            $keterangan     = $val('keterangan', 14);

            // Alamat
            $namaPenerima   = $val('nama_penerima', 15);
            $telponAddr     = preg_replace('/[^0-9]/', '', $val('telepon_penerima', 16));
            $jenisAlamatRaw = $val('jenis_alamat', 17);
            $alamat         = $val('alamat', 18);
            $kota           = $val('kota', 19);
            $provinsi       = $val('provinsi', 20);
            $kodePos        = $val('kode_pos', 21);

            // Validasi wajib: Nama Customer
            if (empty($nama)) {
                $errors[] = "Baris " . ($i + 1) . ": Nama Customer kosong, dilewati.";
                $skipped++;
                continue;
            }

            // Normalisasi Status ('Active' / 'Inactive')
            $status = str_contains(strtolower($statusRaw), 'inact') ? 'Inactive' : 'Active';

            // Normalisasi PKP
            $pkpLower = strtolower($pkpRaw);
            $pkp = in_array($pkpLower, ['1', 'ya', 'yes', 'pkp', 'true']) ? 1 : 0;

            // Normalisasi Credit Limit
            $creditClean = preg_replace('/[^0-9]/', '', $creditRaw);
            $creditLimit = $creditClean !== '' ? (float) $creditClean : 0;

            // Normalisasi Jenis Customer
            $jenisCustomer = 'Retail';
            foreach ($validJenisCustomer as $vj) {
                if (strcasecmp($jenis, $vj) === 0) {
                    $jenisCustomer = $vj;
                    break;
                }
            }

            // Lookup Payment Term ID jika ada
            $paymentTermId = null;
            if ($termRaw !== '') {
                foreach ($paymentTerms as $termName => $termId) {
                    if (strcasecmp($termRaw, $termName) === 0) {
                        $paymentTermId = $termId;
                        break;
                    }
                }
            }

            // Lookup Sales ID jika ada
            $salesId = null;
            if ($salesRaw !== '') {
                foreach ($salesUsers as $salesName => $sId) {
                    if (strcasecmp($salesRaw, $salesName) === 0) {
                        $salesId = $sId;
                        break;
                    }
                }
            }

            // Generate kode customer jika belum ada
            if (empty($kode)) {
                $kode = CodeGeneratorHelper::generate('master_customers', 'CST-', 'kode_customer', 4, false);
            }

            // Cek duplikasi kode customer
            if (isset($existingKodes[strtolower($kode)])) {
                $errors[] = "Baris " . ($i + 1) . " ({$nama}): Kode Customer '{$kode}' sudah ada, dilewati.";
                $skipped++;
                continue;
            }

            // Cek duplikasi email
            if (!empty($email) && isset($existingEmails[$email])) {
                $errors[] = "Baris " . ($i + 1) . " ({$nama}): Email '{$email}' sudah digunakan, dilewati.";
                $skipped++;
                continue;
            }

            // Normalisasi Jenis Alamat
            $jenisAlamat = 'Kantor';
            foreach ($validJenisAlamat as $va) {
                if (strcasecmp($jenisAlamatRaw, $va) === 0) {
                    $jenisAlamat = $va;
                    break;
                }
            }

            // Simpan per baris dengan isolated transaction
            DB::beginTransaction();
            try {
                $customerId = DB::table('master_customers')->insertGetId([
                    'company_id'      => $activeCompanyId,
                    'kode_customer'   => substr($kode, 0, 50),
                    'nama_customer'   => substr($nama, 0, 190),
                    'nama_perusahaan' => $namaPerusahaan ? substr($namaPerusahaan, 0, 190) : null,
                    'jenis_customer'  => $jenisCustomer,
                    'contact_person'  => $contactPerson ? substr($contactPerson, 0, 190) : null,
                    'jabatan'         => $jabatan ? substr($jabatan, 0, 90) : null,
                    'telepon'         => $telepon ? substr($telepon, 0, 30) : null,
                    'hp'              => $hp ? substr($hp, 0, 30) : null,
                    'email'           => $email ? substr($email, 0, 90) : null,
                    'website'         => $website ? substr($website, 0, 190) : null,
                    'npwp'            => $npwp ? substr($npwp, 0, 90) : null,
                    'pkp'             => $pkp,
                    'currency'        => $currency ? substr($currency, 0, 10) : 'IDR',
                    'payment_term_id' => $paymentTermId,
                    'sales_id'        => $salesId,
                    'credit_limit'    => $creditLimit,
                    'status'          => $status,
                    'keterangan'      => $keterangan ?: null,
                    'created_by'      => $userId,
                    'updated_by'      => $userId,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);

                // Simpan Alamat Utama jika data alamat terisi
                if (!empty($alamat) || !empty($kota) || !empty($provinsi)) {
                    $penerima = $namaPenerima ?: $nama;
                    DB::table('customer_addresses')->insert([
                        'customer_id'   => $customerId,
                        'jenis_alamat'  => $jenisAlamat,
                        'nama_penerima' => substr($penerima, 0, 190),
                        'telepon'       => $telponAddr ? substr($telponAddr, 0, 30) : ($hp ? substr($hp, 0, 30) : null),
                        'alamat'        => $alamat ?: '-',
                        'kota'          => $kota ?: '-',
                        'provinsi'      => $provinsi ?: '-',
                        'kode_pos'      => $kodePos ? substr($kodePos, 0, 20) : null,
                        'negara'        => 'Indonesia',
                        'is_default'    => 1,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ]);
                }

                DB::commit();

                // Tandai kode & email sebagai terpakai
                $existingKodes[strtolower($kode)] = true;
                if (!empty($email)) {
                    $existingEmails[$email] = true;
                }

                $inserted++;
            } catch (\Throwable $rowEx) {
                DB::rollBack();
                Log::warning("Gagal import baris {$i}: " . $rowEx->getMessage());
                $errors[] = "Baris " . ($i + 1) . " ({$nama}): " . $rowEx->getMessage();
                $skipped++;
            }
        }

        $message = "Import selesai! {$inserted} customer berhasil ditambahkan.";
        if ($skipped > 0) {
            $message .= " {$skipped} baris dilewati.";
        }

        return response()->json([
            'message'  => $message,
            'inserted' => $inserted,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ]);
    }
}
