<?php namespace Depcore\AltTextAi\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Depcore\AltTextAi\Classes\AltTextApi;
use Depcore\TPayProcessor\Models\AltTextSettings;
use Illuminate\Http\Request;
use Log;
use System\Models\File;

/**
 * Alt Text Webhook Backend Controller
 *
 * @link https://docs.octobercms.com/4.x/extend/system/controllers.html
 */
class AltTextWebhook extends Controller
{
    public function handle(Request $request)
    {
        $expectedKey = AltTextSettings::get('merchant_id');

        $providedKey = null;
        if ($request->headers->has('x-api-key')) {
            $providedKey = $request->header('x-api-key');
        } elseif ($request->headers->has('authorization')) {
            $auth = $request->header('authorization');
            if (stripos($auth, 'apikey ') === 0) {
                $providedKey = substr($auth, 7);
            } elseif (stripos($auth, 'bearer ') === 0) {
                $providedKey = substr($auth, 7);
            } else {
                $providedKey = $auth;
            }
        }

        if (empty($expectedKey) || empty($providedKey) || !hash_equals((string)$expectedKey, (string)$providedKey)) {
            Log::warning('[AltTextWebhook] Unauthorized request', [
              'ip' => $request->ip(),
              'provided' => $providedKey ? 'present' : 'missing'
            ]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // ---- Parse + validate payload ----
        $payload = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['message' => 'Invalid JSON'], 400);
        }

        if (empty($payload['event']) || !in_array($payload['event'], ['uploaded','deleted'], true)) {
            return response()->json(['message' => 'Invalid event'], 400);
        }

        if (empty($payload['data']['images']) || !is_array($payload['data']['images'])) {
            return response()->json(['message' => 'Missing images array'], 400);
        }

        $event = $payload['event'];
        foreach ($payload['data']['images'] as $img) {
            if (! (new AltTextApi())->verifyDestination($img["metadata"])) {
                continue;
            }
            try {
                if ($event === 'uploaded') {
                    [$id, $altText] = (new AltTextApi())->extractAltText($img);
                    $item = File::find($id);
                    $item->description = $altText;
                    $item->save();
                }
            } catch (\Exception $e) {
            }
        }
    }
}
