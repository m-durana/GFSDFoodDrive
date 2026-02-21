<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Maps legacy tblUser table to Laravel users table.
     *
     * Legacy columns: userID, username, password, permission, FirstName, LastName
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();                                        // replaces userID
            $table->string('username')->unique();                // from tblUser.username
            $table->string('first_name');                        // from tblUser.FirstName
            $table->string('last_name');                         // from tblUser.LastName
            $table->string('email')->nullable()->unique();       // new field for Laravel compat
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');                          // from tblUser.password (already bcrypt)
            $table->unsignedTinyInteger('permission')->default(0);
                // 0 = Inactive, 7 = Family Entry, 8 = Coordinator, 9 = Santa
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
