<?php

namespace Commands;

use System\Core\BaseCommand;

/**
 * Assets Build Command
 * Run asset build (combine/minify) for pending signatures in registry.
 * Same as triggered when admin saves Performance settings.
 *
 * Usage: php cmd assets:build
 */
class AssetsBuild extends BaseCommand
{
    protected function initialize(): void
    {
        $this->name = 'assets:build';
        $this->description = 'Build assets (combine/minify) for pending signatures in registry';

        $this->arguments = [];
        $this->options = [
            '--verbose' => 'Show detailed output',
        ];
    }

    public function execute(array $arguments = [], array $options = []): void
    {
        if (function_exists('load_helpers')) {
            load_helpers(['storage']);
        }

        if (!class_exists(\App\Services\Asset\AssetsService::class)) {
            $this->output('AssetsService not found. Asset build system may not be installed.');
            return;
        }

        $verbose = isset($options['verbose']) || isset($options['v']);

        if ($verbose) {
            $this->output('Starting asset build...');
        }

        try {
            $results = \App\Services\Asset\AssetsService::build();
        } catch (\Throwable $e) {
            $this->output('Error: ' . $e->getMessage());
            if (class_exists(\System\Libraries\Logger::class)) {
                \System\Libraries\Logger::error('AssetsBuild command failed: ' . $e->getMessage());
            }
            return;
        }

        $built = $results['built'] ?? 0;
        $skipped = $results['skipped'] ?? 0;
        $errors = $results['errors'] ?? [];

        $this->output("Built: {$built}, Skipped: {$skipped}");

        if (!empty($errors)) {
            $this->output('Errors: ' . count($errors));
            if ($verbose) {
                foreach ($errors as $err) {
                    $key = $err['key'] ?? 'unknown';
                    $msg = $err['message'] ?? 'unknown';
                    $this->output("  - {$key}: {$msg}");
                }
            }
        }

        $this->output('Asset build completed.');
    }

    public function showHelp(): void
    {
        $this->output('Assets Build Command');
        $this->output('');
        $this->output('Usage:');
        $this->output('  php cmd assets:build           # Run build');
        $this->output('  php cmd assets:build --verbose # Show detailed output');
        $this->output('');
        $this->output('Builds minified/combined CSS/JS for signatures recorded in registry.');
        $this->output('Also triggered when admin saves Performance (Scripts & Styles) settings.');
    }
}
