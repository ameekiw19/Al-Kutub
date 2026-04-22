<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class ObservabilitySummaryCommand extends Command
{
    protected $signature = 'observability:summary {--minutes=60 : Lookback window in minutes} {--json : Output JSON}';

    protected $description = 'Ringkas error rate dan latency dari observability log';

    public function handle(): int
    {
        $logPath = storage_path('logs/observability.log');
        if (!is_file($logPath)) {
            $empty = [
                'window_minutes' => max(1, (int) $this->option('minutes')),
                'total_events' => 0,
                'errors_5xx' => 0,
                'warnings_4xx' => 0,
                'slow_requests' => 0,
                'max_duration_ms' => 0,
                'generated_at' => Carbon::now()->toISOString(),
                'note' => 'observability.log belum tersedia',
            ];

            if ((bool) $this->option('json')) {
                $this->line(json_encode($empty, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            } else {
                $this->warn('File observability.log belum ada.');
            }
            return self::SUCCESS;
        }

        $minutes = max(1, (int) $this->option('minutes'));
        $since = Carbon::now()->subMinutes($minutes);

        $total = 0;
        $errors5xx = 0;
        $warnings4xx = 0;
        $slow = 0;
        $maxDuration = 0;

        $handle = fopen($logPath, 'r');
        if ($handle === false) {
            $this->error('Gagal membaca observability.log');
            return self::FAILURE;
        }

        while (($line = fgets($handle)) !== false) {
            if (!preg_match('/^\[(?<ts>[^\]]+)\]/', $line, $match)) {
                continue;
            }

            try {
                $timestamp = Carbon::parse($match['ts']);
            } catch (\Throwable $e) {
                continue;
            }

            if ($timestamp->lt($since)) {
                continue;
            }

            $total++;

            if (str_contains($line, 'API request failed')) {
                $errors5xx++;
            }
            if (str_contains($line, 'API request warning')) {
                $warnings4xx++;
            }
            if (str_contains($line, 'API slow request')) {
                $slow++;
            }

            if (preg_match('/"duration_ms":\s*(\d+)/', $line, $durationMatch)) {
                $duration = (int) $durationMatch[1];
                if ($duration > $maxDuration) {
                    $maxDuration = $duration;
                }
            }
        }
        fclose($handle);

        $result = [
            'window_minutes' => $minutes,
            'total_events' => $total,
            'errors_5xx' => $errors5xx,
            'warnings_4xx' => $warnings4xx,
            'slow_requests' => $slow,
            'max_duration_ms' => $maxDuration,
            'generated_at' => Carbon::now()->toISOString(),
        ];

        if ((bool) $this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return self::SUCCESS;
        }

        $this->info('Observability Summary');
        $this->line('- Window (minutes): ' . $result['window_minutes']);
        $this->line('- Total events: ' . $result['total_events']);
        $this->line('- Errors 5xx: ' . $result['errors_5xx']);
        $this->line('- Warnings 4xx: ' . $result['warnings_4xx']);
        $this->line('- Slow requests: ' . $result['slow_requests']);
        $this->line('- Max duration (ms): ' . $result['max_duration_ms']);

        return self::SUCCESS;
    }
}
