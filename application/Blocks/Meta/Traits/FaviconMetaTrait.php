<?php
namespace App\Blocks\Meta\Traits;

trait FaviconMetaTrait {
    protected function addFaviconTags() {
        $favicon = $this->getImageUrl(option('site_favicon'));
        $this->metaBlock
            ->addcustom('<link rel="icon" href="' . $favicon . '" sizes="32x32" />')
            ->addcustom('<link rel="icon" href="' . $favicon . '" sizes="192x192" />')
            ->addcustom('<link rel="apple-touch-icon" href="' . $favicon . '" />')
            ->addcustom('<meta name="msapplication-TileImage" content="' . $favicon . '" />');

        return $this;
    }
} 