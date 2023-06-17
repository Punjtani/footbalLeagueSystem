<?php

namespace App\Observers;

use App\Club;
use App\DynamoModels\Club as DynamoClub;
use App\Membership;
use App\MembershipLevel;
use App\Stadium;
use BaoPham\DynamoDb\Facades\DynamoDb;
use Exception;

class ClubObserver
{
    /**
     * Handle the club "created" event.
     *
     * @param Club $club
     * @return void
     */
    public function created(Club $club)
    {

//        $firstMembershipLevel = MembershipLevel::where('weight',1)->first();
//        $newMembership = new Membership();
//        $newMembership->club_id = $club->id;
//        $newMembership->membership_level_id = $firstMembershipLevel->id;
//        $newMembership->status = 1;
//        $newMembership->save();

    }

    /**
     * Handle the club "updated" event.
     *
     * @param Club $club
     * @return void
     */
    public function updated(Club $club)
    {
    }

    /**
     * Handle the club "deleted" event.
     *
     * @param Club $club
     * @return void
     */
    public function deleted(Club $club)
    {

    }

    /**
     * Handle the club "restored" event.
     *
     * @param Club $club
     * @return void
     */
    public function restored(Club $club)
    {
        //
    }

    /**
     * Handle the club "force deleted" event.
     *
     * @param Club $club
     * @return void
     */
    public function forceDeleted(Club $club)
    {
        //
    }

}
