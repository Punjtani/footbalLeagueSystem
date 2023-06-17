<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

use App\Association;
use App\Club;
use App\Official;
use App\Player;
use App\Stadium;
use App\Staff;
use App\Team;
use App\Sport;
use App\Season;
use App\Tenant;
use App\Tournament;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Monarobase\CountryList\CountryListFacade as Countries;

class Helper
{
    public function addtext($img,$text)
    {
        $img->text($text, 155, 70, function ($font) {
            $font->size(12);
            $font->file(public_path('fonts/FastHand/FastHand.ttf'));
            $font->color('#FFFFFF');
            $font->align('top');
            $font->valign('left');
            $font->angle(0);
        });
    }
    public static function applClasses(): array
    {
        $data = config('custom.custom');
        $layoutClasses = [
            'theme' => $data['theme'],
            'sidebarCollapsed' => $data['sidebarCollapsed'],
            'navbarColor' => $data['navbarColor'],
            'navbarType' => $data['navbarType'],
            'footerType' => $data['footerType'],
            'sidebarClass' => 'menu-expanded',
            'bodyClass' => $data['bodyClass'],
        ];

        //Theme
        if ($layoutClasses['theme'] == 'dark')
            $layoutClasses['theme'] = "dark-layout";
        elseif ($layoutClasses['theme'] == 'semi-dark')
            $layoutClasses['theme'] = "semi-dark-layout";
        else
            $layoutClasses['theme'] = "";

        //navbar
        switch ($layoutClasses['navbarType']) {
            case "static":
                $layoutClasses['navbarType'] = "navbar-floating";
                break;
            case "sticky":
                $layoutClasses['navbarType'] = "navbar-sticky";
                break;
            case "hidden":
                $layoutClasses['navbarType'] = "navbar-hidden";
                break;
            default:
                $layoutClasses['navbarType'] = "navbar-static";
        }

        // sidebar Collapsed
        if ($layoutClasses['sidebarCollapsed'] == 'true')
            $layoutClasses['sidebarClass'] = "menu-collapsed";

        //footer
        switch ($layoutClasses['footerType']) {
            case "sticky":
                $layoutClasses['footerType'] = "fixed-footer";
                break;
            case "hidden":
                $layoutClasses['footerType'] = "footer-hidden";
                break;
            default:
                $layoutClasses['footerType'] = "footer-static";
        }

        return $layoutClasses;
    }

    public static function get_status_dropdown_values($id, $value = NULL): string
    {
        $menu = config('custom.status');
        $str = '<div class="mr-1"><label for="' . $id . '">Status<span style="color: red" class="required" > *</span></label></div><div class="btn-large">' . PHP_EOL;
        if (isset($menu) && !empty($menu)) {
            $str .= '<select class="form-control" id="' . $id . '" name="status">' . PHP_EOL;
            $str .= '<option value = "">Select Status</option>' . PHP_EOL;
            foreach ($menu as $key => $val) {
                $selected = $key == $value ? 'selected' : '';
                $str .= '<option value = "' . $key . '" ' . $selected . '>' . $val . '</option>' . PHP_EOL;
            }
            $str .= '</select>' . PHP_EOL;
        }
        $str .= '</div>' . PHP_EOL;
        return $str;
    }

    public static function get__subscription_status_dropdown_values($id, $value = NULL): string
    {
        $menu = config('custom.subscription_status');
        $str = '<div class="mr-1"><label for="' . $id . '">Status<span style="color: red" class="required" > *</span></label></div><div class="btn-large">' . PHP_EOL;
        if (isset($menu) && !empty($menu)) {
            $str .= '<select class="form-control" id="' . $id . '" name="status">' . PHP_EOL;
            $str .= '<option value = "">Select Status</option>' . PHP_EOL;
            foreach ($menu as $key => $val) {
                $selected = $key == $value ? 'selected' : '';
                $str .= '<option value = "' . $key . '" ' . $selected . '>' . $val . '</option>' . PHP_EOL;
            }
            $str .= '</select>' . PHP_EOL;
        }
        $str .= '</div>' . PHP_EOL;
        return $str;
    }

    public static function get_status($sel_val): string
    {
        $status = Config::get('custom.status');
        switch ($sel_val) {
            case 1:
                $select = '<span class="text-success">' . $status[$sel_val] . '</span>';
                break;
            case 2:
                $select = '<span class="text-warning">' . $status[$sel_val] . '</span>';
                break;
            case 0:
            default:
                $select = '<span class="text-info">' . $status[$sel_val] . '</span>';
                break;
        }
        return $select;
    }

