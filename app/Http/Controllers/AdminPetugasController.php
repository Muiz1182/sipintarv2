<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminPetugasController extends Controller
{
    /**
     * Menampilkan Dashboard Admin (Petugas & Buku)
     */
    public function index()
    {
        // 1. Mengambil user dengan role petugas
        // Menggunakan get() untuk memastikan hasil berupa Collection (tidak null)
        $petugas = User::where('role', 'petugas')->get();

        // 2. Mengambil daftar buku dengan paginasi
        // latest() akan mengurutkan buku dari yang terbaru diunggah
        $books = Book::latest()->paginate(10); 

        // 3. Mengirim data ke view dashboard
        // Pastikan file view berada di resources/views/dashboard.blade.php
        return view('dashboard', compact('petugas', 'books'));
    }

    /**
     * Fungsi untuk Registrasi Petugas Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'petugas', 
            ]);

            return redirect()->back()->with('success', "Akun petugas <b>{$request->name}</b> berhasil didaftarkan!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mendaftarkan petugas.');
        }
    }

    /**
     * Fungsi Ganti Password Petugas
     */
    public function updatePassword(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', "Password petugas <b>{$user->name}</b> telah diperbarui!");
    }

    /**
     * Fungsi Hapus Petugas
     */
    public function destroy(User $user)
    {
        // Proteksi agar admin tidak menghapus dirinya sendiri
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }

        try {
            $user->delete();
            return redirect()->back()->with('success', "Akun petugas <b>{$user->name}</b> berhasil dihapus.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus akun petugas.');
        }
    }
}