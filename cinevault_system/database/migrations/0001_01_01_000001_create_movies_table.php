<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('genre');
            $table->integer('year');
            $table->string('director')->nullable();
            $table->integer('duration')->nullable()->comment('in minutes');
            $table->string('rating')->default('PG');
            $table->text('description')->nullable();
            $table->string('poster_icon')->default('fa-solid fa-film')->comment('font awesome icon for display');
            $table->string('poster_path')->nullable()->comment('uploaded image path');
            $table->decimal('price_per_day', 8, 2)->default(50.00);
            $table->enum('status', ['available', 'rented', 'reserved', 'inactive'])->default('available');
            $table->unsignedInteger('copies')->default(1);
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};