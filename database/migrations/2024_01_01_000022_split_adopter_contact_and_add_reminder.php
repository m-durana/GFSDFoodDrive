<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->string('adopter_email', 255)->nullable()->after('adopter_contact_info');
            $table->string('adopter_phone', 255)->nullable()->after('adopter_email');
            $table->boolean('adoption_reminder_sent')->default(false)->after('gift_dropped_off');
        });

        // Migrate existing data: parse adopter_contact_info into email vs phone
        DB::table('children')
            ->whereNotNull('adopter_contact_info')
            ->where('adopter_contact_info', '!=', '')
            ->orderBy('id')
            ->chunk(100, function ($rows) {
                foreach ($rows as $row) {
                    $value = trim($row->adopter_contact_info);
                    if (str_contains($value, '@')) {
                        DB::table('children')->where('id', $row->id)->update(['adopter_email' => $value]);
                    } else {
                        DB::table('children')->where('id', $row->id)->update(['adopter_phone' => $value]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->dropColumn(['adopter_email', 'adopter_phone', 'adoption_reminder_sent']);
        });
    }
};
