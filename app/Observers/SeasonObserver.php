<?php

namespace App\Observers;

use App\Player;
use App\Season;
use App\DynamoModels\Season as DynamoSeason;
use App\Team;
use App\Tournament;
use BaoPham\DynamoDb\Facades\DynamoDb;
use Exception;
use Illuminate\Support\Facades\DB;

class SeasonObserver
{
    /**
     * Handle the season "created" event.
     *
     * @param Season $season
     * @return void
     */
    public function created(Season $season)
    {

    }

    /**
     * Handle the season "updated" event.
     *
     * @param Season $season
     * @return void
     */
    public function updated(Season $season)
    {

    }

    /**
     * Handle the season "deleted" event.
     *
     * @param Season $season
     * @return void
     */
    public function deleted(Season $season)
    {

    }

    /**
     * Handle the season "restored" event.
     *
     * @param Season $season
     * @return void
     */
    public function restored(Season $season)
    {
        //
    }

    /**
     * Handle the season "force deleted" event.
     *
     * @param Season $season
     * @return void
     */
    public function forceDeleted(Season $season)
    {
        //
    }

}
