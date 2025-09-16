<?php

namespace Depcore\AltTextAi\Classes;

use Config;
use Depcore\AltTextAi\Models\AltTextSettings;
use Illuminate\Support\Facades\Http;
use October\Rain\Exception\ApplicationException;
use Storage;
use System\Models\File;

/**
 * AltTextApi
 *
 * Uses AltTextSettings::getApiKey() by default to find the API key.
 */
class AltTextApi
{
    protected ?string $apiKey;
    protected string $baseUrl;

    /**
     * Constructor.
     *
     * @param string|null $apiKey Provide API key directly or leave null to read from settings/env/config.
     * @param string|null $baseUrl Optional base URL (defaults to https://alttext.ai/api/v1).
     *
     * @throws ApplicationException if API key not provided/found.
     */
    public function __construct(?string $baseUrl = null)
    {
        // Priority: explicit param -> plugin settings -> env -> config
        $this->apiKey = AltTextSettings::instance()->apiKey;

        if (empty($this->apiKey)) {
            throw new ApplicationException('AltText API key not configured. Set it in plugin settings or pass it to the constructor.');
        }

        $this->baseUrl = rtrim($baseUrl ?: 'https://alttext.ai/api/v1', '/');
    }
    public function promptGeneration(File $file)
    {
        $content = Storage::get($file->getPath());
        $base64 = base64_encode($content);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'X-Api-Key' => $this->apiKey, // optional
        ])->post($this->baseUrl . '/images', [
            'image'=>[
                'url' => $file->getUrl(),
		//Config::get('app.url') . "/altext/webhook/",
                'metadata' => [
                    'oc_website' => \Config::get('app.url'),
                    'oc_hostname' => gethostname(),
                    'oc_file_id' => $file->id
	    	]
  	    ],
	    'webhook_url' => Config::get('app.url') . "/altext/webhook",
	    'async' => true,
            
        ]);
        if (function_exists('debug')) {
            debug($response->json());
	}
	//\Log::debug(Config::get('app.url') . "/altext/webhook/");
	//\Log::debug(($response->json()));
	//Log::info("sending {$file->getUrl()} to api");
        if ($response->successful()) {
            return true;
        }else {
            return false;
        }
    }

    public function verifyDestination(array $metadata)
    {
        if ($metadata['oc_website'] == \Config::get('app.url') &&
        $metadata['oc_hostname'] == gethostname()) {
            return true;
        }
        return false;
    }

    public function extractAltText(array $imagePayload)
    {
        $thisServer = $this->verifyDestination($imagePayload['metadata']);
        if (!$thisServer) {
            return;
        }
        $id = $imagePayload['metadata']['oc_file_id'];
        $alt_text = $imagePayload["alt_text"];
        return [
            'id' => $id,
            'alt_text' => $alt_text,
        ];
    }
}
