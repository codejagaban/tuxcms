<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'pages',
            'page_sections',
            'menus',
            'menu_items',
            'forms',
            'form_submissions',
            'settings',
            'seos',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreignId('tenant_id')
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();

                $blueprint->index('tenant_id');
            });
        }

        // Make settings key unique per tenant, not globally
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['key']);
            $table->unique(['tenant_id', 'key']);
        });
    }

    public function down(): void
    {
        $tables = [
            'pages',
            'page_sections',
            'menus',
            'menu_items',
            'forms',
            'form_submissions',
            'settings',
            'seos',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropForeign(['tenant_id']);
                $blueprint->dropColumn('tenant_id');
            });
        }
    }
};
