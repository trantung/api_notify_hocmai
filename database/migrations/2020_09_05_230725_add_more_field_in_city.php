<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreFieldInCity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hocmai_citys', function (Blueprint $table) {
            $table->string('description')->nullable();
            $table->string('code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hocmai_citys', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('code');
        });
    }
}
