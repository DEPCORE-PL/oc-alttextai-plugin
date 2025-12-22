
AltText.ai Plugin for OctoberCMS

Automatically generates alt text descriptions for uploaded images
using the AltText.ai API service powered by AI.

This plugin extends the OctoberCMS File model to automatically
request alt text generation when new files are created.

***

* Full name: `\Depcore\AltTextAi\Plugin`
* Parent class: [`PluginBase`](../../System/Classes/PluginBase)

**See Also:**

* https://docs.octobercms.com/3.x/extend/system/plugins.html
* https://alttext.ai

## Methods

### pluginDetails

Returns information about this plugin

```php
public pluginDetails(): array
```

**Return Value:**

Plugin details including name, description, author and icon

***

### register

Register plugin components and event listeners

```php
public register(): void
```

Sets up the webhook route and extends the File model
to automatically trigger alt text generation on file creation.

***

### boot

Boot method, called right before the request route

```php
public boot(): void
```

***

### registerComponents

Register frontend components

```php
public registerComponents(): array
```

**Return Value:**

Empty array - no frontend components provided

***

### registerPermissions

Register backend permissions

```php
public registerPermissions(): array
```

**Return Value:**

Empty array - no custom permissions required

***

### registerNavigation

Register backend navigation items

```php
public registerNavigation(): array
```

**Return Value:**

Empty array - no navigation items added

***

### registerSettings

Register plugin settings

```php
public registerSettings(): array
```

Adds AltText.ai settings page to the backend Settings area
where administrators can configure the API key.

**Return Value:**

Settings configuration array

***
