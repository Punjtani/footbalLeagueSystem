<?php

namespace App;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Hidehalo\Nanoid\Client;

class BaseModel extends Model
{
    public const STATUS_DRAFT = 0, STATUS_PUBLISH = 1, STATUS_INACTIVE = 2;

    protected $dates = ['deleted_at'];

    protected static function boot()
    {
        parent::boot();

        static::creating(static function ($model) {
            $client = new Client();
            $model->gid = $client->generateId($size = 21, $mode = Client::MODE_DYNAMIC);
        });

    }


    public function getTenantNameAttribute()
    {
        if ($this->role !== Config::get('app.ROLE_ADMIN')) {
            $tenant = Tenant::query()->where('status', self::STATUS_PUBLISH)->first();
            if ($tenant !== null) {
                return $tenant['name'] ?? 'N/A';
            }
        }
        return 'Super Admin';
    }

    public static function filters(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $class_name = static::class;
        $table_name = with(new $class_name)->getTable();
        $query = self::query();
        if ($request->has('status_filter') && $request->input('status_filter') !== NULL) {
            $query->where($table_name . '.status', $request->input('status_filter'));
        }
        if ($request->has('search_filter') && $request->input('search_filter') !== NULL) {
            if ($request->path() === 'news') {
                $query->where($table_name . '.title', 'ILIKE', '%' . strip_tags($request->input('search_filter')) . '%');
            } elseif ($request->path() === 'back-end/subscriptions') {
                $query->where($table_name . '.subscription_id', 'ILIKE', '%' . strip_tags($request->input('search_filter')) . '%')
                    ->orWhere($table_name . '.email', 'ILIKE', '%' . strip_tags($request->input('search_filter')) . '%');
            } else {
                $query->where($table_name . '.name', 'ILIKE', '%' . strip_tags($request->input('search_filter')) . '%');
            }
        }
        try {
            foreach ($request->all() as $filter_name => $filter) {
                $filtered = explode('-', $filter_name);
                if (isset($filtered[2]) && $filtered[2] === 'filter' && $filter !== NULL && Schema::hasColumn($table_name, $filtered[0])) {
                    if ($filtered[1] !== '') {
                        $operator = '=';
                        if ($filtered[1] === 'range_after') {
                            $operator = '>';
                        } else if ($filtered[1] === 'range_before') {
                            $operator = '<';
                        } else if ($filtered[1] === 'like') {
                            $operator = 'ILIKE';
                            $filter = '%' . $filter . '%';
                        }
                        $query->where($table_name . '.' . $filtered[0], $operator, $filter);
                    } else {
                        $query->where($table_name . '.' . $filtered[0], strip_tags(strtolower($filter)));
                    }
                }
            }
        } catch (Exception $e) {
        }

        return $query;
    }
}
