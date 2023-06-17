<?php

namespace App\Observers;

use App\PointsTable;

class PointsTableObserver
{
    /**
     * Handle the points table "created" event.
     *
     * @param  \App\PointsTable  $pointsTable
     * @return void
     */
    public function created(PointsTable $pointsTable)
    {
        //
    }

    /**
     * Handle the points table "updated" event.
     *
     * @param  \App\PointsTable  $pointsTable
     * @return void
     */
    public function updated(PointsTable $pointsTable)
    {
        //
    }

    /**
     * Handle the points table "deleted" event.
     *
     * @param  \App\PointsTable  $pointsTable
     * @return void
     */
    public function deleted(PointsTable $pointsTable)
    {
        //
    }

    /**
     * Handle the points table "restored" event.
     *
     * @param  \App\PointsTable  $pointsTable
     * @return void
     */
    public function restored(PointsTable $pointsTable)
    {
        //
    }

    /**
     * Handle the points table "force deleted" event.
     *
     * @param  \App\PointsTable  $pointsTable
     * @return void
     */
    public function forceDeleted(PointsTable $pointsTable)
    {
        //
    }
}
