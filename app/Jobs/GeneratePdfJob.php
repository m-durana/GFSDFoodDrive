<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class GeneratePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct(
        private readonly string $jobKey,
        private readonly string $viewName,
        private readonly array $viewData,
        private readonly string $filename,
        private readonly string $paperSize = 'letter',
    ) {}

    public function handle(): void
    {
        Cache::put("pdf:{$this->jobKey}", ['status' => 'processing', 'message' => 'Generating PDF...'], 600);

        try {
            if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                Cache::put("pdf:{$this->jobKey}", [
                    'status' => 'error',
                    'message' => 'DomPDF is not installed.',
                ], 600);
                return;
            }

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($this->viewName, $this->viewData);
            $pdf->setPaper($this->paperSize);

            $path = "pdfs/{$this->jobKey}/{$this->filename}";
            Storage::disk('local')->put($path, $pdf->output());

            Cache::put("pdf:{$this->jobKey}", [
                'status' => 'complete',
                'message' => 'PDF ready for download.',
                'path' => $path,
                'filename' => $this->filename,
            ], 600);
        } catch (\Throwable $e) {
            Cache::put("pdf:{$this->jobKey}", [
                'status' => 'error',
                'message' => 'PDF generation failed: ' . $e->getMessage(),
            ], 600);
            // REL-13: page the on-call channel via Sentry — silent PDF failures
            // during intake week are an operational risk.
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
            throw $e;
        }
    }

    /**
     * REL-13: Horizon retry policy — backoff on transient failures (dompdf
     * memory spikes, disk latency), but cap so we don't retry indefinitely.
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function tries(): int
    {
        return 3;
    }
}
