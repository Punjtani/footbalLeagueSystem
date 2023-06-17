<?php

namespace App\Observers;

use App\Association;
use Illuminate\Support\Facades\DB;

class AssociationObserver
{
    /**
     * Handle the association "created" event.
     *
     * @param Association $association
     * @return void
     */
    public function created(Association $association)
    {
        //
    }

    /**
     * Handle the association "updated" event.
     *
     * @param Association $association
     * @return void
     */
    public function updated(Association $association)
    {
        $model_names_that_have_single_association = array('Tournament');
        $model_names_that_have_multiple_associations = array('Team');
        if ($association->isDirty('status')) {
            foreach ($model_names_that_have_single_association as $model) {
                $model = trim('App\ ') . $model;
                $model::where('association_id', $association->getAttributes()['id'])->update(['status' => $association->getAttributes()['status']]);
            }
//            foreach ($model_names_that_have_multiple_associations as $model) {
//                $model = trim('App\ ') . $model;
//                $model::where('associations.association_id',$association->getAttributes()['id'])->update(['associations.$.name' => $association->getAttributes()['name'], 'associations.$.status' => $association->getAttributes()['status']]);
//            }
        }
    }

    /**
     * Handle the association "deleted" event.
     *
     * @param Association $association
     * @return void
     */
    public function deleted(Association $association)
    {
        //
    }

    /**
     * Handle the association "restored" event.
     *
     * @param Association $association
     * @return void
     */
    public function restored(Association $association)
    {
        //
    }

    /**
     * Handle the association "force deleted" event.
     *
     * @param Association $association
     * @return void
     */
    public function forceDeleted(Association $association)
    {
        //
    }
}
