<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',        // Untuk sub-kategori
        'icon',             // Icon untuk kategori
        'color',            // Warna tema
        'is_active',        // Status aktif
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relasi ke parent category
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Relasi ke child categories
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Relasi ke kitab
    public function kitabs()
    {
        return $this->belongsToMany(Kitab::class, 'kitab_category', 'category_id', 'id_kitab');
    }
}
