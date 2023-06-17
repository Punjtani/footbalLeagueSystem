<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Config;

class BaseController extends Controller
{
    public function validate_lang_tabs($text_box = true, $is_news = false): array
    {
        $tabs = Config::get('app.language');
        $rules = array();
        $customMessages = array();
        $names = array();
        $description = array();
        foreach ($tabs as $tab) {
            if ($is_news)
            {
                $rules['title_' . $tab['languageCode']] = 'required';
                $customMessages['title_' . $tab['languageCode'] . '.required'] = 'Title in ' . $tab['languageName'] . ' required';
                $names[$tab['languageCode']] = request()->input("title_" . $tab['languageCode']);
            } else {
                $rules['name_' . $tab['languageCode']] = 'required';
                $customMessages['name_' . $tab['languageCode'] . '.required'] = 'Name in ' . $tab['languageName'] . ' required';
                $names[$tab['languageCode']] = request()->input("name_" . $tab['languageCode']);
            }
            if ($text_box)
            {
                $rules['description_' . $tab['languageCode']] = 'required|max:' . Config::get('custom.textAreaCharacterLimit');
                $customMessages['description_' . $tab['languageCode'] . '.required'] = 'The description in ' . $tab['languageName'] . ' required';
                $customMessages['description_' . $tab['languageCode'] . '.max'] = 'The description may not exceed :max characters.';
                $description[$tab['languageCode']] = request()->input("description_" . $tab['languageCode']);
            }
        }
        try {
            if ($is_news){
                request()->request->add(['title' => json_encode($names, JSON_THROW_ON_ERROR)]);
                request()->request->add(['content' => json_encode($description, JSON_THROW_ON_ERROR)]);
            } else {
                request()->request->add(['name' => json_encode($names, JSON_THROW_ON_ERROR)]);
            }
            if ($text_box)
            {
                request()->request->add(['description' => json_encode($description, JSON_THROW_ON_ERROR)]);
            }
        } catch (Exception $e)
        {
        }
        return ['rules' => $rules, 'customMessages' => $customMessages];
    }
}
