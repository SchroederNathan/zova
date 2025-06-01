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
        Schema::table('storage_connections', function (Blueprint $table) {
            // S3 Configuration columns
            $table->string('s3_access_key')->nullable();
            $table->string('s3_secret_key')->nullable();
            $table->string('s3_region')->nullable();
            $table->string('s3_bucket')->nullable();
            $table->string('s3_endpoint')->nullable();
            $table->string('s3_url')->nullable();
            
            // GCS Configuration columns
            $table->string('gcs_project_id')->nullable();
            $table->text('gcs_key_file')->nullable(); // Path to key file
            $table->string('gcs_bucket')->nullable();
            
            // NAS Configuration columns
            $table->string('nas_root_path')->nullable();
            
            // Add indexes for frequently queried fields
            $table->index(['provider', 's3_region']);
            $table->index(['provider', 's3_bucket']);
            $table->index(['provider', 'gcs_bucket']);
        });
        
        // Remove the old JSON config column
        Schema::table('storage_connections', function (Blueprint $table) {
            $table->dropColumn('config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('storage_connections', function (Blueprint $table) {
            // Drop the new columns
            $table->dropIndex(['storage_connections_provider_s3_region_index']);
            $table->dropIndex(['storage_connections_provider_s3_bucket_index']);
            $table->dropIndex(['storage_connections_provider_gcs_bucket_index']);
            
            $table->dropColumn([
                's3_access_key',
                's3_secret_key', 
                's3_region',
                's3_bucket',
                's3_endpoint',
                's3_url',
                'gcs_project_id',
                'gcs_key_file',
                'gcs_bucket',
                'nas_root_path'
            ]);
        });
        
        // Re-add the JSON config column
        Schema::table('storage_connections', function (Blueprint $table) {
            $table->json('config');
        });
    }
};
