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
        Schema::table('users', function (Blueprint $table) {
            $table->string('aadhaar_number')->nullable()->after('phone');
            $table->string('pan_number')->nullable()->after('aadhaar_number');
            $table->text('address')->nullable()->after('pan_number');
            $table->string('nominee_name')->nullable()->after('address');
            $table->string('nominee_relation')->nullable()->after('nominee_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['aadhaar_number', 'pan_number', 'address', 'nominee_name', 'nominee_relation']);
        });
    }
};
