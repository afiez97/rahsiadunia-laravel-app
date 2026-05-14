<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('contact_name');
            $table->enum('direction', ['i_owe', 'they_owe']);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->string('payment_method')->default('cash'); // cash/maybank/tng/duitnow/splitwise/other
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'partial', 'settled'])->default('pending');

            // Due date & warning settings
            $table->unsignedTinyInteger('due_day_of_month')->nullable(); // 1-31
            $table->json('warning_days')->nullable(); // e.g. [7,3,1]
            $table->boolean('warn_on_due_date')->default(true);
            $table->boolean('warn_if_overdue')->default(true);

            // Installment settings
            $table->boolean('is_installment')->default(false);
            $table->unsignedSmallInteger('installment_count')->nullable();
            $table->enum('installment_frequency', ['monthly', 'weekly', 'custom'])->default('monthly');
            $table->date('first_installment_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
