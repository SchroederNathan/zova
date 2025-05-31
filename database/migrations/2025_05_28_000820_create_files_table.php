<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('storage_connection_id')->constrained()->onDelete('cascade');
            $table->string('path', 1000); // Full path to the file/folder
            $table->string('name'); // File/folder name
            $table->enum('type', ['file', 'folder']); // Type of item
            $table->bigInteger('size')->nullable(); // File size in bytes (null for folders)
            $table->string('mime_type')->nullable(); // MIME type for files
            $table->string('extension', 10)->nullable(); // File extension
            $table->string('parent_path', 1000)->nullable(); // Path to parent folder
            $table->timestamp('last_modified')->nullable(); // Last modification time from storage
            $table->string('etag')->nullable(); // ETag for change detection
            $table->json('metadata')->nullable(); // Additional provider-specific metadata
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('storage_connection_id');
            $table->index(['storage_connection_id', 'type']);
            $table->index('name');
            
            // Add indexes with length limits using raw SQL
        });
        
        // Add indexes with length limits after table creation
        DB::statement('ALTER TABLE files ADD INDEX files_path_index (path(191))');
        DB::statement('ALTER TABLE files ADD UNIQUE files_storage_connection_id_path_unique (storage_connection_id, path(191))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
