<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class SeoToolsController extends Controller
{
    /**
     * Display the SEO tools management page.
     */
    public function index()
    {
        $group = 'page';
        $page_slug = 'seo-tools';

        $robotsPath = public_path('robots.txt');
        $sitemapPath = public_path('sitemap.xml');

        $robotsContent = File::exists($robotsPath)
            ? File::get($robotsPath)
            : "User-agent: *\nAllow: /\n\nSitemap: " . url('sitemap.xml');

        $sitemapContent = File::exists($sitemapPath)
            ? File::get($sitemapPath)
            : '<?xml version="1.0" encoding="UTF-8"?>' . "\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n</urlset>";

        // Get list of all sitemap files in public folder
        $sitemapFiles = [];
        $files = File::glob(public_path('sitemap*.xml'));
        foreach ($files as $file) {
            try {
                $xml = simplexml_load_file($file);
                $urlCount = $xml->count(); // Counts 'url' or 'sitemap' elements
                $sitemapFiles[] = [
                    'name' => basename($file),
                    'size' => round(File::size($file) / 1024, 2) . ' KB', // Size in KB
                    'url_count' => $urlCount,
                    'last_modified' => date('Y-m-d H:i:s', File::lastModified($file)),
                ];
            } catch (\Exception $e) {
                // Log error if XML is invalid
                Log::error("Could not parse XML file: " . basename($file));
            }
        }


        return view('content.pages.seo_tools.index', compact('group', 'page_slug', 'robotsContent', 'sitemapContent', 'sitemapFiles'));
    }

    /**
     * Save robots.txt content and optionally regenerate the sitemap.
     */
    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'robots' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.seo_tools.index')
                ->withErrors($validator)
                ->withInput();
        }

        File::put(public_path('robots.txt'), $request->input('robots', ''));

        if ($request->has('regenerate_sitemap')) {
            $this->generateSitemap();
        }

        return redirect()->route('admin.seo_tools.index')->with('success', 'Đã cập nhật dữ liệu thành công!');
    }

    /**
     * Generate the sitemap.xml file.
     */
    private function generateSitemap()
    {
        $sitemap = Sitemap::create();

        // Add Posts to Sitemap
        Post::where('status', 'published')->where('is_indexable', true)->with('translations')->get()->each(function (Post $post) use ($sitemap) {
            foreach ($post->translations as $translation) {
                if ($translation->slug) {
                    $url = route('blog.resolver', [
                        'locale' => $translation->locale_code,
                        'slug' => $translation->slug
                    ]);
                    $sitemap->add(
                        Url::create($url)
                            ->setLastModificationDate($post->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.8)
                    );
                }
            }
        });

        // Add Categories to Sitemap
        Category::with('translations')->get()->each(function (Category $category) use ($sitemap) {
            foreach ($category->translations as $translation) {
                if ($translation->slug) {
                    $url = route('blog.resolver', [
                        'locale' => $translation->locale_code,
                        'slug' => $translation->slug
                    ]);
                    $sitemap->add(
                        Url::create($url)
                            ->setLastModificationDate($category->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                            ->setPriority(0.7)
                    );
                }
            }
        });

        $sitemap->writeToFile(public_path('sitemap.xml'));
    }
}
