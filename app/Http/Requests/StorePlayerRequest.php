<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

class StorePlayerRequest extends FormRequest
{
    private array $lang_tabs;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = array_merge($this->lang_tabs['rules'],[
            'country' => 'required',
            'dob' => 'required|date|before:now',
            'status' => 'required|integer',
            'image' => 'mimes:tif,tiff,webp,svg,ico,jpeg,jpg,png,gif|required|max:10000',
            'jersey' => 'required|integer|min:1|max:999',
            'playing_position' => 'required',
            'club' => 'required_with:team',
            'club_joining_date' => 'required_with:club|date|after:dob',
            'team_joining_date' => 'required_with:team|date|after_or_equal:club_joining_date',
        ]);
        $tabs = Config::get('app.language');
        $names = array();
        foreach ($tabs as $tab) {
            if (isset($rules['name_' . $tab['languageCode']])) {
                $names[$tab['languageCode']] = request()->input("name_" . $tab['languageCode']);
            }
        }
        $this->request->add(['name' => json_encode($names, JSON_THROW_ON_ERROR)]);
        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return array_merge($this->lang_tabs['customMessages'],[]);
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        try {
            $this->lang_tabs = Helper::validate_lang_tabs(false);
        } catch (Exception $e) {}
        try {
            $merge = array('dob' => Carbon::createFromFormat('Y-m-d', $this->dob));
        } catch (Exception $e) {
        }
        if ($this->input('club_joining_date')) {
            try {
                $merge['club_joining_date'] = Carbon::createFromFormat('Y-m-d', $this->club_joining_date);
            } catch (Exception $e) {
            }
        }
        if ($this->input('team_joining_date')) {
            $merge['team_joining_date'] = Carbon::createFromFormat('Y-m-d', $this->team_joining_date);
        }
        if ($this->input('jersey')) {
            $merge['jersey'] = (int)$this->jersey;
        }
        $this->merge($merge);
    }
}
