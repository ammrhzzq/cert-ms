<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropCertVerificationsTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('cert_verifications');
    }

    public function down()
    {
        // Optional: recreate the table if you roll back this migration
        Schema::create('cert_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_code');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }
}
