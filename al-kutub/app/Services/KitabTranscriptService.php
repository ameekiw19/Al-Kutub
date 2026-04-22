<?php

namespace App\Services;

use App\Models\Kitab;
use App\Models\KitabTranscriptSegment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class KitabTranscriptService
{
    private ?bool $transcriptTableExists = null;

    public function buildPayload(Kitab $kitab): array
    {
        $segments = $this->loadSegments($kitab)
            ->map(fn (KitabTranscriptSegment $segment) => $this->mapSegment($segment, $kitab))
            ->values();

        $pageMap = $this->buildPageMap($segments);
        $pageSegmentMap = $this->buildPageSegmentMap($segments);
        $chapterMap = $this->buildChapterMap($segments);

        return [
            'kitabId' => (int) $kitab->id_kitab,
            'title' => (string) $kitab->judul,
            'language' => (string) ($kitab->bahasa ?? ''),
            'summaryText' => $this->resolveSummaryText($kitab, $segments),
            'hasTranscript' => $segments->isNotEmpty(),
            'hasSummaryTranscript' => $this->hasSummaryTranscript($segments),
            'hasPageTranscript' => !empty($pageMap),
            'pageMap' => $pageMap,
            'pageSegmentMap' => $pageSegmentMap,
            'chapterMap' => $chapterMap,
            'segments' => $segments->all(),
        ];
    }

    private function loadSegments(Kitab $kitab): Collection
    {
        if (!$this->hasTranscriptTable()) {
            return collect();
        }

        return $kitab->transcriptSegments()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    private function mapSegment(KitabTranscriptSegment $segment, Kitab $kitab): array
    {
        $translationText = $this->normalizeText((string) ($segment->content_translation ?: $segment->content));
        $arabicText = $this->normalizeText((string) ($segment->content_arabic ?? ''));
        $primaryText = $translationText !== '' ? $translationText : $this->normalizeText((string) $segment->content);

        return [
            'id' => (int) $segment->id,
            'key' => $segment->section_key,
            'type' => (string) $segment->transcript_type,
            'title' => $segment->title,
            'content' => $primaryText,
            'textTranslation' => $translationText,
            'textArabic' => $arabicText,
            'translationParagraphs' => $this->splitParagraphs($translationText),
            'arabicParagraphs' => $this->splitParagraphs($arabicText),
            'language' => $segment->language ?: $kitab->bahasa,
            'pageStart' => $segment->page_start !== null ? (int) $segment->page_start : null,
            'pageEnd' => $segment->page_end !== null ? (int) $segment->page_end : null,
            'sortOrder' => (int) ($segment->sort_order ?? 0),
        ];
    }

    private function buildPageMap(Collection $segments): array
    {
        return $segments
            ->filter(function (array $segment) {
                return $segment['pageStart'] !== null
                    && $segment['pageEnd'] !== null
                    && (int) $segment['pageStart'] === (int) $segment['pageEnd']
                    && $this->normalizeText((string) ($segment['textTranslation'] ?? $segment['content'] ?? '')) !== '';
            })
            ->groupBy(fn (array $segment) => (string) $segment['pageStart'])
            ->map(function (Collection $items) {
                return $items
                    ->map(fn (array $segment) => $this->normalizeText((string) ($segment['textTranslation'] ?? $segment['content'] ?? '')))
                    ->filter()
                    ->implode("\n\n");
            })
            ->filter(fn (string $content) => $content !== '')
            ->toArray();
    }

    private function buildPageSegmentMap(Collection $segments): array
    {
        return $segments
            ->filter(function (array $segment) {
                return $segment['pageStart'] !== null
                    && $segment['pageEnd'] !== null
                    && (int) $segment['pageStart'] === (int) $segment['pageEnd']
                    && (
                        $this->normalizeText((string) ($segment['textTranslation'] ?? '')) !== ''
                        || $this->normalizeText((string) ($segment['textArabic'] ?? '')) !== ''
                    );
            })
            ->groupBy(fn (array $segment) => (string) $segment['pageStart'])
            ->map(fn (Collection $items) => $items->values()->all())
            ->toArray();
    }

    private function buildChapterMap(Collection $segments): array
    {
        $map = [];

        $segments
            ->filter(function (array $segment) {
                return $segment['type'] === 'chapter'
                    && $segment['pageStart'] !== null
                    && $segment['pageEnd'] !== null
                    && $segment['title'];
            })
            ->each(function (array $segment) use (&$map) {
                $start = (int) $segment['pageStart'];
                $end = (int) $segment['pageEnd'];

                for ($page = $start; $page <= $end; $page++) {
                    $map[(string) $page] = [
                        'key' => $segment['key'],
                        'title' => $segment['title'],
                        'pageStart' => $start,
                        'pageEnd' => $end,
                    ];
                }
            });

        return $map;
    }

    private function resolveSummaryText(Kitab $kitab, Collection $segments): string
    {
        $summarySegment = $segments->first(function (array $segment) {
            return $segment['type'] === 'summary'
                && $this->normalizeText((string) ($segment['textTranslation'] ?? $segment['content'] ?? '')) !== '';
        });

        return $summarySegment
            ? $this->normalizeText((string) ($summarySegment['textTranslation'] ?? $summarySegment['content'] ?? ''))
            : $this->normalizeText((string) ($kitab->deskripsi ?? ''));
    }

    private function hasSummaryTranscript(Collection $segments): bool
    {
        return $segments->contains(function (array $segment) {
            return $segment['type'] === 'summary'
                && $this->normalizeText((string) ($segment['textTranslation'] ?? $segment['content'] ?? '')) !== '';
        });
    }

    private function splitParagraphs(string $text): array
    {
        if ($text === '') {
            return [];
        }

        return collect(preg_split('/\n{2,}|\n/u', $text) ?: [])
            ->map(fn (string $paragraph) => $this->normalizeText($paragraph))
            ->filter()
            ->values()
            ->all();
    }

    private function normalizeText(string $text): string
    {
        $text = preg_replace('/\R/u', "\n", $text) ?? $text;
        $text = preg_replace('/([\p{L}\x{0600}-\x{06FF}])-\s*\n([\p{L}\x{0600}-\x{06FF}])/u', '$1$2', $text) ?? $text;
        $text = preg_replace('/^\s*\d+\s*$/mu', '', $text) ?? $text;
        $text = str_replace("\0", '', $text);
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+\n/u', "\n", $text) ?? $text;
        $text = preg_replace('/\n\s+/u', "\n", $text) ?? $text;
        $text = preg_replace('/\n{3,}/u', "\n\n", $text) ?? $text;

        return trim($text);
    }

    private function hasTranscriptTable(): bool
    {
        if ($this->transcriptTableExists !== null) {
            return $this->transcriptTableExists;
        }

        $this->transcriptTableExists = Schema::hasTable('kitab_transcript_segments');

        return $this->transcriptTableExists;
    }
}
