<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RegisteredUserController extends Controller
{
    /**
     * Menampilkan halaman registrasi petugas.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Menangani pendaftaran petugas baru.
     */
    public function store(Request $request): RedirectResponse
    {
        // Proteksi Manual jika middleware di route belum dipasang
        if (Auth::user()->email !== 'adminsipintar@gmail.com') {
            abort(403, 'Tindakan ini hanya diizinkan untuk Super Admin.');
        }

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.unique' => 'Email ini sudah terdaftar dalam sistem.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        return DB::transaction(function () use ($request) {
            try {
                $user = User::create([
                    'name'     => trim($request->name),
                    'email'    => strtolower($request->email),
                    'password' => Hash::make($request->password),
                ]);

                event(new Registered($user));

                return redirect()->back()->with('success', "Petugas &nbsp;<b>{$user->name}</b>&nbsp; berhasil didaftarkan.");
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Gagal mendaftarkan petugas: ' . $e->getMessage());
            }
        });
    }

    /**
     * Mengubah password petugas.
     */
    public function updatePetugasPassword(Request $request, User $user): RedirectResponse
    {
        if (Auth::user()->email !== 'adminsipintar@gmail.com') {
            abort(403);
        }

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::min(8)->letters()->numbers()],
        ], [
            'password.min' => 'Password minimal 8 karakter dengan kombinasi huruf dan angka.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.'
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', "Password petugas <b>{$user->name}</b> berhasil diperbarui.");
    }

    /**
     * Menghapus akun petugas.
     */
    public function destroyPetugas(User $user): RedirectResponse
    {
        // Proteksi: Hanya super admin yang bisa menghapus
        if (Auth::user()->email !== 'adminsipintar@gmail.com') {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        // Cegah menghapus diri sendiri
        if (Auth::id() === $user->id) {
            return redirect()->back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        try {
            $namaPetugas = $user->name;
            $user->delete();

            return redirect()->back()->with('success', "Akun petugas <b>{$namaPetugas}</b> telah dihapus.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus akun petugas.');
        }
    }
}