<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoaScopeAndSitesToCertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('certs', function (Blueprint $table) {
            $table->text('soa')->nullable()->after('exp_date');
            $table->text('scope')->nullable()->after('soa');
            $table->json('sites')->nullable()->after('scope');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('certs', function (Blueprint $table) {
            $table->dropColumn(['soa', 'scope', 'sites']);
        });
    }
}