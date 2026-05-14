<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debt_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debt_id')->constrained()->onDelete('cascade');
            $table->unsignedSmallInteger('installment_number');
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending');
            $table->string('proof_path')->nullable();
            $table->enum('proof_source', ['web', 'telegram'])->nullable();
            $table->string('telegram_message_id')->nullable();
            $table->text('notes')->nullable();
            $table->json('warning_sent_at')->nullable(); // {"7_days": "2025-06-21 08:00:00", ...}
            $table->date('snooze_until')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_installments');
    }
};
