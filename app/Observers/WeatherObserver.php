<?php

namespace App\Observers;

use App\Fixture;
use App\DynamoModels\Fixture as DynamoFixture;
use App\Season;
use App\Stage;
use App\Team;
use App\Weather;
use BaoPham\DynamoDb\Facades\DynamoDb;
use Exception;

class WeatherObserver
{
    /**
     * Handle the weather "created" event.
     *
     * @param  \App\Weather  $weather
     * @return void
     */
    public function created(Weather $weather)
    {

    }

    /**
     * Handle the weather "updated" event.
     *
     * @param  \App\Weather  $weather
     * @return void
     */
    public function updated(Weather $weather)
    {

    }

    /**
     * Handle the weather "deleted" event.
     *
     * @param  \App\Weather  $weather
     * @return void
     */
    public function deleted(Weather $weather)
    {
        //
    }

    /**
     * Handle the weather "restored" event.
     *
     * @param  \App\Weather  $weather
     * @return void
     */
    public function restored(Weather $weather)
    {
        //
    }

    /**
     * Handle the weather "force deleted" event.
     *
     * @param  \App\Weather  $weather
     * @return void
     */
    public function forceDeleted(Weather $weather)
    {
        //
    }

}
