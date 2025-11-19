<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // RDASH required fields
            $table->string('organization')->nullable()->after('email');
            $table->string('street_1')->nullable()->after('organization');
            $table->string('street_2')->nullable()->after('street_1');
            $table->string('city')->nullable()->after('street_2');
            $table->string('state')->nullable()->after('city');
            $table->char('country_code', 2)->default('ID')->after('state'); // ISO 3166-1 alpha-2
            $table->string('postal_code')->nullable()->after('country_code');
            $table->string('fax')->nullable()->after('phone');
            
            // Rename phone to voice untuk konsistensi dengan RDASH API
            // Tapi kita tetap pakai phone karena sudah ada data
            // voice akan diambil dari phone saat sync ke RDASH
            
            $table->index('country_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['country_code']);
            $table->dropColumn([
                'organization',
                'street_1',
                'street_2',
                'city',
                'state',
                'country_code',
                'postal_code',
                'fax',
            ]);
        });
    }
};

