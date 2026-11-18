<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['enquiries', 'job_applications'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                // Why the spam filter held or flagged this submission.
                $table->string('spam_reason')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['enquiries', 'job_applications'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn('spam_reason'));
        }
    }
};