    public static function set_lang_tabs($TextArea = FALSE, $name = NULL, $description = NULL, $default_description_length = 350, $is_news = FALSE, $news = NULL): string
    {
        $languages = Config::get('app.language');
        $html = '';
        $html .= '<div class="nav-tabs-group mb-2">' . PHP_EOL;
        $html .= '<ul class="nav nav-tabs mb-0" role="tablist">' . PHP_EOL;

        foreach ($languages as $lang) {
            $status = $lang['isDefault'] == 1 ? 'active' : '';
            $html .= '<li class="nav-item">';
            $html .= '<a class="nav-link ' . $status . '" id="' . $lang['languageName'] . '-tab" data-toggle="tab" href="#' . $lang['languageName'] . '" aria-controls="' . $lang['languageName'] . '" role="tab" aria-selected="false"><i class="flag-icon mr-75 ' . $lang['flag'] . '"></i>' . $lang['languageName'] . '</a>' . PHP_EOL;
            $html .= '</li>' . PHP_EOL;
        }

        $html .= '</ul>' . PHP_EOL;
        $html .= '<div class="tab-content p-3">' . PHP_EOL;
        foreach ($languages as $lang) {
            $status = $lang['isDefault'] == 1 ? 'active show' : '';
            $html .= '<div class="tab-pane fade ' . $status . '" id="' . $lang['languageName'] . '" aria-labelledby="' . $lang['languageName'] . '-tab" role="tabpanel">' . PHP_EOL;
            $html .= '<label for="name_' . $lang['languageCode'] . '">' . ($is_news ? 'Title' : 'Name') . '<span style="color: red" class="required" > * </span></label>' . PHP_EOL;
            $html .= '<div class="form-group">' . PHP_EOL;
            $html .= '<div class="controls">' . PHP_EOL;
            if ($is_news) {
                $html .= '<input type="text" name="title_' . $lang['languageCode'] . '" id="title_' . $lang['languageCode'] . '" placeholder="' . 'Title' . '" class="form-control" value="' . ($name !== NULL && isset($name[$lang['languageCode']]) ? $name[$lang['languageCode']] : '') . '" >' . PHP_EOL;
            } else {
                $html .= '<input type="text" name="name_' . $lang['languageCode'] . '" id="name_' . $lang['languageCode'] . '" placeholder="' . 'Name' . '" class="form-control name_field" value="' . ($name !== NULL && isset($name[$lang['languageCode']]) ? $name[$lang['languageCode']] : '') . '" >' . PHP_EOL;
            }
            $html .= '</div>' . PHP_EOL;
            $html .= '</div>' . PHP_EOL;
            if ($TextArea) {
                $html .= '<label for="description_' . $lang['languageCode'] . '">Description </label>' . PHP_EOL;
                $html .= '<div class="form-group mb-0">' . PHP_EOL;
                $html .= '<div class="controls">' . PHP_EOL;
                $html .= '<textarea class="form-control" name = "description_' . $lang['languageCode'] . '" id= "description_' . $lang['languageCode'] . '" rows="4" cols="' . $default_description_length . '" placeholder = "Description (max ' . Config::get('custom.textAreaCharacterLimit') . ' characters)" style="resize: none">' . ($description !== NULL && isset($description[$lang['languageCode']]) ? $description[$lang['languageCode']] : '') . '</textarea>' . PHP_EOL;
                $html .= '</div>' . PHP_EOL;
                $html .= '</div>' . PHP_EOL;
            }
            if ($is_news) {
                $html .= '<label for="content_' . $lang['languageCode'] . '">Content </label>' . PHP_EOL;
                $html .= '<div class="form-group">' . PHP_EOL;
                $html .= '<div class="controls">' . PHP_EOL;
                $html .= '<textarea class="form-control" name = "content_' . $lang['languageCode'] . '" id= "content_' . $lang['languageCode'] . '" rows="4" placeholder = "News Content " >' . ($news !== NULL && isset($news[$lang['languageCode']]) ? $news[$lang['languageCode']] : '') . '</textarea>' . PHP_EOL;
                $html .= '</div>' . PHP_EOL;
                $html .= '</div>' . PHP_EOL;
            }
            $html .= '</div>' . PHP_EOL;
        }
        $html .= '</div>' . PHP_EOL;
        $html .= '</div>' . PHP_EOL;
        return $html;
    }

    public static function get_default_lang($str)
    {
        $languages = Config::get('app.language');
        $objects = json_decode($str);
        if (json_last_error() === JSON_ERROR_NONE) {
            foreach ($languages as $lang) {
                if ($lang['isDefault'] === 1) {
                    $langCode = $lang['languageCode'];
                    return $objects->$langCode;
                }
            }
        }
        return $str;
    }

    public static function jsonMessage($success, $redirect_url = NULL, $input_message = NULL, $url_params = array()): \Illuminate\Http\JsonResponse
    {
        if ($success) {
            $message['type'] = 'Success';
            $message['message'] = 'Record successfully added';
            $message['icon'] = 'check';
        } else {
            $message['type'] = 'Error';
            $message['message'] = 'Unable to save record';
            $message['icon'] = 'warning';
        }
        if ($redirect_url !== NULL) {
            if (request()->has('save_button') && request()->input('save_button') === 'save_new') {
                $redirect_url .= '.create';
            }
            $message['redirect_url'] = route($redirect_url, $url_params);
        }
        if ($input_message !== NULL) {
            $message['message'] = $input_message;
        }
        return response()->json($message);
    }

