<?php

namespace App\Observers;

use Spatie\Permission\Models\Role;

class RoleObserver
{
    /**
     * Handle the Role "created" event.
     */
    public function created(Role $role): void
    {
        activity('roles')
            ->performedOn($role)
            ->causedBy(auth()->user())
            ->withProperties([
                'name' => $role->name,
                'guard_name' => $role->guard_name,
            ])
            ->log('created');
    }

    /**
     * Handle the Role "updated" event.
     */
    public function updated(Role $role): void
    {
        activity('roles')
            ->performedOn($role)
            ->causedBy(auth()->user())
            ->withProperties([
                'name' => $role->name,
                'changes' => $role->getChanges(),
            ])
            ->log('updated');
    }

    /**
     * Handle the Role "deleted" event.
     */
    public function deleted(Role $role): void
    {
        activity('roles')
            ->performedOn($role)
            ->causedBy(auth()->user())
            ->withProperties([
                'name' => $role->name,
            ])
            ->log('deleted');
    }

    /**
     * Handle the Role "restored" event.
     */
    public function restored(Role $role): void
    {
        //
    }

    /**
     * Handle the Role "force deleted" event.
     */
    public function forceDeleted(Role $role): void
    {
        //
    }
}
