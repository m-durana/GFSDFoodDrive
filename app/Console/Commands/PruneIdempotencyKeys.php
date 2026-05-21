<?php

namespace App\Console\Commands;

use App\Http\Middleware\IdempotentRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneIdempotencyKeys extends Command
{
    protected $signature = 'idempotency:prune {--hours=}';

    protected $description = 'Delete idempotency keys older than the TTL (default 24h).';

    public function handle(): int
    {
        $hours = (int) ($this->option('hours') ?? IdempotentRequest::TTL_HOURS);
        $cutoff = now()->subHours($hours);

        $deleted = DB::table('idempotency_keys')->where('created_at', '<', $cutoff)->delete();

        $this->info("Pruned {$deleted} idempotency key(s) older than {$hours}h.");
        return self::SUCCESS;
    }
}
