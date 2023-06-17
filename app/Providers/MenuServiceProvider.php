<?php

namespace App\Providers;

use Illuminate\Console\Application as ConsoleApplication;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Settings;
use Spatie\Permission\Models\Role;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // get all data from menu.json file
        $menuJson = file_get_contents(base_path('resources/json/menu.json'));
        $menuData = json_decode($menuJson);
        $whatsappContacts = [];
        $settings =[];
        if(!app()->runningInConsole()){
            $settings = Settings::find(1);

            try {
                $role = Role::findByName('Sales person');
                $whatsappContacts = $role->users;
            }catch(\Spatie\Permission\Exceptions\RoleDoesNotExist $e){}

        }
        // Share all menuData to all the views
        View::share(['menuData' => $menuData, 'settings' => $settings, 'whatsappContacts' => $whatsappContacts]);
    }
}
