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
        }
    }
}
