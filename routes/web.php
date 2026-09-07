<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\BranchSwitcherController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\MasterKaryawanController;
use App\Http\Controllers\UserLoginController;
use App\Http\Controllers\DepartemenController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AppSettingsController;
use App\Http\Controllers\MasterSatuanController;
use App\Http\Controllers\MasterBrandController;
use App\Http\Controllers\MasterTipeBarangController;
use App\Http\Controllers\MasterTipeAkunController;
use App\Http\Controllers\MasterJenisBiayaController;
use App\Http\Controllers\MasterTipePengeluaranController;
use App\Http\Controllers\MasterWarnaController;
use App\Http\Controllers\MasterJenisGudangController;
use App\Http\Controllers\MasterKategoriBarangController;
use App\Http\Controllers\MasterGudangController;
use App\Http\Controllers\MasterLokasiRakController;
use App\Http\Controllers\MasterBarangController;
use App\Http\Controllers\MasterBundleController;
use App\Http\Controllers\MasterSupplierController;
use App\Http\Controllers\MasterCustomerController;
use App\Http\Controllers\TenderController;
use App\Http\Controllers\SalesQuotationController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\DeliveryOrderController;
use App\Http\Controllers\PurchaseRequisitionController;
use App\Http\Controllers\MasterPajakController;
use App\Http\Controllers\MasterCurrencyController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PaymentTermController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\FixedAssetController;
use App\Http\Controllers\GoodsIssueController;
use App\Http\Controllers\WarehouseTransferController;
use App\Http\Controllers\KartuStokController;
use App\Http\Controllers\StokGudangController;
use App\Http\Controllers\RegistrasiJasaController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\OpeningStockController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\AccountMappingController;
use App\Http\Controllers\AkuntansiSettingsController;
use App\Http\Controllers\BomController;
use App\Http\Controllers\MainDashboardController;
use App\Http\Controllers\UserActivityLogController;
use Illuminate\Support\Facades\Route;

// ===== PUBLIC/GUEST ROUTES =====
Route::get('/', [UserLoginController::class, 'tampilFormLogin'])->name('login');
Route::post('/login', [UserLoginController::class, 'prosesLogin']);
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'id'])) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back();
})->name('lang.switch');

