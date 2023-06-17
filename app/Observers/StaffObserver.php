<?php

namespace App\Observers;

use App\Staff;
use App\DynamoModels\Staff as DynamoStaff;
use App\Team;
use BaoPham\DynamoDb\Facades\DynamoDb;
use Exception;

class StaffObserver
{
    /**
     * Handle the staff "created" event.
     *
     * @param Staff $staff
     * @return void
     */
    public function created(Staff $staff)
    {

    }

    /**
     * Handle the staff "updated" event.
     *
     * @param Staff $staff
     * @return void
     */
    public function updated(Staff $staff)
    {

    }

    /**
     * Handle the staff "deleted" event.
     *
     * @param Staff $staff
     * @return void
     */
    public function deleted(Staff $staff)
    {

    }

    /**
     * Handle the staff "restored" event.
     *
     * @param Staff $staff
     * @return void
     */
    public function restored(Staff $staff)
    {
        //
    }

    /**
     * Handle the staff "force deleted" event.
     *
     * @param Staff $staff
     * @return void
     */
    public function forceDeleted(Staff $staff)
    {
        //
    }


}
