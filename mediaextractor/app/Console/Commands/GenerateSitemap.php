<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Post;
use App\Models\Category;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap for the website';

    public function handle()
    {
        $sitemap = Sitemap::create();

        // ✅ FIX: Add the 'locale' parameter for the homepage route
        $sitemap->add(Url::create(route('home', ['locale' => 'vi']))
            ->setPriority(1.0)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));

        // Add Posts
        Post::where('status', 'published')->with('translations')->get()->each(function (Post $post) use ($sitemap) {
            $translation = $post->translations->where('locale_code', 'vi')->first();

            if ($translation && $translation->slug) {
                // Assuming localized_route helper doesn't need explicit locale in console
                // If it does, you would change it to route('blog.resolver', ['locale' => 'vi', 'slug' => ...])
                $url = localized_route('blog.resolver', ['slug' => $translation->slug]);

                $sitemap->add(Url::create($url)
                    ->setLastModificationDate($post->updated_at)
                    ->setPriority(0.8)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));
            }
        });

        // Add Categories
        Category::where('is_active', 1)->with('translations')->get()->each(function (Category $category) use ($sitemap) {
            $translation = $category->translations->where('locale_code', 'vi')->first();

            if ($translation && $translation->slug) {
                $url = localized_route('blog.resolver', ['slug' => $translation->slug]);

                $sitemap->add(Url::create($url)
                    ->setPriority(0.7)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY));
            }
        });

        // Write the sitemap to a file
        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully!');
    }
}
