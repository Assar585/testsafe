<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Page;
use App\Models\Language;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;

class SitemapController extends Controller
{
    /**
     * Generate sitemap index pointing to localized sitemaps.
     */
    public function index()
    {
        $languages = Language::where('is_active', 1)->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($languages as $language) {
            $xml .= '    <sitemap>' . PHP_EOL;
            // Use path instead of route to avoid middleware issues for the index itself
            $xml .= '        <loc>' . url($language->code . '/sitemap.xml') . '</loc>' . PHP_EOL;
            $xml .= '        <lastmod>' . date('c') . '</lastmod>' . PHP_EOL;
            $xml .= '    </sitemap>' . PHP_EOL;
        }

        $xml .= '</sitemapindex>';

        return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * Generate a localized sitemap for a specific locale.
     */
    public function localized($locale)
    {
        $language = Language::where('code', $locale)->where('is_active', 1)->first();
        if (!$language) {
            abort(404);
        }

        // Temporarily set the locale for route generation if necessary
        // although passing 'locale' to route() should work

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . PHP_EOL;

        // Home Page
        $this->addUrl($xml, url($locale), '1.0', 'daily');

        // Products
        $products = Product::where('published', 1)->where('approved', 1)->get();
        foreach ($products as $product) {
            $this->addUrl($xml, route('product', ['slug' => $product->slug, 'locale' => $locale]), '0.8', 'weekly');
        }

        // Categories
        $categories = Category::all();
        foreach ($categories as $category) {
            if ($category->slug) {
                $this->addUrl($xml, route('products.category', ['category_slug' => $category->slug, 'locale' => $locale]), '0.7', 'weekly');
            }
        }

        // Custom Pages
        $pages = Page::all();
        foreach ($pages as $page) {
            if ($page->slug) {
                $this->addUrl($xml, route('custom-pages.show_custom_page', ['slug' => $page->slug, 'locale' => $locale]), '0.6', 'monthly');
            }
        }

        $xml .= '</urlset>';

        return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * Helper to add a URL entry to the XML string.
     */
    private function addUrl(&$xml, $url, $priority, $changefreq)
    {
        $xml .= '    <url>' . PHP_EOL;
        $xml .= '        <loc>' . htmlspecialchars($url) . '</loc>' . PHP_EOL;
        $xml .= '        <lastmod>' . date('c') . '</lastmod>' . PHP_EOL;
        $xml .= '        <changefreq>' . $changefreq . '</changefreq>' . PHP_EOL;
        $xml .= '        <priority>' . $priority . '</priority>' . PHP_EOL;
        $xml .= '    </url>' . PHP_EOL;
    }
}
