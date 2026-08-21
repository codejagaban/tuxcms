<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Owner record for media uploaded to the site library rather than attached to
 * a specific page. Spatie's `media` table requires a non-nullable owner, so
 * the library needs to be a real model rather than a null morph.
 *
 * Exactly one row ever exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_libraries', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_libraries');
    }
};
