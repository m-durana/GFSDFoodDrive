<?php

namespace App\Jobs;

use App\Helpers\QrCodeHelper;
use App\Models\Child;
use App\Models\Family;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GeneratePdfBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $batchId,
        public int $batchNumber,
        public string $type,
        public array $itemIds,
    ) {}

    public function handle(): void
    {
        $statusFile = "pdf-batches/{$this->batchId}/status.json";
        $pdfFile = "pdf-batches/{$this->batchId}/batch-{$this->batchNumber}.pdf";

        if ($this->type === 'gift-tags') {
            $children = Child::with('family')->whereIn('id', $this->itemIds)->get()
                ->sortBy(fn($c) => $c->family->family_number);

            $qrCodes = [];
            foreach ($children as $child) {
                $qrCodes[$child->id] = QrCodeHelper::generateBase64(
                    QrCodeHelper::scanUrl($child->id),
                    3
                );
            }

            $filter = 'batch';
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('documents.gift-tags', compact('children', 'filter', 'qrCodes'));
            $pdf->setPaper('letter');
        } elseif ($this->type === 'family-summary') {
            $families = Family::whereIn('id', $this->itemIds)->orderBy('family_number')->get();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('documents.family-summary', compact('families'));
            $pdf->setPaper('letter');
        } elseif ($this->type === 'delivery-day') {
            $families = Family::whereIn('id', $this->itemIds)->orderBy('family_number')->get();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('documents.delivery-day', compact('families'));
            $pdf->setPaper('letter');
        } else {
            return;
        }

        Storage::put($pdfFile, $pdf->output());

        // Update status
        $status = json_decode(Storage::get($statusFile) ?: '{}', true);
        $status['batches'][$this->batchNumber] = [
            'status' => 'completed',
            'file' => $pdfFile,
        ];
        $status['completed'] = count(array_filter(
            $status['batches'] ?? [],
            fn($b) => $b['status'] === 'completed'
        ));
        Storage::put($statusFile, json_encode($status));
    }
}
