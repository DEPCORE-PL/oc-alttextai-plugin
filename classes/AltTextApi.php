<?php

namespace Depcore\AltTextAi\Classes;

use Config;
use Depcore\AltTextAi\Models\AltTextSettings;
use Illuminate\Support\Facades\Http;
use October\Rain\Exception\ApplicationException;
use Storage;
use System\Models\File;

/**
 * AltText.ai API Client
 *
 * Provides integration with the AltText.ai service for automatic
 * generation of alt text descriptions for images using AI.
 *
 * @package Depcore\AltTextAi\Classes
 * @link https://alttext.ai/api/v1
 */
class AltTextApi
{
    /**
     * AltText.ai API key
     *
     * @var string|null
     */
    protected ?string $apiKey;

    /**
     * Base URL for the AltText.ai API
     *
     * @var string
     */
    protected string $baseUrl;

    /**
     * Constructor - Initialize API client
     *
     * Retrieves the API key from plugin settings and configures the base URL.
     *
     * @param string|null $baseUrl Optional base URL (defaults to https://alttext.ai/api/v1)
     * @throws ApplicationException if API key is not configured in plugin settings
     */
    public function __construct(?string $baseUrl = null)
    {
        // Retrieve API key from plugin settings
        $this->apiKey = AltTextSettings::instance()->apiKey;

        if (empty($this->apiKey)) {
            throw new ApplicationException('AltText API key not configured. Set it in plugin settings.');
        }

        $this->baseUrl = rtrim($baseUrl ?: 'https://alttext.ai/api/v1', '/');
    }
    /**
     * Request alt text generation for a file
     *
     * Sends an image to AltText.ai API for asynchronous alt text generation.
     * The API will process the image and send results back to the webhook endpoint.
     * Metadata is included to verify webhook authenticity.
     *
     * @param File $file The OctoberCMS File model instance to generate alt text for
     * @return bool True if request was successful, false otherwise
     */
    public function promptGeneration(File $file)
    {
        $content = Storage::get($file->getPath());
        $base64 = base64_encode($content);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'X-Api-Key' => $this->apiKey,
        ])->post($this->baseUrl . '/images', [
            'image' => [
                'url' => $file->getUrl(),
                'metadata' => [
                    'oc_website' => \Config::get('app.url'),
                    'oc_hostname' => gethostname(),
                    'oc_file_id' => $file->id
                ]
            ],
            'webhook_url' => Config::get('app.url') . "/altext/webhook",
            'async' => true,
        ]);

        if ($response->successful()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Verify webhook destination matches this OctoberCMS instance
     *
     * Validates that a webhook payload's metadata matches this instance's
     * website URL and hostname to prevent processing webhooks intended
     * for other installations.
     *
     * @param array $metadata The metadata array from the webhook payload
     * @return bool True if metadata matches this instance, false otherwise
     */
    public function verifyDestination(array $metadata)
    {
        if (isset($metadata['oc_website']) && isset($metadata['oc_hostname']) &&
            $metadata['oc_website'] == \Config::get('app.url') &&
            $metadata['oc_hostname'] == gethostname()) {
            return true;
        }
        return false;
    }

    /**
     * Extract alt text data from webhook image payload
     *
     * Parses the webhook image payload and extracts the file ID and
     * generated alt text. Verifies the payload is intended for this instance.
     *
     * @param array $imagePayload The image data from webhook payload
     * @return array|null Array with 'id' and 'alt_text' keys, or null if verification fails
     */
    public function extractAltText(array $imagePayload)
    {
        // Verify this webhook is for our instance
        if (!isset($imagePayload['metadata']) || !$this->verifyDestination($imagePayload['metadata'])) {
            return null;
        }

        // Extract file ID and alt text
        $id = $imagePayload['metadata']['oc_file_id'] ?? null;
        $alt_text = $imagePayload['alt_text'] ?? '';

        if (!$id) {
            return null;
        }

        return [
            'id' => $id,
            'alt_text' => $alt_text,
        ];
    }
}
