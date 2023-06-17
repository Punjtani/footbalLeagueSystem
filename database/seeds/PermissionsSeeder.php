<?php

use App\Helpers\AclHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('role_has_permissions')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        // Adding Basic permission that comes under each module
        foreach (AclHelper::$MODULES_ARRAY as $module) {
            foreach (AclHelper::$BASIC_PERMISSIONS_ARRAY as $permission) {
                Permission::create(['name' => $module.'.'.$permission]);
            }
        }
        // Special Permissions
        foreach (AclHelper::$SPECIAL_PERMISSIONS_ARRAY as $permission) {
            Permission::create(['name' => $permission]);
        }

    }
}
