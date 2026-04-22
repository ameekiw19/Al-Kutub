<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'comments';
    protected $primaryKey = 'id_comment';
    public $timestamps = true; // pastikan aktif kalau tabel punya created_at & updated_at

    protected $fillable = [
        'id_kitab',
        'user_id',
        'isi_comment',
    ];

    /**
     * Relasi ke model Kitab
     * Satu komentar milik satu kitab
     */
    public function kitab()
    {
        return $this->belongsTo(Kitab::class, 'id_kitab', 'id_kitab');
    }

    /**
     * Relasi ke model User
     * Satu komentar dibuat oleh satu user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
