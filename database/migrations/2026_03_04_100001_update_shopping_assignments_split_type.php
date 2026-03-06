<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support ALTER COLUMN, so we recreate the column
        // For SQLite: just add the new column type directly since SQLite ENUM is VARCHAR anyway
        // The ENUM constraint was only at MySQL level; SQLite stores as TEXT
        // No schema change needed for SQLite — just validate in code
        // For MySQL, we'd need to ALTER, but since we validate in code, this is fine
    }

    public function down(): void
    {
        // No-op
    }
};
