<?php namespace Depcore\AltTextAi\Console;

use Carbon\Carbon;
use Depcore\AltTextAi\Classes\AltTextApi;
use Illuminate\Console\Command;
use System\Models\File;

/**
 * Bulk Update Command
 *
 * Generates alt text for all images without descriptions within a date range.
 * Useful for processing existing images that were uploaded before the plugin was installed.
 *
 * @package Depcore\AltTextAi\Console
 * @link https://docs.octobercms.com/3.x/extend/console-commands.html
 */
class BulkUpdate extends Command
{
    /**
     * @var string The command signature (name and arguments).
     */
    protected $signature = 'alttextai:bulkupdate {date_from?} {date_to?}';

    /**
     * @var string The command description shown in help.
     */
    protected $description = 'Generate alt text for images without descriptions. Optionally filter by date range (date_from and date_to).';

    /**
     * Handle the console command.
     *
     * Queries all images without descriptions, optionally filtered by date range.
     * Sends each image to AltText.ai API for alt text generation.
     * Shows progress bar during processing.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $date_from = $this->argument('date_from');
        $date_to = $this->argument('date_to');

        $date_from = $date_from ? Carbon::parse($date_from)->startOfDay() : null;
        $date_to   = $date_to   ? Carbon::parse($date_to)->endOfDay()   : null;

        /** @var File[] $files */
        $files = File::query()
            ->where(['description' => null])
            ->when($date_from, function ($q) use ($date_from) {
                $q->where('created_at', '>=', $date_from);
            })
            ->when($date_to, function ($q) use ($date_to) {
                $q->where('created_at', '<=', $date_to);
            })
            ->get();

        $filtered = [];

        foreach ($files as $file) {
            $type = $file->getContentType();
            if (str_contains($type, 'image')) {
                $filtered[] = $file;
            }
        }

        $num = count($filtered);

        if ($num === 0) {
            $this->info('No images without descriptions found.');
            return;
        }

        if (!$this->confirm("Processing {$num} files. Continue?")) {
            return;
        }

        $api = new AltTextApi();
        $this->output->progressStart($num);
        foreach ($filtered as $file) {
            try {
                $api->promptGeneration($file);
            } catch (\Exception $exception) {
                $this->error("Error processing file {$file->getUrl()}: {$exception->getMessage()}");
            }
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
    }
}
