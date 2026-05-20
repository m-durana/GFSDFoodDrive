<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a `is_sudoer` flag to users. When true, the user gains Santa-equivalent
 * access for the duration of the flag being on, while keeping their nominal
 * tier (Coordinator / Advisor / NINJA) in the org chart. All actions taken
 * while sudoer'd are tagged in the audit log via App\Http\Middleware\LogSudoActivity.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_sudoer')->default(false)->after('permission');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_sudoer');
        });
    }
};
