<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('type', 20); // food, gift, supply, baby
            $table->string('unit', 20)->default('item');
            $table->string('barcode_prefix', 20)->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('warehouse_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('warehouse_categories')->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('barcode', 50)->nullable()->unique();
            $table->string('description', 500)->nullable();
            $table->boolean('is_generic')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('warehouse_transactions', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('season_year')->index();
            $table->foreignId('item_id')->nullable()->constrained('warehouse_items')->nullOnDelete();
            $table->foreignId('category_id')->constrained('warehouse_categories');
            $table->foreignId('family_id')->nullable()->constrained('families')->nullOnDelete();
            $table->unsignedBigInteger('child_id')->nullable();
            $table->foreign('child_id')->references('id')->on('children')->nullOnDelete();
            $table->string('transaction_type', 20);
            $table->smallInteger('quantity')->default(1);
            $table->string('source', 100)->nullable();
            $table->string('donor_name', 200)->nullable();
            $table->string('barcode_scanned', 50)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('scanned_at')->nullable();
            $table->timestamps();

            $table->index(['season_year', 'category_id']);
            $table->index(['season_year', 'transaction_type']);
            $table->index(['family_id', 'created_at']);
        });

        Artisan::call('db:seed', ['--class' => 'WarehouseCategorySeeder']);
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_transactions');
        Schema::dropIfExists('warehouse_items');
        Schema::dropIfExists('warehouse_categories');
    }
};
