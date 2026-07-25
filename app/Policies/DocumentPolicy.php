<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
    }

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view documents') || $user->isAdmin();
    }

    public function view(User $user, Document $document)
    {
        if (!$user->canAccessCompany($document->company_id)) {
            return false;
        }
        return $user->hasPermissionTo('view documents') || $user->isAdmin();
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('upload documents') || $user->isAdmin();
    }

    public function update(User $user, Document $document)
    {
        if (!$user->canAccessCompany($document->company_id)) {
            return false;
        }
        return $user->hasPermissionTo('edit documents') || $user->isAdmin();
    }

    public function delete(User $user, Document $document)
    {
        if (!$user->canAccessCompany($document->company_id)) {
            return false;
        }
        return $user->hasPermissionTo('delete documents') || $user->isAdmin();
    }

    public function restore(User $user, Document $document)
    {
        if (!$user->canAccessCompany($document->company_id)) {
            return false;
        }
        return $user->hasPermissionTo('restore documents') || $user->isAdmin();
    }

    public function forceDelete(User $user, Document $document)
    {
        return $user->isSuperAdmin();
    }

    public function download(User $user, Document $document)
    {
        if (!$user->canAccessCompany($document->company_id)) {
            return false;
        }
        return $user->hasPermissionTo('download documents') || $user->isAdmin();
    }
}
