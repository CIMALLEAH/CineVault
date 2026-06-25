<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add rental_type, price_per_screening, duration_days to rentals
        Schema::table('rentals', function (Blueprint $table) {
            $table->enum('rental_type', ['screening', 'days', 'weeks'])->default('days')->after('days');
            $table->decimal('price_per_screening', 8, 2)->nullable()->after('rental_type');
            // rename price_per_day conceptually but keep column; we'll add price_base instead
            $table->decimal('price_base', 8, 2)->nullable()->after('price_per_screening');
        });

        // Add available_copies tracking, price_per_screening to movies
        Schema::table('movies', function (Blueprint $table) {
            $table->decimal('price_per_screening', 8, 2)->default(0)->after('price_per_day');
            $table->decimal('price_per_week', 8, 2)->default(0)->after('price_per_screening');
            $table->unsignedInteger('available_copies')->default(1)->after('copies');
        });
    }

    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn(['rental_type', 'price_per_screening', 'price_base']);
        });
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn(['price_per_screening', 'price_per_week', 'available_copies']);
        });
    }
};