
AltText.ai API Client

Provides integration with the AltText.ai service for automatic
generation of alt text descriptions for images using AI.

***

* Full name: `\Depcore\AltTextAi\Classes\AltTextApi`

**See Also:**

* https://alttext.ai/api/v1

## Properties

### apiKey

AltText.ai API key

```php
protected string|null $apiKey
```

***

### baseUrl

Base URL for the AltText.ai API

```php
protected string $baseUrl
```

***

## Methods

### __construct

Constructor - Initialize API client

```php
public __construct(string|null $baseUrl = null): mixed
```

Retrieves the API key from plugin settings and configures the base URL.

**Parameters:**

| Parameter  | Type             | Description                                               |
|------------|------------------|-----------------------------------------------------------|
| `$baseUrl` | **string\|null** | Optional base URL (defaults to https://alttext.ai/api/v1) |

**Throws:**

if API key is not configured in plugin settings
- [`ApplicationException`](../../../October/Rain/Exception/ApplicationException)

***

### promptGeneration

Request alt text generation for a file

```php
public promptGeneration(\System\Models\File $file): bool
```

Sends an image to AltText.ai API for asynchronous alt text generation.
The API will process the image and send results back to the webhook endpoint.
Metadata is included to verify webhook authenticity.

**Parameters:**

| Parameter | Type                    | Description                                                 |
|-----------|-------------------------|-------------------------------------------------------------|
| `$file`   | **\System\Models\File** | The OctoberCMS File model instance to generate alt text for |

**Return Value:**

True if request was successful, false otherwise

***

### verifyDestination

Verify webhook destination matches this OctoberCMS instance

```php
public verifyDestination(array $metadata): bool
```

Validates that a webhook payload's metadata matches this instance's
website URL and hostname to prevent processing webhooks intended
for other installations.

**Parameters:**

| Parameter   | Type      | Description                                 |
|-------------|-----------|---------------------------------------------|
| `$metadata` | **array** | The metadata array from the webhook payload |

**Return Value:**

True if metadata matches this instance, false otherwise

***

### extractAltText

Extract alt text data from webhook image payload

```php
public extractAltText(array $imagePayload): array|null
```

Parses the webhook image payload and extracts the file ID and
generated alt text. Verifies the payload is intended for this instance.

**Parameters:**

| Parameter       | Type      | Description                         |
|-----------------|-----------|-------------------------------------|
| `$imagePayload` | **array** | The image data from webhook payload |

**Return Value:**

Array with 'id' and 'alt_text' keys, or null if verification fails

***
