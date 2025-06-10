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
        Schema::create('cert_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cert_id')->constrained('certs')->onDelete('cascade');
            $table->text('comment');
            $table->string('commented_by');
            $table->enum('comment_type', ['verification', 'revision_request', 'internal'])->default('internal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cert_comments');
    }
};