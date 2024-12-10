<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description');
            $table->string('location');
            $table->string('category');
            $table->json('tags')->nullable();
            $table->string('image_url');
            $table->decimal('rating', 3, 2)->default(0);
            $table->decimal('price', 10, 2);
            $table->string('duration');
            $table->string('difficulty');
            $table->json('included_items');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('schedule_type');
            $table->json('recurring_pattern')->nullable();
            $table->decimal('cost', 10, 2);
            $table->integer('capacity');
            $table->integer('min_participants');
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_booking')->default(true);
            $table->integer('booking_deadline_hours');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('map_source');
            $table->string('thumbnail_url');
            $table->string('contact_number');
            $table->string('contact_phone')->nullable();
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
        Schema::dropIfExists('activities');
    }
}
