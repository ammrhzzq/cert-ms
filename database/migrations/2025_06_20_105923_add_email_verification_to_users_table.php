<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email_verification_token', 6)->nullable();
            $table->timestamp('email_verification_expires_at')->nullable();
            $table->tinyInteger('email_verification_attempts')->default(0);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email_verification_token',
                'email_verification_expires_at',
                'email_verification_attempts'
            ]);
        });
    }
};