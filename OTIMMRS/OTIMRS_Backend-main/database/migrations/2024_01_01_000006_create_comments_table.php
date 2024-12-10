<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tourist_id');
            $table->uuid('commentable_id');
            $table->string('commentable_type'); // 'attraction', 'activity', or 'accommodation'
            $table->text('comment');
            $table->string('transportation')->nullable();
            $table->decimal('transportation_fee', 10, 2)->nullable();
            $table->text('services')->nullable();
            $table->text('road_problems')->nullable();
            $table->text('price_increase')->nullable();
            $table->text('others')->nullable();
            $table->timestamps();

            $table->foreign('tourist_id')
                ->references('id')
                ->on('tourists')
                ->onDelete('cascade');

            // Create an index for polymorphic relationship
            $table->index(['commentable_id', 'commentable_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('comments');
    }
};
