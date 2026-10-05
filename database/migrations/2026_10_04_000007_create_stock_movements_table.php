<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // in, out, adjustment, inventory
            $table->integer('quantity'); // positif ou négatif
            $table->integer('previous_stock');
            $table->integer('new_stock');
            $table->string('reason'); // reception, sale, damaged, expired, loss, return_supplier, inventory_adjustment
            $table->string('reference')->nullable();
            $table->text('comment')->nullable();
            $table->nullableMorphs('referenceable');
            $table->boolean('synced')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
