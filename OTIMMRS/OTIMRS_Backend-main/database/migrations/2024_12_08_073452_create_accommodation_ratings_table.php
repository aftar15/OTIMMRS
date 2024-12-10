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
        Schema::create('accommodation_ratings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('accommodation_id');
            $table->uuid('tourist_id');
            $table->decimal('rating', 3, 1);
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('accommodation_id')
                ->references('id')
                ->on('accommodations')
                ->onDelete('cascade');

            $table->foreign('tourist_id')
                ->references('id')
                ->on('tourists')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodation_ratings');
    }
};
