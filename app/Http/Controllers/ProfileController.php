<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil user.
     */
    public function index()
    {
        $user = Auth::user();
        // Load branches, department, position, and activeSignature relations
        $user->load(['branches', 'department', 'position', 'activeSignature']);
        
        return view('settings.profile', compact('user'));
    }

    /**
     * Update password user.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ], [
            'current_password.required' => 'Password lama wajib diisi!',
            'new_password.required' => 'Password baru wajib diisi!',
            'new_password.min' => 'Password baru minimal harus 6 karakter!',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok!',
        ]);

        $user = Auth::user();

        // Cek kecocokan password lama
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama yang Anda masukkan salah.'
            ], 422);
        }

        // Simpan password baru
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diperbarui!'
        ]);
    }

    /**
     * Update foto profil user.
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ], [
            'foto.required'  => 'Pilih file gambar terlebih dahulu!',
            'foto.image'     => 'File harus berupa gambar!',
            'foto.mimes'     => 'Format gambar harus jpg, jpeg, png, gif, atau webp!',
            'foto.max'       => 'Ukuran gambar maksimal 2MB!',
        ]);

        $user = Auth::user();

        // Hapus foto lama jika ada dan bukan gambar default
        if ($user->foto && file_exists(public_path($user->foto))) {
            @unlink(public_path($user->foto));
        }

        // Simpan foto baru
        $file = $request->file('foto');
        $fileName = 'user_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $destPath = public_path('uploads/karyawan');

        if (!file_exists($destPath)) {
            mkdir($destPath, 0755, true);
        }

        $file->move($destPath, $fileName);
        $fotoPath = 'uploads/karyawan/' . $fileName;

        // Update database
        $user->foto = $fotoPath;
        $user->save();

        return response()->json([
            'success'  => true,
            'message'  => 'Foto profil berhasil diperbarui!',
            'foto_url' => asset($fotoPath),
        ]);
    }

    /**
     * Update tanda tangan digital user.
     */
    public function updateSignature(Request $request)
    {
        $request->validate([
            'signature' => 'required|string',
        ], [
            'signature.required' => 'Goresan tanda tangan wajib diisi!',
        ]);

        $user = Auth::user();

        // Decode base64
        $base64String = $request->signature;
        if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
            $base64String = substr($base64String, strpos($base64String, ',') + 1);
            $type = strtolower($type[1]);

            if (!in_array($type, ['png', 'jpg', 'jpeg'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format file tidak didukung!'
                ], 422);
            }

            $data = base64_decode($base64String);
            if ($data === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membaca gambar tanda tangan!'
                ], 422);
            }

            $folder = public_path('uploads/signatures');
            if (!file_exists($folder)) {
                mkdir($folder, 0755, true);
            }

            $fileName = 'sig_' . $user->id . '_' . time() . '_' . uniqid() . '.' . $type;
            $filePath = $folder . '/' . $fileName;
            file_put_contents($filePath, $data);

            $sigPath = 'uploads/signatures/' . $fileName;

            // Set all previous signatures as inactive
            \App\Models\UserSignature::where('user_id', $user->id)->update(['is_active' => false]);

            // Create new active signature
            \App\Models\UserSignature::create([
                'user_id' => $user->id,
                'signature_path' => $sigPath,
                'is_active' => true,
                'created_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tanda tangan digital berhasil diperbarui!',
                'signature_url' => asset($sigPath)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Data tanda tangan tidak valid!'
        ], 422);
    }
}
