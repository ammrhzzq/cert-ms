<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('certs', function (Blueprint $table) {
            Schema::table('certs', function (Blueprint $table) {
                $table->timestamp('last_edited_at')->nullable();
                $table->unsignedBigInteger('last_edited_by')->nullable();
                $table->foreign('last_edited_by')->references('id')->on('users')->onDelete('set null');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certs', function (Blueprint $table) {
            $table->dropForeign(['last_edited_by']);
            $table->dropColumn(['last_edited_at', 'last_edited_by']);
        });
    }
};
