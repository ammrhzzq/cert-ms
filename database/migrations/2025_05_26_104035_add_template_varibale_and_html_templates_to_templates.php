<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->json('template_variables')->nullable()->after('file_type');
            $table->boolean('is_html_template')->default(false)->after('template_variables');
        });
    }

    public function down()
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn(['template_variables', 'is_html_template']);
        });
    }
};