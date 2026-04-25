<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ValveLog;
use Illuminate\Auth\Access\HandlesAuthorization;

class ValveLogPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ValveLog');
    }

    public function view(AuthUser $authUser, ValveLog $valveLog): bool
    {
        return $authUser->can('View:ValveLog');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ValveLog');
    }

    public function update(AuthUser $authUser, ValveLog $valveLog): bool
    {
        return $authUser->can('Update:ValveLog');
    }

    public function delete(AuthUser $authUser, ValveLog $valveLog): bool
    {
        return $authUser->can('Delete:ValveLog');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ValveLog');
    }

    public function restore(AuthUser $authUser, ValveLog $valveLog): bool
    {
        return $authUser->can('Restore:ValveLog');
    }

    public function forceDelete(AuthUser $authUser, ValveLog $valveLog): bool
    {
        return $authUser->can('ForceDelete:ValveLog');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ValveLog');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ValveLog');
    }

    public function replicate(AuthUser $authUser, ValveLog $valveLog): bool
    {
        return $authUser->can('Replicate:ValveLog');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ValveLog');
    }

}