<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'last_seen', 
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen' => 'datetime', // Memastikan manipulasi waktu menggunakan Carbon
        ];
    }

    /**
     * Relasi Chat: User sebagai pengirim pesan.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Relasi Chat: User sebagai penerima pesan.
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Logika Status Online yang lebih akurat.
     * Konsisten dengan middleware logout yang sebelumnya kita buat (subMinutes 6).
     */
    public function isOnline(): bool
    {
        if (!$this->last_seen) {
            return false;
        }

        // Kita gunakan 5 menit sebagai standar toleransi aktivitas
        return $this->last_seen->gt(now()->subMinutes(5));
    }

    /**
     * Helper untuk tampilan Status Online (Bagus untuk UI Chat)
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->isOnline() ? 'Online' : 'Offline';
    }

    /**
     * Helper untuk mendapatkan waktu terakhir terlihat yang cantik
     */
    public function getLastSeenFormattedAttribute(): string
    {
        if (!$this->last_seen) return 'Tidak pernah aktif';
        
        return $this->last_seen->diffForHumans();
    }
}