<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AppSettingsController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-hak-akses');

        $settings = AppSetting::pluck('value', 'key')->all();
        $appNameSetting = $settings['app_name'] ?? 'Lumen ERP';
        $appLogoSetting = isset($settings['app_logo']) && $settings['app_logo'] 
            ? asset('storage/' . $settings['app_logo']) 
            : asset('gambar/logo.png');

        return view('settings.app_settings', compact('appNameSetting', 'appLogoSetting'));
    }

    public function update(Request $request)
    {
        $this->authorize('edit-hak-akses');

        $request->validate([
            'app_name' => 'required|string|max:100',
            'app_logo' => 'nullable|image|mimes:png,jpg,jpeg,webp,gif|max:5120', // allow up to 5MB original file upload, then compress to 800kb
        ], [
            'app_name.required' => 'Nama aplikasi wajib diisi!',
            'app_name.max' => 'Nama aplikasi maksimal 100 karakter!',
            'app_logo.image' => 'File logo harus berupa gambar!',
            'app_logo.mimes' => 'Format gambar harus png, jpg, jpeg, webp, atau gif!',
            'app_logo.max' => 'Ukuran gambar maksimal 5MB sebelum dikompres!',
        ]);

        try {
            DB::beginTransaction();

            // Update App Name
            AppSetting::updateOrCreate(['key' => 'app_name'], ['value' => $request->app_name]);

            // Handle App Logo Upload and Compression
            if ($request->hasFile('app_logo')) {
                $file = $request->file('app_logo');
                
                // Get old logo path to clean up later
                $oldLogoPath = AppSetting::where('key', 'app_logo')->value('value');

                $fileName = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
                $tempPath = $file->getRealPath();
                
                // Create storage directory if it doesn't exist
                $storageDir = storage_path('app/public/gambar');
                if (!file_exists($storageDir)) {
                    mkdir($storageDir, 0755, true);
                }

                $destinationPath = $storageDir . '/' . $fileName;

                // Compress image to around 800 KB
                $compressed = $this->compressImageToLimit($tempPath, $destinationPath, 800);

                if ($compressed) {
                    $relativeLogoPath = 'gambar/' . $fileName;
                    AppSetting::updateOrCreate(['key' => 'app_logo'], ['value' => $relativeLogoPath]);

                    // Clean up old logo file if it was uploaded
                    if ($oldLogoPath && Storage::disk('public')->exists($oldLogoPath)) {
                        Storage::disk('public')->delete($oldLogoPath);
                    }
                } else {
                    // Fallback to standard save if compression failed
                    $file->storeAs('public/gambar', $fileName);
                    $relativeLogoPath = 'gambar/' . $fileName;
                    AppSetting::updateOrCreate(['key' => 'app_logo'], ['value' => $relativeLogoPath]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengaturan aplikasi berhasil diperbarui!',
                'logo_url' => isset($relativeLogoPath) ? asset('storage/' . $relativeLogoPath) : null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui pengaturan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compress uploaded image file to around target KB size limit.
     */
    private function compressImageToLimit($sourcePath, $destinationPath, $targetSizeKb = 800)
    {
        list($width, $height, $type) = getimagesize($sourcePath);
        
        switch ($type) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($sourcePath);
                // Keep transparency settings
                imagealphablending($image, false);
                imagesavealpha($image, true);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($sourcePath);
                break;
            case IMAGETYPE_WEBP:
                $image = imagecreatefromwebp($sourcePath);
                break;
            default:
                return false;
        }

        $quality = 85;
        $tempFile = tempnam(sys_get_temp_dir(), 'compress_');

        do {
            ob_start();
            if ($type == IMAGETYPE_PNG) {
                // PNG compression quality is 0 (no compression) to 9. Map 100-0 quality scale to 0-9 png Quality
                $pngQuality = round((100 - $quality) / 10);
                imagepng($image, $tempFile, $pngQuality);
            } elseif ($type == IMAGETYPE_WEBP) {
                imagewebp($image, $tempFile, $quality);
            } else {
                imagejpeg($image, $tempFile, $quality);
            }
            ob_get_clean();

            $sizeKb = filesize($tempFile) / 1024;
            $quality -= 5;
        } while ($sizeKb > $targetSizeKb && $quality > 15);

        // Copy from temp file to final storage destination path
        copy($tempFile, $destinationPath);
        @unlink($tempFile);
        imagedestroy($image);
        return true;
    }
}
