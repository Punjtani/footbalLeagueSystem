<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

class UpdatePlayerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = array_merge($this->lang_tabs['rules'],[
            'country' => 'required',
            'dob' => 'required|date|before:now,club_joining_date',
            'status' => 'required|integer',
            'image' => 'mimes:tif,tiff,webp,svg,ico,jpeg,jpg,png,gif|max:10000',
            'jersey' => 'required|integer|min:1|max:999',
            'playing_position' => 'required',
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
        if ($this->input('jersey')) {
            $merge['jersey'] = (int)$this->jersey;
        }
        $this->merge($merge);
    }
}
