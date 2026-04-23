<?php
namespace App\Blocks\Meta;

class MetaBlock {
    protected $title;
    protected $description;
    protected $keywords;
    protected $robots;
    protected $canonical;
    protected $ogTags = [];
    protected $twitterTags = [];
    protected $schemaTags = [];
    protected $customTags = [];

    /**
     * Set title
     */
    public function title($title) {
        $this->title = $title;
        return $this;
    }

    /**
     * Set description
     */
    public function description($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * Set keywords
     */
    public function keywords($keywords) {
        $this->keywords = $keywords;
        return $this;
    }

    /**
     * Set robots
     */
    public function robots($robots) {
        $this->robots = $robots;
        return $this;
    }

    /**
     * Set canonical URL
     */
    public function canonical($url) {
        $this->canonical = $url;
        return $this;
    }

    /**
     * Add Open Graph tag
     */
    public function og($property, $content) {
        $this->ogTags[$property] = $content;
        return $this;
    }

    /**
     * Add Twitter Card tag
     */
    public function twitter($name, $content) {
        $this->twitterTags[$name] = $content;
        return $this;
    }

    /**
     * Add Schema tag
     */
    public function schema($schema) {
        $this->schemaTags[] = $schema;
        return $this;
    }

    /**
     * Add custom tag
     */
    public function custom($tag) {
        $this->customTags[] = $tag;
        return $this;
    }

    /**
     * Process value before rendering
     */
    protected function sanitizeValue($value) {
        if (is_null($value)) {
            return '';
        }
        if (is_array($value)) {
            return implode(', ', array_filter($value));
        }
        return (string) $value;
    }

    /**
     * Render all meta tags
     */
    public function render() {
        $tags = [];

        // Basic meta tags
        if ($this->title) {
            $tags[] = '<title>' . htmlspecialchars($this->sanitizeValue($this->title)) . '</title>';
        }
        if ($this->description) {
            $tags[] = '<meta name="description" content="' . htmlspecialchars($this->sanitizeValue($this->description)) . '">';
        }
        if ($this->keywords) {
            $tags[] = '<meta name="keywords" content="' . htmlspecialchars($this->sanitizeValue($this->keywords)) . '">';
        }
        if ($this->robots) {
            $tags[] = '<meta name="robots" content="' . htmlspecialchars($this->sanitizeValue($this->robots)) . '">';
        }
        if ($this->canonical) {
            $tags[] = '<link rel="canonical" href="' . htmlspecialchars($this->sanitizeValue($this->canonical)) . '">';
        }

        // Open Graph tags
        foreach ($this->ogTags as $property => $content) {
            $tags[] = '<meta property="og:' . htmlspecialchars($property) . '" content="' . htmlspecialchars($this->sanitizeValue($content)) . '">';
        }

        // Twitter Card tags
        foreach ($this->twitterTags as $name => $content) {
            $tags[] = '<meta name="twitter:' . htmlspecialchars($name) . '" content="' . htmlspecialchars($this->sanitizeValue($content)) . '">';
        }

        // Schema tags
        if (!empty($this->schemaTags)) {
            $tags[] = '<script type="application/ld+json">' . json_encode($this->schemaTags) . '</script>';
        }

        // Custom tags
        $tags = array_merge($tags, $this->customTags);

        return implode("\n", $tags);
    }
} 