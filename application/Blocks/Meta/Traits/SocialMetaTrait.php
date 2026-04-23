<?php
namespace App\Blocks\Meta\Traits;

trait SocialMetaTrait {
    protected function addSocialMetaTags($title, $description, $url, $image) {
        $this->metaBlock
            ->addog('locale', 'en_US')
            ->addog('type', 'website')
            ->addog('title', $title)
            ->addog('description', $description)
            ->addog('url', $url)
            ->addog('site_name', option('site_title'))
            ->addog('updated_time', date('c'))
            ->addtwitter('card', 'summary_large_image')
            ->addtwitter('title', $title)
            ->addtwitter('description', $description)
            ->addtwitter('site', '@' . option('site_title'))
            ->addtwitter('creator', '@' . option('site_title'))
            // ->addtwitter('label1', 'Written by')
            ->addtwitter('data1', option('site_title'));
            // ->addtwitter('label2', 'Reading time')
            // ->addtwitter('data2', 'Less than a minute');

        return $this;
    }
} 