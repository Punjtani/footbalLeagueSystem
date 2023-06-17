<?php

namespace App\Observers;

use App\Fixture;
use App\DynamoModels\Fixture as DynamoFixture;
use App\MatchSquad;
use App\MatchStatistic;
use App\Player;
use App\Season;
use App\Stadium;
use App\Stage;
use App\Team;
use BaoPham\DynamoDb\Facades\DynamoDb;
use Exception;

class FixtureObserver
{
    /**
     * Handle the fixture "created" event.
     *
     * @param Fixture $fixture
     * @return void
     */
    public function created(Fixture $fixture)
    {

    }

    /**
     * Handle the fixture "updated" event.
     *
     * @param Fixture $fixture
     * @return void
     */
    public function updated(Fixture $fixture)
    {

    }

    /**
     * Handle the fixture "deleted" event.
     *
     * @param Fixture $fixture
     * @return void
     */
    public function deleted(Fixture $fixture)
    {
    }

    /**
     * Handle the fixture "restored" event.
     *
     * @param Fixture $fixture
     * @return void
     */
    public function restored(Fixture $fixture)
    {
        //
    }

    /**
     * Handle the fixture "force deleted" event.
     *
     * @param Fixture $fixture
     * @return void
     */
    public function forceDeleted(Fixture $fixture)
    {
        //
    }
}
