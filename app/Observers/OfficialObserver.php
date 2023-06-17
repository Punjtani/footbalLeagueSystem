<?php

namespace App\Observers;

use App\DynamoModels\Staff as DynamoStaff;
use App\Official;
use BaoPham\DynamoDb\Facades\DynamoDb;
use Exception;

class OfficialObserver
{
    /**
     * Handle the official "created" event.
     *
     * @param Official $official
     * @return void
     */
    public function created(Official $official)
    {
    }

    /**
     * Handle the official "updated" event.
     *
     * @param Official $official
     * @return void
     */
    public function updated(Official $official)
    {

    }

    /**
     * Handle the official "deleted" event.
     *
     * @param Official $official
     * @return void
     */
    public function deleted(Official $official)
    {

    }

    /**
     * Handle the official "restored" event.
     *
     * @param Official $official
     * @return void
     */
    public function restored(Official $official)
    {
        //
    }

    /**
     * Handle the official "force deleted" event.
     *
     * @param Official $official
     * @return void
     */
    public function forceDeleted(Official $official)
    {
        //
    }
}
