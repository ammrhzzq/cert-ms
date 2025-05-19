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
            $table->string('phone1_name')->after('comp_phone1');
            $table->string('phone2_name')->after('comp_phone2');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certs', function (Blueprint $table) {
            // Remove contact_name column
            $table->dropColumn('phone1_name');
            $table->dropColumn('phone2_name');
        });
    }
};
