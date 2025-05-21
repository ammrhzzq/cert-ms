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
        Schema::create('cert_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cert_id')->constrained('certs')->onDelete('cascade');
            $table->string('token', 64)->unique();
            $table->timestamp('expired_at');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cert_verifications');
    }
};
