<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('location')->nullable();
            $table->string('job_type')->nullable();
            $table->string('hours')->nullable();
            $table->string('salary')->nullable();
            $table->string('employment_type')->nullable();
            $table->text('summary');
            $table->longText('description')->nullable();
            $table->json('responsibilities')->nullable();
            $table->json('essential')->nullable();
            $table->json('desirable')->nullable();
            $table->json('benefits')->nullable();
            $table->string('application_email')->nullable();
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->date('closes_at')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
