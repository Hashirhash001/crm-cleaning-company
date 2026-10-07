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
        Schema::table('branches', function (Blueprint $table) {
            $table->string('domain')->nullable()->after('code')
                  ->comment('Website domain for lead source mapping, e.g. ctree.co.in, bayleafclean.com');
        });

        // Auto-set domain values for known branches
        \Illuminate\Support\Facades\DB::table('branches')
            ->whereRaw('LOWER(code) = ?', ['ctree'])
            ->update(['domain' => 'ctree.co.in']);

        \Illuminate\Support\Facades\DB::table('branches')
            ->whereRaw('LOWER(code) = ?', ['bayleaf'])
            ->update(['domain' => 'bayleafclean.com']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('domain');
        });
    }
};
