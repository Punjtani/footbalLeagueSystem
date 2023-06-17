<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use ReflectionClass;

class HtmlTemplatesHelper
{
    public static function getImageDiv($image = NULL, $class = '', $disabled = false)
    {
        $output = '<div class="mb-1">';
        $output .= '<img id="temp_image" class="rounded border img-fluid ' . $class . '" alt="image" src="' . ($image ?? asset('images\1080x720.png')) . '" />';
        $output .= '</div>';
        $output .= '<div class="custom-file">';
        $output .= '<input type="file" class="custom-file-input" name="image" id="pro-thumb-upload" accept="image/*" ' . ($disabled ? 'disabled' : '') . '>';
        $output .= '<label class="custom-file-label text-truncate" for="pro-thumb-upload">Choose file</label>';
        $output .= '</div>';
        echo $output;
    }
    public static function getMobileImageDiv($mobile_image = NULL, $class = '', $disabled = false)
    {
        $output = '<div class="mb-1">';
        $output .= '<img id="temp_mobile_image" class="rounded border img-fluid ' . $class . '" alt="image" src="' . ($mobile_image ?? asset('images\1080x720.png')) . '" />';
        $output .= '</div>';
        $output .= '<div class="custom-file">';
        $output .= '<input type="file" class="custom-file-input" name="mobile_image" id="pro-mobile-thumb-upload" accept="image/*" ' . ($disabled ? 'disabled' : '') . '>';
        $output .= '<label class="custom-file-label text-truncate" for="pro-mobile-thumb-upload">Choose file</label>';
        $output .= '</div>';
        echo $output;
    }

    public static function getFiltersDiv($advance_filters = array(), $is_title = NULL): void
    {
        $status = Config::get('custom.status');
        $output = '<div class="page-filter">';
        $output .= '';

        $output = '<div class="row">';
        $output .= '<div class="col-3">';
        $output .= '<fieldset class="form-group">';
        $output .= '<div class="input-group">';
        $output .= '<input type="text" class="form-control" name="search_filter" id="search_filter" placeholder="Search ' . ($is_title ?? 'Name') . '">';
        $output .= '<div class="input-group-append">';
        $output .= '<button id="search_btn" class="input-group-text btn btn-primary fa fa-search"></button>';
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</fieldset>';
        $output .= '</div>';
        if (!empty($advance_filters)) {
            $output .= '<div class="rslt-btn">';
            $output .= '<a id="advance_filters_btn" data-toggle="collapse" href="#advance_filter_collapse" class="btn btn-block btn-outline-lighten-5">Advance Filters <i class="fa fa-angle-down"></i> </a>';
            $output .= '</div>';
        }
        $output .= '</div>';
        if (!empty($advance_filters)) {
            $output .= '<div class="collapse-bordered">';
            $output .= '<div class="card">';
            $output .= '<div id="advance_filter_collapse" class="collapse">';
            $output .= '<div class="card-content">';
            $output .= '<div class="card-body">';
            $output .= '<div class="row">';
            foreach ($advance_filters as $label => $advance_filter) {
                $output .= '<div class="col-2">';
                $output .= '<label for="' . strtolower(str_replace(' ', '_', $label)) . '_filter" class="col-form-label-sm">' . $label . '</label>';
                $output .= '<fieldset class="form-group">';
                if (strtolower($advance_filter['type']) === 'dropdown') {
                    $output .= '<select class="select_class filter" name="' . ($advance_filter['name'] ?? strtolower(str_replace(' ', '_', $label))) . '-' . ($advance_filter['sub_type'] ?? '') . '-filter' . '" id="' . strtolower(str_replace(' ', '_', $label)) . '-filter" data-placeholder="' . ($advance_filter['placeholder'] ?? ('Select' . $label)) . '">';
                    $output .= '<option value="">' . ($advance_filter['placeholder'] ?? 'Select') . '</option>';
                    foreach ($advance_filter['data'] as $key => $value) {
                        $output .= '<option value="' . $key . '">' . $value . '</option>';
                    }
                    $output .= '</select>';
                } else if (strtolower($advance_filter['type']) === 'text') {
                    $output .= '<input type="text" class="form-control form-control-sm filter" name="' . ($advance_filter['name'] ?? strtolower(str_replace(' ', '_', $label))) . '-' . ($advance_filter['sub_type'] ?? '') . '-filter' . '" id="' . strtolower(str_replace(' ', '_', $label)) . '-filter" placeholder="' . ($advance_filter['placeholder'] ?? '') . '">';
                } else if (strtolower($advance_filter['type']) === 'number') {
                    $output .= '<input type="number" class="form-control form-control-sm filter" name="' . ($advance_filter['name'] ?? strtolower(str_replace(' ', '_', $label))) . '-' . ($advance_filter['sub_type'] ?? '') . '-filter' . '" id="' . strtolower(str_replace(' ', '_', $label)) . '-filter" placeholder="' . ($advance_filter['placeholder'] ?? '') . '">';
                } else if (strtolower($advance_filter['type']) === 'date') {
//                    $output .= '<div class="input-group date" id="dob-datetimepicker" data-target-input="nearest">';
//                    $output .= '<input type="text" id="'. strtolower(str_replace(' ','_',$label)) .'-filter" placeholder="'. ($advance_filter['placeholder'] ?? '') .'" name="'. ($advance_filter['name'] ?? strtolower(str_replace(' ','_',$label))) . '-' . ($advance_filter['sub_type'] ?? '') . '-filter' .'" class="form-control datetimepicker-input" data-target="#dob-datetimepicker" placeholder="Select Date"/>';
//                    $output .= '<div class="input-group-append" id="dob-date-btn" data-target="#dob-datetimepicker" data-toggle="datetimepicker">';
//                    $output .= '<div class="input-group-text"><i class="fa fa-calendar"></i></div>';
//                    $output .= '</div>';
//                    $output .= '</div>';
//                    $output .= '<input id="">';
                    $output .= '<input type="text" class="form-control form-control-sm pickadate-months-year filter" name="' . ($advance_filter['name'] ?? strtolower(str_replace(' ', '_', $label))) . '-' . ($advance_filter['sub_type'] ?? '') . '-filter' . '" id="' . strtolower(str_replace(' ', '_', $label)) . '-filter" placeholder="' . ($advance_filter['placeholder'] ?? '') . '">';
                }
                $output .= '</fieldset>';
                $output .= '</div>';
            }
            $output .= '<div class="col-2">';
            $output .= '<label for="status_filter" class="col-form-label-sm">Status</label>';
            $output .= '<fieldset class="form-group">';
            $output .= '<select class="select_class filter" name="status_filter" id="status_filter" data-placeholder="Select Status">';
            $output .= '<option value="">Select Status</option>';
            foreach ($status as $key => $value) {
                $output .= '<option value="' . $key . '">' . $value . '</option>';
            }
            $output .= '</select>';
            $output .= '</fieldset>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '<div class="row justify-content-end">';
            $output .= '<div class="col-1 rslt-btn">';
            $output .= '<button type="button" id="apply-filter-btn" class="form-control form-control-sm btn mt-2 btn-block btn-primary waves-effect waves-light">Apply</button>';
            $output .= '</div>';
            $output .= '<div class="col-1 rslt-btn">';
            $output .= '<button type="button" id="reset-filter-btn" class="form-control form-control-sm btn mt-2 btn-block btn-warning waves-effect waves-light">Reset</button>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';
        }
        echo $output;
    }

