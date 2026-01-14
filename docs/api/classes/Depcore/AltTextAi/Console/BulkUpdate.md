
Bulk Update Command

Generates alt text for all images without descriptions within a date range.
Useful for processing existing images that were uploaded before the plugin was installed.

***

* Full name: `\Depcore\AltTextAi\Console\BulkUpdate`
* Parent class: [`Command`](../../../Illuminate/Console/Command)

**See Also:**

* https://docs.octobercms.com/3.x/extend/console-commands.html

## Properties

### signature

```php
protected string $signature
```

***

### description

```php
protected string $description
```

***

## Methods

### handle

Handle the console command.

```php
public handle(): void
```

Queries all images without descriptions, optionally filtered by date range.
Sends each image to AltText.ai API for alt text generation.
Shows progress bar during processing.

**Throws:**

- [`Exception`](../../../Exception)

***
