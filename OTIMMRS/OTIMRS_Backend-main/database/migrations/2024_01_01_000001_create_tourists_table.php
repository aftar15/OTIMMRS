<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tourists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('gender');
            $table->string('nationality');
            $table->string('address');
            $table->json('hobbies')->nullable();
            $table->string('accommodation_name')->nullable();
            $table->string('accommodation_location')->nullable();
            $table->integer('accommodation_days')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tourists');
    }
};
