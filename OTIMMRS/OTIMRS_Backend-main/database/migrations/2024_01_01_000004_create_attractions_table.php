<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attractions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description');
            $table->string('location');
            $table->string('category');
            $table->string('image_url');
            $table->integer('views')->default(0);
            $table->decimal('rating', 3, 1)->default(0);
            $table->decimal('latitude', 10, 6)->nullable();
            $table->decimal('longitude', 10, 6)->nullable();
            $table->json('contact_info')->nullable();
            $table->json('opening_hours')->nullable();
            $table->decimal('admission_fee', 10, 2)->nullable();
            $table->json('tags')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('map_source')->nullable();
            $table->string('contact_phone')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attractions');
    }
};
