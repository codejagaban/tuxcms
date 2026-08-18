<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('key')->nullable();
            $table->string('type')->default('text');
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->json('data')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index(['page_id', 'order']);
            $table->index('type');
            $table->index('key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_sections');
    }
};
