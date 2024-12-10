<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tourist_id');
            $table->uuid('attraction_id');
            $table->integer('rating');
            $table->timestamps();
            $table->unique(['tourist_id', 'attraction_id']);

            $table->foreign('tourist_id')
                ->references('id')
                ->on('tourists')
                ->onDelete('cascade');

            $table->foreign('attraction_id')
                ->references('id')
                ->on('attractions')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ratings');
    }
};
