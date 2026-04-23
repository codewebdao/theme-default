<?php
namespace App\Blocks\Schema;

class SchemaGraph {
    protected $context = 'https://schema.org';
    protected $graph = [];
    protected $baseUrl;
    
    public function __construct($baseUrl = null) {
        $this->baseUrl = $baseUrl ?? base_url();
    }
    
    /**
     * Add schema item to graph
     */
    public function addItem($schemaItem) {
        if ($schemaItem instanceof SchemaBlock) {
            $this->graph[] = $schemaItem->getSchemaData();
        } elseif (is_array($schemaItem)) {
            $this->graph[] = $schemaItem;
        }
        return $this;
    }
    
    /**
     * Get complete schema graph
     */
    public function getGraph() {
        return [
            '@context' => $this->context,
            '@graph' => $this->graph
        ];
    }
    
    /**
     * Render schema as JSON-LD
     */
    public function render() {
        $schema = $this->getGraph();
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
    }
    
    /**
     * Generate @id for schema items
     */
    public function generateId($type, $suffix = '') {
        $baseId = rtrim($this->baseUrl, '/') . '/';
        return $baseId . '#' . strtolower($type) . ($suffix ? '-' . $suffix : '');
    }
}
