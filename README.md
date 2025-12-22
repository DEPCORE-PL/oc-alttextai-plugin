# AltText.ai Plugin for OctoberCMS

Automatically generate alt text descriptions for images using AI.

## What It Does

When you upload an image, this plugin:
- Sends it to AltText.ai for AI analysis
- Receives a description back
- Saves it to the image's description field

**Result**: All your images automatically get accessibility-friendly alt text.

## Requirements

- OctoberCMS 3.x or 4.x
- AltText.ai account ([sign up here](https://alttext.ai))
- Your site must be publicly accessible (webhooks need to reach your server)

## Installation

### 1. Install the Plugin

Install via OctoberCMS marketplace or copy to `plugins/depcore/alttextai/`

### 2. Get Your API Key

1. Create account at [alttext.ai](https://alttext.ai)
2. Go to [API Keys page](https://alttext.ai/account/api_keys)
3. Create a new API key
4. Copy it

### 3. Configure Plugin

1. In OctoberCMS backend, go to **Settings** → **AltText.ai Settings**
2. Paste your API key
3. Click **Save**

### 4. Set Webhook URL

In your AltText.ai account settings, set webhook URL to:
```
https://yourdomain.com/altext/webhook
```

Done! The plugin is now active.

## How to Use

### Automatic Mode (Default)

Just upload images normally through:
- Media Manager
- File uploads in backend
- Any file attachment field

Alt text is generated automatically within 5-30 seconds.

### Using Alt Text in Templates

```twig
<img src="{{ image.path }}" alt="{{ image.description }}">
```

The `description` field contains the generated alt text.

## Checking It Works

1. Upload a test image through Media Manager
2. Wait 5-30 seconds
3. Refresh and check the image - description field should contain alt text

### If Alt Text Doesn't Appear

Check the logs:
```bash
tail -f storage/logs/system.log | grep "AltText"
```

Common issues:
- API key not configured
- No credits remaining in your AltText.ai account
- Webhook URL not accessible

## Support

- Plugin issues: [GitHub](https://github.com/DEPCORE-PL/oc-alttextai-plugin/issues)
- AltText.ai service: [alttext.ai/support](https://alttext.ai/support)

## License

MIT License