    public static function get_action_dropdown($data, $html = '', $super_only = false, $view = false, $edit = true, $delete = true,$duplicate = false, $gallery = false): string
    {

        $url = url()->current();
        if (strpos($url,'users') !== false) {
            $index_url = 'users';
        } else {
            $index_url = get_class($data)::INDEX_URL;
        }

        $buttons = '<div class="dropdown open">';
        $buttons .= '<a class="btn btn-light dropdown-toggle" href="#" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</a>';
        $buttons .= '<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">';
        if ($view) {
            $buttons .= '<a id="view-btn" title="view" class="dropdown-item" onclick="window.location=' . "'" . route($index_url . '.show', ['id' => $data->id]) . "'" . '">View Details</a>';
        }
        if ($gallery) {
            $buttons .= '<a id="view-btn" title="view" class="dropdown-item" onclick="window.location=' . "'" . route($index_url . '.gallery', ['id' => $data->id]) . "'" . '">Add/Edit Gallery</a>';
        }

        $buttons .= $html;
        if ($edit) {
            $buttons .= '<a title="edit" class="dropdown-item" onclick="window.location=' . "'" . route($index_url . '.edit', ['id' => $data->id]) . "'" . '">Edit</a>';
        }

        if ($duplicate) {

            $buttons .= '<a title="edit" class="dropdown-item" onclick="window.location=' . "'" . route($index_url . '.duplicate', ['id' => $data->id,'type' => 'copy']) . "'" . '">Duplicate</a>';
        }
        if ($delete) {
            $buttons .= '<a title="delete" class="dropdown-item destroy-item" id="' . $data->id . '">Delete</a>';
        }

        $buttons .= '</div>';
        $buttons .= '</div>';
        return $buttons;
    }

    public static function get_form_action_buttons($update = true)
    {
        $html = '<div class="text-right">';
        $html .= '<input type="hidden" id="save_button_pressed" name="save_button" value="save">';
        if ($update) {
            $html .= '<button type="submit" id="save" class="save_btn btn btn-primary">Save</button>';
        } else {
            $html .= '<button type="submit" id="save" class="save_btn btn btn-primary ml-75">Save & Exit</button>';
            $html .= '<button type="submit" id="save_new" class="save_btn btn btn-light ml-75">Save & New</button>';
            $html .= '<button id="reset_btn" type="reset" class="btn btn-light ml-75">Reset</button>';
        }
        $html .= '<button id="cancel_btn" type="button" class="btn btn-light ml-75">Cancel</button>';
        $html .= '</div>';
        echo $html;
    }

    public static function get_subscription_form_action_buttons($item, $update = true)
    {
        $html = '<div class="text-right">';
        $html .= '<input type="hidden" id="save_button_pressed" name="save_button" value="save">';
        if ($update) {
//            if ($item->status == 'active') {
//                $html .= '<button type="submit" id="save" class="save_btn btn btn-primary reactivate_subscription">Reactivate subscription</button>';
//            } else {
//                $html .= '<button type="submit" id="save" class="save_btn btn btn-primary ">Activate Subscription</button>';
//            }
            $html .= '<button type="button" id="subscription_save" class="save_btn btn btn-primary" data-btn-value="activate">Activate Subscription</button>';
            $html .= '<button type="button" id="subscription_save" class="save_btn btn btn-info ml-75" data-btn-value="recurring">Recurring Billing</button>';

        } else {
            $html .= '<button type="submit" id="save" class="save_btn btn btn-primary ml-75">Save & Exit</button>';
            $html .= '<button type="submit" id="save_new" class="save_btn btn btn-light ml-75">Save & New</button>';
            $html .= '<button id="reset_btn" type="reset" class="btn btn-light ml-75">Reset</button>';
        }
        $html .= '<button id="cancel_btn" type="button" class="btn btn-light ml-75">Cancel</button>';
        $html .= '</div>';
        echo $html;
    }
}
