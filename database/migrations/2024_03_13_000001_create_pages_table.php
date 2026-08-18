<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('template')->default('default');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->foreignId('parent_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->integer('order')->default(0);
            $table->boolean('is_homepage')->default(false);
            $table->json('custom_fields')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index('template');
            $table->index('parent_id');
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
