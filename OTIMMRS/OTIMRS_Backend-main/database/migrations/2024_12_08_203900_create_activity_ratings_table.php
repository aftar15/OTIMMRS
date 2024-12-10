<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_ratings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('activity_id');
            $table->uuid('tourist_id');
            $table->decimal('rating', 3, 2);
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('activity_id')
                  ->references('id')
                  ->on('activities')
                  ->onDelete('cascade');

            $table->foreign('tourist_id')
                  ->references('id')
                  ->on('tourists')
                  ->onDelete('cascade');

            $table->unique(['activity_id', 'tourist_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_ratings');
    }
}
