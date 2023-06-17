<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\StaticPage;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class StaticPagesController extends BaseController
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \JsonException
     */
    public function store(Request $request): JsonResponse
    {
        $page = StaticPage::query()->where('page_name', $request->input('page_name'))->first();
        if ($page === null) {
            $page = new StaticPage;
        }
        $page->fill($request->all());
        $page->save();
        return Helper::jsonMessage(true, null, 'Record Updated Successfully');
    }


    public function privacy_policy()
    {
        $page = StaticPage::query()->where('page_name', 'privacy_policy')->first();
        return view('pages.static-pages.privacy_policy', ['item' => $page !== null ? $page->content : null, 'breadcrumbs' => Breadcrumbs::generate('static-pages.privacy_policy')]);
    }

    public function terms_and_conditions()
    {
        $page = StaticPage::query()->where('page_name', 'terms_and_conditions')->first();
        return view('pages.static-pages.terms_and_conditions', ['item' => $page !== null ? $page->content : null, 'breadcrumbs' => Breadcrumbs::generate('static-pages.terms_and_conditions')]);
    }
    public function subscription_terms_and_conditions()
    {
        $page = StaticPage::query()->where('page_name', 'subscription_terms_and_conditions')->first();
        return view('pages.static-pages.subscription_terms_and_conditions', ['item' => $page !== null ? $page->content : null, 'breadcrumbs' => Breadcrumbs::generate('static-pages.subscription_terms_and_conditions')]);
    }

    public function about_us()
    {
        $page = StaticPage::query()->where('page_name', 'about_us')->first();
        return view('pages.static-pages.about_us', ['item' => $page !== null ? $page->content : null, 'breadcrumbs' => Breadcrumbs::generate('static-pages.about_us')]);
    }

    public function delete_faq(Request $request) {
        $id = $request->input('id');
        if ($id !== 0 && $id !== '0') {
            $faq = StaticPage::query()->where('page_name', 'faq')->where('id', $id)->delete();
            return Helper::jsonMessage($faq, null, $faq ? 'FAQ Deleted Successfully' : 'Unable to Delete FAQ');
        }
        return Helper::jsonMessage(true, null, 'FAQ Deleted Successfully');
    }

    public function add_update_faq(Request $request) {
        $question_id = $request->input('id');
        $action = 'Added';
        if ($question_id === 0 || $question_id === '0') {
            $faq = new StaticPage;
        } else {
            $action = 'Updated';
            $faq = StaticPage::query()->find($question_id);
        }
        $faq->page_name = 'faq';
        $faq->content = json_encode(array(
            'question' => $request->input('question'),
            'answer' => $request->input('answer')
        ), JSON_THROW_ON_ERROR);
        $faq->save();
        return Helper::jsonMessage($faq, null, 'FAQ ' . $action . ' Successfully');
    }
}
