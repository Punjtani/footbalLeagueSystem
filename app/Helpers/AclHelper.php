<?php


namespace App\Helpers;


use Spatie\Permission\Models\Permission;

class AclHelper
{
    public static $BASIC_PERMISSIONS_ARRAY = ['Create', 'Read', 'Update', 'Delete'];
    public static $SPECIAL_PERMISSIONS_ARRAY = [];
    public static $MODULES_ARRAY = array('Dashboard', 'Users', 'Roles', 'Sports', 'Bookings', 'Discounts', 'Leagues', "Matches", 'Teams', 'Locations', 'LocationFacilities', 'Sponsors', 'BookingRules', "MembershipLevels", "StaticPages", 'Settings', 'Subscription');
    public static $ROLES_ARRAY = array('SuperAdmin', 'Admin');


    public static function getPermissionsForRole($role)
    {
        $permissions = array();
        $i = 0;
        switch ($role) {
            // Super Admin
            case AclHelper::$ROLES_ARRAY[0]:
                $permissions = Permission::all();
                break;
            // Creator
            case AclHelper::$ROLES_ARRAY[1]:
                foreach (Permission::all() as $permission) {
                    $nameArr = explode(".", $permission->name);
                    // Create & READ
                    if (count($nameArr) >= 2 && ($nameArr[1] == AclHelper::$BASIC_PERMISSIONS_ARRAY[0] || $nameArr[1] == AclHelper::$BASIC_PERMISSIONS_ARRAY[1])) {
                        $permissions[$i++] = $permission;
                    }
                }
                break;
            // Editor
            case AclHelper::$ROLES_ARRAY[2]:
                foreach (Permission::all() as $permission) {
                    $nameArr = explode(".", $permission->name);
                    // READ, UPDATE, DELETE
                    if (count($nameArr) >= 2 && ($nameArr[1] == AclHelper::$BASIC_PERMISSIONS_ARRAY[1] || $nameArr[1] == AclHelper::$BASIC_PERMISSIONS_ARRAY[2] || $nameArr[1] == AclHelper::$BASIC_PERMISSIONS_ARRAY[3])) {
                        $permissions[$i++] = $permission;
                    }
                }
                break;
        }
        return $permissions;
    }

}
