<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First ensure the activities table exists
        if (!Schema::hasTable('activities')) {
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
                $table->timestamps();
            });
        }

        // Then create the activity_bookings table
        Schema::create('activity_bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('activity_id');
            $table->uuid('tourist_id');
            $table->integer('number_of_participants');
            $table->dateTime('scheduled_date');
            $table->string('status')->default('pending'); // pending, confirmed, cancelled, completed
            $table->text('special_requests')->nullable();
            $table->decimal('total_cost', 10, 2);
            $table->json('participant_details')->nullable();
            $table->timestamps();

            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('tourist_id')->references('id')->on('tourists')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_bookings');
        Schema::dropIfExists('activities');
    }
};
