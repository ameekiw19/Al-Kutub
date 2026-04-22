<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    use HasFactory;

    protected $table = 'bookmarks';
    protected $primaryKey = 'id_bookmark';

    protected $fillable = [
        'user_id',
        'id_kitab',
        'page_number',
        'page_title',
        'bookmark_type',
        'notes'
    ];

    // Cast timestamps
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relasi ke Kitab
    public function kitab()
    {
        return $this->belongsTo(Kitab::class, 'id_kitab', 'id_kitab');
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}