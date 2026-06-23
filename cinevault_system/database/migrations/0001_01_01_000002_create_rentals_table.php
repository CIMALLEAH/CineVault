<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()
                  ->comment('registered user who rented, null if walk-in');
            $table->string('customer_name');
            $table->string('customer_contact')->nullable();
            $table->date('rental_date');
            $table->date('due_date');
            $table->date('returned_date')->nullable();
            $table->integer('days');
            $table->decimal('price_per_day', 8, 2);
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_method', ['cash', 'gcash', 'card'])->default('cash');
            $table->string('payment_reference')->nullable()->comment('GCash ref or card last4');
            $table->enum('status', ['active', 'returned', 'overdue'])->default('active');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
 