<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_number')->unique();
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('livreur_id')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->text('shipping_address')->nullable();
            $table->text('delivery_address')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('status')->default('pending'); // pending, preparation, ready, assigned, in_transit, delivered, failed, cancelled
            $table->string('otp_code', 10)->nullable();
            $table->string('proof_photo')->nullable();
            $table->text('proof_signature')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('synced')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
