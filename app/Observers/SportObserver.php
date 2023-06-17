<?php

namespace App\Observers;

use App\Sport;
use App\Tenant;

class SportObserver
{
    /**
     * Handle the sport "created" event.
     *
     * @param Sport $sport
     * @return void
     */
    public function created(Sport $sport)
    {
        //
    }

    /**
     * Handle the sport "updated" event.
     *
     * @param Sport $sport
     * @return void
     */
    public function updated(Sport $sport)
    {
        if ( $sport->isDirty('status'))
        {
            Tenant::query()->where('sport_id', $sport->id)->update(['status' => $sport->getAttributes()['status']]);
        }
    }

    /**
     * Handle the sport "deleted" event.
     *
     * @param Sport $sport
     * @return void
     */
    public function deleted(Sport $sport)
    {
        //
    }

    /**
     * Handle the sport "restored" event.
     *
     * @param Sport $sport
     * @return void
     */
    public function restored(Sport $sport)
    {
        //
    }

    /**
     * Handle the sport "force deleted" event.
     *
     * @param Sport $sport
     * @return void
     */
    public function forceDeleted(Sport $sport)
    {
        //
    }
}
