<?php

declare(strict_types=1);

namespace App\Support\VHeart\DataExport;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Normalizer\TextNormalizerInterface;
use RuntimeException;
use SensitiveParameter;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Throwable;
use ZipArchive;

readonly class DataExporter
{
    /**
     * @param  list<ExportSection>  $sections
     */
    public function __construct(private array $sections) {}

    /**
     * Returns the zip path containing the exported data
     *
     * **Make sure to delete the file as soon as possible (e.g. after download by user)**
     *
     * @return non-empty-string
     *
     * @throws Throwable
     */
    public function export(User $user): string
    {
        $temporaryDirectory = TemporaryDirectory::make()
            ->deleteWhenDestroyed();

        $includedSections = [];
        $skippedSections = [];

        File::makeDirectory($temporaryDirectory->path('data/'), force: true);

        foreach ($this->sections as $section) {
            if (! $section->shouldExport($user)) {
                $skippedSections[] = $section;

                continue;
            }
            $sectionFilePath = $temporaryDirectory->path('data/'.$section->slug()).'.json';

            $this->writeExport($sectionFilePath, $section->export($user));
            $includedSections[] = $section;
        }

        $markdown = view('exports.markdown', [
            'user' => $user,
            'sections' => $includedSections,
            'skippedSections' => $skippedSections,
            'exportedAt' => now()->toIso8601ZuluString(),
        ])->render();

        // could probably skip markdown and directly generate html but we want it to be simple
        // this also is a nice to have for anyone who wants to read it as (mostly) text instead of html i guess
        File::put($temporaryDirectory->path('README.md'), $markdown);

        File::put($temporaryDirectory->path('README.html'), view('exports.html', [
            'user' => $user,
            'content' => Str::markdown($markdown, [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
                'heading_permalink' => [
                    'id_prefix' => '',
                    'apply_id_to_heading' => true,
                    'insert' => 'none',
                ],
                'slug_normalizer' => [
                    'instance' => new class implements TextNormalizerInterface
                    {
                        public function normalize(string $text, array $context = []): string
                        {
                            return Str::slug($text, language: app()->currentLocale());
                        }
                    },
                ],
            ], [new HeadingPermalinkExtension()]),
        ])->render());

        // makes it easier for users to make a custom reader, on any manifest change we need to increase the version though
        File::put($temporaryDirectory->path('manifest.json'), json_encode([
            'version' => 1,
            'sections' => [
                'included' => array_map(static fn (ExportSection $section) => $section->slug(), $includedSections),
                'skipped' => array_map(static fn (ExportSection $section) => $section->slug(), $skippedSections),
            ],
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

        return $this->makeZip($temporaryDirectory);
    }

    private function makeZip(TemporaryDirectory $directory): string
    {
        $files = File::allFiles($directory->path());
        $zipName = 'export-'.Str::uuid();
        $zipPath = $directory->path("$zipName.zip");
        $storagePath = Storage::disk('exports')->path("$zipName.zip");

        $zip = new ZipArchive;
        if (($result = $zip->open($zipPath, ZipArchive::CREATE)) !== true) {
            throw new RuntimeException("Could not create zip file at $zipPath: $result");
        }

        foreach ($files as $file) {
            $zip->addFile($file->getPathname(), str_replace('\\', '/', $file->getRelativePathname()));
        }

        $zip->close();

        File::move($zipPath, $storagePath);

        return $storagePath;
    }

    private function writeExport(string $path, #[SensitiveParameter] array|Collection|LazyCollection $exportData): void
    {
        $jsonFlags = JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        if (! $exportData instanceof LazyCollection) {
            File::put($path, json_encode($exportData, $jsonFlags));

            return;
        }

        $handle = fopen($path, 'wb') ?: throw new RuntimeException("Cannot open '$path' for writing.");

        try {
            fwrite($handle, "[\n");

            foreach ($exportData->values() as $i => $item) {
                fwrite($handle, ($i === 0 ? '' : ",\n").json_encode($item, $jsonFlags));
            }

            fwrite($handle, "\n]");
        } finally {
            fclose($handle);
        }
    }
}
