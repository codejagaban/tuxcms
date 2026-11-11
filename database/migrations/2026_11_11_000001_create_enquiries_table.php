<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            // staff_request, registration or contact — decides the inbox
            // grouping and the email subject, nothing else.
            $table->string('type')->default('contact')->index();
            $table->string('name');
            $table->string('organisation')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message')->nullable();
            // Everything form-specific (roles, shifts, postcode…) so a new
            // field on a form never needs a schema change.
            $table->json('details')->nullable();
            $table->string('delivery_status')->default('pending');
            $table->timestamp('emailed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
