<?php

namespace App;

use App\Helpers\Helper;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends BaseModel implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Notifiable, Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail, HasRoles, SoftDeletes;
    
    protected $table = 'admins';
    protected $dates = ['deleted_at'];
    public static array $validation = [
        'name' => 'required',
        'email' => 'required|email:rfc,strict,spoof,filter',
        'role' => 'required',
        'status' => 'required',
    ];

    public const INDEX_URL = 'admins';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'status', 'tenant_id', 'stadium_id', 'phone', 'otp', 'post_code', 'gender', 'user_type','date_of_birth','is_save','subscription_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $appends = [
        'tenantName', 'displayStatus',
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function filters(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::filters($request);
        return $query;
    }

    /**
     *
     * @return string
     */
    public function getDisplayStatusAttribute()
    {
        return Helper::get_status($this->status);
    }

    public function stadium()
    {
        return $this->belongsTo(Stadium::class);
    }

    public function getIntlPhoneAttribute()
    {
        if (empty($this->phone)) {
            return null;
        }
        $phone = str_replace("-", "", $this->phone);
        if ($phone[0] !== '+') {
            $phone = "+60" . ltrim($phone, "0");
        }

        return $phone;
    }
}
