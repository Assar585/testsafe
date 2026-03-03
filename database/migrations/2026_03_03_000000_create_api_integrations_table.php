<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('api_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50)->index();        // logistics, insurance, banking, ai, customs, analytics
            $table->string('service_name', 100)->index();   // e.g. "dhl", "openai", "sberbank"
            $table->string('label', 150);                   // Human-friendly name shown in UI
            $table->text('api_key')->nullable();            // Encrypted
            $table->text('api_secret')->nullable();         // Encrypted
            $table->string('api_url', 500)->nullable();     // Base endpoint URL
            $table->text('extra_data')->nullable();         // JSON: additional fields (account_id, region, etc.)
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['category', 'service_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_integrations');
    }
};
