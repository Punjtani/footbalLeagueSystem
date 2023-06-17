<?php

namespace App\Observers;

use App\Mail\UserInvitation;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserObserver
{
    /**
     * Handle the user "created" event.
     *
     * @param User $user
     * @return void
     */
    public function created(User $user)
    {
         $user->password = request()->get('password_original');
         if ($user->status == User::STATUS_PUBLISH){
             Mail::to($user->email)->send(new UserInvitation($user));
         }
    }

    /**
     * Handle the user "updated" event.
     *
     * @param User $user
     * @return void
     */
    public function updated(User $user)
    {
        // if ($user->isDirty(['status']) && $user->status == User::STATUS_PUBLISH && $user->getAttribute('email_verified_at') === NULL) {
        //     Mail::to($user->getAttribute('email'))->send(new UserInvitation($user));
        //     $user->email_verified_at = Carbon::now();
        //     $user->password = Hash::make($user->getAttribute('password'));
        //     $user->save();
        // }
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param User $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the user "restored" event.
     *
     * @param User $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @param User $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
