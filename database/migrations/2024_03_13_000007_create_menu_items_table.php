<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            $table->string('title');
            $table->text('url')->nullable();
            $table->string('target')->default('_self');
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->enum('type', ['custom', 'post', 'category', 'page'])->default('custom');
            $table->nullableMorphs('linkable');
            $table->timestamps();

            $table->index('menu_id');
            $table->index('parent_id');
            $table->index('order');
            $table->index(['linkable_id', 'linkable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
