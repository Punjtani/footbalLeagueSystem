<?php

namespace App;
use Illuminate\Http\Request;

class AdminLog extends BaseModel
{
    protected $table = 'audits';

    public const INDEX_URL = 'admin-logs';

    protected $appends = [
        'adminEmail',
    ];

    public static function filters(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::filters($request);
        return $query;
    }

    public function getAdminEmailAttribute(){
        if($this->user_id !== null) {
            $user = User::query()->where('id', $this->user_id)->first();
            if($user !== null)
                return $user->email;
        }
        return '';
    }

    public function getAuditableTypeAttribute($value){
        $value = explode('\\', $value);
        return $value[1];
    }
}
