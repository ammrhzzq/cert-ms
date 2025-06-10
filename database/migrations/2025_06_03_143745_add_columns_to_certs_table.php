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
            $table->boolean('hod_approved')->default(false)->after('status');
            $table->string('pdf_path')->nullable()->after('status');
            $table->string('revision_source')->nullable()->after('status');
            $table->string('cert_number')->nullable()->after('iso_num');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certs', function (Blueprint $table) {
            $table->dropColumn('hod_approved');
            $table->dropColumn('pdf_path');
            $table->dropColumn('revision_source');
            $table->dropColumn('cert_number');
        });
    }
};
