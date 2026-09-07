<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterTipePengeluaran;
use App\Models\Company;

class MasterTipePengeluaranSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();
        if ($companies->isEmpty()) {
            $this->seedForCompany(null);
        } else {
            foreach ($companies as $comp) {
                $this->seedForCompany($comp->id);
            }
        }
    }

    private function seedForCompany($companyId)
    {
        $defaults = [
            ['kode_tipe' => 'TP-INT', 'nama_tipe' => 'Internal',       'deskripsi' => 'Pengeluaran barang untuk pemakaian internal/operasional perusahaan'],
            ['kode_tipe' => 'TP-RET', 'nama_tipe' => 'Retur Supplier', 'deskripsi' => 'Pengeluaran barang karena dikembalikan ke supplier'],
            ['kode_tipe' => 'TP-ADJ', 'nama_tipe' => 'Penyesuaian',    'deskripsi' => 'Pengeluaran barang untuk penyesuaian selisih stok persediaan'],
            ['kode_tipe' => 'TP-OTH', 'nama_tipe' => 'Lainnya',        'deskripsi' => 'Pengeluaran barang untuk keperluan lainnya'],
        ];

        foreach ($defaults as $data) {
            MasterTipePengeluaran::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'nama_tipe'  => $data['nama_tipe'],
                ],
                [
                    'kode_tipe' => $data['kode_tipe'],
                    'deskripsi' => $data['deskripsi'],
                    'status'    => 'Active',
                ]
            );
        }
    }
}
