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
        Schema::create('arrivals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tourist_id');
            $table->uuid('accommodation_id')->nullable();
            $table->dateTime('arrival_date');
            $table->dateTime('departure_date');
            $table->string('purpose_of_visit');
            $table->string('transportation_mode');
            $table->integer('number_of_companions')->default(0);
            $table->string('status')->default('pending'); // pending, confirmed, cancelled, completed
            $table->text('notes')->nullable();
            $table->string('contact_number');
            $table->string('emergency_contact');
            $table->string('emergency_contact_number');
            $table->timestamps();

            $table->foreign('tourist_id')->references('id')->on('tourists')->onDelete('cascade');
            $table->foreign('accommodation_id')->references('id')->on('accommodations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arrivals');
    }
};
