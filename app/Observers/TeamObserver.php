<?php

namespace App\Observers;

use App\Club;
use App\Team;
use App\Http\Controllers\ClubController;

class TeamObserver
{
    /**
     * Handle the team "created" event.
     *
     * @param Team $team
     * @return void
     */
    public function created(Team $team)
    {

    }

    /**
     * Handle the team "updated" event.
     *
     * @param Team $team
     * @return void
     */
    public function updated(Team $team)
    {

    }

    /**
     * Handle the team "deleted" event.
     *
     * @param Team $team
     * @return void
     */
    public function deleted(Team $team)
    {
        //
    }

    /**
     * Handle the team "restored" event.
     *
     * @param Team $team
     * @return void
     */
    public function restored(Team $team)
    {
        //
    }

    /**
     * Handle the team "force deleted" event.
     *
     * @param Team $team
     * @return void
     */
    public function forceDeleted(Team $team)
    {
        //
    }
}
