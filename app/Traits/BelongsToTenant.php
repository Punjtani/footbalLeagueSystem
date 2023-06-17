<?php

namespace App\Traits;

use App\AdminLog;
use App\Sport;
use App\Tenant;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{

    /**
     * Boot the BelongsToTenant trait for a model.
     *
     * @return void
     */
    protected static function bootBelongsToTenant()
    {
//        $classes_with_no_tenant = array(User::class, Tenant::class, AdminLog::class, Sport::class);
//        static::saving(static function ($model) use ($classes_with_no_tenant) {
//            if (in_array(get_class($model) , $classes_with_no_tenant)) {
//                return;
//            }
//            $model->tenant_id = request()->tenant_id;
//        });

//        static::addGlobalScope('tenant_id', static function (Builder $builder) use ($classes_with_no_tenant) {
//            if (in_array(request()->model_name, $classes_with_no_tenant, true)) {
//                return;
//            }
//            if (request()->isTenant === 'true') {
//                $builder->where('tenant_id', request()->tenant_id);
//            }
//        });
    }

    /**
     * initialize the BelongsToTenant trait for a model.
     *
     * @return void
     */
    public function initializeBelongsToTenant()
    {
//        $this->addHidden('tenant_id');
    }

    public function curlRequest($url, $data)
    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));


        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $err = curl_error($ch);
        // Execute the POST request
        $result = curl_exec($ch);

        curl_close($ch);


        if ($err != '') {
            $response = [
                'status' => false,
                'data' => $err
            ];
            return $response;
        } else {
            $response = [
                'status' => true,
                'data' => json_decode($result, true)
            ];
            return $response;
        }
    }


}
