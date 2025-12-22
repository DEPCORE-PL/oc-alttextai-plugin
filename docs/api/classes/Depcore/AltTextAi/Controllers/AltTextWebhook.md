
Alt Text Webhook Backend Controller

Handles incoming webhook notifications from AltText.ai service.
Processes uploaded and deleted image events to update alt text descriptions.

***

* Full name: `\Depcore\AltTextAi\Controllers\AltTextWebhook`
* Parent class: [`Controller`](../../../Backend/Classes/Controller)

**See Also:**

* https://docs.octobercms.com/4.x/extend/system/controllers.html
* https://alttext.ai/api/v1

## Methods

### handle

Handle incoming webhook from AltText.ai

```php
public handle(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
```

Validates the webhook payload and processes image events.
Webhooks are authenticated via metadata verification to ensure
they originated from this OctoberCMS instance.

**Parameters:**

| Parameter  | Type                         | Description               |
|------------|------------------------------|---------------------------|
| `$request` | **\Illuminate\Http\Request** | The incoming HTTP request |

***
