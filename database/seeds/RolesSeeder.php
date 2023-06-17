<?php

use App\Helpers\AclHelper;
use App\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (AclHelper::$ROLES_ARRAY as $roleStr) {
            $role = Role::create(['name' => $roleStr]);
            $permissions = AclHelper::getPermissionsForRole($roleStr);
            $role->syncPermissions($permissions);
        }

        $user = User::findOrFail(1);
        $user->assignRole(Role::findById(1));

        $user = User::findOrFail(2);
        $user->assignRole(Role::findById(2));
    }
}
