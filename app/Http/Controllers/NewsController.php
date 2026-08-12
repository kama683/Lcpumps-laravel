<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        return view('pages.news.index', [
            'metaTitle' => __('PageMetadata.news.title'),
            'breadcrumb' => [
                ['label' => __('Common.home'), 'route' => 'home'],
                ['label' => __('News.label')],
            ],
        ]);
    }
}
