<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique()->nullable();
            $table->string('inventory_number')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('general'); // general, category, product
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->date('inventory_date')->nullable();
            $table->string('status')->default('draft'); // draft, completed, validated, cancelled
            $table->text('notes')->nullable();
            $table->boolean('synced')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
