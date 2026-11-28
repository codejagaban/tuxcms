<?php

namespace App\Services\Seo;

use App\Models\Post;
use App\Support\Site;
use Illuminate\Support\Collection;

/**
 * Builds the blog's RSS feed (`/blog/feed.xml`) from the newest published
 * posts, so readers and other services can follow the blog without visiting.
 */
class FeedGenerator
{
    /** @param Collection<int, Post> $posts newest first */
    public function generate(Collection $posts): string
    {
        $base = rtrim(Site::url(), '/');
        $x = fn (?string $text) => htmlspecialchars((string) $text, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">',
            '<channel>',
            '  <title>'.$x(Site::name().' blog').'</title>',
            '  <link>'.$x($base.'/blog/').'</link>',
            '  <description>'.$x(Site::description() ?: 'Posts from '.Site::name()).'</description>',
            '  <atom:link href="'.$x($base.'/blog/feed.xml').'" rel="self" type="application/rss+xml"/>',
        ];

        if ($latest = $posts->max('published_at')) {
            $lines[] = '  <lastBuildDate>'.$latest->toRssString().'</lastBuildDate>';
        }

        foreach ($posts->take(20) as $post) {
            $lines[] = '  <item>';
            $lines[] = '    <title>'.$x($post->title).'</title>';
            $lines[] = '    <link>'.$x($base.$post->path()).'</link>';
            $lines[] = '    <guid isPermaLink="true">'.$x($base.$post->path()).'</guid>';
            if ($post->published_at) {
                $lines[] = '    <pubDate>'.$post->published_at->toRssString().'</pubDate>';
            }
            foreach ($post->tagNames() as $tag) {
                $lines[] = '    <category>'.$x($tag).'</category>';
            }
            $lines[] = '    <description>'.$x($post->summary(300)).'</description>';
            $lines[] = '  </item>';
        }

        $lines[] = '</channel>';
        $lines[] = '</rss>';

        return implode("\n", $lines)."\n";
    }
}
