# Getting Started

Quick guide to install and configure the AltText.ai plugin.

## What You'll Need

- OctoberCMS 3.x or 4.x installed
- An AltText.ai account (free to create)
- Your site must be publicly accessible on the internet

## Step 1: Install Plugin

**Via OctoberCMS Backend:**
1. Go to **Settings** → **Updates & Plugins**
2. Click **Install Plugins**
3. Search for "AltText.ai"
4. Click **Install**

**Or manually:**
1. Extract plugin to `plugins/depcore/alttextai/`
2. Run: `php artisan october:migrate`

## Step 2: Get API Key

1. Go to [alttext.ai](https://alttext.ai) and create an account
2. Visit [API Keys page](https://alttext.ai/account/api_keys)
3. Click **Create New API Key**
4. Copy the key (you'll need it next)

## Step 3: Configure Plugin

1. In OctoberCMS, go to **Settings** → **AltText.ai Settings**
2. Paste your API key
3. Click **Save**

## Step 4: Set Webhook URL

1. Log into your [AltText.ai account](https://alttext.ai/account/edit)
2. Find the "Webhook URL" field
3. Enter: `https://yourdomain.com/altext/webhook` (use your actual domain)
4. Save

## Test It

1. Go to **Media** in OctoberCMS backend
2. Upload any image
3. Wait 10-30 seconds
4. Refresh the page
5. Click on the image - you should see a description

## Troubleshooting

**No description appearing?**

Check logs to see what's happening:
```bash
tail -f storage/logs/system.log | grep "AltText"
```

**Common issues:**
- **"API key not configured"** - Re-enter your API key in Settings
- **"No credits"** - Add credits to your AltText.ai account
- **No log entries** - Plugin may not be properly installed
- **"Webhook" errors** - Make sure your site is publicly accessible (not localhost)

## Next Steps

The plugin is now working! All uploaded images will automatically get alt text.

**Want to customize behavior?** See [Advanced Usage](advanced-usage.md)

**Having problems?** See [Troubleshooting Guide](troubleshooting.md)
