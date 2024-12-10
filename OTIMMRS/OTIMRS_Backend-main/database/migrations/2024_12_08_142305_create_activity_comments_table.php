<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityCommentsTable extends Migration
{
    public function up()
    {
        Schema::create('activity_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('activity_id');
            $table->uuid('tourist_id');
            $table->text('comment');
            $table->decimal('rating', 3, 2)->nullable();
            $table->timestamps();

            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('tourist_id')->references('id')->on('tourists')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_comments');
    }
}
