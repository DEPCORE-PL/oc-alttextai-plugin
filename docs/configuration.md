# Configuration

How to set up the AltText.ai plugin.

## Step 1: Plugin Settings in OctoberCMS

Go to **Settings** → **AltText.ai Settings**

### API Key

Paste your API key here. Get it from [alttext.ai/account/api_keys](https://alttext.ai/account/api_keys)

Click **Save**.

## Step 2: AltText.ai Account Settings

Log into your [AltText.ai account](https://alttext.ai/account/edit) and configure:

### Webhook URL (Required)

Set to: `https://yourdomain.com/altext/webhook`

Replace `yourdomain.com` with your actual website address.

**Important:**
- Must be `https://` (not `http://`)
- No slash at the end
- Must be publicly accessible on the internet

### Language (Optional)

Choose which language you want for alt text:
- English (default)
- Spanish, French, German, Italian, etc.

### Character Limit (Optional)

How long should alt text be?

**Recommended:** 125-200 characters for best screen reader compatibility.

### Custom Prompt (Optional)

Add instructions to customize how the AI writes. Examples:
- "Focus on products and colors"
- "Use professional business language"
- "Describe emotions and atmosphere"

Leave blank for standard descriptions.

## Checking It Works

Upload a test image. After 10-30 seconds, check if it has a description:

1. Go to **Media** in backend
2. Click the image
3. Look at the description field - should have alt text

### If Not Working

Check logs:
```bash
tail storage/logs/system.log | grep "AltText"
```

**Look for:**
- ✅ "generation request" = Plugin sent image to API
- ✅ "webhook updated" = Alt text was saved
- ❌ Errors = See troubleshooting below

## Common Problems

### "Metadata mismatch" in logs

Your `APP_URL` setting doesn't match your domain.

**Fix:** Edit `.env` file:
```
APP_URL=https://yourdomain.com
```

Must exactly match your domain (including `https://`).

### Webhook not accessible

Your site must be reachable from the internet for webhooks to work.

**Test it:**
```bash
curl https://yourdomain.com/altext/webhook
```

Should get a response (not timeout).

**Won't work if:**
- Site is on localhost
- Behind firewall blocking incoming requests
- No valid SSL certificate

### No credits

Check your AltText.ai account has available credits.

## That's It

Once configured, all uploaded images will automatically get alt text. No further setup needed.
