<?php

namespace App\Observers;

use App\Stadium;
use App\DynamoModels\Stadium as DynamoStadium;
use BaoPham\DynamoDb\Facades\DynamoDb;
use Exception;

class StadiumObserver
{
    /**
     * Handle the stadium "created" event.
     *
     * @param Stadium $stadium
     * @return void
     */
    public function created(Stadium $stadium)
    {

    }

    /**
     * Handle the stadium "updated" event.
     *
     * @param Stadium $stadium
     * @return void
     */
    public function updated(Stadium $stadium)
    {

    }

    /**
     * Handle the stadium "deleted" event.
     *
     * @param Stadium $stadium
     * @return void
     */
    public function deleted(Stadium $stadium)
    {

    }

    /**
     * Handle the stadium "restored" event.
     *
     * @param Stadium $stadium
     * @return void
     */
    public function restored(Stadium $stadium)
    {
        //
    }

    /**
     * Handle the stadium "force deleted" event.
     *
     * @param Stadium $stadium
     * @return void
     */
    public function forceDeleted(Stadium $stadium)
    {
        //
    }


}
