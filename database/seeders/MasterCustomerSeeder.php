<?php

namespace Database\Seeders;

use App\Models\MasterCustomer;
use App\Models\CustomerAddress;
use App\Models\CustomerContact;
use App\Models\PaymentTerm;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class MasterCustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Fetch references
        $paymentTerms = PaymentTerm::pluck('id')->toArray();
        if (empty($paymentTerms)) {
            $paymentTerms = [null];
        }
        $userIds = User::pluck('id')->toArray();
        if (empty($userIds)) {
            $userIds = [null];
        }
        
        $currencies = ['IDR', 'USD', 'SGD', 'EUR'];
        $customerTypes = ['Distributor', 'Agen', 'Retail', 'End User'];
        $addressTypes = ['Kantor', 'Gudang', 'Toko', 'Rumah', 'Lainnya'];
        
        $provinces = [
            'DKI Jakarta', 'Jawa Barat', 'Jawa Tengah', 'Jawa Timur', 
            'Banten', 'DI Yogyakarta', 'Bali', 'Sumatera Utara'
        ];
        
        $cities = [
            'DKI Jakarta' => ['Jakarta Selatan', 'Jakarta Pusat', 'Jakarta Barat', 'Jakarta Utara', 'Jakarta Timur'],
            'Jawa Barat' => ['Bandung', 'Bekasi', 'Depok', 'Bogor', 'Cirebon'],
            'Jawa Tengah' => ['Semarang', 'Surakarta', 'Tegal', 'Pekalongan'],
            'Jawa Timur' => ['Surabaya', 'Malang', 'Sidoarjo', 'Gresik'],
            'Banten' => ['Tangerang', 'Tangerang Selatan', 'Serang', 'Cilegon'],
            'DI Yogyakarta' => ['Sleman', 'Bantul', 'Yogyakarta'],
            'Bali' => ['Denpasar', 'Badung', 'Gianyar'],
            'Sumatera Utara' => ['Medan', 'Binjai', 'Deli Serdang']
        ];

        DB::beginTransaction();
        try {
            for ($i = 1; $i <= 1000; $i++) {
                $customerCode = 'CST-' . str_pad($i, 5, '0', STR_PAD_LEFT);
                if (MasterCustomer::where('kode_customer', $customerCode)->exists()) {
                    continue;
                }

                $companyPrefix = $faker->randomElement(['PT', 'CV', 'UD', 'Toko']);
                $companyName = $companyPrefix . ' ' . $faker->company;
                $custName = $faker->name;
                
                $prov = $faker->randomElement($provinces);
                $city = $faker->randomElement($cities[$prov] ?? ['Jakarta']);
                $creatorId = $faker->randomElement($userIds);
                
                $customer = MasterCustomer::create([
                    'kode_customer'   => $customerCode,
                    'nama_customer'   => $custName,
                    'nama_perusahaan' => $companyName,
                    'jenis_customer'  => $faker->randomElement($customerTypes),
                    'contact_person'  => $faker->name,
                    'jabatan'         => $faker->randomElement(['Purchasing Manager', 'Owner', 'Direktur Utama', 'Staff Adm', 'SPV Procurement']),
                    'telepon'         => $faker->phoneNumber,
                    'hp'              => '08' . $faker->numerify('##########'),
                    'email'           => $faker->unique()->safeEmail,
                    'website'         => $faker->randomElement([null, 'www.' . strtolower(str_replace(' ', '', $faker->word)) . '.com']),
                    'npwp'            => $faker->randomElement([null, $faker->numerify('##.###.###.#-###.###')]),
                    'pkp'             => $faker->boolean(40),
                    'currency'        => $faker->randomElement($currencies),
                    'payment_term_id' => $faker->randomElement($paymentTerms),
                    'credit_limit'    => $faker->randomElement([0, 5000000, 10000000, 25000000, 50000000, 100000000]),
                    'sales_id'        => $faker->randomElement($userIds),
                    'status'          => $faker->randomElement(['Active', 'Active', 'Active', 'Inactive']),
                    'created_by'      => $creatorId,
                    'updated_by'      => $creatorId,
                ]);
                
                // Addresses (1-2 per customer)
                $numAddresses = $faker->numberBetween(1, 2);
                for ($a = 0; $a < $numAddresses; $a++) {
                    $isDefault = ($a === 0);
                    $custProv = $a === 0 ? $prov : $faker->randomElement($provinces);
                    $custCity = $a === 0 ? $city : $faker->randomElement($cities[$custProv] ?? ['Jakarta']);
                    
                    CustomerAddress::create([
                        'customer_id'   => $customer->id,
                        'jenis_alamat'  => $faker->randomElement($addressTypes),
                        'nama_penerima' => $customer->nama_perusahaan ?: $customer->nama_customer,
                        'telepon'       => $customer->telepon ?: $customer->hp,
                        'alamat'        => $faker->streetAddress,
                        'kota'          => $custCity,
                        'provinsi'      => $custProv,
                        'kode_pos'      => $faker->postcode,
                        'negara'        => 'Indonesia',
                        'is_default'    => $isDefault,
                    ]);
                }
                
                // Contacts (1-2 per customer)
                $numContacts = $faker->numberBetween(1, 2);
                for ($c = 0; $c < $numContacts; $c++) {
                    CustomerContact::create([
                        'customer_id' => $customer->id,
                        'nama'        => $faker->name,
                        'jabatan'     => $faker->randomElement(['Staff Gudang', 'Finance', 'SPV Sales', 'Admin']),
                        'hp'          => '08' . $faker->numerify('##########'),
                        'email'       => $faker->safeEmail,
                    ]);
                }
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
