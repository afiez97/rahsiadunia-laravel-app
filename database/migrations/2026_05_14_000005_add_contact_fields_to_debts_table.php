<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('debts', function (Blueprint $table) {
            $table->string('contact_phone')->nullable()->after('contact_name');
            $table->string('contact_telegram_chat_id')->nullable()->after('contact_phone');
            $table->string('invite_token', 64)->nullable()->unique()->after('contact_telegram_chat_id');
            $table->timestamp('contact_linked_at')->nullable()->after('invite_token');
        });
    }

    public function down(): void
    {
        Schema::table('debts', function (Blueprint $table) {
            $table->dropColumn(['contact_phone', 'contact_telegram_chat_id', 'invite_token', 'contact_linked_at']);
        });
    }
};
