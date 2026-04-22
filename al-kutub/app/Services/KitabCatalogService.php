<?php

namespace App\Services;

use App\Models\Kitab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class KitabCatalogService
{
    public function buildQuery(array $filters = []): Builder
    {
        $query = Kitab::published();

        $kategori = $this->normalizeCategory($filters['kategori'] ?? $filters['category'] ?? null);
        if ($kategori !== null && $kategori !== '') {
            $query->where('kategori', $kategori);
        }

        $bahasa = $this->normalizeLanguage($filters['bahasa'] ?? $filters['language'] ?? null);
        if ($bahasa !== null && $bahasa !== '') {
            $this->applyLanguageFilter($query, $bahasa);
        }

        $search = trim((string) ($filters['search'] ?? $filters['query'] ?? ''));
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('judul', 'like', "%{$search}%")
                    ->orWhere('penulis', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%")
                    ->orWhere('bahasa', 'like', "%{$search}%");
            });
        }

        $sortBy = $this->normalizeSortBy($filters['sort_by'] ?? null);
        $sortOrder = $this->normalizeSortOrder($filters['sort_order'] ?? null);
        $this->applySorting($query, $sortBy, $sortOrder);

        return $query;
    }

    public function getAvailableCategories(): array
    {
        return Kitab::published()
            ->select('kategori')
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori')
            ->values()
            ->all();
    }

    public function getAvailableLanguageLabels(): array
    {
        $rawLanguages = Kitab::published()
            ->select('bahasa')
            ->whereNotNull('bahasa')
            ->where('bahasa', '!=', '')
            ->distinct()
            ->pluck('bahasa')
            ->all();

        $labels = collect($rawLanguages)
            ->map(function (string $value): ?string {
                $key = $this->normalizeLanguage($value);
                if ($key === null || $key === '') {
                    return null;
                }

                return $key === 'arab' ? 'Arab' : ($key === 'indonesia' ? 'Indonesia' : ucfirst($key));
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($labels)) {
            return ['Indonesia', 'Arab'];
        }

        return $labels;
    }

    public function normalizeLanguage(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtolower(trim($value));
        if ($normalized === '' || $normalized === 'semua' || $normalized === 'all') {
            return null;
        }

        if (str_contains($normalized, 'arab') || str_contains($normalized, 'عرب')) {
            return 'arab';
        }

        if (str_contains($normalized, 'indo') || str_contains($normalized, 'indonesia')) {
            return 'indonesia';
        }

        return $normalized;
    }

    public function normalizeCategory(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim($value);
        if ($normalized === '' || strtolower($normalized) === 'semua' || strtolower($normalized) === 'all') {
            return null;
        }

        return $normalized;
    }

    private function normalizeSortBy(?string $sortBy): string
    {
        $key = strtolower(trim((string) $sortBy));

        return match ($key) {
            'views', 'rating', 'pages', 'title', 'latest' => $key,
            default => 'latest',
        };
    }

    private function normalizeSortOrder(?string $sortOrder): string
    {
        return strtolower(trim((string) $sortOrder)) === 'asc' ? 'asc' : 'desc';
    }

    private function applyLanguageFilter(Builder $query, string $languageKey): void
    {
        if ($languageKey === 'arab') {
            $query->where(function (Builder $builder): void {
                $builder->whereRaw('LOWER(bahasa) LIKE ?', ['%arab%'])
                    ->orWhereRaw('bahasa LIKE ?', ['%عرب%']);
            });
            return;
        }

        if ($languageKey === 'indonesia') {
            $query->where(function (Builder $builder): void {
                $builder->whereRaw('LOWER(bahasa) LIKE ?', ['%indonesia%'])
                    ->orWhereRaw('LOWER(bahasa) LIKE ?', ['%indo%']);
            });
            return;
        }

        $query->whereRaw('LOWER(bahasa) = ?', [$languageKey]);
    }

    private function applySorting(Builder $query, string $sortBy, string $sortOrder): void
    {
        switch ($sortBy) {
            case 'views':
                $query->orderBy('views', $sortOrder);
                break;
            case 'rating':
                $query->select('kitab.*')
                    ->selectSub(function ($sub): void {
                        $sub->from('ratings')
                            ->selectRaw('COALESCE(AVG(rating), 0)')
                            ->whereColumn('ratings.id_kitab', 'kitab.id_kitab');
                    }, 'avg_rating')
                    ->orderBy('avg_rating', $sortOrder)
                    ->orderBy('kitab.views', 'desc');
                break;
            case 'pages':
                $pageColumn = $this->resolvePageColumn();
                if ($pageColumn !== null) {
                    $query->orderBy($pageColumn, $sortOrder);
                } else {
                    $direction = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
                    $query->orderByRaw("CHAR_LENGTH(COALESCE(deskripsi, '')) {$direction}");
                }
                break;
            case 'title':
                $query->orderBy('judul', $sortOrder);
                break;
            case 'latest':
            default:
                $query->orderBy('created_at', $sortOrder);
                break;
        }
    }

    private function resolvePageColumn(): ?string
    {
        foreach (['jumlah_halaman', 'halaman', 'pages', 'page_count'] as $candidate) {
            if (Schema::hasColumn('kitab', $candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
