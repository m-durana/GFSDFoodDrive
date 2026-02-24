<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support adding NOT NULL without default, so add nullable first
        Schema::table('shopping_assignments', function (Blueprint $table) {
            $table->string('token', 64)->nullable()->after('id');
            $table->string('ninja_name')->nullable()->after('user_id');
        });

        // Generate tokens for any existing rows
        foreach (DB::table('shopping_assignments')->whereNull('token')->get() as $row) {
            DB::table('shopping_assignments')->where('id', $row->id)->update(['token' => Str::random(32)]);
        }

        // Now add unique index
        Schema::table('shopping_assignments', function (Blueprint $table) {
            $table->unique('token');
        });

        // Recreate table to make user_id nullable (SQLite limitation)
        // For SQLite: drop and recreate the FK constraint by rebuilding
        Schema::table('shopping_assignments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('shopping_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopping_assignment_id')->constrained()->onDelete('cascade');
            $table->string('item_key');
            $table->string('checked_by');
            $table->timestamp('checked_at');
            $table->timestamps();

            $table->unique(['shopping_assignment_id', 'item_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopping_checks');

        Schema::table('shopping_assignments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['token', 'ninja_name']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
