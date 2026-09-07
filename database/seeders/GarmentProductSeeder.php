<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Helpers\CodeGeneratorHelper;

class GarmentProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Fetch default company
        $companyId = DB::table('companies')->value('id');
        if (!$companyId) {
            $companyId = DB::table('companies')->insertGetId([
                'kode_perusahaan' => 'PT-SATRIA',
                'nama_perusahaan' => 'PT. SATRIA KARYA UTAMA',
                'status'          => 'Active',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        // 2. Ensure 15 Garment Categories exist with company_id
        $categories = [
            ['nama_kategori' => 'Bahan Baku Kain', 'kode_kategori' => 'KAT-GAR01', 'deskripsi' => 'Kain gulungan/meteran (Katun, Denim, Rayon, Polyester, Linen, Fleece)', 'urutan' => 1, 'status' => 'Active'],
            ['nama_kategori' => 'Benang Jahit & Obras', 'kode_kategori' => 'KAT-GAR02', 'deskripsi' => 'Benang jahit, benang obras, benang bordir, dan benang elastis', 'urutan' => 2, 'status' => 'Active'],
            ['nama_kategori' => 'Kancing & Fastener', 'kode_kategori' => 'KAT-GAR03', 'deskripsi' => 'Kancing kemeja, kancing jepret, rivet denim, dan eyelet', 'urutan' => 3, 'status' => 'Active'],
            ['nama_kategori' => 'Resleting & Zipper', 'kode_kategori' => 'KAT-GAR04', 'deskripsi' => 'Zipper metal, nylon, vislon, jepang, dan kepala resleting', 'urutan' => 4, 'status' => 'Active'],
            ['nama_kategori' => 'Kain Furing & Interlining', 'kode_kategori' => 'KAT-GAR05', 'deskripsi' => 'Kain lining, furing asahi/tricot, kain keras, dan busa padding', 'urutan' => 5, 'status' => 'Active'],
            ['nama_kategori' => 'Karet Elastis & Webbing', 'kode_kategori' => 'KAT-GAR06', 'deskripsi' => 'Karet pinggang, pita elastis, pita satin, dan webbing strap', 'urutan' => 6, 'status' => 'Active'],
            ['nama_kategori' => 'Label, Tag & Branding', 'kode_kategori' => 'KAT-GAR07', 'deskripsi' => 'Label woven, care wash label, hangtag, dan size sticker', 'urutan' => 7, 'status' => 'Active'],
            ['nama_kategori' => 'Bahan Penolong & Kimia', 'kode_kategori' => 'KAT-GAR08', 'deskripsi' => 'Oli mesin jahit, spot remover, lem kain, dan kapur jahit', 'urutan' => 8, 'status' => 'Active'],
            ['nama_kategori' => 'Suku Cadang & Jarum Mesin', 'kode_kategori' => 'KAT-GAR09', 'deskripsi' => 'Jarum jahit/obras, sepatu jahit, sekoci, pisau potong, dan belt', 'urutan' => 9, 'status' => 'Active'],
            ['nama_kategori' => 'Pakaian Jadi Pria', 'kode_kategori' => 'KAT-GAR10', 'deskripsi' => 'Kaos, kemeja casual/formal, celana chino/denim, dan jaket pria', 'urutan' => 10, 'status' => 'Active'],
            ['nama_kategori' => 'Pakaian Jadi Wanita', 'kode_kategori' => 'KAT-GAR11', 'deskripsi' => 'Gamis, blouse, rok, kulot, dress, dan outerwear wanita', 'urutan' => 11, 'status' => 'Active'],
            ['nama_kategori' => 'Pakaian Anak & Bayi', 'kode_kategori' => 'KAT-GAR12', 'deskripsi' => 'Baju bayi, setelan anak, romper, dan seragam sekolah anak', 'urutan' => 12, 'status' => 'Active'],
            ['nama_kategori' => 'Seragam & Pakaian Kerja', 'kode_kategori' => 'KAT-GAR13', 'deskripsi' => 'Seragam kantor, baju PDH/PDL, wearpack K3, dan apron', 'urutan' => 13, 'status' => 'Active'],
            ['nama_kategori' => 'Kemasan & Packaging', 'kode_kategori' => 'KAT-GAR14', 'deskripsi' => 'Plastik polybag, karton box, lakban, hanger, dan pita packaging', 'urutan' => 14, 'status' => 'Active'],
            ['nama_kategori' => 'Limbah & Sisa Potongan', 'kode_kategori' => 'KAT-GAR15', 'deskripsi' => 'Kain perca sisa cutting, benang sisa, dan barang afval/BS', 'urutan' => 15, 'status' => 'Active'],
        ];

        foreach ($categories as $cat) {
            DB::table('master_kategori_barang')->updateOrInsert(
                ['kode_kategori' => $cat['kode_kategori']],
                array_merge($cat, [
                    'company_id' => $companyId,
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ])
            );
        }

        // Backfill any remaining null company_id records
        DB::table('master_kategori_barang')->whereNull('company_id')->update(['company_id' => $companyId]);

        // 2. Ensure Garment Brands exist
        $brands = [
            ['nama_brand' => 'Garment Co.', 'deskripsi' => 'Brand utama pakaian jadi', 'status' => 'Active'],
            ['nama_brand' => 'Textile Prima', 'deskripsi' => 'Brand kain premium', 'status' => 'Active'],
            ['nama_brand' => 'Aksesoris Indah', 'deskripsi' => 'Brand kancing & sleting', 'status' => 'Active'],
        ];

        foreach ($brands as $brand) {
            $exists = DB::table('master_brand')->where('nama_brand', $brand['nama_brand'])->first();
            if (!$exists) {
                $kode = CodeGeneratorHelper::generate('master_brand', 'BRD-', 'kode_brand');
                DB::table('master_brand')->insert(array_merge($brand, [
                    'kode_brand' => $kode,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // 3. Ensure a Branch and Garment Warehouse exist
        $existsBranch = DB::table('branches')->first();
        if (!$existsBranch) {
            DB::table('branches')->insert([
                'kode_cabang' => 'CBG-001',
                'nama_cabang' => 'Cabang Utama Jakarta',
                'nama_perusahaan' => 'PT. Pasta Tekstil Indonesia',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $cabangId = DB::table('branches')->value('id');

        $existsWarehouse = DB::table('master_gudang')->where('kode_gudang', 'GDG-GAR01')->first();
        if (!$existsWarehouse) {
            // Get first jenis gudang
            $jg = DB::table('master_jenis_gudang')->first();
            DB::table('master_gudang')->insert([
                'kode_gudang' => 'GDG-GAR01',
                'nama_gudang' => 'Gudang Utama Garment',
                'jenis_gudang_id' => $jg ? $jg->id : 1,
                'cabang_id' => $cabangId,
                'alamat' => 'Kawasan Industri Tekstil Blok C No. 4',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Fetch IDs for references
        $catPakaian = DB::table('master_kategori_barang')->where('kode_kategori', 'KAT-GAR01')->value('id');
        $catKain = DB::table('master_kategori_barang')->where('kode_kategori', 'KAT-GAR02')->value('id');
        $catAksesoris = DB::table('master_kategori_barang')->where('kode_kategori', 'KAT-GAR03')->value('id');

        $brandGarment = DB::table('master_brand')->where('nama_brand', 'Garment Co.')->value('id');
        $brandTextile = DB::table('master_brand')->where('nama_brand', 'Textile Prima')->value('id');
        $brandAksesoris = DB::table('master_brand')->where('nama_brand', 'Aksesoris Indah')->value('id');

        $gudangId = DB::table('master_gudang')->where('kode_gudang', 'GDG-GAR01')->value('id');

        $satuanPcs = DB::table('master_satuan')->where('singkatan', 'Pcs')->value('id') ?? 1;
        $satuanMtr = DB::table('master_satuan')->where('singkatan', 'Mtr')->value('id') ?? 1;
        
        $warnaIds = DB::table('master_warna')->pluck('id')->toArray();
        if (empty($warnaIds)) {
            $warnaIds = [1];
        }

        $tipeBahanBaku = DB::table('master_tipe_barang')->where('nama_tipe', 'Bahan Baku')->value('id') ?? 1;
        $tipeBarangJadi = DB::table('master_tipe_barang')->where('nama_tipe', 'Barang Jadi')->value('id') ?? 1;

        // Combinations for Pakaian Jadi (Finished Goods)
        $fgFabrics = ['Katun Combed 30s', 'Rayon Twill', 'Linen Premium', 'Denim Wash', 'Sifon Silk', 'Polyester Dryfit', 'Fleece Tebal', 'Baby Terry'];
        $fgItems = ['Kaos Polos', 'Kemeja Casual', 'Celana Chino', 'Jaket Bomber', 'Hoodie Jumper', 'Rok Plisket', 'Gamis Syari', 'Polo Shirt', 'Cardigan Rajut', 'Sweater Fleece'];
        $fgSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

        // Combinations for Bahan Baku (Kain)
        $rawFabrics = ['Kain Katun Rayon', 'Kain Linen Rubia', 'Kain Sifon Sutra', 'Kain Denim Stretch', 'Kain Fleece Wool', 'Kain Drill Premium', 'Kain Jersey Premium'];
        
        // Combinations for Aksesoris
        $accItems = ['Kancing Hias Cangkang 15mm', 'Resleting Zipper YKK 20cm', 'Benang Jahit Astra 500m', 'Karet Elastis Celana 2cm', 'Label Woven Premium', 'Pita Satin 1.5cm'];

        // Set parameters
        $totalItems = 5000;
        $batchSize = 500;
        $batches = $totalItems / $batchSize;

        $this->command->info("Memulai seeding 5000 data barang garmen...");

        for ($b = 0; $b < $batches; $b++) {
            $itemsData = [];
            
            for ($i = 1; $i <= $batchSize; $i++) {
                $itemIndex = ($b * $batchSize) + $i;
                $randType = rand(1, 3); // 1: Pakaian Jadi, 2: Kain (Bahan Baku), 3: Aksesoris

                $namaBarang = '';
                $kategoriId = null;
                $brandId = null;
                $satuanId = null;
                $tipeBarangId = null;
                $ukuran = '-';
                $spesifikasi = '';

                if ($randType === 1) {
                    // Pakaian Jadi
                    $fabric = $fgFabrics[array_rand($fgFabrics)];
                    $item = $fgItems[array_rand($fgItems)];
                    $size = $fgSizes[array_rand($fgSizes)];
                    
                    $namaBarang = "$item $fabric Size $size - " . Str::random(5);
                    $kategoriId = $catPakaian;
                    $brandId = $brandGarment;
                    $satuanId = $satuanPcs;
                    $tipeBarangId = $tipeBarangJadi;
                    $ukuran = $size;
                    $spesifikasi = "Pakaian jadi premium berbahan $fabric nyaman dipakai.";
                } elseif ($randType === 2) {
                    // Kain
                    $fabric = $rawFabrics[array_rand($rawFabrics)];
                    $namaBarang = "$fabric Gulung - " . Str::random(5);
                    $kategoriId = $catKain;
                    $brandId = $brandTextile;
                    $satuanId = $satuanMtr;
                    $tipeBarangId = $tipeBahanBaku;
                    $spesifikasi = "Bahan kain rol/meteran berkualitas tinggi untuk konveksi.";
                } else {
                    // Aksesoris
                    $acc = $accItems[array_rand($accItems)];
                    $namaBarang = "$acc - " . Str::random(5);
                    $kategoriId = $catAksesoris;
                    $brandId = $brandAksesoris;
                    $satuanId = $satuanPcs;
                    $tipeBarangId = $tipeBahanBaku;
                    $spesifikasi = "Aksesoris pelengkap produksi garment berkualitas tinggi.";
                }

                $kodeBarang = 'BRG-GAR-' . str_pad($itemIndex, 5, '0', STR_PAD_LEFT);
                $barcode = '899' . str_pad($itemIndex, 10, '0', STR_PAD_LEFT);

                $itemsData[] = [
                    'kode_barang' => $kodeBarang,
                    'barcode' => $barcode,
                    'nama_barang' => $namaBarang,
                    'nama_alias' => $namaBarang,
                    'tipe_item' => 'Stok',
                    'kategori_barang_id' => $kategoriId,
                    'brand_id' => $brandId,
                    'tipe_barang_id' => $tipeBarangId,
                    'satuan_id' => $satuanId,
                    'warna_id' => $warnaIds[array_rand($warnaIds)],
                    'ukuran' => $ukuran,
                    'spesifikasi' => $spesifikasi,
                    'berat' => rand(100, 1000) / 1000,
                    'panjang' => rand(10, 150),
                    'lebar' => rand(10, 150),
                    'tinggi' => rand(1, 50),
                    'minimum_stok' => 10.00,
                    'maksimum_stok' => 1000.00,
                    'reorder_point' => 50.00,
                    'status' => 'Active',
                    'created_by' => 1,
                    'updated_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Bulk Insert
            DB::table('master_barang')->insert($itemsData);
            $this->command->info("Berhasil mengimpor batch " . ($b + 1) . "/$batches (" . (($b + 1) * $batchSize) . " barang)");
        }

        // 4. Seeding photos for the 5000 goods
        $this->command->info("Memetakan foto utama ke 5000 barang...");
        
        // Fetch all generated garment IDs
        $barangIds = DB::table('master_barang')->where('kode_barang', 'like', 'BRG-GAR-%')->pluck('id')->toArray();
        
        $photosData = [];
        foreach ($barangIds as $id) {
            $photosData[] = [
                'barang_id' => $id,
                'foto_path' => 'uploads/barang/placeholder_garment.png',
                'is_primary' => true,
                'urutan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Bulk insert photos in chunks of 500
        $photoChunks = array_chunk($photosData, 500);
        foreach ($photoChunks as $index => $chunk) {
            DB::table('master_barang_foto')->insert($chunk);
        }

        $this->command->info("Seluruh 5000 barang garmen dan foto selesai di-seed!");
    }
}
