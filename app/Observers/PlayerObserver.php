<?php

namespace App\Observers;

use App\Player;
use App\DynamoModels\Player as DynamoPlayer;
use BaoPham\DynamoDb\Facades\DynamoDb;
use Exception;

class PlayerObserver
{
    /**
     * Handle the player "created" event.
     *
     * @param Player $player
     * @return void
     */
    public function created(Player $player)
    {

    }

    /**
     * Handle the player "updated" event.
     *
     * @param Player $player
     * @return void
     */
    public function updated(Player $player)
    {

    }

    /**
     * Handle the player "deleted" event.
     *
     * @param Player $player
     * @return void
     */
    public function deleted(Player $player)
    {

    }

    /**
     * Handle the player "restored" event.
     *
     * @param Player $player
     * @return void
     */
    public function restored(Player $player)
    {
        //
    }

    /**
     * Handle the player "force deleted" event.
     *
     * @param Player $player
     * @return void
     */
    public function forceDeleted(Player $player)
    {
        //
    }

}
