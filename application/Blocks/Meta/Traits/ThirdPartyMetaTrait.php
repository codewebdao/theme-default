<?php
namespace App\Blocks\Meta\Traits;

trait ThirdPartyMetaTrait {
    protected function addThirdPartyTags() {
        // Google Analytics
        if (option('google_analytics_id')) {
            $this->metaBlock->addcustom('
                <script async src="https://www.googletagmanager.com/gtag/js?id=' . option('google_analytics_id') . '"></script>
                <script>
                    window.dataLayer = window.dataLayer || [];
                    function gtag(){dataLayer.push(arguments);}
                    gtag("js", new Date());
                    gtag("config", "' . option('google_analytics_id') . '");
                </script>
            ');
        }

        // Google AdSense
        if (option('google_adsense_id')) {
            $this->metaBlock->addcustom('
                <meta name="google-adsense-account" content="' . option('google_adsense_id') . '">
            ');
        }

        // DMCA
        if (option('dmca_verification')) {
            $this->metaBlock->addcustom('
                <meta name="dmca-site-verification" content="' . option('dmca_verification') . '" />
            ');
        }

        return $this;
    }
} 