    public static function get_country_name($country_code): string
    {
        return Countries::getOne($country_code);
    }

    public static function get_player_position($code)
    {
        $player_roles = Sport::query()->select(['roles'])->first();
        $player_roles = json_decode($player_roles['roles']);
        if (isset($player_roles) && !empty($player_roles)) {
            foreach ($player_roles as $key => $value) {
                if ($value->$code) {
                    return $value->$code;
                }
            }
        }
        return '';
    }


    public static function get_team_players($teamID, $fixture_id, $status, $sub = false)
    {
        return DB::table('players')
            ->join('match_squad', 'players.id', '=', 'match_squad.player_id')
            ->where('players.status', Player::STATUS_PUBLISH)
            ->where('match_squad.team_id', $teamID)
            ->where('match_squad.match_id', $fixture_id)
            ->whereIn('match_squad.player_status', $status)
            ->where('match_squad.player_status', '!=', 'substituted')
            ->select('players.*')
            ->get();
    }

    public static function get_name_with_image($name, $image, $class = ''): string
    {
        $response = '<div class="media px-1">';
        $response .= '<img src="' . $image . '" alt="avatar" class="mr-1 ' . $class . '" width="40">';
        $response .= '<div class="media-body">';
        $response .= $name;
        $response .= '</div>';
        $response .= '</div>';
        return $response;
    }

    public static function get_all_content(): array
    {
//        TODO: Add more type of contents
        $content = array();
        $content['Associations'] = Association::query()->select('name')->where('status', Association::STATUS_PUBLISH)->orderBy('name')->get();
        $content['Tournaments'] = Tournament::query()->select('name')->where('status', Tournament::STATUS_PUBLISH)->orderBy('name')->get();
        $content['Clubs'] = Club::query()->select('name')->where('status', Club::STATUS_PUBLISH)->orderBy('name')->get();
        $content['Players'] = Player::query()->select('name')->where('status', Player::STATUS_PUBLISH)->orderBy('name')->get();
        $content['Stadiums'] = Stadium::query()->select('name')->where('status', Stadium::STATUS_PUBLISH)->orderBy('name')->get();
        $content['Seasons'] = Season::query()->select('name')->where('status', Season::STATUS_PUBLISH)->orderBy('name')->get();
        $content['Officials'] = Official::query()->select('name')->where('status', Official::STATUS_PUBLISH)->orderBy('name')->get();
        $content['Staff'] = Staff::query()->select('name')->where('status', Staff::STATUS_PUBLISH)->orderBy('name')->get();
        return $content;
    }

    public static function validate_lang_tabs($text_box = true, $is_news = false): array
    {
        $tabs = Config::get('app.language');
        $rules = array();
        $customMessages = array();
        $names = array();
        $description = array();
        foreach ($tabs as $tab) {
            if ($is_news) {
                $rules['title_' . $tab['languageCode']] = 'required';
                $customMessages['title_' . $tab['languageCode'] . '.required'] = 'Title in ' . $tab['languageName'] . ' is required';
                $names[$tab['languageCode']] = request()->input("title_" . $tab['languageCode']);
            } else {
                $rules['name_' . $tab['languageCode']] = 'required';
                $customMessages['name_' . $tab['languageCode'] . '.required'] = 'Name in ' . $tab['languageName'] . ' is required';
                $names[$tab['languageCode']] = request()->input("name_" . $tab['languageCode']);
            }
            if ($text_box) {
                $rules['description_' . $tab['languageCode']] = 'max:' . Config::get('custom.textAreaCharacterLimit');
                $customMessages['description_' . $tab['languageCode'] . '.max'] = 'The description may not exceed :max characters.';
                $description[$tab['languageCode']] = request()->input("description_" . $tab['languageCode']);
            }
        }
        try {
            if ($is_news) {
                request()->request->add(['title' => json_encode($names, JSON_THROW_ON_ERROR)]);
                request()->request->add(['content' => json_encode($description, JSON_THROW_ON_ERROR)]);
            } else {
                request()->request->add(['name' => json_encode($names, JSON_THROW_ON_ERROR)]);
            }
            if ($text_box) {
                request()->request->add(['description' => json_encode($description, JSON_THROW_ON_ERROR)]);
            }
        } catch (Exception $e) {
        }
        return ['rules' => $rules, 'customMessages' => $customMessages];
    }


    public static function getSocialLinkValue($social_links, $link_type)
    {

        $links = json_decode($social_links, true);

        return !empty($links[$link_type]) ? $links[$link_type] : '';

    }
}

