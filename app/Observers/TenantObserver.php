<?php

namespace App\Observers;

use App\Mail\UserInvitation;
use App\Tenant;
use App\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TenantObserver
{
    /**
     * Handle the tenant "created" event.
     *
     * @param Tenant $tenant
     * @return void
     */
    public function created(Tenant $tenant)
    {
        $str = Str::random();
        if ($tenant->id !== '' && $tenant->id !== null) {
            $user = new User;
            $user->name = $tenant->name;
            $user->email = $tenant->email;
            $user->password = Hash::make($str);
            $user->role = Config::get('app.ROLE_TENANT');
            $user->isDefault = true;
            $user->status = $tenant->getAttributes()['status'];
            $user->tenant_id = $tenant->id;
            $user->login_token = Hash::make($tenant->id . $tenant->email);
            request()->request->add(['password_original' => $str]);
            $user->save();
        }
    }

    /**
     * Handle the tenant "updated" event.
     *
     * @param Tenant $tenant
     * @return void
     */
    public function updated(Tenant $tenant)
    {
        if ($tenant->isDirty('status') && $tenant->getAttributes()['status'] !== Tenant::STATUS_PUBLISH) {
            User::query()->where('tenant_id', $tenant->getAttributes()['id'])->update(['status' => Tenant::STATUS_DRAFT]);
        }
    }

    /**
     * Handle the tenant "deleted" event.
     *
     * @param Tenant $tenant
     * @return void
     */
    public function deleted(Tenant $tenant)
    {
        //
    }

    /**
     * Handle the tenant "restored" event.
     *
     * @param Tenant $tenant
     * @return void
     */
    public function restored(Tenant $tenant)
    {
        //
    }

    /**
     * Handle the tenant "force deleted" event.
     *
     * @param Tenant $tenant
     * @return void
     */
    public function forceDeleted(Tenant $tenant)
    {
        //
    }
}
