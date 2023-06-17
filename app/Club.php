<?php

namespace App;

use App\Helpers\S3Helper;
use App\Traits\BelongsToTenant;
use Illuminate\Http\Request;
use App\Helpers\Helper;

class Club extends BaseModel
{
    use BelongsToTenant;

    public static array $validation = [
        'image' => 'mimes:jpeg,jpg,png,gif|max:10000',
        'name' => 'required|unique:clubs,name',
    ];
    public static array $validationUpdate = [
        'image' => 'mimes:jpeg,jpg,png,gif|max:10000',
        'name' => 'required'
    ];

    public const S3_FOLDER_PATH = 'club/';
    public const INDEX_URL = 'clubs';

    protected $fillable = [
        'name', 'description', 'status', 'staff', 'image', 'primary_color', 'jersey_color',
        'secondary_color', 'facebook', 'twitter', 'instagram', 'youtube', 'founding_date', 'stadium_id',
        'hide_frontend', 'subscription_id'
    ];

    public static function filters(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::filters($request);

        $query->when($request->get('membership_level_id'), function ($query, $membershipLevelId) {
            $query->whereHas('activeMembershipRelation', function ($query) use ($membershipLevelId) {
                $query->where('membership_level_id', $membershipLevelId);
            });
        });
        if (!empty($request->startDate) && !empty($request->endDate)) {
            $query->whereHas('activeMembershipRelation', function ($query) use ($request) {
                $query->whereBetween('expires_at', [$request->startDate, $request->endDate]);
            });
        }

        return $query;
    }

    public function getNameAttribute($value)
    {
        return Helper::get_default_lang($value);
    }


    public function getStatusAttribute($value)
    {
        return Helper::get_status($value);
    }


    /**
     * @param $value
     */
    public function setImageAttribute($value)
    {
        $imageName = '';
        if ($value !== '') {
            $imageName = time() . '.' . $value->getClientOriginalExtension();
            $filePath = self::S3_FOLDER_PATH . $imageName;
            S3Helper::upload_image($filePath, $value);
        }
        $this->attributes['image'] = $imageName;
    }

    /**
     * @param $value
     * @return string
     */
    public function getImageAttribute($value)
    {
        return S3Helper::get_image_url($value, self::S3_FOLDER_PATH);
    }

//    public function players_history(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
//    {
//        return $this->hasMany(Player::class);
//    }

    public function players(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'club_player', 'club_id', 'player_id')->withPivot('left_on')->withTimestamps();
    }

    public function teams(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function activeMembership()
    {
        return $this->memberships()->where('status', 1)->first();
    }

    public function activeMembershipRelation()
    {
        return $this->hasOne(Membership::class, 'club_id')->where('status', '=', 1);
    }

    public function bookings1()
    {
        return $this->hasMany(Booking::class, 'club1_id');
    }

    public function bookings2()
    {
        return $this->hasMany(Booking::class, 'club2_id');
    }

}
