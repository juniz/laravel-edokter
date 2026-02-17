<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::create('product_types', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('slug')->unique();
            $table->string('name');
            $table->enum('status', ['active', 'draft', 'archived'])->default('active');
            $table->string('icon')->nullable();
            $table->integer('display_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('display_order');
        });

        if (! Schema::hasColumn('products', 'product_type_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->char('product_type_id', 26)->nullable()->after('id');
                $table->index('product_type_id');
            });
        }

        if ($driver === 'mysql') {
            Schema::table('products', function (Blueprint $table) {
                $table->foreign('product_type_id')
                    ->references('id')
                    ->on('product_types')
                    ->restrictOnDelete();
            });
        }

        if (Schema::hasColumn('products', 'type')) {
            $existingTypes = DB::table('products')
                ->select('type')
                ->distinct()
                ->pluck('type')
                ->filter(fn ($t) => is_string($t) && $t !== '')
                ->values();

            foreach ($existingTypes as $typeSlug) {
                $existing = DB::table('product_types')->where('slug', $typeSlug)->first();

                if (! $existing) {
                    $id = (string) Str::ulid();
                    [$name, $icon, $displayOrder, $metadata] = match ($typeSlug) {
                        'hosting_shared' => ['Shared Hosting', 'HardDrive', 1, [
                            'color' => 'text-blue-600',
                            'bgColor' => 'from-blue-500 to-cyan-500',
                            'gradient' => 'from-blue-500/10 to-cyan-500/10',
                        ]],
                        'vps' => ['VPS', 'Server', 2, [
                            'color' => 'text-purple-600',
                            'bgColor' => 'from-purple-500 to-pink-500',
                            'gradient' => 'from-purple-500/10 to-pink-500/10',
                        ]],
                        'addon' => ['Addon', 'Package', 3, [
                            'color' => 'text-emerald-600',
                            'bgColor' => 'from-emerald-500 to-green-500',
                            'gradient' => 'from-emerald-500/10 to-green-500/10',
                        ]],
                        'app' => ['Aplikasi (SaaS)', 'LayoutGrid', 4, [
                            'color' => 'text-indigo-600',
                            'bgColor' => 'from-indigo-500 to-violet-500',
                            'gradient' => 'from-indigo-500/10 to-violet-500/10',
                        ]],
                        'domain' => ['Domain', 'Globe', 5, [
                            'color' => 'text-orange-600',
                            'bgColor' => 'from-orange-500 to-amber-500',
                            'gradient' => 'from-orange-500/10 to-amber-500/10',
                        ]],
                        default => [Str::headline($typeSlug), 'LayoutGrid', 99, null],
                    };

                    DB::table('product_types')->insert([
                        'id' => $id,
                        'slug' => $typeSlug,
                        'name' => $name,
                        'status' => 'active',
                        'icon' => $icon,
                        'display_order' => $displayOrder,
                        'metadata' => $metadata ? json_encode($metadata) : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $typeId = DB::table('product_types')->where('slug', $typeSlug)->value('id');
                if ($typeId) {
                    DB::table('products')
                        ->where('type', $typeSlug)
                        ->update(['product_type_id' => $typeId]);
                }
            }

            $unknownType = DB::table('products')->whereNull('product_type_id')->exists();
            if ($unknownType) {
                $other = DB::table('product_types')->where('slug', 'other')->first();
                if (! $other) {
                    $otherId = (string) Str::ulid();
                    DB::table('product_types')->insert([
                        'id' => $otherId,
                        'slug' => 'other',
                        'name' => 'Other',
                        'status' => 'active',
                        'icon' => 'LayoutGrid',
                        'display_order' => 999,
                        'metadata' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $otherId = DB::table('product_types')->where('slug', 'other')->value('id');
                DB::table('products')->whereNull('product_type_id')->update(['product_type_id' => $otherId]);
            }
        }

        if ($driver === 'mysql' && Schema::hasColumn('products', 'type')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex(['type']);
                $table->dropColumn('type');
            });
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (! Schema::hasColumn('products', 'type')) {
            Schema::table('products', function (Blueprint $table) {
                $table->enum('type', ['hosting_shared', 'vps', 'addon', 'domain'])->default('hosting_shared')->after('id');
                $table->index('type');
            });
        }

        if (Schema::hasColumn('products', 'product_type_id')) {
            $typeMap = DB::table('product_types')->select('id', 'slug')->pluck('slug', 'id');
            $products = DB::table('products')->select('id', 'product_type_id')->get();
            foreach ($products as $p) {
                $slug = $typeMap[$p->product_type_id] ?? 'hosting_shared';
                DB::table('products')->where('id', $p->id)->update(['type' => $slug]);
            }

            if ($driver === 'mysql') {
                Schema::table('products', function (Blueprint $table) {
                    $table->dropForeign(['product_type_id']);
                });
            }

            if ($driver === 'mysql') {
                Schema::table('products', function (Blueprint $table) {
                    $table->dropIndex(['product_type_id']);
                    $table->dropColumn('product_type_id');
                });
            }
        }

        Schema::dropIfExists('product_types');
    }
};
