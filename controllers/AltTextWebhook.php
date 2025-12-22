<?php namespace Depcore\AltTextAi\Controllers;

use Backend\Classes\Controller;
use Depcore\AltTextAi\Classes\AltTextApi;
use Depcore\AltTextAi\Models\AltTextSettings;
use Illuminate\Http\Request;
use Log;
use System\Models\File;

/**
 * Alt Text Webhook Backend Controller
 *
 * Handles incoming webhook notifications from AltText.ai service.
 * Processes uploaded and deleted image events to update alt text descriptions.
 *
 * @package Depcore\AltTextAi\Controllers
 * @link https://docs.octobercms.com/4.x/extend/system/controllers.html
 * @link https://alttext.ai/api/v1
 */
class AltTextWebhook extends Controller
{
    /**
     * Handle incoming webhook from AltText.ai
     *
     * Validates the webhook payload and processes image events.
     * Webhooks are authenticated via metadata verification to ensure
     * they originated from this OctoberCMS instance.
     *
     * @param Request $request The incoming HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request)
    {
        // Parse and validate JSON payload
        $payload = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('AltText.ai webhook received invalid JSON', [
                'error' => json_last_error_msg(),
                'content' => $request->getContent()
            ]);
            return response()->json(['message' => 'Invalid JSON'], 400);
        }

        // Validate event type
        if (empty($payload['event']) || !in_array($payload['event'], ['uploaded', 'deleted'], true)) {
            Log::warning('AltText.ai webhook received invalid event type', [
                'event' => $payload['event'] ?? 'none'
            ]);
            return response()->json(['message' => 'Invalid event'], 400);
        }

        // Validate images data structure
        if (empty($payload['data']['images']) || !is_array($payload['data']['images'])) {
            Log::warning('AltText.ai webhook missing images array');
            return response()->json(['message' => 'Missing images array'], 400);
        }

        $event = $payload['event'];
        $processedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($payload['data']['images'] as $img) {
            // Verify this webhook is intended for this OctoberCMS instance
            if (!isset($img['metadata']) || !(new AltTextApi())->verifyDestination($img['metadata'])) {
                $skippedCount++;
                Log::info('AltText.ai webhook skipped image - metadata mismatch', [
                    'asset_id' => $img['asset_id'] ?? 'unknown'
                ]);
                continue;
            }

            try {
                if ($event === 'uploaded') {
                    $extracted = (new AltTextApi())->extractAltText($img);
                    
                    if (!$extracted) {
                        $skippedCount++;
                        continue;
                    }

                    ['id' => $id, 'alt_text' => $altText] = $extracted;

                    $item = File::find($id);
                    if ($item) {
                        $item->description = $altText;
                        $item->save();
                        $processedCount++;
                        
                        Log::info('AltText.ai webhook updated file description', [
                            'file_id' => $id,
                            'alt_text_length' => strlen($altText)
                        ]);
                    } else {
                        $skippedCount++;
                        Log::warning('AltText.ai webhook - file not found', ['file_id' => $id]);
                    }
                } elseif ($event === 'deleted') {
                    // Future: Handle deleted event if needed
                    $processedCount++;
                }
            } catch (\Exception $e) {
                $errorCount++;
                Log::error('AltText.ai webhook processing error', [
                    'asset_id' => $img['asset_id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        Log::info('AltText.ai webhook processing complete', [
            'event' => $event,
            'processed' => $processedCount,
            'skipped' => $skippedCount,
            'errors' => $errorCount
        ]);

        return response()->json([
            'message' => 'Webhook processed',
            'processed' => $processedCount,
            'skipped' => $skippedCount,
            'errors' => $errorCount
        ], 200);
    }
}
