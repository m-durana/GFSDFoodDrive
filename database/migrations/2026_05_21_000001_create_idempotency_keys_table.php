<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64);
            $table->string('endpoint', 128);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedSmallInteger('response_status');
            $table->json('response_body');
            $table->timestamp('created_at')->useCurrent();
            // Same client-side UUID can legitimately apply to different endpoints
            // or be replayed across users — scope uniqueness by all three.
            $table->unique(['key', 'endpoint', 'user_id'], 'idempotency_keys_lookup');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
    }
};
