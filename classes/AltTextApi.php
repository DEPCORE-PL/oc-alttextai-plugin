<?php

namespace Depcore\AltTextAi\Classes;

use Depcore\TPayProcessor\Models\AltTextSettings;
use Illuminate\Support\Facades\Http;
use October\Rain\Exception\ApplicationException;

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

    /**
     * Generate alt text by passing an image URL.
     */
    public function generateFromUrl(string $imageUrl, array $options = [], bool $async = false): array
    {
        $payload = ['image' => ['url' => $imageUrl]];

        if (!empty($options)) {
            $payload = array_merge($payload, $options);
        }

        if ($async) {
            $payload['async'] = true;
        }

        $response = Http::withHeaders($this->headers())
            ->timeout(30)
            ->post($this->baseUrl . '/images', $payload);

        return $this->handleResponse($response, $async);
    }

    /**
     * Generate alt text by uploading a local file.
     */
    public function generateFromFile(string $filePath, array $options = [], bool $async = false): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new ApplicationException('File not found or unreadable: ' . $filePath);
        }

        $multipart = Http::withHeaders($this->headers())->timeout(60);

        $multipart = $multipart->attach('file', file_get_contents($filePath), basename($filePath));

        $response = $multipart->post($this->baseUrl . '/images', array_merge($options, $async ? ['async' => true] : []));

        return $this->handleResponse($response, $async);
    }

    public function getImage(string $id): array
    {
        $response = Http::withHeaders($this->headers())
            ->timeout(15)
            ->get($this->baseUrl . '/images/' . urlencode($id));

        if ($response->successful()) {
            return $response->json();
        }

        throw new ApplicationException('AltText API error: ' . $response->status() . ' - ' . $response->body());
    }

    public function listImages(array $params = []): array
    {
        $response = Http::withHeaders($this->headers())
            ->timeout(15)
            ->get($this->baseUrl . '/images', $params);

        if ($response->successful()) {
            return $response->json();
        }

        throw new ApplicationException('AltText API error: ' . $response->status() . ' - ' . $response->body());
    }

    public function verifyWebhookSignature($request, string $headerName = 'X-AltText-Signature', ?string $secret = null): bool
    {
        $secret = $secret ?: env('ALT_TEXT_WEBHOOK_SECRET') ?: (config('alttext.webhook_secret') ?? '');

        if (empty($secret)) {
            throw new ApplicationException('Webhook secret not configured (ALT_TEXT_WEBHOOK_SECRET).');
        }

        $signature = $request->header($headerName);
        if (empty($signature)) {
            return false;
        }

        $payload = (string) $request->getContent();
        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }

    protected function headers(): array
    {
        return [
            'X-API-Key' => $this->apiKey,
            'Accept' => 'application/json',
        ];
    }

    protected function handleResponse($response, bool $async): array
    {
        if ($response->successful()) {
            $json = $response->json();
            if (empty($json)) {
                return [
                    'status' => 'accepted',
                    'async'  => $async,
                ];
            }
            return $json;
        }

        $body = $response->body();
        $msg = $response->status() . ' - ' . ($response->json()['error'] ?? $body);
        throw new ApplicationException('AltText API error: ' . $msg);
    }
}
