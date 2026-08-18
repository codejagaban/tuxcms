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
            $table->string('slug');
            // Materialized full path ('/', '/about', '/services/web-design').
            // Maintained by PageObserver; removes the getFullPath() N+1 and is
            // what the static builder writes files from.
            $table->string('path', 500)->nullable();
            $table->longText('content')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('template')->default('default');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->foreignId('parent_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->integer('order')->default(0);
            $table->boolean('is_homepage')->default(false);
            // Navigation is derived from the page tree — there is no menu builder.
            $table->boolean('show_in_nav')->default(true);
            $table->string('nav_label')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Slugs are unique per parent, not globally, so /services/design
            // and /about/design can coexist. `path` is the real guarantee.
            $table->unique(['parent_id', 'slug']);
            $table->index('path');
            $table->index(['status', 'published_at']);
            $table->index(['status', 'show_in_nav', 'order']);
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
