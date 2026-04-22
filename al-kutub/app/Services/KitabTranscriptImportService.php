<?php

namespace App\Services;

use App\Models\Kitab;
use App\Models\KitabTranscriptSegment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class KitabTranscriptImportService
{
    public function import(Kitab $kitab, bool $force = false): array
    {
        $pdfPath = public_path('pdf/' . ltrim((string) $kitab->file_pdf, '/'));

        if (!is_file($pdfPath)) {
            throw new RuntimeException('File PDF kitab tidak ditemukan di server.');
        }

        if (!is_readable($pdfPath)) {
            throw new RuntimeException('File PDF kitab tidak dapat dibaca.');
        }

        $existingCount = $kitab->transcriptSegments()->count();
        if ($existingCount > 0 && !$force) {
            return [
                'status' => 'skipped',
                'kitab_id' => (int) $kitab->id_kitab,
                'title' => $kitab->judul,
                'message' => 'Transcript sudah ada. Gunakan mode force untuk generate ulang.',
                'page_count' => 0,
                'page_segments' => 0,
                'chapter_segments' => 0,
                'summary_segments' => 0,
                'toc_segments' => 0,
                'total_segments' => $existingCount,
            ];
        }

        $this->assertBinaryAvailable('pdfinfo');
        $this->assertBinaryAvailable('pdftotext');

        $pageCount = $this->resolvePageCount($pdfPath);
        if ($pageCount <= 0) {
            throw new RuntimeException('Jumlah halaman PDF tidak valid.');
        }

        $rawPages = [];
        for ($page = 1; $page <= $pageCount; $page++) {
            $rawPages[$page] = $this->extractRawPageText($pdfPath, $page);
        }

        $boilerplateLines = $this->detectBoilerplateLines($rawPages, $pageCount);
        $pages = [];
        foreach ($rawPages as $pageNumber => $rawText) {
            $normalizedText = $this->normalizePageText($rawText, $boilerplateLines);
            $pages[$pageNumber] = $this->splitBilingualContent($normalizedText);
        }

        $summaryText = $this->resolveSummaryText($kitab, $pages);
        $chapterEntries = $this->detectChapters($pages);

        $segments = [];
        $sortOrder = 1;

        if ($summaryText !== '') {
            $segments[] = $this->buildSegmentRow($kitab, [
                'section_key' => 'summary',
                'transcript_type' => 'summary',
                'title' => 'Ringkasan',
                'content_translation' => $summaryText,
                'content_arabic' => '',
                'page_start' => null,
                'page_end' => null,
                'sort_order' => $sortOrder++,
                'metadata' => [
                    'source' => 'description_or_pdf',
                    'importer' => 'pdftotext',
                ],
            ]);
        }

        if (!empty($chapterEntries)) {
            $tocContent = collect($chapterEntries)
                ->map(fn (array $entry) => trim(($entry['title'] ?? 'Bab') . ' - halaman ' . ($entry['page_start'] ?? '?')))
                ->implode("\n");

            if ($tocContent !== '') {
                $segments[] = $this->buildSegmentRow($kitab, [
                    'section_key' => 'toc',
                    'transcript_type' => 'toc',
                    'title' => 'Daftar Isi',
                    'content_translation' => $tocContent,
                    'content_arabic' => '',
                    'page_start' => null,
                    'page_end' => null,
                    'sort_order' => $sortOrder++,
                    'metadata' => [
                        'source' => 'pdf_detected_toc',
                        'count' => count($chapterEntries),
                    ],
                ]);
            }

            foreach ($chapterEntries as $index => $chapter) {
                $segments[] = $this->buildSegmentRow($kitab, [
                    'section_key' => 'chapter-' . ($index + 1),
                    'transcript_type' => 'chapter',
                    'title' => $chapter['title'],
                    'content_translation' => $chapter['content_translation'],
                    'content_arabic' => $chapter['content_arabic'],
                    'page_start' => $chapter['page_start'],
                    'page_end' => $chapter['page_end'],
                    'sort_order' => $sortOrder++,
                    'metadata' => [
                        'source' => $chapter['source'],
                    ],
                ]);
            }
        }

        foreach ($pages as $pageNumber => $pageContent) {
            if ($pageContent['translation'] === '' && $pageContent['arabic'] === '') {
                continue;
            }

            $segments[] = $this->buildSegmentRow($kitab, [
                'section_key' => 'page-' . $pageNumber,
                'transcript_type' => 'page',
                'title' => 'Halaman ' . $pageNumber,
                'content_translation' => $pageContent['translation'],
                'content_arabic' => $pageContent['arabic'],
                'page_start' => $pageNumber,
                'page_end' => $pageNumber,
                'sort_order' => $sortOrder++,
                'metadata' => [
                    'source' => 'pdftotext_page',
                ],
            ]);
        }

        DB::transaction(function () use ($kitab, $segments) {
            $kitab->transcriptSegments()->delete();

            if (!empty($segments)) {
                KitabTranscriptSegment::insert($segments);
            }
        });

        return [
            'status' => 'imported',
            'kitab_id' => (int) $kitab->id_kitab,
            'title' => $kitab->judul,
            'message' => 'Transcript kitab berhasil digenerate dari PDF.',
            'page_count' => $pageCount,
            'page_segments' => count(array_filter($segments, fn (array $segment) => $segment['transcript_type'] === 'page')),
            'chapter_segments' => count(array_filter($segments, fn (array $segment) => $segment['transcript_type'] === 'chapter')),
            'summary_segments' => count(array_filter($segments, fn (array $segment) => $segment['transcript_type'] === 'summary')),
            'toc_segments' => count(array_filter($segments, fn (array $segment) => $segment['transcript_type'] === 'toc')),
            'total_segments' => count($segments),
        ];
    }

    private function buildSegmentRow(Kitab $kitab, array $payload): array
    {
        $sectionKey = (string) $payload['section_key'];
        $translationText = trim((string) ($payload['content_translation'] ?? ''));
        $arabicText = trim((string) ($payload['content_arabic'] ?? ''));

        return [
            'kitab_id' => $kitab->id_kitab,
            'section_key' => $sectionKey,
            'transcript_type' => (string) $payload['transcript_type'],
            'title' => $payload['title'] ?? null,
            'content' => $translationText !== '' ? $translationText : $arabicText,
            'content_translation' => $translationText !== '' ? $translationText : null,
            'content_arabic' => $arabicText !== '' ? $arabicText : null,
            'language' => $kitab->bahasa,
            'page_start' => $payload['page_start'],
            'page_end' => $payload['page_end'],
            'sort_order' => (int) $payload['sort_order'],
            'is_active' => true,
            'metadata' => json_encode($payload['metadata'] ?? []),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function splitBilingualContent(string $text): array
    {
        if ($text === '') {
            return [
                'full' => '',
                'translation' => '',
                'arabic' => '',
            ];
        }

        $translationLines = [];
        $arabicLines = [];

        foreach (preg_split('/\R/u', $text) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if ($this->isArabicDominantLine($line)) {
                $arabicLines[] = $line;
            } else {
                $translationLines[] = $line;
            }
        }

        $translation = trim(implode("\n", $translationLines));
        $arabic = trim(implode("\n", $arabicLines));

        if ($translation === '' && $arabic === '') {
            $translation = $text;
        }

        return [
            'full' => $text,
            'translation' => $translation,
            'arabic' => $arabic,
        ];
    }

    private function isArabicDominantLine(string $line): bool
    {
        preg_match_all('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}]/u', $line, $arabicMatches);
        preg_match_all('/[A-Za-z]/u', $line, $latinMatches);

        $arabicCount = count($arabicMatches[0] ?? []);
        $latinCount = count($latinMatches[0] ?? []);

        if ($arabicCount === 0) {
            return false;
        }

        return $arabicCount >= max(4, ($latinCount * 2));
    }

    private function assertBinaryAvailable(string $binary): void
    {
        $process = new Process(['bash', '-lc', 'command -v ' . escapeshellarg($binary)]);
        $process->run();

        if (!$process->isSuccessful() || trim($process->getOutput()) === '') {
            throw new RuntimeException("Binary {$binary} tidak tersedia di server.");
        }
    }

    private function resolvePageCount(string $pdfPath): int
    {
        $process = new Process(['pdfinfo', $pdfPath]);
        $process->setTimeout(20);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        if (preg_match('/^Pages:\s+(\d+)/mi', $process->getOutput(), $matches) !== 1) {
            throw new RuntimeException('Gagal membaca jumlah halaman PDF.');
        }

        return (int) $matches[1];
    }

    private function extractRawPageText(string $pdfPath, int $pageNumber): string
    {
        $process = new Process([
            'pdftotext',
            '-f',
            (string) $pageNumber,
            '-l',
            (string) $pageNumber,
            '-layout',
            '-enc',
            'UTF-8',
            $pdfPath,
            '-',
        ]);
        $process->setTimeout(20);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return (string) $process->getOutput();
    }

    private function detectBoilerplateLines(array $rawPages, int $pageCount): array
    {
        $frequency = [];

        foreach ($rawPages as $rawText) {
            $lines = $this->splitMeaningfulLines($rawText);
            $candidates = array_merge(
                array_slice($lines, 0, 3),
                array_slice($lines, -3)
            );

            foreach ($candidates as $line) {
                $normalized = $this->normalizeLineForFrequency($line);
                if ($normalized === '' || mb_strlen($normalized) > 120) {
                    continue;
                }
                $frequency[$normalized] = ($frequency[$normalized] ?? 0) + 1;
            }
        }

        $threshold = max(3, (int) ceil($pageCount * 0.18));

        return collect($frequency)
            ->filter(fn (int $count) => $count >= $threshold)
            ->keys()
            ->values()
            ->all();
    }

    private function splitMeaningfulLines(string $text): array
    {
        $text = str_replace(["\r\n", "\r", "\f", "\0"], ["\n", "\n", "\n", ''], $text);

        return collect(explode("\n", $text))
            ->map(function (string $line) {
                $line = preg_replace('/[ \t]+/u', ' ', $line) ?? $line;
                return trim($line);
            })
            ->filter(fn (string $line) => $line !== '')
            ->values()
            ->all();
    }

    private function normalizeLineForFrequency(string $line): string
    {
        $line = preg_replace('/\s+/u', ' ', trim($line)) ?? trim($line);
        $line = preg_replace('/[^[:alnum:]\p{L}\p{N}\x{0600}-\x{06FF}\s]/u', '', $line) ?? $line;
        return trim(mb_strtolower($line));
    }

    private function normalizePageText(string $rawText, array $boilerplateLines): string
    {
        $lines = $this->splitMeaningfulLines($rawText);
        $cleaned = [];

        foreach ($lines as $line) {
            $normalizedFrequencyLine = $this->normalizeLineForFrequency($line);

            if (in_array($normalizedFrequencyLine, $boilerplateLines, true)) {
                continue;
            }

            if ($this->looksLikePageNumber($line) || $this->looksLikeNoiseLine($line)) {
                continue;
            }

            $cleaned[] = $line;
        }

        $text = implode("\n", $cleaned);
        $text = preg_replace('/([\p{L}\x{0600}-\x{06FF}])-\n([\p{L}\x{0600}-\x{06FF}])/u', '$1$2', $text) ?? $text;
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+\n/u', "\n", $text) ?? $text;
        $text = preg_replace('/\n\s+/u', "\n", $text) ?? $text;
        $text = preg_replace('/\n{3,}/u', "\n\n", $text) ?? $text;

        return trim($text);
    }

    private function looksLikePageNumber(string $line): bool
    {
        $stripped = trim($line);

        return preg_match('/^[^\p{L}\x{0600}-\x{06FF}]*\d{1,4}[^\p{L}\x{0600}-\x{06FF}]*$/u', $stripped) === 1;
    }

    private function looksLikeNoiseLine(string $line): bool
    {
        $stripped = trim($line);

        if ($stripped === '') {
            return true;
        }

        if (preg_match('/^www\./iu', $stripped) === 1) {
            return true;
        }

        if (preg_match('/^[\p{So}\p{Sk}\-\–\—\(\)\[\]{}•·]+$/u', $stripped) === 1) {
            return true;
        }

        return false;
    }

    private function resolveSummaryText(Kitab $kitab, array $pages): string
    {
        $description = trim((string) ($kitab->deskripsi ?? ''));
        if ($description !== '') {
            return $this->normalizePageText($description, []);
        }

        $preview = collect($pages)
            ->map(fn (array $page) => $page['translation'] ?: $page['full'])
            ->filter()
            ->take(2)
            ->implode("\n\n");

        return Str::limit($preview, 1600, '');
    }

    private function detectChapters(array $pages): array
    {
        $tocEntries = $this->detectTableOfContentsEntries($pages);
        if (!empty($tocEntries)) {
            return $this->buildChapterRangesFromEntries($tocEntries, $pages, 'toc');
        }

        $headingEntries = $this->detectHeadingEntries($pages);
        if (!empty($headingEntries)) {
            return $this->buildChapterRangesFromEntries($headingEntries, $pages, 'heading');
        }

        return [];
    }

    private function detectTableOfContentsEntries(array $pages): array
    {
        $entries = [];

        foreach (array_slice($pages, 0, 8, true) as $pageData) {
            $sourceText = $pageData['translation'] ?: $pageData['full'];
            foreach (preg_split('/\R/u', $sourceText) ?: [] as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                if (preg_match('/^(Bab\s+.+?|باب\s+.+?|فصل\s+.+?)\s*\.{2,}\s*(\d{1,4})$/iu', $line, $matches) === 1) {
                    $entries[] = [
                        'title' => trim($matches[1]),
                        'page' => (int) $matches[2],
                    ];
                }
            }
        }

        return $this->deduplicateChapterEntries($entries);
    }

    private function detectHeadingEntries(array $pages): array
    {
        $entries = [];

        foreach ($pages as $pageNumber => $pageData) {
            $sourceText = $pageData['translation'] ?: $pageData['full'];
            if ($sourceText === '') {
                continue;
            }

            $lines = collect(preg_split('/\R/u', $sourceText) ?: [])
                ->map(fn (string $line) => trim($line))
                ->filter()
                ->take(10)
                ->values()
                ->all();

            foreach ($lines as $line) {
                if (preg_match('/^(Bab\b.+|باب\b.+|فصل\b.+|Pendahuluan\b.*|Muqaddimah\b.*|Mukadimah\b.*|المقدمة\b.*)$/iu', $line) === 1) {
                    $entries[] = [
                        'title' => $line,
                        'page' => (int) $pageNumber,
                    ];
                    break;
                }
            }
        }

        return $this->deduplicateChapterEntries($entries);
    }

    private function deduplicateChapterEntries(array $entries): array
    {
        $seen = [];
        $result = [];

        foreach ($entries as $entry) {
            $page = (int) ($entry['page'] ?? 0);
            $title = trim((string) ($entry['title'] ?? ''));
            if ($page <= 0 || $title === '') {
                continue;
            }

            $signature = $page . '|' . mb_strtolower($title);
            if (isset($seen[$signature])) {
                continue;
            }

            $seen[$signature] = true;
            $result[] = [
                'title' => $title,
                'page' => $page,
            ];
        }

        usort($result, fn (array $left, array $right) => $left['page'] <=> $right['page']);

        return $result;
    }

    private function buildChapterRangesFromEntries(array $entries, array $pages, string $source): array
    {
        $ranges = [];
        $totalPages = count($pages);

        foreach ($entries as $index => $entry) {
            $startPage = max(1, min($totalPages, (int) $entry['page']));
            $nextPage = isset($entries[$index + 1]) ? (int) $entries[$index + 1]['page'] : ($totalPages + 1);
            $endPage = max($startPage, min($totalPages, $nextPage - 1));

            $translation = collect(range($startPage, $endPage))
                ->map(fn (int $page) => $pages[$page]['translation'] ?? '')
                ->filter()
                ->implode("\n\n");

            $arabic = collect(range($startPage, $endPage))
                ->map(fn (int $page) => $pages[$page]['arabic'] ?? '')
                ->filter()
                ->implode("\n\n");

            if ($translation === '' && $arabic === '') {
                continue;
            }

            $ranges[] = [
                'title' => $entry['title'],
                'page_start' => $startPage,
                'page_end' => $endPage,
                'content_translation' => $translation,
                'content_arabic' => $arabic,
                'source' => $source,
            ];
        }

        return $ranges;
    }
}
