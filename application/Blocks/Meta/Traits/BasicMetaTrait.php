<?php
namespace App\Blocks\Meta\Traits;

trait BasicMetaTrait {
    /**
     * Add basic meta tags
     */
    protected function addBasicMetaTags() {
        $this->metaBlock
            ->addcustom('<meta charset="UTF-8">')
            ->addcustom('<meta name="viewport" content="width=device-width, initial-scale=1.0">')
            ->addcustom('<meta name="generator" content="' . $this->config['generator'] . '">')
            ->addcustom('<meta name="language" content="' . $this->config['language'] . '">')
            ->addcustom('<meta name="revisit-after" content="7 days">')
            ->addcustom('<meta name="author" content="' . $this->config['author'] . '">')
            ->addcustom('<meta name="copyright" content="' . $this->config['copyright'] . '">')
            ->addcustom('<meta name="rating" content="general">')
            ->addcustom('<meta name="distribution" content="global">')
            ->addcustom('<meta name="coverage" content="Worldwide">')
            ->addcustom('<meta name="target" content="all">')
            ->addcustom('<meta name="HandheldFriendly" content="true">')
            ->addcustom('<meta name="MobileOptimized" content="width">')
            ->addcustom('<meta name="apple-mobile-web-app-capable" content="yes">')
            ->addcustom('<meta name="apple-mobile-web-app-status-bar-style" content="black">')
            ->addcustom('<meta name="format-detection" content="telephone=no">')
            ->addcustom('<meta name="format-detection" content="date=no">')
            ->addcustom('<meta name="format-detection" content="address=no">')
            ->addcustom('<meta name="format-detection" content="email=no">')
            ->addcustom('<meta name="theme-color" content="' . $this->config['theme_color'] . '">');

        return $this;
    }
} 