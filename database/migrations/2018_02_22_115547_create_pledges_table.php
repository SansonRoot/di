<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePledgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pledges', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('pledger_id');
            $table->integer('receiver_id')->nullable();
            $table->double('amount');
            $table->string('date');
            $table->string('time');
            $table->integer('plegde_status')->default(0);
            $table->integer('receive_status')->default(0);
            $table->string('maturity');
            $table->string('added_time')->default('24');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pledges');
    }
}
