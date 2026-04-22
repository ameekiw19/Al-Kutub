<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoryKatalog extends Model
{
    use HasFactory;

    protected $table = 'category_katalog';

    protected $fillable = ['name', 'slug', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function kitabs()
    {
        return $this->hasMany(Kitab::class, 'kategori', 'slug');
    }

    public static function getActiveForSelect(): array
    {
        try {
            $items = static::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name', 'slug')
                ->toArray();
            if (!empty($items)) {
                return $items;
            }
        } catch (\Exception $e) {
            // Table mungkin belum ada
        }
        // Fallback jika tabel kosong atau belum ada
        return [
            'aqidah' => 'Aqidah', 'tauhid' => 'Tauhid', 'fiqih' => 'Fiqih',
            'hadis' => 'Hadis', 'tafsir' => 'Tafsir', 'tasawuf' => 'Tasawuf',
            'sirah' => 'Sirah Nabawiyah', 'bahasa-arab' => 'Bahasa Arab',
        ];
    }

    public static function getActiveSlugs(): array
    {
        try {
            $items = static::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('slug')
                ->toArray();
            if (!empty($items)) {
                return $items;
            }
        } catch (\Exception $e) {
            // Table mungkin belum ada
        }
        return ['aqidah', 'tauhid', 'fiqih', 'hadis', 'tafsir', 'tasawuf', 'sirah', 'bahasa-arab'];
    }
}
