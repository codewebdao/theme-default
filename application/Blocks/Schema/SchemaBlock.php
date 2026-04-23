<?php
namespace App\Blocks\Schema;
use System\Core\BaseBlock;

class SchemaBlock extends BaseBlock {
    // Array containing schema data according to JSON-LD standard
    protected $schemaData = [];
    protected $schemaType = '';
    protected $schemaContext = 'https://schema.org';
    protected $schemaChildren = [];

    public function __construct() {
        $this->setLabel('Schema Block');
        $this->setName('Schema');
        $this->setProps([
            'layout'      => 'default',  // Layout file name: default.php
        ]);
    }

    public function setSchemaType($type) {
        $this->schemaType = $type;
        return $this;
    }

    /**
     * Set complete schema data
     *
     * @param array $data Array of schema properties
     * @return $this
     */
    public function setSchemaData($data) {
        $this->schemaData = array_merge($this->schemaData, $data);
        return $this;
    }

    /**
     * Add or update a schema property
     *
     * @param string $key Property name (e.g., 'name', 'address')
     * @param mixed  $value Property value
     * @return $this
     */
    public function addSchemaProperty($key, $value) {
        $this->schemaData[$key] = $value;
        return $this;
    }

    /**
     * Add a child schema to an array property (e.g., 'department')
     *
     * @param string      $key   Name of the property containing schema array
     * @param SchemaBlock $child A SchemaBlock object to be nested
     * @return $this
     */
    public function addSchemaChild($key, $child) {
        if ($child instanceof SchemaBlock) {
            $this->schemaChildren[$key] = $child;
        }
        return $this;
    }

    /**
     * Get schema data as array
     *
     * @return array
     */
    public function getSchemaData() {
        $schema = [
            '@type' => $this->schemaType
        ];

        // Merge schema data
        $schema = array_merge($schema, $this->schemaData);

        // Add children schemas
        foreach ($this->schemaChildren as $key => $child) {
            $schema[$key] = $child->getSchemaData();
        }

        return $schema;
    }

    /**
     * Function to process data for the block.
     * The returned value will be extracted to the view file.
     *
     * @return array
     */
    public function handleData() {
        return ['schemaData' => $this->schemaData];
    }

    /**
     * Return JSON-LD of schema (can be used for debug)
     *
     * @return string
     */
    public function render($noscript = false) {
        $schema = [
            '@context' => $this->schemaContext,
            '@type' => $this->schemaType
        ];

        // Merge schema data
        $schema = array_merge($schema, $this->schemaData);

        // Add children schemas
        foreach ($this->schemaChildren as $key => $child) {
            $schema[$key] = $child->getSchemaData();
        }
        if ($noscript) {
            return json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }else{
            return '<script type="application/ld+json">'.json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).'</script>';
        }
    }
}
