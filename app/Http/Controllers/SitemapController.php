<?php

namespace App\Http\Controllers;

use App\Services\SeoMetadataService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(private readonly SeoMetadataService $seo) {}

    public function index(): Response
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        $urlset = $xml->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'urlset');
        $urlset->setAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
        $xml->appendChild($urlset);

        $entries = $this->seo->sitemapEntries();

        // Debug comment inserted temporarily to help diagnostics in CI — shows
        // how many entries the service produced. Will be removed once tests
        // are green and behavior is confirmed.
        $xml->appendChild($xml->createComment('sitemap-entries-count:'.count($entries)));

        foreach ($entries as $entry) {
            $urlNode = $xml->createElement('url');
            $locNode = $xml->createElement('loc', $entry['loc']);
            $urlNode->appendChild($locNode);

            foreach ($entry['alternates'] as $locale => $href) {
                $xhtmlLink = $xml->createElementNS('http://www.w3.org/1999/xhtml', 'xhtml:link');
                $xhtmlLink->setAttribute('rel', 'alternate');
                $xhtmlLink->setAttribute('hreflang', $locale);
                $xhtmlLink->setAttribute('href', $href);
                $urlNode->appendChild($xhtmlLink);
            }

            $urlset->appendChild($urlNode);
        }

        return response($xml->saveXML(), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
