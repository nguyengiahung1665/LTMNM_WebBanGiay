<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('gender', 10)->nullable()->after('phone');
            $table->string('province_code', 10)->nullable()->after('address');
            $table->string('province_name')->nullable()->after('province_code');
            $table->string('ward_code', 10)->nullable()->after('province_name');
            $table->string('ward_name')->nullable()->after('ward_code');
            $table->string('address_detail')->nullable()->after('ward_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['gender', 'province_code', 'province_name', 'ward_code', 'ward_name', 'address_detail']);
        });
    }
};
