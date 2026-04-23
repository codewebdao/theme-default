<?php

namespace Commands;

use System\Core\BaseCommand;
use System\Database\DB;

/**
 * RenameTable Command
 *
 * Usage:
 *   php cmd rename:table <old_name> <new_name> [--dry-run]
 */
class RenameTable extends BaseCommand
{
    protected $name        = 'rename:table';
    protected $description = 'Rename an existing database table';

    public function execute(array $arguments = [], array $options = []): void
    {
        $old = $arguments[0] ?? null;
        $new = $arguments[1] ?? null;

        if (!$old || !$new) {
            $this->output('❌ Old and new table names are required', 'error');
            $this->showHelp();
            return;
        }

        // Initialize DB if not yet
        $this->initializeDB();

        $schema = DB::schema();

        // Dry-run option
        $dryRun = isset($options['dry-run']) || isset($options['dry_run']);
        if ($dryRun) {
            $schema->dryRun(true);
            $this->output("DRY RUN: Would rename table '{$old}' to '{$new}'", 'info');
        }

        // Use prefixed names because BaseSchema::renameTable expects raw, but DB::tableName adds prefix
        $prefOld = DB::tableName($old);
        $prefNew = DB::tableName($new);

        try {
            $result = $schema->renameTable($prefOld, $prefNew);

            if (is_array($result)) {
                // dry-run SQL list
                $this->output("\nSQL statements to be executed:", 'info');
                foreach ($result as $sql) {
                    $this->output($sql.';');
                }
            } else {
                $this->output("✅ Renamed table '{$prefOld}' to '{$prefNew}' successfully", 'success');
            }
        } catch (\Exception $e) {
            $this->output('❌ Error renaming table: '.$e->getMessage(), 'error');
        }
    }

    private function initializeDB(): void
    {
        try {
            DB::connection();
        } catch (\RuntimeException $e) {
            $dbConfig = include_once PATH_ROOT.'/application/Config/Database.php';
            DB::init($dbConfig);
        }
    }

    public function showHelp(): void
    {
        $this->output('📖 Rename Table Command');
        $this->output('=====================');
        $this->output('');
        $this->output('Description: Rename an existing database table');
        $this->output('Usage: php cmd rename:table <old_name> <new_name> [--dry-run]');
        $this->output('');
        $this->output('Options:');
        $this->output('  --dry-run   Show SQL statements without executing');
    }
}

