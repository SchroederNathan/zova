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
            // Add only the missing NAS fields
            $table->string('nas_mount_point')->nullable(); // Local mount point
            $table->boolean('nas_is_mounted')->default(false); // Track mount status
            
            // Add index for mount tracking
            $table->index(['provider', 'nas_is_mounted']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('storage_connections', function (Blueprint $table) {
            $table->dropIndex(['storage_connections_provider_nas_is_mounted_index']);
            
            $table->dropColumn([
                'nas_mount_point',
                'nas_is_mounted'
            ]);
        });
    }
};
