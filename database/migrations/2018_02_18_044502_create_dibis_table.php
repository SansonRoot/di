<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDibisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dibis', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('username',100)->unique();
            $table->string('phone',100)->unique();
            $table->string('email',100)->nullable();
            $table->string('gender');
            $table->integer('tokens');
            $table->string('image');
            $table->string('region')->nullable();
            $table->integer('status')->default(0);
            $table->integer('active')->default(1);
            $table->string('verification_code');
            $table->string('password');
            $table->integer('wallet')->nullable();
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
        Schema::dropIfExists('dibis');
    }
}
