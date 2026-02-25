<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportTranslationsCommand extends Command
{
    protected $signature = 'translations:export';

    protected $description = 'Export and convert Laravel translations to i18n compatible static json files.';

    public function handle(): void
    {
        $this->info('Starting translation export...');

        $locales = collect(config('app.locales', []))->pluck('locale')->toArray();
        $targetRoot = public_path('locales');
        $processedPaths = [];

        if (empty($locales)) {
            $this->warn('No locales found in configuration.');

            return;
        }

        foreach ($locales as $locale) {
            $sourcePath = lang_path($locale);

            if (! File::exists($sourcePath)) {
                $this->warn("Skipping '{$locale}': Directory not found.");

                continue;
            }

            $files = File::allFiles($sourcePath);
            $count = count($files);

            $this->info("Processing '{$locale}' ({$count} files)...");

            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $relativePath = $file->getRelativePathname();
                $namespace = mb_substr($relativePath, 0, -4);

                try {
                    $data = File::getRequire($file->getRealPath());

                    if (is_array($data)) {
                        $convertedData = $this->i18n($data);
                        $targetPath = "{$targetRoot}/{$locale}/{$namespace}.json";

                        File::ensureDirectoryExists(dirname($targetPath));

                        File::put(
                            $targetPath,
                            json_encode($convertedData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)
                        );

                        $processedPaths[] = $targetPath;
                    }
                } catch (Exception $e) {
                    $this->newLine();
                    $this->error("Error processing {$relativePath}: {$e->getMessage()}");
                }
            }
        }

        // cleanup stuff we have removed
        if (File::isDirectory($targetRoot)) {
            $allExistingFiles = File::allFiles($targetRoot);

            foreach ($allExistingFiles as $existingFile) {
                $existingPath = $existingFile->getPathname();

                if (! in_array($existingPath, $processedPaths, true)) {
                    File::delete($existingPath);
                }
            }
        }
        $this->info('Translations have been exported.');
    }

    /**
     * Convert standard Laravel translation keys that look like ":foo"
     * into key structures that are supported by the front-end i18n
     * library, like "{{foo}}".
     */
    protected function i18n(array $data): array
    {
        array_walk_recursive($data, static function (&$value): void {
            if (is_string($value) && str_contains($value, ':')) {
                // Find a Laravel style translation replacement in the string and replace it with
                // one that the front-end is able to use. This won't always be present, especially
                // for complex strings or things where we'd never have a backend component anyway.
                // We strictly require the key to start with a letter/underscore [a-zA-Z_]
                //
                // For example:
                // "Hello :name, the :notifications.0.title notification needs :count actions :foo.0.bar before 12:00."
                //
                // Becomes:
                // "Hello {{name}}, the {{notifications.0.title}} notification needs {{count}} actions {{foo.0.bar}} before 12:00."

                // https://regex101.com/r/dA9Xs6/1
                $value = preg_replace(
                    '/:([a-zA-Z_](?:[\w.-]*\w)?)([^\w:]?|$)/m',
                    '{{$1}}$2',
                    $value
                );
            }
        });

        return $data;
    }
}
