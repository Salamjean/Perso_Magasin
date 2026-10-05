<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            if (! Schema::hasColumn('inventories', 'reference')) {
                $table->string('reference')->nullable()->after('id');
            }
            if (! Schema::hasColumn('inventories', 'type')) {
                $table->string('type')->default('general')->after('user_id');
            }
            if (Schema::hasColumn('inventories', 'inventory_number')) {
                $table->string('inventory_number')->nullable()->change();
            }
            if (Schema::hasColumn('inventories', 'inventory_date')) {
                $table->date('inventory_date')->nullable()->change();
            }
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_items', 'system_stock')) {
                $table->decimal('system_stock', 12, 2)->default(0)->after('product_id');
            }
            if (Schema::hasColumn('inventory_items', 'theoretical_stock')) {
                $table->decimal('theoretical_stock', 12, 2)->nullable()->change();
            }
            if (Schema::hasColumn('inventory_items', 'physical_stock')) {
                $table->decimal('physical_stock', 12, 2)->default(0)->change();
            }
            if (Schema::hasColumn('inventory_items', 'difference')) {
                $table->decimal('difference', 12, 2)->default(0)->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            if (Schema::hasColumn('inventories', 'reference')) {
                $table->dropColumn('reference');
            }
            if (Schema::hasColumn('inventories', 'type')) {
                $table->dropColumn('type');
            }
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_items', 'system_stock')) {
                $table->dropColumn('system_stock');
            }
        });
    }
};
