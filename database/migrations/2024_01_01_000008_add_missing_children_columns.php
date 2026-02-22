<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add columns for gift tracking, fulfillment, and mail merge.
     * Discovered from PDF spec, 706.docx merge fields, and 2019 gift level system.
     */
    public function up(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->text('toy_ideas')->nullable()->after('gift_preferences');
            $table->text('all_sizes')->nullable()->after('toy_ideas');
            $table->boolean('mail_merged')->default(false)->after('all_sizes');
            $table->text('gifts_received')->nullable()->after('mail_merged');
            $table->unsignedTinyInteger('gift_level')->default(0)->after('gifts_received');
            $table->string('where_is_tag')->nullable()->after('gift_level');
            $table->string('adopter_name')->nullable()->after('where_is_tag');
            $table->string('adopter_contact_info')->nullable()->after('adopter_name');
        });
    }

    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->dropColumn([
                'toy_ideas',
                'all_sizes',
                'mail_merged',
                'gifts_received',
                'gift_level',
                'where_is_tag',
                'adopter_name',
                'adopter_contact_info',
            ]);
        });
    }
};
