# Troubleshooting

Common problems and how to fix them.

## Alt Text Not Appearing

Upload an image but no description is added? Try these:

### 1. Check Plugin is Configured

Go to **Settings** → **AltText.ai Settings**

- Is your API key entered?
- Did you click Save?

### 2. Check Your Credits

Log into [AltText.ai account](https://alttext.ai/account)

- Do you have credits remaining?
- Is your subscription active?

### 3. Check the Logs

```bash
tail -f storage/logs/system.log | grep "AltText"
```

Look for:
- ✅ `generation request` - Plugin sent the request
- ✅ `webhook updated` - Alt text was saved
- ❌ `generation request failed` - Something went wrong
- ❌ `webhook - file not found` - File was deleted

### 4. Test Webhook Access

Your site must be publicly accessible. Test it:

```bash
curl https://yourdomain.com/altext/webhook
```

Should return something (not an error). If you see connection refused or timeout, your webhook isn't accessible.

**Common causes:**
- Site is localhost (webhooks can't reach localhost)
- Firewall blocking incoming requests
- SSL certificate issues

## Slow or Delayed Alt Text

Alt text generation normally takes 5-30 seconds.

If it's taking much longer:

1. **Check AltText.ai status** - Visit their website to see if there are service issues
2. **Check your queue** - If you uploaded many images, they're processed in order
3. **Wait a bit longer** - First-time setup can take a minute

## Webhook Errors in Logs

### "Metadata mismatch"

The webhook was intended for a different site.

**Fix**: Check your `APP_URL` in `.env` file matches your actual domain:

```env
APP_URL=https://yourdomain.com
```

No `http://` vs `https://` mismatches!
No trailing slashes!

### "Invalid JSON"

Webhook received bad data.

**Usually means**: Middleware or server is modifying the request. Contact your hosting provider.

### "File not found"

The image was deleted between upload and webhook arrival.

**This is normal** - if you deleted the image, ignore this error.

## Images Not Being Processed

### Check File Types

Only images are processed. Ensure you're uploading:
- JPG/JPEG
- PNG
- GIF
- WEBP
- BMP

PDFs, videos, etc. won't get alt text.

### Check File Size

Images must be:
- **Minimum**: 50x50 pixels
- **Maximum**: 16 MB

Very small icons or huge files won't work.

## Getting Help

### Collect Information

Before asking for help, gather:

1. **OctoberCMS version**: `php artisan october:version`
2. **Plugin version**: Check `updates/version.yaml`
3. **Last 20 log lines**:
   ```bash
   tail -n 20 storage/logs/system.log | grep "AltText"
   ```
4. **What you tried**: Steps you took before the error

### Contact Support

**Plugin issues:**
- GitHub: [github.com/DEPCORE-PL/oc-alttextai-plugin/issues](https://github.com/DEPCORE-PL/oc-alttextai-plugin/issues)

**AltText.ai service issues:**
- Support: [alttext.ai/support](https://alttext.ai/support)

## FAQ

**Q: Can I regenerate alt text for old images?**

A: Not through the UI, but developers can do it programmatically. See [Advanced Usage](advanced-usage.md).

**Q: Does it cost credits every time?**

A: Yes, each generation (including re-generation) uses credits.

**Q: Can I edit the generated alt text?**

A: Yes! Just edit the image's description field in Media Manager.

**Q: What language is the alt text?**

A: Default is English. Change this in your AltText.ai account settings.

**Q: Can I disable the plugin temporarily?**

A: Yes, go to **Settings** → **Updates & Plugins**, find AltText.ai, click **Disable**.

**Q: Will it process existing images?**

A: No, only new uploads. For batch processing, see [Advanced Usage](advanced-usage.md).

## Quick Diagnostics

### Health Check Command

Run these checks to diagnose issues:

```bash
# 1. Check plugin is installed
php artisan plugin:list | grep AltTextAi

# 2. Check API key is configured
php artisan tinker
>>> Depcore\AltTextAi\Models\AltTextSettings::instance()->apiKey

# 3. Check webhook route is registered
php artisan route:list | grep webhook

# 4. Test webhook endpoint
curl -X POST https://yourdomain.com/altext/webhook \
  -H "Content-Type: application/json" \
  -d '{"event":"test"}'

# 5. Check recent logs
tail -n 50 storage/logs/system.log | grep "AltText"
```

## Common Issues

### 1. Alt Text Not Generated

**Symptoms**: Images uploaded but description remains empty

#### Cause A: API Key Not Configured

**Check**:
```php
php artisan tinker
>>> Depcore\AltTextAi\Models\AltTextSettings::instance()->apiKey
```

**Solution**: Configure API key in Settings → AltText.ai Settings

#### Cause B: No Credits Remaining

**Check**: Log in to [AltText.ai account](https://alttext.ai/account)

**Solution**: Purchase more credits or upgrade plan

#### Cause C: Image Not Accessible

**Error in logs**: `Image URL is not accessible`

**Solution**: Ensure image URLs are publicly accessible:
```bash
curl -I https://yourdomain.com/storage/app/media/image.jpg
```

#### Cause D: Webhook Not Configured

**Check**: AltText.ai account webhook URL

**Solution**: Set webhook URL to `https://yourdomain.com/altext/webhook`

### 2. Webhook Not Received

**Symptoms**: API request succeeds but webhook never arrives

#### Cause A: Webhook URL Not Accessible

**Test**:
```bash
curl -X POST https://yourdomain.com/altext/webhook \
  -H "Content-Type: application/json" \
  -d '{"event":"uploaded","data":{"images":[]}}'
```

**Expected**: JSON response (not 404/403/500)

**Solution**:
- Check firewall rules
- Verify SSL certificate is valid
- Ensure site is not in maintenance mode
- Check WAF/security settings

#### Cause B: Localhost Development

**Problem**: AltText.ai cannot reach `localhost` or `127.0.0.1`

**Solution**: Use a tunneling service:
```bash
# Using ngrok
ngrok http 80

# Update app.url to ngrok URL
# Update webhook URL in AltText.ai account
```

#### Cause C: Webhook Delivery Failed

**Check**: AltText.ai webhook delivery logs (if available in their dashboard)

**Solution**:
- Verify your server returns 200 OK for valid webhooks
- Check your server logs for webhook requests
- Ensure no rate limiting is blocking webhooks

### 3. Metadata Mismatch

**Symptoms**: Log shows `AltText.ai webhook skipped image - metadata mismatch`

#### Cause A: Incorrect app.url Configuration

**Check**:
```php
php artisan tinker
>>> config('app.url')
```

**Solution**: Ensure `app.url` exactly matches your domain:

**File**: `config/app.php`
```php
'url' => env('APP_URL', 'https://yourdomain.com'),
```

**File**: `.env`
```env
APP_URL=https://yourdomain.com
```

**Important**: Include `https://`, no trailing slash

#### Cause B: Hostname Mismatch

**Common in**: Docker, Kubernetes, load-balanced environments

**Check**:
```bash
hostname
```

**Problem**: Hostname changes between request and webhook

**Solution**:
- Use consistent hostname
- Or remove hostname check (less secure):

```php
public function verifyDestination(array $metadata)
{
    // Only check website, not hostname
    return $metadata['oc_website'] == \Config::get('app.url');
}
```

### 4. File Not Found Errors

**Symptoms**: Log shows `AltText.ai webhook - file not found`

#### Cause A: File Deleted

**Problem**: File was deleted between request and webhook

**Solution**: This is expected behavior - no action needed

#### Cause B: Wrong File ID

**Problem**: Metadata has incorrect file ID

**Debug**:
```php
Log::info('File ID from webhook', ['id' => $id]);
Log::info('File exists', ['exists' => File::where('id', $id)->exists()]);
```

**Solution**: Check file is properly created before requesting generation

### 5. SSL/HTTPS Issues

**Symptoms**: Webhook URL fails validation, SSL errors

#### Test SSL Certificate

```bash
# Check SSL certificate
curl -v https://yourdomain.com/altext/webhook

# Should show:
# * SSL certificate verify ok
```

#### Common SSL Issues

1. **Self-signed certificate**: AltText.ai requires valid SSL
2. **Expired certificate**: Renew via Let's Encrypt or your provider
3. **Mixed content**: Ensure all resources use HTTPS
