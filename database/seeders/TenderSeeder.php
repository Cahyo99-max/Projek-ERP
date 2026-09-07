<?php

namespace Database\Seeders;

use App\Models\Tender;
use App\Models\TenderItem;
use App\Models\TenderDocument;
use App\Models\TenderFollowup;
use App\Models\TenderApproval;
use App\Models\MasterCustomer;
use App\Models\MasterBarang;
use App\Models\User;
use App\Models\Karyawan;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class TenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Fetch references
        $customers = MasterCustomer::pluck('id')->toArray();
        $users = User::pluck('id')->toArray();
        $karyawans = Karyawan::pluck('id')->toArray();
        $barangList = MasterBarang::pluck('id')->toArray();
        
        if (empty($customers) || empty($users) || empty($karyawans) || empty($barangList)) {
            return;
        }

        $priorities = ['Low', 'Medium', 'High'];
        $statuses = ['Draft', 'Sent', 'Won', 'Lost', 'Cancelled'];
        $documentTypes = ['RKS', 'Proposal', 'Penawaran', 'Kontrak', 'Lainnya'];
        $activities = ['Telepon', 'Meeting', 'Presentasi', 'Negosiasi', 'Lainnya'];

        DB::beginTransaction();
        try {
            for ($i = 1; $i <= 50; $i++) {
                $nomorKontrak = 'CON/' . date('Ymd') . '/' . str_pad($i, 4, '0', STR_PAD_LEFT);
                if (Tender::where('nomor_kontrak', $nomorKontrak)->exists()) {
                    continue;
                }

                $status = $faker->randomElement($statuses);
                $priority = $faker->randomElement($priorities);
                $salesId = $faker->randomElement($karyawans);
                $creatorId = $faker->randomElement($users);

                // Date logic
                $tanggal = $faker->dateTimeBetween('-3 months', 'now');
                $deadline = (clone $tanggal)->modify('+30 days');
                $estimasiClosing = (clone $tanggal)->modify('+45 days');

                $tender = Tender::create([
                    'nomor_kontrak' => $nomorKontrak,
                    'customer_id' => $faker->randomElement($customers),
                    'sales_id' => $salesId,
                    'nama_tender' => 'Tender Pengadaan ' . $faker->words(3, true),
                    'tanggal' => $tanggal,
                    'deadline' => $deadline,
                    'estimasi_closing' => $estimasiClosing,
                    'currency' => $faker->randomElement(['IDR', 'USD']),
                    'status' => $status,
                    'priority' => $priority,
                    'nilai_estimasi' => 0.00,
                    'catatan' => $faker->sentence(),
                    'created_by' => $creatorId,
                    'updated_by' => $creatorId,
                ]);

                // Create items (2-5 per tender)
                $numItems = $faker->numberBetween(2, 5);
                $totalVal = 0;
                for ($j = 0; $j < $numItems; $j++) {
                    $qty = $faker->numberBetween(10, 100);
                    $harga = $faker->randomElement([50000, 150000, 500000, 1200000, 2500000]);
                    $subtotal = $qty * $harga;
                    $totalVal += $subtotal;

                    TenderItem::create([
                        'tender_id' => $tender->id,
                        'barang_id' => $faker->randomElement($barangList),
                        'qty' => $qty,
                        'satuan' => $faker->randomElement(['Pcs', 'Unit', 'Box', 'Pack']),
                        'harga_estimasi' => $harga,
                        'subtotal' => $subtotal,
                    ]);
                }

                // Update total estimation value
                $tender->update(['nilai_estimasi' => $totalVal]);

                // Create documents (1-3 per tender)
                $numDocs = $faker->numberBetween(1, 3);
                for ($d = 0; $d < $numDocs; $d++) {
                    $docType = $faker->randomElement($documentTypes);
                    TenderDocument::create([
                        'tender_id' => $tender->id,
                        'nama_file' => strtolower($docType) . '_document_' . $tender->id . '.pdf',
                        'jenis_dokumen' => $docType,
                        'path' => 'uploads/tenders/mock_document.pdf',
                    ]);
                }

                // Create followups (1-4 per tender)
                $numFollow = $faker->numberBetween(1, 4);
                for ($f = 0; $f < $numFollow; $f++) {
                    TenderFollowup::create([
                        'tender_id' => $tender->id,
                        'tanggal' => $faker->dateTimeBetween($tanggal, 'now'),
                        'aktivitas' => $faker->randomElement($activities),
                        'keterangan' => 'Melakukan ' . strtolower($faker->word) . ' dengan perwakilan panitia tender.',
                        'user_id' => $salesId,
                    ]);
                }

                // Create approvals (1 per tender)
                $approvalStatus = 'Pending';
                if ($status === 'Won' || $status === 'Sent') {
                    $approvalStatus = 'Approved';
                } elseif ($status === 'Cancelled' || $status === 'Lost') {
                    $approvalStatus = $faker->randomElement(['Approved', 'Rejected']);
                }

                TenderApproval::create([
                    'tender_id' => $tender->id,
                    'user_id' => $faker->randomElement($users),
                    'status' => $approvalStatus,
                    'approved_at' => $approvalStatus !== 'Pending' ? $faker->dateTimeBetween($tanggal, 'now') : null,
                    'catatan' => $approvalStatus === 'Approved' ? 'Memenuhi semua kualifikasi RKS' : ($approvalStatus === 'Rejected' ? 'Harga penawaran melebihi batas estimasi pagu' : null),
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
