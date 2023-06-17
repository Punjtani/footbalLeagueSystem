<?php

namespace App\Observers;

use App\Fixture;
use App\MatchStatistic;
use App\DynamoModels\MatchStatistic as DynamoMatchStatistic;
use App\Team;
use BaoPham\DynamoDb\Facades\DynamoDb;
use Exception;

class MatchStatisticObserver
{
    /**
     * Handle the match statistic "created" event.
     *
     * @param MatchStatistic $matchStatistic
     * @return void
     */
    public function created(MatchStatistic $matchStatistic)
    {

    }

    /**
     * Handle the match statistic "updated" event.
     *
     * @param MatchStatistic $matchStatistic
     * @return void
     */
    public function updated(MatchStatistic $matchStatistic)
    {

    }

    /**
     * Handle the match statistic "deleted" event.
     *
     * @param MatchStatistic $matchStatistic
     * @return void
     */
    public function deleted(MatchStatistic $matchStatistic)
    {

    }

    /**
     * Handle the match statistic "restored" event.
     *
     * @param MatchStatistic $matchStatistic
     * @return void
     */
    public function restored(MatchStatistic $matchStatistic)
    {
        //
    }

    /**
     * Handle the match statistic "force deleted" event.
     *
     * @param MatchStatistic $matchStatistic
     * @return void
     */
    public function forceDeleted(MatchStatistic $matchStatistic)
    {
        //
    }

    private function dynamo_update(MatchStatistic $matchStatistic): void
    {

    }
}
