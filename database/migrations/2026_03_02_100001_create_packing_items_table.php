<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('warehouse_categories')->nullOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('warehouse_items')->nullOnDelete();
            $table->foreignId('child_id')->nullable()->constrained('children')->nullOnDelete();
            $table->foreignId('grocery_item_id')->nullable()->constrained('grocery_items')->nullOnDelete();
            $table->string('description');
            $table->unsignedSmallInteger('quantity_needed')->default(1);
            $table->unsignedSmallInteger('quantity_packed')->default(0);
            $table->string('status', 20)->default('pending');
            $table->foreignId('packed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('packed_at')->nullable();
            $table->text('substitute_notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packing_items');
    }
};
