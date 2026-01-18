<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop existing foreign key constraint if it exists
        if ($this->hasForeignKey('order_items', 'order_items_product_id_foreign')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropForeign(['product_id']);
            });
        }

        // Clean up invalid product_id references before making nullable
        // Set product_id to null for items that reference non-existent products
        $validProductIds = DB::table('products')->pluck('id')->toArray();

        if (! empty($validProductIds)) {
            DB::table('order_items')
                ->whereNotNull('product_id')
                ->whereNotIn('product_id', $validProductIds)
                ->update(['product_id' => null]);
        } else {
            // If no products exist, set all product_id to null
            DB::table('order_items')
                ->whereNotNull('product_id')
                ->update(['product_id' => null]);
        }

        Schema::table('order_items', function (Blueprint $table) {
            // Make product_id nullable
            $table->char('product_id', 26)->nullable()->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            // Re-add foreign key constraint
            // Note: Foreign key only applies to non-null values
            // Null values (for domains) are allowed without foreign key constraint
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraint if it exists
        if ($this->hasForeignKey('order_items', 'order_items_product_id_foreign')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropForeign(['product_id']);
            });
        }

        Schema::table('order_items', function (Blueprint $table) {
            // Make product_id not nullable again
            // First, we need to ensure no null values exist
            DB::table('order_items')->whereNull('product_id')->delete();

            $table->char('product_id', 26)->nullable(false)->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            // Re-add foreign key constraint
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
        });
    }

    /**
     * Check if foreign key exists
     */
    private function hasForeignKey(string $table, string $foreignKey): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        $result = $connection->selectOne(
            'SELECT COUNT(*) as count 
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = ? 
             AND CONSTRAINT_NAME = ?',
            [$database, $table, $foreignKey]
        );

        return $result->count > 0;
    }
};
