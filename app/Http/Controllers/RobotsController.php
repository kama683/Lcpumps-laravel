<?php

namespace App\Http\Controllers;

use App\Services\SeoMetadataService;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __construct(private readonly SeoMetadataService $seo) {}

    public function index(): Response
    {
        $body = "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /api\nSitemap: {$this->seo->baseUrl()}/sitemap.xml\n";

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
