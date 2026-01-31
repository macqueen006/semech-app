<?php

namespace App\Observers;

use Spatie\Permission\Models\Permission;

class PermissionObserver
{
    /**
     * Handle the Permission "created" event.
     */
    public function created(Permission $permission): void
    {
        activity('permissions')
            ->performedOn($permission)
            ->causedBy(auth()->user())
            ->withProperties([
                'name' => $permission->name,
                'guard_name' => $permission->guard_name,
            ])
            ->log('created');
    }

    /**
     * Handle the Permission "updated" event.
     */
    public function updated(Permission $permission): void
    {
        activity('permissions')
            ->performedOn($permission)
            ->causedBy(auth()->user())
            ->withProperties([
                'name' => $permission->name,
                'changes' => $permission->getChanges(),
            ])
            ->log('updated');
    }

    /**
     * Handle the Permission "deleted" event.
     */
    public function deleted(Permission $permission): void
    {
        activity('permissions')
            ->performedOn($permission)
            ->causedBy(auth()->user())
            ->withProperties([
                'name' => $permission->name,
            ])
            ->log('deleted');
    }

    /**
     * Handle the Permission "restored" event.
     */
    public function restored(Permission $permission): void
    {
        //
    }

    /**
     * Handle the Permission "force deleted" event.
     */
    public function forceDeleted(Permission $permission): void
    {
        //
    }
}