// ===== AUTHENTICATED ROUTES =====
Route::middleware('auth')->group(function () {
    Route::match(['get', 'post'], '/logout', [UserLoginController::class, 'logout'])->name('logout');

    // Branch Switcher (Requires login only)
    Route::get('/branch/list', [BranchSwitcherController::class, 'getBranchesList'])->name('branch.list');
    Route::post('/branch/switch', [BranchSwitcherController::class, 'switchBranch'])->name('branch.switch');

    // ===== BUSINESS LOGIC ROUTES (Requires Auth + Active Branch) =====
    Route::middleware('active_branch')->group(function () {
        
        // Dashboard
        Route::get('/dashbord', [MainDashboardController::class, 'index'])->name('dashboard');

        // Master Perusahaan (PT) CRUD
        Route::get('/companies/export', [CompanyController::class, 'exportExcel'])->name('companies.export');
        Route::get('/companies/import/template', [CompanyController::class, 'downloadTemplate'])->name('companies.import.template');
        Route::post('/companies/import', [CompanyController::class, 'importExcel'])->name('companies.import');
        Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
        Route::get('/companies/data', [CompanyController::class, 'data'])->name('companies.data');
        Route::post('/companies/store', [CompanyController::class, 'store'])->middleware('throttle:30,1')->name('companies.store');
        Route::get('/companies/{company}/show', [CompanyController::class, 'show'])->name('companies.show');
        Route::get('/companies/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
        Route::post('/companies/{company}/update', [CompanyController::class, 'update'])->middleware('throttle:30,1')->name('companies.update');
        Route::delete('/companies/{company}/delete', [CompanyController::class, 'destroy'])->middleware('throttle:30,1')->name('companies.destroy');

        // Master Cabang CRUD
        Route::get('/branches/export', [BranchController::class, 'exportExcel'])->name('branches.export');
        Route::get('/branches/import/template', [BranchController::class, 'downloadTemplate'])->name('branches.import.template');
        Route::post('/branches/import', [BranchController::class, 'importExcel'])->name('branches.import');
        Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('/branches/data', [BranchController::class, 'data'])->name('branches.data');
        Route::post('/cabang/store', [BranchController::class, 'store'])->middleware('throttle:30,1')->name('branches.store');
        Route::get('/cabang/{branch}/show', [BranchController::class, 'show'])->name('branches.show');
        Route::get('/cabang/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
        Route::post('/cabang/{branch}/update', [BranchController::class, 'update'])->middleware('throttle:30,1')->name('branches.update');
        Route::delete('/cabang/{branch}/delete', [BranchController::class, 'destroy'])->middleware('throttle:30,1')->name('branches.destroy');

        // Master Karyawan (HRD) CRUD
        Route::get('/karyawan', [MasterKaryawanController::class, 'index'])->name('master-karyawan.index');
        Route::get('/karyawan/data', [MasterKaryawanController::class, 'data'])->name('master-karyawan.data');
        Route::get('/karyawan/export', [MasterKaryawanController::class, 'exportExcel'])->name('master-karyawan.export');
        Route::get('/karyawan/import/template', [MasterKaryawanController::class, 'downloadTemplate'])->name('master-karyawan.import.template');
        Route::post('/karyawan/import', [MasterKaryawanController::class, 'importExcel'])->name('master-karyawan.import');
        Route::post('/karyawan/store', [MasterKaryawanController::class, 'store'])->middleware('throttle:30,1')->name('master-karyawan.store');
        Route::get('/karyawan/{karyawan}/edit', [MasterKaryawanController::class, 'edit'])->name('master-karyawan.edit');
        Route::post('/karyawan/{karyawan}/update', [MasterKaryawanController::class, 'update'])->middleware('throttle:30,1')->name('master-karyawan.update');
        Route::delete('/karyawan/{karyawan}/delete', [MasterKaryawanController::class, 'destroy'])->middleware('throttle:30,1')->name('master-karyawan.destroy');

        // Manajemen User / Akun CRUD
        Route::get('/viewusers', [KaryawanController::class, 'index'])->name('karyawan.index');
        Route::get('/users/data', [KaryawanController::class, 'data'])->name('karyawan.data');
        Route::get('/users/export', [KaryawanController::class, 'exportExcel'])->name('karyawan.export');
        Route::get('/users/import/template', [KaryawanController::class, 'downloadTemplate'])->name('karyawan.import.template');
        Route::post('/users/import', [KaryawanController::class, 'importExcel'])->name('karyawan.import');
        Route::post('/users/store', [KaryawanController::class, 'store'])->name('karyawan.store');
        Route::get('/users/{karyawan}/edit', [KaryawanController::class, 'edit'])->name('karyawan.edit');
        Route::post('/users/{karyawan}/update', [KaryawanController::class, 'update'])->name('karyawan.update');
        Route::delete('/users/{karyawan}/delete', [KaryawanController::class, 'destroy'])->name('karyawan.destroy');
        Route::post('/users/{karyawan}/reset-password', [KaryawanController::class, 'resetPassword'])->name('karyawan.reset-password');

        // Master Departemen CRUD
        Route::get('/departemen', [DepartemenController::class, 'index'])->name('departemen.index');
        Route::get('/departemen/data', [DepartemenController::class, 'data'])->name('departemen.data');
        Route::get('/departemen/export', [DepartemenController::class, 'exportExcel'])->name('departemen.export');
        Route::get('/departemen/import/template', [DepartemenController::class, 'downloadTemplate'])->name('departemen.import.template');
        Route::post('/departemen/import', [DepartemenController::class, 'importExcel'])->name('departemen.import');
        Route::post('/departemen/store', [DepartemenController::class, 'store'])->middleware('throttle:30,1')->name('departemen.store');
        Route::get('/departemen/{departemen}/edit', [DepartemenController::class, 'edit'])->name('departemen.edit');
        Route::post('/departemen/{departemen}/update', [DepartemenController::class, 'update'])->middleware('throttle:30,1')->name('departemen.update');
        Route::delete('/departemen/{departemen}/delete', [DepartemenController::class, 'destroy'])->middleware('throttle:30,1')->name('departemen.destroy');

        // Master Jabatan/Position CRUD
        Route::get('/positions', [PositionController::class, 'index'])->name('positions.index');
        Route::get('/positions/data', [PositionController::class, 'data'])->name('positions.data');
        Route::get('/positions/export', [PositionController::class, 'exportExcel'])->name('positions.export');
        Route::get('/positions/import/template', [PositionController::class, 'downloadTemplate'])->name('positions.import.template');
        Route::post('/positions/import', [PositionController::class, 'importExcel'])->name('positions.import');
        Route::post('/positions/store', [PositionController::class, 'store'])->middleware('throttle:30,1')->name('positions.store');
        Route::get('/positions/{position}/edit', [PositionController::class, 'edit'])->name('positions.edit');
        Route::post('/positions/{position}/update', [PositionController::class, 'update'])->middleware('throttle:30,1')->name('positions.update');
        Route::delete('/positions/{position}/delete', [PositionController::class, 'destroy'])->middleware('throttle:30,1')->name('positions.destroy');

        // Master Satuan CRUD
        Route::get('/satuan', [MasterSatuanController::class, 'index'])->name('satuan.index');
        Route::get('/satuan/data', [MasterSatuanController::class, 'data'])->name('satuan.data');
        Route::get('/satuan/export', [MasterSatuanController::class, 'exportExcel'])->name('satuan.export');
        Route::get('/satuan/import/template', [MasterSatuanController::class, 'downloadTemplate'])->name('satuan.import.template');
        Route::post('/satuan/import', [MasterSatuanController::class, 'importExcel'])->name('satuan.import');
        Route::post('/satuan/store', [MasterSatuanController::class, 'store'])->middleware('throttle:30,1')->name('satuan.store');
        Route::get('/satuan/{satuan}/edit', [MasterSatuanController::class, 'edit'])->name('satuan.edit');
        Route::post('/satuan/{satuan}/update', [MasterSatuanController::class, 'update'])->middleware('throttle:30,1')->name('satuan.update');
        Route::delete('/satuan/{satuan}/delete', [MasterSatuanController::class, 'destroy'])->middleware('throttle:30,1')->name('satuan.destroy');

        // Master Brand CRUD
        Route::get('/brand', [MasterBrandController::class, 'index'])->name('brand.index');
        Route::get('/brand/data', [MasterBrandController::class, 'data'])->name('brand.data');
        Route::get('/brand/export', [MasterBrandController::class, 'exportExcel'])->name('brand.export');
        Route::get('/brand/import/template', [MasterBrandController::class, 'downloadTemplate'])->name('brand.import.template');
        Route::post('/brand/import', [MasterBrandController::class, 'importExcel'])->name('brand.import');
        Route::post('/brand/store', [MasterBrandController::class, 'store'])->middleware('throttle:30,1')->name('brand.store');
        Route::get('/brand/{brand}/edit', [MasterBrandController::class, 'edit'])->name('brand.edit');
        Route::post('/brand/{brand}/update', [MasterBrandController::class, 'update'])->middleware('throttle:30,1')->name('brand.update');
        Route::delete('/brand/{brand}/delete', [MasterBrandController::class, 'destroy'])->middleware('throttle:30,1')->name('brand.destroy');

        // Master Tipe Barang CRUD
        Route::get('/tipe-barang', [MasterTipeBarangController::class, 'index'])->name('tipe-barang.index');
        Route::get('/tipe-barang/data', [MasterTipeBarangController::class, 'data'])->name('tipe-barang.data');
        Route::get('/tipe-barang/export', [MasterTipeBarangController::class, 'exportExcel'])->name('tipe-barang.export');
        Route::get('/tipe-barang/import/template', [MasterTipeBarangController::class, 'downloadTemplate'])->name('tipe-barang.import.template');
        Route::post('/tipe-barang/import', [MasterTipeBarangController::class, 'importExcel'])->name('tipe-barang.import');
        Route::post('/tipe-barang/store', [MasterTipeBarangController::class, 'store'])->middleware('throttle:30,1')->name('tipe-barang.store');
        Route::get('/tipe-barang/{tipe}/edit', [MasterTipeBarangController::class, 'edit'])->name('tipe-barang.edit');
        Route::post('/tipe-barang/{tipe}/update', [MasterTipeBarangController::class, 'update'])->middleware('throttle:30,1')->name('tipe-barang.update');
        Route::delete('/tipe-barang/{tipe}/delete', [MasterTipeBarangController::class, 'destroy'])->middleware('throttle:30,1')->name('tipe-barang.destroy');

        // Master Warna CRUD
        Route::get('/warna', [MasterWarnaController::class, 'index'])->name('warna.index');
        Route::get('/warna/data', [MasterWarnaController::class, 'data'])->name('warna.data');
        Route::get('/warna/export', [MasterWarnaController::class, 'exportExcel'])->name('warna.export');
        Route::get('/warna/import/template', [MasterWarnaController::class, 'downloadTemplate'])->name('warna.import.template');
        Route::post('/warna/import', [MasterWarnaController::class, 'importExcel'])->name('warna.import');
        Route::post('/warna/store', [MasterWarnaController::class, 'store'])->middleware('throttle:30,1')->name('warna.store');
        Route::get('/warna/{warna}/edit', [MasterWarnaController::class, 'edit'])->name('warna.edit');
        Route::post('/warna/{warna}/update', [MasterWarnaController::class, 'update'])->middleware('throttle:30,1')->name('warna.update');
        Route::delete('/warna/{warna}/delete', [MasterWarnaController::class, 'destroy'])->middleware('throttle:30,1')->name('warna.destroy');

        // Master Jenis Gudang CRUD
        Route::get('/jenis-gudang/export', [MasterJenisGudangController::class, 'exportExcel'])->name('jenis-gudang.export');
        Route::get('/jenis-gudang/import/template', [MasterJenisGudangController::class, 'downloadTemplate'])->name('jenis-gudang.import.template');
        Route::post('/jenis-gudang/import', [MasterJenisGudangController::class, 'importExcel'])->name('jenis-gudang.import');
        Route::get('/jenis-gudang', [MasterJenisGudangController::class, 'index'])->name('jenis-gudang.index');
        Route::get('/jenis-gudang/data', [MasterJenisGudangController::class, 'data'])->name('jenis-gudang.data');
        Route::post('/jenis-gudang/store', [MasterJenisGudangController::class, 'store'])->middleware('throttle:30,1')->name('jenis-gudang.store');
        Route::get('/jenis-gudang/{jenisGudang}/edit', [MasterJenisGudangController::class, 'edit'])->name('jenis-gudang.edit');
        Route::post('/jenis-gudang/{jenisGudang}/update', [MasterJenisGudangController::class, 'update'])->middleware('throttle:30,1')->name('jenis-gudang.update');
        Route::delete('/jenis-gudang/{jenisGudang}/delete', [MasterJenisGudangController::class, 'destroy'])->middleware('throttle:30,1')->name('jenis-gudang.destroy');

        // Master Kategori Barang CRUD
        Route::get('/kategori-barang', [MasterKategoriBarangController::class, 'index'])->name('kategori-barang.index');
        Route::get('/kategori-barang/data', [MasterKategoriBarangController::class, 'data'])->name('kategori-barang.data');
        Route::get('/kategori-barang/export', [MasterKategoriBarangController::class, 'exportExcel'])->name('kategori-barang.export');
        Route::get('/kategori-barang/import/template', [MasterKategoriBarangController::class, 'downloadTemplate'])->name('kategori-barang.import.template');
        Route::post('/kategori-barang/import', [MasterKategoriBarangController::class, 'importExcel'])->name('kategori-barang.import');
        Route::post('/kategori-barang/store', [MasterKategoriBarangController::class, 'store'])->middleware('throttle:30,1')->name('kategori-barang.store');
        Route::get('/kategori-barang/{kategori}/edit', [MasterKategoriBarangController::class, 'edit'])->name('kategori-barang.edit');
        Route::post('/kategori-barang/{kategori}/update', [MasterKategoriBarangController::class, 'update'])->middleware('throttle:30,1')->name('kategori-barang.update');
        Route::delete('/kategori-barang/{kategori}/delete', [MasterKategoriBarangController::class, 'destroy'])->middleware('throttle:30,1')->name('kategori-barang.destroy');

        // Master Gudang CRUD
        Route::get('/gudang/export', [MasterGudangController::class, 'exportExcel'])->name('gudang.export');
        Route::get('/gudang/import/template', [MasterGudangController::class, 'downloadTemplate'])->name('gudang.import.template');
        Route::post('/gudang/import', [MasterGudangController::class, 'importExcel'])->name('gudang.import');
        Route::get('/gudang', [MasterGudangController::class, 'index'])->name('gudang.index');
        Route::get('/gudang/data', [MasterGudangController::class, 'data'])->name('gudang.data');
        Route::post('/gudang/store', [MasterGudangController::class, 'store'])->middleware('throttle:30,1')->name('gudang.store');
        Route::get('/gudang/{gudang}/edit', [MasterGudangController::class, 'edit'])->name('gudang.edit');
        Route::post('/gudang/{gudang}/update', [MasterGudangController::class, 'update'])->middleware('throttle:30,1')->name('gudang.update');
        Route::delete('/gudang/{gudang}/delete', [MasterGudangController::class, 'destroy'])->middleware('throttle:30,1')->name('gudang.destroy');

        // Master Lokasi Rak CRUD
        Route::get('/lokasi-rak/export', [MasterLokasiRakController::class, 'exportExcel'])->name('lokasi-rak.export');
        Route::get('/lokasi-rak/import/template', [MasterLokasiRakController::class, 'downloadTemplate'])->name('lokasi-rak.import.template');
        Route::post('/lokasi-rak/import', [MasterLokasiRakController::class, 'importExcel'])->name('lokasi-rak.import');
        Route::get('/lokasi-rak', [MasterLokasiRakController::class, 'index'])->name('lokasi-rak.index');
        Route::get('/lokasi-rak/data', [MasterLokasiRakController::class, 'data'])->name('lokasi-rak.data');
        Route::post('/lokasi-rak/store', [MasterLokasiRakController::class, 'store'])->middleware('throttle:30,1')->name('lokasi-rak.store');
        Route::get('/lokasi-rak/{lokasiRak}/edit', [MasterLokasiRakController::class, 'edit'])->name('lokasi-rak.edit');
        Route::post('/lokasi-rak/{lokasiRak}/update', [MasterLokasiRakController::class, 'update'])->middleware('throttle:30,1')->name('lokasi-rak.update');
        Route::delete('/lokasi-rak/{lokasiRak}/delete', [MasterLokasiRakController::class, 'destroy'])->middleware('throttle:30,1')->name('lokasi-rak.destroy');

        // Master Barang CRUD
        Route::get('/barang/summary-counts', [MasterBarangController::class, 'getSummaryCounts'])->name('barang.summary-counts');
        Route::get('/barang/data', [MasterBarangController::class, 'getData'])->name('barang.data');
        Route::get('/barang/export', [MasterBarangController::class, 'exportExcel'])->name('barang.export');
        Route::get('/barang/import/template', [MasterBarangController::class, 'downloadTemplate'])->name('barang.import.template');
        Route::post('/barang/import', [MasterBarangController::class, 'importExcel'])->name('barang.import');
        Route::get('/barang', [MasterBarangController::class, 'index'])->name('barang.index');
        Route::post('/barang/store', [MasterBarangController::class, 'store'])->name('barang.store');
        Route::get('/barang/{barang}/edit', [MasterBarangController::class, 'edit'])->name('barang.edit');
        Route::post('/barang/{barang}/update', [MasterBarangController::class, 'update'])->name('barang.update');
        Route::delete('/barang/{barang}/delete', [MasterBarangController::class, 'destroy'])->name('barang.destroy');
        Route::delete('/barang/foto/{foto}/delete', [MasterBarangController::class, 'deleteFoto'])->name('barang.foto.delete');
        Route::post('/barang/foto/{foto}/set-primary', [MasterBarangController::class, 'setPrimaryFoto'])->name('barang.foto.set-primary');
        Route::post('/barang/foto/reorder', [MasterBarangController::class, 'reorderFotos'])->name('barang.foto.reorder');

        // Master Bundle Produk & Visibility Setting
        Route::get('/master-bundle/data', [MasterBundleController::class, 'getData'])->name('master-bundle.data');
        Route::get('/master-bundle/pengaturan-kategori', [MasterBundleController::class, 'pengaturanKategori'])->name('master-bundle.pengaturan-kategori');
        Route::post('/master-bundle/pengaturan-kategori/toggle', [MasterBundleController::class, 'toggleKategoriVisibility'])->name('master-bundle.pengaturan-kategori.toggle');
        Route::post('/master-bundle/pengaturan-kategori/bulk', [MasterBundleController::class, 'bulkUpdateKategoriVisibility'])->name('master-bundle.pengaturan-kategori.bulk');
        Route::get('/master-bundle', [MasterBundleController::class, 'index'])->name('master-bundle.index');
        Route::post('/master-bundle/store', [MasterBundleController::class, 'store'])->name('master-bundle.store');
        Route::get('/master-bundle/{id}/show', [MasterBundleController::class, 'show'])->name('master-bundle.show');
        Route::get('/master-bundle/{id}/edit', [MasterBundleController::class, 'edit'])->name('master-bundle.edit');
        Route::post('/master-bundle/{id}/update', [MasterBundleController::class, 'update'])->name('master-bundle.update');
        Route::delete('/master-bundle/{id}/delete', [MasterBundleController::class, 'destroy'])->name('master-bundle.destroy');
        Route::post('/master-bundle/rakit', [MasterBundleController::class, 'rakitBundle'])->name('master-bundle.rakit');

        // Master Supplier CRUD
        Route::get('/supplier', [MasterSupplierController::class, 'index'])->name('supplier.index');
        Route::get('/supplier/export', [MasterSupplierController::class, 'exportExcel'])->name('supplier.export');
        Route::get('/supplier/download-template', [MasterSupplierController::class, 'downloadTemplate'])->name('supplier.download-template');
        Route::get('/supplier/generate-code', [MasterSupplierController::class, 'getNextCode'])->name('supplier.generate-code');
        Route::post('/supplier/import', [MasterSupplierController::class, 'import'])->name('supplier.import');
        Route::post('/supplier/store', [MasterSupplierController::class, 'store'])->name('supplier.store');
        Route::get('/supplier/{supplier}/edit', [MasterSupplierController::class, 'edit'])->name('supplier.edit');
        Route::post('/supplier/{supplier}/update', [MasterSupplierController::class, 'update'])->name('supplier.update');
        Route::delete('/supplier/{supplier}/delete', [MasterSupplierController::class, 'destroy'])->name('supplier.destroy');

        // Master Customer CRUD
        Route::get('/customer', [MasterCustomerController::class, 'index'])->name('customer.index');
        Route::get('/customer/data', [MasterCustomerController::class, 'getData'])->name('customer.data');
        Route::post('/customer/store', [MasterCustomerController::class, 'store'])->name('customer.store');
        Route::get('/customer/{customer}/edit', [MasterCustomerController::class, 'edit'])->name('customer.edit');
        Route::post('/customer/{customer}/update', [MasterCustomerController::class, 'update'])->name('customer.update');
        Route::delete('/customer/{customer}/delete', [MasterCustomerController::class, 'destroy'])->name('customer.destroy');

        // Tender Management CRUD & Approval Workflow
        Route::get('/tender', [TenderController::class, 'index'])->name('tender.index');
        Route::get('/tender/data', [TenderController::class, 'getData'])->name('tender.data');
        Route::get('/tender/kanban-data', [TenderController::class, 'getKanbanData'])->name('tender.kanban-data');
        Route::post('/tender/store', [TenderController::class, 'store'])->name('tender.store');
        Route::get('/tender/{tender}/detail-json', [TenderController::class, 'getDetailJson'])->name('tender.detail-json');
        Route::get('/tender/{tender}/edit', [TenderController::class, 'edit'])->name('tender.edit');
        Route::post('/tender/{tender}/update', [TenderController::class, 'update'])->name('tender.update');
        Route::post('/tender/{tender}/update-stage', [TenderController::class, 'updateStage'])->name('tender.update-stage');
        Route::post('/tender/{tender}/submit-approval', [TenderController::class, 'submitApproval'])->name('tender.submit-approval');
        Route::post('/tender/{tender}/approve', [TenderController::class, 'approve'])->name('tender.approve');
        Route::post('/tender/{tender}/reject', [TenderController::class, 'reject'])->name('tender.reject');
        Route::post('/tender/{tender}/mark-as-won', [TenderController::class, 'markAsWon'])->name('tender.mark-as-won');
        Route::post('/tender/{tender}/mark-as-lost', [TenderController::class, 'markAsLost'])->name('tender.mark-as-lost');
        Route::post('/tender/{tender}/convert-so', [TenderController::class, 'convertToSalesOrder'])->name('tender.convert-so');
        Route::post('/tender/{tender}/convert-to-so', [TenderController::class, 'convertToSalesOrder'])->name('tender.convert-to-so');
        Route::delete('/tender/{tender}/delete', [TenderController::class, 'destroy'])->name('tender.destroy');
        Route::post('/tender/{tender}/followup', [TenderController::class, 'storeFollowup'])->name('tender.followup.store');
        Route::get('/tender/{tender}/print-penawaran', [TenderController::class, 'printPenawaran'])->name('tender.print-penawaran');

        // Penawaran Harga (Sales Quotation) CRUD
        Route::get('/penawaran', [SalesQuotationController::class, 'index'])->name('penawaran.index');
        Route::get('/penawaran/data', [SalesQuotationController::class, 'getData'])->name('penawaran.data');
        Route::get('/penawaran/create', [SalesQuotationController::class, 'create'])->name('penawaran.create');
        Route::post('/penawaran/store', [SalesQuotationController::class, 'store'])->name('penawaran.store');
        Route::get('/penawaran/{quotation}', [SalesQuotationController::class, 'show'])->name('penawaran.show');
        Route::get('/penawaran/{quotation}/edit', [SalesQuotationController::class, 'edit'])->name('penawaran.edit');
        Route::post('/penawaran/{quotation}/update', [SalesQuotationController::class, 'update'])->name('penawaran.update');
        Route::delete('/penawaran/{quotation}/delete', [SalesQuotationController::class, 'destroy'])->name('penawaran.destroy');
        Route::post('/penawaran/{quotation}/revisi', [SalesQuotationController::class, 'createRevision'])->name('penawaran.revisi');
        Route::post('/penawaran/{quotation}/approve', [SalesQuotationController::class, 'approve'])->name('penawaran.approve');
        Route::post('/penawaran/{quotation}/convert-so', [SalesQuotationController::class, 'convertToSalesOrder'])->name('penawaran.convert-so');
        Route::get('/penawaran/{quotation}/print', [SalesQuotationController::class, 'printPdf'])->name('penawaran.print');

        // Order Penjualan (Sales Order / POS)
        Route::prefix('marketing/sales-order')->name('marketing.sales-order.')->group(function () {
            Route::get('/', [SalesOrderController::class, 'index'])->name('index');
            Route::get('/products', [SalesOrderController::class, 'getProducts'])->name('products');
            Route::get('/product/{barang}/detail', [SalesOrderController::class, 'getProductDetail'])->name('product-detail');
            Route::get('/history-data', [SalesOrderController::class, 'getOrders'])->name('history-data');
            Route::get('/orders-data', [SalesOrderController::class, 'getOrdersData'])->name('orders.data');
            Route::post('/store', [SalesOrderController::class, 'store'])->name('store');
            Route::get('/{order}', [SalesOrderController::class, 'show'])->name('show');
            Route::get('/{order}/detail', [SalesOrderController::class, 'show'])->name('detail');
            Route::get('/{order}/print-thermal', [SalesOrderController::class, 'printThermalReceipt'])->name('print.thermal');
            Route::get('/{order}/print-invoice', [SalesOrderController::class, 'printInvoicePdf'])->name('print.invoice');
            Route::put('/{order}/update', [SalesOrderController::class, 'update'])->name('update');
            Route::put('/{order}/status', [SalesOrderController::class, 'updateStatus'])->name('update-status');
            Route::post('/{order}/cancel', [SalesOrderController::class, 'cancelOrder'])->name('cancel');
        });

        // Pengiriman & Surat Jalan (Delivery Order / DO)
        Route::prefix('marketing/delivery-order')->name('marketing.delivery-order.')->group(function () {
            Route::get('/', [DeliveryOrderController::class, 'index'])->name('index');
            Route::get('/data', [DeliveryOrderController::class, 'getData'])->name('data');
            Route::get('/available-sos', [DeliveryOrderController::class, 'getAvailableSalesOrders'])->name('available-sos');
            Route::get('/so/{order}/details', [DeliveryOrderController::class, 'getSalesOrderDetails'])->name('so-details');
            Route::post('/store', [DeliveryOrderController::class, 'store'])->name('store');
            Route::get('/{deliveryOrder}', [DeliveryOrderController::class, 'show'])->name('show');
            Route::put('/{deliveryOrder}/status', [DeliveryOrderController::class, 'updateStatus'])->name('update-status');
            Route::get('/{deliveryOrder}/print', [DeliveryOrderController::class, 'printSuratJalan'])->name('print');
        });

        // Purchase Requisition CRUD
        Route::get('/purchase-requisition', [PurchaseRequisitionController::class, 'index'])->name('pr.index');
        Route::get('/purchase-requisition/export', [PurchaseRequisitionController::class, 'exportExcel'])->name('pr.export');
        Route::get('/purchase-requisition/search-barang', [PurchaseRequisitionController::class, 'searchBarang'])->name('pr.search-barang');
        Route::post('/purchase-requisition/store', [PurchaseRequisitionController::class, 'store'])->name('pr.store');
        Route::get('/purchase-requisition/{purchaseRequisition}', [PurchaseRequisitionController::class, 'show'])->name('pr.show');
        Route::get('/purchase-requisition/{purchaseRequisition}/edit', [PurchaseRequisitionController::class, 'edit'])->name('pr.edit');
        Route::post('/purchase-requisition/{purchaseRequisition}/update', [PurchaseRequisitionController::class, 'update'])->name('pr.update');
        Route::delete('/purchase-requisition/{purchaseRequisition}/delete', [PurchaseRequisitionController::class, 'destroy'])->name('pr.destroy');
        Route::post('/purchase-requisition/{pr}/approve', [PurchaseRequisitionController::class, 'approve'])->name('pr.approve');
        Route::post('/purchase-requisition/{pr}/reject', [PurchaseRequisitionController::class, 'reject'])->name('pr.reject');
        Route::get('/purchase-requisition/{purchaseRequisition}/print', [PurchaseRequisitionController::class, 'print'])->name('pr.print');

        // Master Pajak CRUD
        Route::get('/pajak', [MasterPajakController::class, 'index'])->name('pajak.index');
        Route::get('/pajak/generate-code', [MasterPajakController::class, 'generateKode'])->name('pajak.generate-code');
        Route::post('/pajak/store', [MasterPajakController::class, 'store'])->name('pajak.store');
        Route::get('/pajak/{pajak}/edit', [MasterPajakController::class, 'edit'])->name('pajak.edit');
        Route::post('/pajak/{pajak}/update', [MasterPajakController::class, 'update'])->name('pajak.update');
        Route::delete('/pajak/{pajak}/delete', [MasterPajakController::class, 'destroy'])->name('pajak.destroy');
        // Master Pajak Export / Import
        Route::get('/pajak/export', [MasterPajakController::class, 'exportExcel'])->name('pajak.export');
        Route::get('/pajak/import/template', [MasterPajakController::class, 'downloadTemplate'])->name('pajak.import.template');
        Route::post('/pajak/import', [MasterPajakController::class, 'importExcel'])->name('pajak.import');

        // Master Currency CRUD
        Route::get('/currency', [MasterCurrencyController::class, 'index'])->name('currency.index');
        Route::post('/currency/store', [MasterCurrencyController::class, 'store'])->name('currency.store');
        Route::get('/currency/{currency}/edit', [MasterCurrencyController::class, 'edit'])->name('currency.edit');
        Route::post('/currency/{currency}/update', [MasterCurrencyController::class, 'update'])->name('currency.update');
        Route::delete('/currency/{currency}/delete', [MasterCurrencyController::class, 'destroy'])->name('currency.destroy');
        // Master Currency Export / Import
        Route::get('/currency/export', [MasterCurrencyController::class, 'exportExcel'])->name('currency.export');
        Route::get('/currency/import/template', [MasterCurrencyController::class, 'downloadTemplate'])->name('currency.import.template');
        Route::post('/currency/import', [MasterCurrencyController::class, 'importExcel'])->name('currency.import');

        // Master Payment Term CRUD
        Route::get('/payment-term', [PaymentTermController::class, 'index'])->name('payment-term.index');
        Route::post('/payment-term/store', [PaymentTermController::class, 'store'])->name('payment-term.store');
        Route::get('/payment-term/{paymentTerm}/edit', [PaymentTermController::class, 'edit'])->name('payment-term.edit');
        Route::post('/payment-term/{paymentTerm}/update', [PaymentTermController::class, 'update'])->name('payment-term.update');
        Route::delete('/payment-term/{paymentTerm}/delete', [PaymentTermController::class, 'destroy'])->name('payment-term.destroy');
        // Master Payment Term Export / Import
        Route::get('/payment-term/export', [PaymentTermController::class, 'exportExcel'])->name('payment-term.export');
        Route::get('/payment-term/import/template', [PaymentTermController::class, 'downloadTemplate'])->name('payment-term.import.template');
        Route::post('/payment-term/import', [PaymentTermController::class, 'importExcel'])->name('payment-term.import');

        // Master Payment Method CRUD
        Route::get('/payment-method', [PaymentMethodController::class, 'index'])->name('payment-method.index');
        Route::get('/payment-method/generate-code', [PaymentMethodController::class, 'generateKode'])->name('payment-method.generate-code');
        Route::post('/payment-method/store', [PaymentMethodController::class, 'store'])->name('payment-method.store');
        Route::get('/payment-method/{paymentMethod}/edit', [PaymentMethodController::class, 'edit'])->name('payment-method.edit');
        Route::post('/payment-method/{paymentMethod}/update', [PaymentMethodController::class, 'update'])->name('payment-method.update');
        Route::delete('/payment-method/{paymentMethod}/delete', [PaymentMethodController::class, 'destroy'])->name('payment-method.destroy');
        // Master Payment Method Export / Import
        Route::get('/payment-method/export', [PaymentMethodController::class, 'exportExcel'])->name('payment-method.export');
        Route::get('/payment-method/import/template', [PaymentMethodController::class, 'downloadTemplate'])->name('payment-method.import.template');
        Route::post('/payment-method/import', [PaymentMethodController::class, 'importExcel'])->name('payment-method.import');

        // Purchase Order CRUD
        Route::get('/purchase-order', [PurchaseOrderController::class, 'index'])->name('po.index');
        Route::get('/purchase-order/datatable', [PurchaseOrderController::class, 'datatable'])->name('po.datatable');
        Route::get('/purchase-order/export', [PurchaseOrderController::class, 'exportExcel'])->name('po.export');
        Route::get('/purchase-order/search-barang', [PurchaseOrderController::class, 'searchBarang'])->name('po.search-barang');
        Route::get('/purchase-order/pr-details/{pr}', [PurchaseOrderController::class, 'getPrDetails'])->name('po.pr-details');
        Route::get('/purchase-order/search-supplier', [PurchaseOrderController::class, 'searchSupplier'])->name('po.search-supplier');
        Route::get('/purchase-order/search-pr', [PurchaseOrderController::class, 'searchPr'])->name('po.search-pr');
        Route::get('/purchase-order/search-tender', [PurchaseOrderController::class, 'searchTender'])->name('po.search-tender');
        Route::get('/purchase-order/search-currency', [PurchaseOrderController::class, 'searchCurrency'])->name('po.search-currency');
        Route::post('/purchase-order/store', [PurchaseOrderController::class, 'store'])->name('po.store');
        Route::get('/purchase-order/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('po.show');
        Route::delete('/purchase-order/{purchaseOrder}/delete', [PurchaseOrderController::class, 'destroy'])->name('po.destroy');
        Route::get('/purchase-order/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->name('po.edit');
        Route::post('/purchase-order/{purchaseOrder}/update', [PurchaseOrderController::class, 'update'])->name('po.update');
        Route::post('/purchase-order/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('po.approve');
        Route::post('/purchase-order/{purchaseOrder}/reject', [PurchaseOrderController::class, 'reject'])->name('po.reject');
        Route::get('/purchase-order/{purchaseOrder}/print', [PurchaseOrderController::class, 'print'])->name('po.print');


        // Goods Receipt CRUD
        Route::get('/goods-receipt', [GoodsReceiptController::class, 'index'])->name('gr.index');
        Route::get('/goods-receipt/active-pos', [GoodsReceiptController::class, 'getActivePos'])->name('gr.active-pos');
        Route::get('/goods-receipt/po-details/{po}', [GoodsReceiptController::class, 'getPoDetails'])->name('gr.po-details');
        Route::get('/goods-receipt/gudangs', [GoodsReceiptController::class, 'getGudangs'])->name('gr.gudangs');
        Route::get('/goods-receipt/lokasi-raks/{gudang}', [GoodsReceiptController::class, 'getLokasiRaks'])->name('gr.lokasi-raks');
        Route::post('/goods-receipt/store', [GoodsReceiptController::class, 'store'])->name('gr.store');
        Route::put('/goods-receipt/{goodsReceipt}/update', [GoodsReceiptController::class, 'update'])->name('gr.update');
        Route::patch('/goods-receipt/{goodsReceipt}/update-kurs', [GoodsReceiptController::class, 'updateKurs'])->name('gr.update-kurs');
        Route::get('/goods-receipt/{goodsReceipt}', [GoodsReceiptController::class, 'show'])->name('gr.show');
        Route::get('/goods-receipt/{goodsReceipt}/print', [GoodsReceiptController::class, 'print'])->name('gr.print');
        Route::post('/goods-receipt/{goodsReceipt}/post', [GoodsReceiptController::class, 'post'])->name('gr.post');
        Route::delete('/goods-receipt/{goodsReceipt}/delete', [GoodsReceiptController::class, 'destroy'])->name('gr.destroy');

        // Purchase Invoice (Faktur Pembelian)
        Route::get('/purchase-invoices', [\App\Http\Controllers\PurchaseInvoiceController::class, 'index'])->name('purchase-invoices.index');
        Route::get('/purchase-invoices/gr-detail/{gr}', [\App\Http\Controllers\PurchaseInvoiceController::class, 'getGrDetailForInvoice'])->name('purchase-invoices.gr-detail');
        Route::post('/purchase-invoices/store', [\App\Http\Controllers\PurchaseInvoiceController::class, 'store'])->name('purchase-invoices.store');
        Route::get('/purchase-invoices/{purchaseInvoice}', [\App\Http\Controllers\PurchaseInvoiceController::class, 'show'])->name('purchase-invoices.show');
        Route::get('/purchase-invoices/{purchaseInvoice}/edit', [\App\Http\Controllers\PurchaseInvoiceController::class, 'edit'])->name('purchase-invoices.edit');
        Route::post('/purchase-invoices/{purchaseInvoice}/update', [\App\Http\Controllers\PurchaseInvoiceController::class, 'update'])->name('purchase-invoices.update');
        Route::post('/purchase-invoices/{purchaseInvoice}/post', [\App\Http\Controllers\PurchaseInvoiceController::class, 'post'])->name('purchase-invoices.post');
        Route::post('/purchase-invoices/{purchaseInvoice}/approve', [\App\Http\Controllers\PurchaseInvoiceController::class, 'approve'])->name('purchase-invoices.approve');
        Route::post('/purchase-invoices/{purchaseInvoice}/void', [\App\Http\Controllers\PurchaseInvoiceController::class, 'void'])->name('purchase-invoices.void');
        Route::get('/purchase-invoices/{purchaseInvoice}/pdf', [\App\Http\Controllers\PurchaseInvoiceController::class, 'pdf'])->name('purchase-invoices.pdf');
        Route::delete('/purchase-invoices/{purchaseInvoice}/delete', [\App\Http\Controllers\PurchaseInvoiceController::class, 'destroy'])->name('purchase-invoices.destroy');

        // Purchase Payments (Pembayaran Utang Supplier)
        Route::prefix('purchase-payments')->name('purchase-payments.')->group(function () {
            Route::get('/', [\App\Http\Controllers\PurchasePaymentController::class, 'index'])->name('index');
            Route::get('/check-sync', [\App\Http\Controllers\PurchasePaymentController::class, 'checkSync'])->name('check-sync');
            Route::post('/', [\App\Http\Controllers\PurchasePaymentController::class, 'store'])->name('store');
            Route::post('/store', [\App\Http\Controllers\PurchasePaymentController::class, 'store'])->name('store.alias');
            Route::get('/lookup-invoices', [\App\Http\Controllers\PurchasePaymentController::class, 'lookupInvoices'])->name('lookup-invoices');
            Route::get('/unpaid-invoices/{supplier}', [\App\Http\Controllers\PurchasePaymentController::class, 'getUnpaidInvoices'])->name('unpaid-invoices');
            Route::get('/generate-code', [\App\Http\Controllers\PurchasePaymentController::class, 'generateCode'])->name('generate-code');
            Route::get('/{purchasePayment}', [\App\Http\Controllers\PurchasePaymentController::class, 'show'])->name('show');
            Route::get('/{purchasePayment}/edit', [\App\Http\Controllers\PurchasePaymentController::class, 'edit'])->name('edit');
            Route::post('/{purchasePayment}', [\App\Http\Controllers\PurchasePaymentController::class, 'update'])->name('update.rest');
            Route::put('/{purchasePayment}', [\App\Http\Controllers\PurchasePaymentController::class, 'update'])->name('update.put');
            Route::post('/{purchasePayment}/update', [\App\Http\Controllers\PurchasePaymentController::class, 'update'])->name('update');
            // Approval Workflow Routes
            Route::post('/{purchasePayment}/submit', [\App\Http\Controllers\PurchasePaymentController::class, 'submit'])->name('submit');
            Route::post('/{purchasePayment}/approve', [\App\Http\Controllers\PurchasePaymentController::class, 'approve'])->name('approve');
            Route::post('/{purchasePayment}/reject', [\App\Http\Controllers\PurchasePaymentController::class, 'reject'])->name('reject');
            // End Approval Workflow Routes
            Route::post('/{purchasePayment}/post', [\App\Http\Controllers\PurchasePaymentController::class, 'post'])->name('post');
            Route::post('/{purchasePayment}/cancel', [\App\Http\Controllers\PurchasePaymentController::class, 'cancel'])->name('cancel');
            Route::delete('/{purchasePayment}', [\App\Http\Controllers\PurchasePaymentController::class, 'destroy'])->name('destroy.rest');
            Route::delete('/{purchasePayment}/delete', [\App\Http\Controllers\PurchasePaymentController::class, 'destroy'])->name('destroy');
            Route::get('/{purchasePayment}/pdf', [\App\Http\Controllers\PurchasePaymentController::class, 'pdf'])->name('pdf');
        });



        // Fixed Asset & Depreciation
        Route::get('/fixed-assets', [FixedAssetController::class, 'index'])->name('assets.index');
        Route::get('/fixed-assets/{asset}/edit', [FixedAssetController::class, 'edit'])->name('assets.edit');
        Route::post('/fixed-assets/{asset}/update', [FixedAssetController::class, 'update'])->name('assets.update');
        Route::get('/fixed-assets/{asset}/history', [FixedAssetController::class, 'getDepreciationHistory'])->name('assets.history');
        Route::get('/fixed-assets/monthly-detail/{periode}', [FixedAssetController::class, 'getMonthlyDetail'])->name('assets.monthly-detail');
        Route::post('/fixed-assets/depreciate', [FixedAssetController::class, 'runDepreciation'])->name('assets.depreciate');
        Route::post('/fixed-assets/depreciate/rollback', [FixedAssetController::class, 'rollbackDepreciation'])->name('assets.rollback');

        // Registrasi & Pelacakan Jasa
        Route::get('/registrasi-jasa', [RegistrasiJasaController::class, 'index'])->name('jasa.index');
        Route::post('/registrasi-jasa/store', [RegistrasiJasaController::class, 'store'])->name('jasa.store');
        Route::get('/registrasi-jasa/{jasa}/edit', [RegistrasiJasaController::class, 'edit'])->name('jasa.edit');
        Route::post('/registrasi-jasa/{jasa}/update', [RegistrasiJasaController::class, 'update'])->name('jasa.update');
        Route::delete('/registrasi-jasa/{jasa}/delete', [RegistrasiJasaController::class, 'destroy'])->name('jasa.destroy');

        // Goods Issue (Barang Keluar)
        Route::get('/goods-issue', [GoodsIssueController::class, 'index'])->name('gi.index');
        Route::get('/goods-issue/stok/{gudang}/{barang}', [GoodsIssueController::class, 'getStokBarang'])->name('gi.stok');
        Route::get('/goods-issue/barangs-by-gudang/{gudang}', [GoodsIssueController::class, 'getBarangsByGudang'])->name('gi.barangs-by-gudang');
        Route::post('/goods-issue/store', [GoodsIssueController::class, 'store'])->name('gi.store');
        Route::get('/goods-issue/{goodsIssue}', [GoodsIssueController::class, 'show'])->name('gi.show');
        Route::get('/goods-issue/{goodsIssue}/print', [GoodsIssueController::class, 'printPdf'])->name('gi.print');
        Route::put('/goods-issue/{goodsIssue}', [GoodsIssueController::class, 'update'])->name('gi.update');
        Route::post('/goods-issue/{goodsIssue}/submit', [GoodsIssueController::class, 'submit'])->name('gi.submit');
        Route::post('/goods-issue/{goodsIssue}/reject', [GoodsIssueController::class, 'reject'])->name('gi.reject');
        Route::post('/goods-issue/{goodsIssue}/confirm', [GoodsIssueController::class, 'confirm'])->name('gi.confirm');
        Route::delete('/goods-issue/{goodsIssue}/delete', [GoodsIssueController::class, 'destroy'])->name('gi.destroy');

        // Warehouse Transfer (Transfer Gudang)
        Route::get('/warehouse-transfer', [WarehouseTransferController::class, 'index'])->name('transfer.index');
        Route::get('/warehouse-transfer/stok/{gudang}/{barang}', [WarehouseTransferController::class, 'getStokBarang'])->name('transfer.stok');
        Route::get('/warehouse-transfer/gudang-items/{gudang}', [WarehouseTransferController::class, 'getGudangItems'])->name('transfer.gudang-items');
        Route::get('/warehouse-transfer/company-coas/{company}', [WarehouseTransferController::class, 'getCompanyCoas'])->name('transfer.company-coas');
        Route::post('/warehouse-transfer/store', [WarehouseTransferController::class, 'store'])->name('transfer.store');
        Route::get('/warehouse-transfer/{warehouseTransfer}', [WarehouseTransferController::class, 'show'])->name('transfer.show');
        Route::get('/warehouse-transfer/{warehouseTransfer}/edit', [WarehouseTransferController::class, 'edit'])->name('transfer.edit');
        Route::put('/warehouse-transfer/{warehouseTransfer}', [WarehouseTransferController::class, 'update'])->name('transfer.update');
        Route::post('/warehouse-transfer/{warehouseTransfer}/submit', [WarehouseTransferController::class, 'submit'])->name('transfer.submit');
        Route::post('/warehouse-transfer/{warehouseTransfer}/confirm', [WarehouseTransferController::class, 'confirm'])->name('transfer.confirm');
        Route::delete('/warehouse-transfer/{warehouseTransfer}/delete', [WarehouseTransferController::class, 'destroy'])->name('transfer.destroy');

        // Stock Opname (Penyesuaian Stok)
        Route::get('/stock-opname', [StockOpnameController::class, 'index'])->name('opname.index');
        Route::get('/stock-opname/search-barang', [StockOpnameController::class, 'searchBarang'])->name('opname.search-barang');
        Route::get('/stock-opname/{stockOpname}/find-barang', [StockOpnameController::class, 'findBarangByCode'])->name('opname.find-barang');
        Route::get('/stock-opname/stok/{gudang}/{barang}', [StockOpnameController::class, 'getStokBarang'])->name('opname.stok');
        Route::post('/stock-opname/store', [StockOpnameController::class, 'store'])->name('opname.store');
        Route::get('/stock-opname/{stockOpname}', [StockOpnameController::class, 'show'])->name('opname.show');
        Route::get('/stock-opname/{stockOpname}/edit', [StockOpnameController::class, 'edit'])->name('opname.edit');
        Route::post('/stock-opname/{stockOpname}/update', [StockOpnameController::class, 'update'])->name('opname.update');
        Route::post('/stock-opname/{stockOpname}/submit', [StockOpnameController::class, 'submit'])->name('opname.submit');
        Route::post('/stock-opname/{stockOpname}/approve', [StockOpnameController::class, 'approve'])->name('opname.approve');
        Route::post('/stock-opname/{stockOpname}/reject', [StockOpnameController::class, 'reject'])->name('opname.reject');
        Route::post('/stock-opname/{stockOpname}/save-physical', [StockOpnameController::class, 'savePhysical'])->name('opname.save-physical');
        Route::post('/stock-opname/{stockOpname}/complete', [StockOpnameController::class, 'complete'])->name('opname.complete');
        Route::post('/stock-opname/{stockOpname}/close', [StockOpnameController::class, 'close'])->name('opname.close');
        Route::get('/stock-opname/{stockOpname}/details-datatable', [StockOpnameController::class, 'detailsDatatable'])->name('opname.details-datatable');
        Route::get('/stock-opname/{stockOpname}/print', [StockOpnameController::class, 'print'])->name('opname.print');
        Route::get('/stock-opname/{stockOpname}/export-excel', [StockOpnameController::class, 'exportExcel'])->name('opname.export-excel');
        Route::delete('/stock-opname/{stockOpname}/delete', [StockOpnameController::class, 'destroy'])->name('opname.destroy');

        // Saldo Awal Persediaan (Opening Stock)
        Route::get('/opening-stock', [OpeningStockController::class, 'index'])->name('opening-stock.index');
        Route::get('/opening-stock/search-barang', [OpeningStockController::class, 'searchBarang'])->name('opening-stock.search-barang');
        Route::get('/opening-stock/export', [OpeningStockController::class, 'exportExcel'])->name('opening-stock.export');
        Route::post('/opening-stock/store', [OpeningStockController::class, 'store'])->name('opening-stock.store');
        Route::post('/opening-stock/import', [OpeningStockController::class, 'importExcel'])->name('opening-stock.import');
        Route::get('/opening-stock/template', [OpeningStockController::class, 'downloadTemplate'])->name('opening-stock.template');
        Route::get('/opening-stock/{openingStock}', [OpeningStockController::class, 'show'])->name('opening-stock.show');
        Route::get('/opening-stock/{openingStock}/export', [OpeningStockController::class, 'exportDocExcel'])->name('opening-stock.export-doc');
        Route::get('/opening-stock/{openingStock}/edit', [OpeningStockController::class, 'edit'])->name('opening-stock.edit');
        Route::put('/opening-stock/{openingStock}', [OpeningStockController::class, 'update'])->name('opening-stock.update');
        Route::post('/opening-stock/{openingStock}/submit', [OpeningStockController::class, 'submit'])->name('opening-stock.submit');
        Route::post('/opening-stock/{openingStock}/confirm', [OpeningStockController::class, 'confirm'])->name('opening-stock.confirm');
        Route::delete('/opening-stock/{openingStock}/delete', [OpeningStockController::class, 'destroy'])->name('opening-stock.destroy');

        // Stock Adjustment (Penyesuaian Stok)
        Route::get('/stock-adjustment', [\App\Http\Controllers\StockAdjustmentController::class, 'index'])->name('stock-adjustment.index');
        Route::get('/stock-adjustment/search-barang', [\App\Http\Controllers\StockAdjustmentController::class, 'searchBarang'])->name('stock-adjustment.search-barang');
        Route::post('/stock-adjustment/store', [\App\Http\Controllers\StockAdjustmentController::class, 'store'])->name('stock-adjustment.store');
        Route::get('/stock-adjustment/{stockAdjustment}', [\App\Http\Controllers\StockAdjustmentController::class, 'show'])->name('stock-adjustment.show');
        Route::get('/stock-adjustment/{stockAdjustment}/edit', [\App\Http\Controllers\StockAdjustmentController::class, 'edit'])->name('stock-adjustment.edit');
        Route::put('/stock-adjustment/{stockAdjustment}', [\App\Http\Controllers\StockAdjustmentController::class, 'update'])->name('stock-adjustment.update');
        Route::post('/stock-adjustment/{stockAdjustment}/confirm', [\App\Http\Controllers\StockAdjustmentController::class, 'confirm'])->name('stock-adjustment.confirm');
        Route::get('/stock-adjustment/{stockAdjustment}/print', [\App\Http\Controllers\StockAdjustmentController::class, 'printPdf'])->name('stock-adjustment.print');
        Route::delete('/stock-adjustment/{stockAdjustment}', [\App\Http\Controllers\StockAdjustmentController::class, 'destroy'])->name('stock-adjustment.destroy');

        // Stock Card Report
        Route::get('/stock-card', [KartuStokController::class, 'index'])->name('stock-card.index');
        Route::get('/stock-card/print-pdf', [KartuStokController::class, 'printPdf'])->name('stock-card.print-pdf');
        Route::get('/stock-card/export-excel', [KartuStokController::class, 'exportExcel'])->name('stock-card.export-excel');
        Route::get('/stock-overview', [StokGudangController::class, 'index'])->name('stock-overview.index');
        Route::get('/stock-overview/print', [StokGudangController::class, 'print'])->name('stock-overview.print');
        Route::get('/stock-overview/last-updated', [StokGudangController::class, 'lastUpdated'])->name('stock-overview.last-updated');
        Route::get('/stock-overview/kartu-info', [StokGudangController::class, 'kartuInfo'])->name('stock-overview.kartu-info');
        Route::get('/stock-overview/mutasi-history', [StokGudangController::class, 'mutasiHistory'])->name('stock-overview.mutasi-history');
        Route::get('/stock-overview/search-barang', [StokGudangController::class, 'searchBarang'])->name('stock-overview.search-barang');

        // Pengaturan Akses (Roles & Permissions)
        Route::get('/roles', [RolePermissionController::class, 'index'])->name('roles.index');
        Route::get('/roles/{role}/permissions', [RolePermissionController::class, 'getPermissions'])->name('roles.permissions');
        Route::post('/roles/{role}/permissions/update', [RolePermissionController::class, 'update'])->name('roles.permissions.update');

        // Profile Settings
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
        Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
        Route::post('/profile/signature', [ProfileController::class, 'updateSignature'])->name('profile.signature');

        // Application Settings
        Route::get('/app-settings', [AppSettingsController::class, 'index'])->name('app-settings.index');
        Route::post('/app-settings/update', [AppSettingsController::class, 'update'])->name('app-settings.update');

        // WhatsApp Gateway
        Route::get('/settings/whatsapp', [\App\Http\Controllers\WhatsAppGatewayController::class, 'index'])->name('settings.whatsapp.index');
        Route::post('/settings/whatsapp/save', [\App\Http\Controllers\WhatsAppGatewayController::class, 'saveSettings'])->name('settings.whatsapp.save');
        Route::post('/settings/whatsapp/test', [\App\Http\Controllers\WhatsAppGatewayController::class, 'sendTestMessage'])->name('settings.whatsapp.test');
        Route::post('/settings/whatsapp/refresh-qr', [\App\Http\Controllers\WhatsAppGatewayController::class, 'refreshQr'])->name('settings.whatsapp.refresh-qr');
        Route::post('/settings/whatsapp/disconnect', [\App\Http\Controllers\WhatsAppGatewayController::class, 'disconnect'])->name('settings.whatsapp.disconnect');
        Route::get('/settings/whatsapp/status', [\App\Http\Controllers\WhatsAppGatewayController::class, 'checkStatus'])->name('settings.whatsapp.status');

        // Session status check for real-time logout
        Route::get('/check-session', function () {
            return response()->json(['status' => 'active']);
        })->name('session.check');

        // Active Users online/offline AJAX endpoint for dashboard
        Route::get('/dashboard/active-users', [UserActivityLogController::class, 'getActiveUsersAjax'])->name('dashboard.active-users');

        // System Health & Security AJAX endpoint for dashboard
        Route::get('/dashboard/system-health', [MainDashboardController::class, 'getSystemHealth'])->name('dashboard.system-health');

        // User Activity Logs AJAX endpoint for dashboard
        Route::get('/dashboard/activity-logs', [UserActivityLogController::class, 'getActivityLogsAjax'])->name('dashboard.activity-logs');

        // Chart of Accounts (COA) CRUD & Export/Import
        Route::get('/finance/chart-of-accounts', [ChartOfAccountController::class, 'index'])->name('chart-of-accounts.index');
        Route::get('/finance/chart-of-accounts/export', [ChartOfAccountController::class, 'exportExcel'])->name('chart-of-accounts.export');
        Route::get('/finance/chart-of-accounts/import/template', [ChartOfAccountController::class, 'downloadTemplate'])->name('chart-of-accounts.import.template');
        Route::post('/finance/chart-of-accounts/import', [ChartOfAccountController::class, 'importExcel'])->name('chart-of-accounts.import');
        Route::post('/finance/chart-of-accounts', [ChartOfAccountController::class, 'store'])->name('chart-of-accounts.store');
        Route::get('/finance/chart-of-accounts/{chartOfAccount}/edit', [ChartOfAccountController::class, 'edit'])->name('chart-of-accounts.edit');
        Route::put('/finance/chart-of-accounts/{chartOfAccount}', [ChartOfAccountController::class, 'update'])->name('chart-of-accounts.update');
        Route::delete('/finance/chart-of-accounts/{chartOfAccount}', [ChartOfAccountController::class, 'destroy'])->name('chart-of-accounts.destroy');

        // Central Account Mappings
        Route::get('/settings/account-mappings', [AccountMappingController::class, 'index'])->name('account-mappings.index');
        Route::post('/settings/account-mappings', [AccountMappingController::class, 'update'])->name('account-mappings.update');

        // Pengaturan Akuntansi (Unified Tabbed View)
        Route::get('/settings/akuntansi', [AkuntansiSettingsController::class, 'index'])->name('akuntansi.index');
        Route::post('/settings/akuntansi/category-mappings', [AkuntansiSettingsController::class, 'updateCategoryMappings'])->name('akuntansi.category-mappings.update');
        Route::post('/settings/akuntansi/system-mappings', [AkuntansiSettingsController::class, 'updateSystemMappings'])->name('akuntansi.system-mappings.update');

        // Master Jenis Biaya CRUD
        Route::post('/settings/master-jenis-biayas', [MasterJenisBiayaController::class, 'store'])->name('master-jenis-biayas.store');
        Route::get('/settings/master-jenis-biayas/{masterJenisBiaya}/edit', [MasterJenisBiayaController::class, 'edit'])->name('master-jenis-biayas.edit');
        Route::put('/settings/master-jenis-biayas/{masterJenisBiaya}', [MasterJenisBiayaController::class, 'update'])->name('master-jenis-biayas.update');
        Route::delete('/settings/master-jenis-biayas/{masterJenisBiaya}', [MasterJenisBiayaController::class, 'destroy'])->name('master-jenis-biayas.destroy');

        // Master Tipe Akun CRUD
        Route::post('/settings/master-tipe-akuns', [MasterTipeAkunController::class, 'store'])->name('master-tipe-akuns.store');
        Route::get('/settings/master-tipe-akuns/{masterTipeAkun}/edit', [MasterTipeAkunController::class, 'edit'])->name('master-tipe-akuns.edit');
        Route::put('/settings/master-tipe-akuns/{masterTipeAkun}', [MasterTipeAkunController::class, 'update'])->name('master-tipe-akuns.update');
        Route::delete('/settings/master-tipe-akuns/{masterTipeAkun}', [MasterTipeAkunController::class, 'destroy'])->name('master-tipe-akuns.destroy');

        // Master Tipe Pengeluaran CRUD
        Route::post('/settings/master-tipe-pengeluarans', [MasterTipePengeluaranController::class, 'store'])->name('master-tipe-pengeluarans.store');
        Route::get('/settings/master-tipe-pengeluarans/{masterTipePengeluaran}/edit', [MasterTipePengeluaranController::class, 'edit'])->name('master-tipe-pengeluarans.edit');
        Route::put('/settings/master-tipe-pengeluarans/{masterTipePengeluaran}', [MasterTipePengeluaranController::class, 'update'])->name('master-tipe-pengeluarans.update');
        Route::delete('/settings/master-tipe-pengeluarans/{masterTipePengeluaran}', [MasterTipePengeluaranController::class, 'destroy'])->name('master-tipe-pengeluarans.destroy');

        // Full Activity Logs View Route
        Route::get('/activity-logs', [UserActivityLogController::class, 'index'])->name('activity-logs.index');

        // ===== MODUL PRODUKSI: MASTER BOM =====
        Route::prefix('produksi/bom')->name('produksi.bom.')->group(function () {
            Route::get('/', [BomController::class, 'index'])->name('index');
            Route::get('/generate-kode', [BomController::class, 'generateKode'])->name('generate-kode');
            Route::post('/', [BomController::class, 'store'])->name('store');
            Route::get('/search-barang', [BomController::class, 'searchBarang'])->name('search-barang');
            Route::post('/{bom}/send-pending', [BomController::class, 'sendPending'])->name('send-pending');
            Route::post('/{bom}/approve', [BomController::class, 'approve'])->name('approve');
            Route::post('/{bom}/reject', [BomController::class, 'reject'])->name('reject');
            Route::post('/{bom}/complete', [BomController::class, 'complete'])->name('complete');
            Route::post('/{bom}/reactivate', [BomController::class, 'reactivate'])->name('reactivate');
            Route::get('/{bom}', [BomController::class, 'show'])->name('show');
            Route::get('/{bom}/json', [BomController::class, 'showJson'])->name('show-json');
            Route::get('/{bom}/edit', [BomController::class, 'edit'])->name('edit');
            Route::put('/{bom}', [BomController::class, 'update'])->name('update');
            Route::delete('/{bom}', [BomController::class, 'destroy'])->name('destroy');
            Route::get('/{bom}/print', [BomController::class, 'printPdf'])->name('print');
        });

        // ===== MODUL PRODUKSI: SPK (SURAT PERINTAH KERJA / WORK ORDER) =====
        Route::prefix('produksi/spk')->name('produksi.spk.')->group(function () {
            Route::get('/', [\App\Http\Controllers\SpkProduksiController::class, 'index'])->name('index');
            Route::get('/generate-kode', [\App\Http\Controllers\SpkProduksiController::class, 'generateKodeSpk'])->name('generate-kode');
            Route::get('/search-bom', [\App\Http\Controllers\SpkProduksiController::class, 'searchApprovedBom'])->name('search-bom');
            Route::get('/bom/{bomId}/materials', [\App\Http\Controllers\SpkProduksiController::class, 'getBomDetailsWithStock'])->name('bom-materials');
            Route::post('/', [\App\Http\Controllers\SpkProduksiController::class, 'store'])->name('store');
            Route::post('/{id}/send-pending', [\App\Http\Controllers\SpkProduksiController::class, 'sendPending'])->name('send-pending');
            Route::post('/{id}/approve', [\App\Http\Controllers\SpkProduksiController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [\App\Http\Controllers\SpkProduksiController::class, 'reject'])->name('reject');
            Route::post('/{id}/start-production', [\App\Http\Controllers\SpkProduksiController::class, 'startProduction'])->name('start-production');
            Route::post('/{id}/cancel-production', [\App\Http\Controllers\SpkProduksiController::class, 'cancelProduction'])->name('cancel-production');
            Route::get('/{id}/progress', [\App\Http\Controllers\SpkProduksiController::class, 'getTahapanProgress'])->name('get-progress');
            Route::post('/{id}/progress-tahapan/{tahapanId}', [\App\Http\Controllers\SpkProduksiController::class, 'updateTahapanProgress'])->name('update-tahapan-progress');
            Route::post('/{id}/complete-production', [\App\Http\Controllers\SpkProduksiController::class, 'completeProduction'])->name('complete-production');
            Route::get('/{id}', [\App\Http\Controllers\SpkProduksiController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\SpkProduksiController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\SpkProduksiController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/print', [\App\Http\Controllers\SpkProduksiController::class, 'printPdf'])->name('print');
            Route::get('/{id}/print-progress', [\App\Http\Controllers\SpkProduksiController::class, 'printProgressPdf'])->name('print-progress');
        });

        // ===== MODUL PRODUKSI: MONITORING & REALISASI PRODUKSI PER DIVISI =====
        Route::prefix('produksi/realisasi-produksi')->name('produksi.realisasi.')->group(function () {
            Route::get('/', [\App\Http\Controllers\RealisasiProduksiController::class, 'index'])->name('index');
            Route::get('/spk/{id}', [\App\Http\Controllers\RealisasiProduksiController::class, 'detailSpk'])->name('spk-detail');
            Route::get('/{tahapanId}/logs', [\App\Http\Controllers\RealisasiProduksiController::class, 'getLogsHistory'])->name('logs');
            Route::post('/{tahapanId}/start', [\App\Http\Controllers\RealisasiProduksiController::class, 'startTahapan'])->name('start-tahapan');
            Route::post('/{tahapanId}/store-log', [\App\Http\Controllers\RealisasiProduksiController::class, 'storeDailyLog'])->name('store-log');
            Route::post('/{tahapanId}/update', [\App\Http\Controllers\RealisasiProduksiController::class, 'updateProgress'])->name('update-progress');
            Route::delete('/log/{logId}', [\App\Http\Controllers\RealisasiProduksiController::class, 'deleteLog'])->name('delete-log');
            Route::get('/log/{logId}', [\App\Http\Controllers\RealisasiProduksiController::class, 'getLogDetail'])->name('get-log');
            Route::post('/log/{logId}/update', [\App\Http\Controllers\RealisasiProduksiController::class, 'updateLog'])->name('update-log');
        });

        // ===== MODUL PRODUKSI: PENGELUARAN BAHAN BAKU SPK =====
        Route::prefix('produksi/pengeluaran-bahan')->name('produksi.pengeluaran-bahan.')->group(function () {
            Route::get('/', [\App\Http\Controllers\PengeluaranBahanController::class, 'index'])->name('index');
            Route::get('/spk/{spkId}/materials', [\App\Http\Controllers\PengeluaranBahanController::class, 'getSpkMaterials'])->name('spk-materials');
            Route::get('/spk-materials/{spkId}', [\App\Http\Controllers\PengeluaranBahanController::class, 'getSpkMaterials'])->name('spk-materials-alt');
            Route::get('/spk/{spkId}/history', [\App\Http\Controllers\PengeluaranBahanController::class, 'showSpkHistory'])->name('spk-history');
            Route::get('/spk/{spkId}/history/sessions', [\App\Http\Controllers\PengeluaranBahanController::class, 'getSpkHistorySessions'])->name('spk-history-sessions');
            Route::post('/', [\App\Http\Controllers\PengeluaranBahanController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\PengeluaranBahanController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\PengeluaranBahanController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\PengeluaranBahanController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/print', [\App\Http\Controllers\PengeluaranBahanController::class, 'printPdf'])->name('print');
        });

        // ===== MODUL PRODUKSI: PENERIMAAN BARANG JADI SPK (HASIL PRODUKSI) =====
        Route::prefix('produksi/barang-jadi')->name('produksi.barang-jadi.')->group(function () {
            Route::get('/', [\App\Http\Controllers\PenerimaanBarangJadiController::class, 'index'])->name('index');
            Route::get('/spk/{spkId}/detail', [\App\Http\Controllers\PenerimaanBarangJadiController::class, 'getSpkDetail'])->name('spk-detail');
            Route::get('/spk/{spkId}/history', [\App\Http\Controllers\PenerimaanBarangJadiController::class, 'showSpkHistory'])->name('spk-history');
            Route::get('/spk/{spkId}/history/sessions', [\App\Http\Controllers\PenerimaanBarangJadiController::class, 'getSpkHistorySessions'])->name('spk-history-sessions');
            Route::get('/spk/{spkId}/sessions', [\App\Http\Controllers\PenerimaanBarangJadiController::class, 'getSpkHistorySessions'])->name('spk-sessions');
            Route::post('/', [\App\Http\Controllers\PenerimaanBarangJadiController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\PenerimaanBarangJadiController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\PenerimaanBarangJadiController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\PenerimaanBarangJadiController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/print', [\App\Http\Controllers\PenerimaanBarangJadiController::class, 'printPdf'])->name('print');
            Route::post('/spk/{id}/adjust-hpp', [\App\Http\Controllers\PenerimaanBarangJadiController::class, 'adjustHpp'])->name('spk-adjust-hpp');
            Route::post('/spk/{id}/unlock-hpp', [\App\Http\Controllers\PenerimaanBarangJadiController::class, 'unlockHpp'])->name('spk-unlock-hpp');
        });

        // ===== MODUL PRODUKSI: MASTER TAHAPAN (CUSTOM ROUTING PER TIPE SPK & BOM) =====
        Route::prefix('produksi/master-tahapan')->name('produksi.master-tahapan.')->group(function () {
            Route::get('/', [\App\Http\Controllers\MasterTahapanProduksiController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\MasterTahapanProduksiController::class, 'store'])->name('store');
            Route::get('/api/all-active', [\App\Http\Controllers\MasterTahapanProduksiController::class, 'getAllActive'])->name('all-active');
            Route::get('/bom/{bomKey}', [\App\Http\Controllers\MasterTahapanProduksiController::class, 'getTahapanByBom'])->name('by-bom');
            Route::post('/bom/{bomKey}/batch-update', [\App\Http\Controllers\MasterTahapanProduksiController::class, 'updateBatchByBom'])->name('batch-update-bom');
            Route::delete('/bom/{bomKey}', [\App\Http\Controllers\MasterTahapanProduksiController::class, 'destroyByBom'])->name('destroy-bom');
            Route::get('/{id}', [\App\Http\Controllers\MasterTahapanProduksiController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\MasterTahapanProduksiController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\MasterTahapanProduksiController::class, 'destroy'])->name('destroy');
            Route::get('/api/by-tipe/{tipeSpk}', [\App\Http\Controllers\MasterTahapanProduksiController::class, 'getByTipeSpk'])->name('by-tipe');
        });

        // ===== MODUL AKUNTANSI & KEUANGAN: KAS & BANK =====
        Route::prefix('accounting/kas-bank')->name('accounting.kas-bank.')->group(function () {
            Route::get('/', [\App\Http\Controllers\KasBankController::class, 'index'])->name('index');
            Route::get('/data', [\App\Http\Controllers\KasBankController::class, 'getData'])->name('data');
            Route::get('/ledger', [\App\Http\Controllers\KasBankController::class, 'ledger'])->name('ledger');
            Route::get('/ledger/print', [\App\Http\Controllers\KasBankController::class, 'printLedger'])->name('ledger.print');
            Route::get('/ledger/{account}', [\App\Http\Controllers\KasBankController::class, 'ledger'])->name('ledger.account');
            Route::get('/ledger/{account}/print', [\App\Http\Controllers\KasBankController::class, 'printLedger'])->name('ledger.account.print');
            Route::post('/account', [\App\Http\Controllers\KasBankController::class, 'storeAccount'])->name('account.store');
            Route::get('/account/{account}/edit', [\App\Http\Controllers\KasBankController::class, 'editAccount'])->name('account.edit');
            Route::post('/account/{account}/update', [\App\Http\Controllers\KasBankController::class, 'updateAccount'])->name('account.update');
            Route::post('/cash-in', [\App\Http\Controllers\KasBankController::class, 'storeCashIn'])->name('cash-in.store');
            Route::post('/cash-out', [\App\Http\Controllers\KasBankController::class, 'storeCashOut'])->name('cash-out.store');
            Route::post('/transfer', [\App\Http\Controllers\KasBankController::class, 'storeTransfer'])->name('transfer.store');
            Route::get('/{mutasi}', [\App\Http\Controllers\KasBankController::class, 'show'])->name('show');
            Route::post('/{mutasi}/cancel', [\App\Http\Controllers\KasBankController::class, 'cancel'])->name('cancel');
        });

        // ===== MODUL AKUNTANSI & KEUANGAN: JURNAL UMUM (GENERAL JOURNAL) =====
        Route::prefix('accounting/jurnal-umum')->name('accounting.jurnal-umum.')->group(function () {
            Route::get('/', [\App\Http\Controllers\JurnalUmumController::class, 'index'])->name('index');
            Route::get('/data', [\App\Http\Controllers\JurnalUmumController::class, 'getData'])->name('data');
            Route::get('/print-all', [\App\Http\Controllers\JurnalUmumController::class, 'printAll'])->name('print-all');
            Route::post('/store', [\App\Http\Controllers\JurnalUmumController::class, 'storeManual'])->name('store');
            Route::get('/{jurnal}', [\App\Http\Controllers\JurnalUmumController::class, 'show'])->name('show');
            Route::get('/{jurnal}/print', [\App\Http\Controllers\JurnalUmumController::class, 'print'])->name('print');
        });

        // ===== MODUL AKUNTANSI & KEUANGAN: PETTY CASH (KAS KECIL) =====
        Route::prefix('accounting/petty-cash')->name('accounting.petty-cash.')->group(function () {
            Route::get('/', [\App\Http\Controllers\PettyCashController::class, 'index'])->name('index');
            Route::get('/data', [\App\Http\Controllers\PettyCashController::class, 'data'])->name('data');
            Route::post('/', [\App\Http\Controllers\PettyCashController::class, 'store'])->name('store');
            Route::get('/{pettyCash}', [\App\Http\Controllers\PettyCashController::class, 'show'])->name('show');
            Route::get('/{pettyCash}/edit', [\App\Http\Controllers\PettyCashController::class, 'edit'])->name('edit');
            Route::put('/{pettyCash}', [\App\Http\Controllers\PettyCashController::class, 'update'])->name('update');
            Route::delete('/{pettyCash}', [\App\Http\Controllers\PettyCashController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('accounting/paty-cas')->name('accounting.paty-cas.')->group(function () {
            Route::get('/', [\App\Http\Controllers\PettyCashController::class, 'index'])->name('index');
            Route::get('/data', [\App\Http\Controllers\PettyCashController::class, 'data'])->name('data');
            Route::post('/', [\App\Http\Controllers\PettyCashController::class, 'store'])->name('store');
            Route::get('/{pettyCash}', [\App\Http\Controllers\PettyCashController::class, 'show'])->name('show');
            Route::get('/{pettyCash}/edit', [\App\Http\Controllers\PettyCashController::class, 'edit'])->name('edit');
            Route::put('/{pettyCash}', [\App\Http\Controllers\PettyCashController::class, 'update'])->name('update');
            Route::delete('/{pettyCash}', [\App\Http\Controllers\PettyCashController::class, 'destroy'])->name('destroy');
        });
    });
});

