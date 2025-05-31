<?php

namespace App\Policies;

use App\Models\StorageConnection;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StorageConnectionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own connections
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StorageConnection $storageConnection): bool
    {
        return $user->id === $storageConnection->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create connections
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StorageConnection $storageConnection): bool
    {
        return $user->id === $storageConnection->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StorageConnection $storageConnection): bool
    {
        return $user->id === $storageConnection->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, StorageConnection $storageConnection): bool
    {
        return $user->id === $storageConnection->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, StorageConnection $storageConnection): bool
    {
        return $user->id === $storageConnection->user_id;
    }
}
