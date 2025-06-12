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
            // Make issue_date and exp_date nullable
            $table->date('issue_date')->nullable()->change();
            $table->date('exp_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certs', function (Blueprint $table) {
            // Revert back to non-nullable (only if you want to be able to rollback)
            // Note: This might fail if there are NULL values in the database
            $table->date('issue_date')->nullable(false)->change();
            $table->date('exp_date')->nullable(false)->change();
        });
    }
};