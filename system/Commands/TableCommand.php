<?php

namespace System\Commands;

class TableCommand
{

    protected $db;

    public function __construct($dbConnection)
    {
        // Receive database connection from outside
        $this->db = $dbConnection;
    }

    /**
     * Run Artisan Command to synchronize schema
     */
    public function handle($tableName)
    {
        $modelClass = "\\App\\Models\\" . ucfirst($tableName) . 'Model';
        // Initialize model
        $model = new $modelClass();
        $table = $model->_table();
        $schema = $model->_schema();
        // Check table and synchronize structure
        $this->syncTableSchema($table, $schema);

        echo "Synchronized table {$table}\n";
    }

    /**
     * Synchronize table structure with schema
     */
    protected function syncTableSchema($table, $schema)
    {
        if (!$this->tableExists($table)) {
            $this->createTable($table, $schema);
        } else {
            $this->updateTable($table, $schema);
        }
    }

    /**
     * Check if table exists
     */
    protected function tableExists($table)
    {
        $query = "SHOW TABLES LIKE '{$table}'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Create new table from schema
     */
    protected function createTable($table, $schema)
    {
        $columns = [];

        foreach ($schema as $column => $attributes) {
            $columns[] = $this->buildColumnTable($column, $attributes);
        }
        $configdb = db_config();
        $charset = $configdb['db_charset'] ?? 'utf8mb4';
        $collate = $configdb['db_collate'] ?? 'utf8mb4_unicode_ci';

        $query = "CREATE TABLE {$table} (" . implode(', ', $columns) . ") ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate};";
        echo $query;
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        echo "Created table {$table}\n";
    }

    /**
     * Update current table with new schema
     */
    protected function updateTable($table, $schema)
    {
        // Get current columns list in table
        $currentColumns = $this->getCurrentColumns($table);
        $dbColumns = [];
        if ($currentColumns && count($currentColumns) > 0) {
            foreach ($currentColumns as $dbField) {
                $dbColumns[$dbField["Field"]] = $dbField;
            }
        }
        print_r($dbColumns);
        foreach ($schema as $column => $attributes) {
            if (!array_key_exists($column, $dbColumns)) {
                // Column doesn't exist, add new
                $this->addColumn($table, $column, $attributes);
            } else {
                // Column exists, check and update if needed
                print_r($dbColumns[$column]);
                print_r($attributes);
                if ($this->needsModification($column, $dbColumns[$column], $attributes)) {
                    $this->modifyColumn($table, $column, $attributes);
                }
            }
        }
    }


    protected function buildColumnTable($column, $attributes)
    {
        $definition = "{$column} {$attributes['type']}";

        if (isset($attributes['null']) && !$attributes['null']) {
            $definition .= " NULL";
        }

        if (isset($attributes['auto_increment']) && $attributes['auto_increment'] === true) {
            $definition .= " AUTO_INCREMENT";
        }

        if (isset($attributes['key'])) {
            if ($attributes['key'] == 'primary') {
                $definition .= " PRIMARY KEY";
            } else if ($attributes['key'] == 'unique') {
                $definition .= " UNIQUE";
            }
        }

        if (isset($attributes['default'])) {
            if ('CURRENT_TIMESTAMP' == $attributes['default']) {
                $definition .= " DEFAULT {$attributes['default']}";
            } else {
                $definition .= " DEFAULT '{$attributes['default']}'";
            }
        }

        if (isset($attributes['on_update'])) {
            $definition .= " ON UPDATE " . $attributes['on_update'];
        }

        return $definition;
    }

    /**
     * Build column definition from schema
     */
    protected function buildColumnUpdate($column, $attributes)
    {
        $definition = "{$column} {$attributes['type']}";

        if (isset($attributes['null'])) {
            if (!$attributes['null']) {
                $definition .= " NOT NULL";
            } else {
                $definition .= " NULL";
            }
        }


        if (isset($attributes['default'])) {
            if ('CURRENT_TIMESTAMP' == $attributes['default']) {
                $definition .= " DEFAULT {$attributes['default']}";
            } else {
                $definition .= " DEFAULT '{$attributes['default']}'";
            }
        }

        if (isset($attributes['on_update'])) {
            $definition .= " ON UPDATE " . $attributes['on_update'];
        }

        if (isset($attributes['auto_increment']) && $attributes['auto_increment'] === true) {
            $definition .= " AUTO_INCREMENT";
        } else {
            if (isset($attributes['key'])) {
                if ($attributes['key'] == 'primary') {
                    $definition .= " PRIMARY KEY";
                } else if ($attributes['key'] == 'unique') {
                    $definition .= " , ADD UNIQUE (`$column`) ";
                } else if ($attributes['key'] == 'index') {
                    $definition .= " , ADD INDEX (`$column`) ";
                }
            }
        }


        return $definition;
    }

    /**
     * Check if column needs modification
     */
    protected function needsModification($column, $currentColumn, $newAttributes)
    {
        // Compare Type property
        if (strtolower($currentColumn['Type']) !== strtolower($newAttributes['type'])) {
            echo $column . ' change TYPE from ' . $currentColumn['Type'] . ' to ' . $newAttributes['type'];
            return true;
        }
        // Compare Null property
        $currentNull = strtolower($currentColumn['Null']) === 'yes' ? true : false;
        $newNull = isset($newAttributes['null']) && $newAttributes['null'] ? true : !empty($newAttributes['null']);
        if ($currentNull !== $newNull) {
            echo $column . ' change Null from ' . (int)$currentNull . ' to ' . (int)$newNull;
            return true;
        }
        // Compare Key property (Primary, Unique)
        $currentKey = strtolower($currentColumn['Key']);
        switch ($currentKey) {
            case 'mul':
                $currentKey = 'index';
                break;
            case 'uni':
                $currentKey = 'unique';
                break;
            case 'pri':
                $currentKey = 'primary';
                break;
            default:
                $currentKey = '';
                break;
        }
        $newKey = isset($newAttributes['key']) ? strtolower($newAttributes['key']) : '';
        if ($currentKey !== $newKey) {
            echo $column . ' change Key from ' . $currentKey . ' to ' . $newKey;
            return true;
        }

        // Compare default value (Default)
        $currentDefault = $currentColumn['Default'] ?? '';
        $newDefault = $newAttributes['default'] ?? '';
        if ($currentDefault != $newDefault) {
            echo $column . ' change ValueDefault from ' . $currentDefault . ' to ' . $newDefault;
            return true;
        }

        // Compare Extra property (auto_increment)
        $currentExtra = strpos(strtolower($currentColumn['Extra']), 'auto_increment') !== FALSE ? true : false;
        $newExtra = isset($newAttributes['auto_increment']) && $newAttributes['auto_increment'] ? true : false;
        if ($currentExtra !== $newExtra) {
            echo $column . ' change auto_increment from ' . (int)$currentExtra . ' to ' . (int)$newExtra;
            return true;
        }

        return false;
    }

    /**
     * Get current columns list
     */
    protected function getCurrentColumns($table)
    {
        $query = "SHOW COLUMNS FROM {$table}";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Add new column to table
     */
    protected function addColumn($table, $column, $attributes)
    {
        $query = "ALTER TABLE {$table} ADD " . $this->buildColumnUpdate($column, $attributes);
        echo $query;
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        echo "Added column {$column} to table {$table}\n";
    }

    /**
     * Update current column
     */
    protected function modifyColumn($table, $column, $attributes)
    {
        $query = "ALTER TABLE {$table} MODIFY " . $this->buildColumnUpdate($column, $attributes);
        echo $query;
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        echo "Updated column {$column} in table {$table}\n";
    }
}
