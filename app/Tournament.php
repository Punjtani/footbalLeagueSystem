<?php

namespace App;
use App\Helpers\Helper;
use App\Helpers\S3Helper;
use App\Traits\BelongsToTenant;
use Illuminate\Http\Request;
use Symfony\Component\Console\Input\Input;

class Tournament extends BaseModel
{
    use BelongsToTenant;

    public static array $validation = [
        'image' => 'mimes:jpeg,jpg,png,gif|required|max:10000',
    ];

    public const S3_FOLDER_PATH = 'tournaments/';
    public const INDEX_URL = 'tournaments';

    public const TYPE_FRIENDLY = 'Friendly';
    public const TYPE_PROFESSIONAL = 'Professional';

    protected $fillable = [
        'name', 'description', 'status', 'association_id', 'team_group', 'image', 'occurrence', 'since', 'booking_type','type', 'hide_frontend'
    ];

    public static function filters(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::filters($request);
        return $query;
    }

    public function getNameAttribute($value)
    {
        return Helper::get_default_lang($value);
    }

    public function getDescriptionAttribute($value)
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
    public function setImageAttribute($value){
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
    public function getImageAttribute($value){
        return S3Helper::get_image_url($value, self::S3_FOLDER_PATH);
    }

    /**
     * @param $value
     */
    public function setOccurrenceAttribute($value){
        if ($value === '2') {
            $this->attributes['occurrence'] = request('year_occurrence');
        } else {
            $this->attributes['occurrence'] = $value;
        }
    }

    public function setAssociationAttribute($value)
    {
        $association = Association::query()->findOrFail($value);
        $this->attributes['association_id'] = $association->getAttributes()['id'];
    }

    public function association(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Association::class);
    }

    public function seasons(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Season::class);
    }

    public function bookings(){
        return $this->hasMany(Booking::class);
    }

}
