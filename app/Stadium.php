<?php

namespace App;

use App\Helpers\Helper;
use App\Helpers\S3Helper;
use App\Traits\BelongsToTenant;
use Illuminate\Http\Request;

class Stadium extends BaseModel
{
    use BelongsToTenant;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stadiums';

    public static array $validation = [
        'name' => 'required|max:100',
        'location' => 'required|max:100',
//        'latitude' => 'required',
//        'longitude' => 'required',
        'status' => 'required',
        'image' => 'mimes:jpeg,jpg,png,gif|required|max:10000',
        'locationFacilities.*.name' => 'required',
    ];

    public const S3_FOLDER_PATH = 'stadiums/';
    public const INDEX_URL = 'stadiums';

    protected string $collection = 'stadiums';

    protected $fillable = [
        'name', 'status', 'image','mobile_image', 'country', 'location', 'capacity', 'latitude', 'longitude', 'is_display_frontend','heading'
    ];
    protected $casts = [
        'latitude' => 'string',
        'longitude' => 'string',
    ];

    public static function filters(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::filters($request);
        if ($request->has('club_id--filter') && $request->input('club_id--filter') !== NULL) {
            $query->leftJoin('clubs', 'stadiums.id', '=', 'clubs.stadium_id');
            $query->where('clubs.id', $request->input('club_id--filter'));
        }

        if (!empty(request()->user()->stadium_id)) {
            $query->where('id', request()->user()->stadium_id);
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
    public function setLatitudeAttribute($value)
    {
        $this->attributes['latitude'] = htmlentities($value);
    }

    /**
     * @param $value
     */
    public function setLongitudeAttribute($value)
    {
        $this->attributes['longitude'] = htmlentities($value);
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
    public function setMobileImageAttribute($value)
    {
        $imageName = '';
        if ($value !== '') {
            $imageName = time() . '.' . $value->getClientOriginalExtension();
            $filePath = self::S3_FOLDER_PATH . $imageName;
            S3Helper::upload_image($filePath, $value);
        }
        $this->attributes['mobile_image'] = $imageName;
    }

    /**
     * @param $value
     * @return string
     */
    public function getImageAttribute($value)
    {
        return S3Helper::get_image_url($value, self::S3_FOLDER_PATH);
    }

     /**
     * @param $value
     * @return string
     */
    public function getMobileImageAttribute($value)
    {
        return S3Helper::get_image_url($value, self::S3_FOLDER_PATH);
    }

    public function facilities()
    {
        return $this->hasMany(StadiumFacility::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

//    public function setIsDisplayFrontendAttribute($value)
//    {
//        $this->attributes['is_display_frontend'] = $value === '1' ? true : false;
//    }
}
