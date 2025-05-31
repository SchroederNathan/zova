<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('storage_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Something like "My S3 Bucket"
            $table->enum('provider', ['s3', 'gcs', 'nas']); // Storage provider type
            $table->json('config'); // Provider-specific configuration (credentials, endpoints, etc.)
            $table->boolean('is_active')->default(true); // Enable/disable connection
            $table->timestamp('last_synced_at')->nullable(); // Last time files were synced
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['user_id', 'is_active']);
            $table->index('provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_connections');
    }
};
