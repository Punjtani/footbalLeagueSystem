<?php

namespace App\Observers;

use App\Association;
use App\tournament;
use App\DynamoModels\Tournament as DynamoTournament;
use BaoPham\DynamoDb\Facades\DynamoDb;
use Exception;

class TournamentObserver
{
    /**
     * Handle the tournament "created" event.
     *
     * @param tournament $tournament
     * @return void
     */
    public function created(tournament $tournament): void
    {

    }

    /**
     * Handle the tournament "updated" event.
     *
     * @param tournament $tournament
     * @return void
     */
    public function updated(tournament $tournament): void
    {

    }


    /**
     * Handle the tournament "deleted" event.
     *
     * @param tournament $tournament
     * @return void
     */
    public function deleted(tournament $tournament): void
    {

    }

    /**
     * Handle the tournament "restored" event.
     *
     * @param tournament $tournament
     * @return void
     */
    public function restored(tournament $tournament): void
    {
        //
    }

    /**
     * Handle the tournament "force deleted" event.
     *
     * @param tournament $tournament
     * @return void
     */
    public function forceDeleted(tournament $tournament): void
    {
        //
    }


}
