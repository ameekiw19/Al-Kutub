<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database
     * Pastikan tabel kamu bernama 'history' (bukan 'histories')
     */
    protected $table = 'history';

    protected $primaryKey = 'id';

    /**
     * Kolom yang dapat diisi secara mass assignment
     */
    protected $fillable = [
        'user_id',
        'kitab_id',
        'last_read_at',
        'current_page',
        'total_pages',
        'last_position',
        'reading_time_minutes',
    ];

    /**
     * Casting tipe data kolom
     */
    protected $casts = [
        'last_read_at' => 'datetime',
        // created_at & updated_at otomatis dicasting Laravel, tapi ditulis pun tidak masalah
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke tabel users
     */
    public function user()
    {
        // Asumsi tabel users primary key-nya standar 'id'
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Relasi ke tabel kitab
     * PERBAIKAN: Menambahkan parameter ke-3 ('id_kitab')
     */
    public function kitab()
    {
        // Format: belongsTo(Model, ForeignKey_di_sini, PrimaryKey_di_tabel_tujuan)
        // Kita WAJIB definisikan 'id_kitab' karena default laravel mencari 'id'
        return $this->belongsTo(Kitab::class, 'kitab_id', 'id_kitab');
    }

    /**
     * Scope untuk filter berdasarkan user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('last_read_at', today());
    }

    /**
     * Scope untuk minggu ini
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('last_read_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope untuk bulan ini
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('last_read_at', now()->month)
                    ->whereYear('last_read_at', now()->year);
    }
}