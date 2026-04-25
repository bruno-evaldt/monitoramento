<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Reading;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReadingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Reading');
    }

    public function view(AuthUser $authUser, Reading $reading): bool
    {
        return $authUser->can('View:Reading');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Reading');
    }

    public function update(AuthUser $authUser, Reading $reading): bool
    {
        return $authUser->can('Update:Reading');
    }

    public function delete(AuthUser $authUser, Reading $reading): bool
    {
        return $authUser->can('Delete:Reading');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Reading');
    }

    public function restore(AuthUser $authUser, Reading $reading): bool
    {
        return $authUser->can('Restore:Reading');
    }

    public function forceDelete(AuthUser $authUser, Reading $reading): bool
    {
        return $authUser->can('ForceDelete:Reading');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Reading');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Reading');
    }

    public function replicate(AuthUser $authUser, Reading $reading): bool
    {
        return $authUser->can('Replicate:Reading');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Reading');
    }

}