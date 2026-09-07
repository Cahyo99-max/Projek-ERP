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

class MasterCustomerController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-customer');

        // Fetch currencies, payment terms, and users (who can act as Sales)
        $currencies = MasterCurrency::where('status', 'Active')->get();
        $paymentTerms = PaymentTerm::all();
        $salesList = User::where('status', 'Aktif')->get(); // Fetch active users

        return view('data_master.viewcustomer', compact('currencies', 'paymentTerms', 'salesList'));
    }

    public function getData(Request $request)
    {
        $this->authorize('lihat-customer');

        $search = $request->input('search.value');
        $start = intval($request->input('start', 0));
        $length = intval($request->input('length', 10));

        $query = MasterCustomer::with(['defaultAddress', 'paymentTerm', 'sales'])->latest();

        // Global Search
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('kode_customer', 'like', "%{$search}%")
                  ->orWhere('nama_customer', 'like', "%{$search}%")
                  ->orWhere('nama_perusahaan', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $total = $query->count();
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
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
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

        $request->validate([
            'kode_customer'       => 'nullable|string|max:50|unique:master_customers,kode_customer,NULL,id,deleted_at,NULL',
            'nama_customer'       => 'required|string|max:200',
            'nama_perusahaan'     => 'nullable|string|max:200',
            'jenis_customer'      => 'required|in:Distributor,Agen,Retail,End User',
            'contact_person'      => 'nullable|string|max:200',
            'jabatan'             => 'nullable|string|max:100',
            'telepon'             => 'nullable|string|max:50',
            'hp'                  => 'nullable|string|max:50',
            'email'               => 'nullable|email|max:100|unique:master_customers,email,NULL,id,deleted_at,NULL',
            'website'             => 'nullable|string|max:200',
            'npwp'                => 'nullable|string|max:100',
            'pkp'                 => 'nullable|boolean',
            'currency'            => 'nullable|string|max:10',
            'payment_term_id'     => 'nullable|exists:payment_terms,id',
            'credit_limit'        => 'nullable|numeric|min:0',
            'sales_id'            => 'nullable|exists:users,id',
            'status'              => 'required|in:Active,Inactive',
            'keterangan'          => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $kode = $request->kode_customer;
            if (empty($kode)) {
                $kode = CodeGeneratorHelper::generate('master_customers', 'CST-', 'kode_customer');
            }

            $customer = MasterCustomer::create([
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

        // Load addresses and contacts relationships
        $customer->load(['addresses', 'contacts']);
        return response()->json(['customer' => $customer]);
    }

    public function update(Request $request, MasterCustomer $customer)
    {
        $this->authorize('edit-customer');

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

        $request->validate([
            'kode_customer'       => 'required|string|max:50|unique:master_customers,kode_customer,' . $customer->id . ',id,deleted_at,NULL',
            'nama_customer'       => 'required|string|max:200',
            'nama_perusahaan'     => 'nullable|string|max:200',
            'jenis_customer'      => 'required|in:Distributor,Agen,Retail,End User',
            'contact_person'      => 'nullable|string|max:200',
            'jabatan'             => 'nullable|string|max:100',
            'telepon'             => 'nullable|string|max:50',
            'hp'                  => 'nullable|string|max:50',
            'email'               => 'nullable|email|max:100|unique:master_customers,email,' . $customer->id . ',id,deleted_at,NULL',
            'website'             => 'nullable|string|max:200',
            'npwp'                => 'nullable|string|max:100',
            'pkp'                 => 'nullable|boolean',
            'currency'            => 'nullable|string|max:10',
            'payment_term_id'     => 'nullable|exists:payment_terms,id',
            'credit_limit'        => 'nullable|numeric|min:0',
            'sales_id'            => 'nullable|exists:users,id',
            'status'              => 'required|in:Active,Inactive',
            'keterangan'          => 'nullable|string',
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

        try {
            $customer->delete();
            return response()->json(['message' => 'Data customer berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Error delete customer: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus data customer.'], 500);
        }
    }
}
