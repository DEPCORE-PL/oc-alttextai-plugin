<?php namespace Depcore\AltTextAi;

use Depcore\AltTextAi\Classes\AltTextApi;
use Depcore\AltTextAi\Models\AltTextSettings;
use Log;
use System\Classes\PluginBase;
use System\Models\File;

/**
 * AltText.ai Plugin for OctoberCMS
 *
 * Automatically generates alt text descriptions for uploaded images
 * using the AltText.ai API service powered by AI.
 *
 * This plugin extends the OctoberCMS File model to automatically
 * request alt text generation when new files are created.
 *
 * @package Depcore\AltTextAi
 * @author Depcore
 * @link https://docs.octobercms.com/3.x/extend/system/plugins.html
 * @link https://alttext.ai
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin
     *
     * @return array Plugin details including name, description, author and icon
     */
    public function pluginDetails()
    {
        return [
            'name' => 'AltTextAi',
            'description' => 'Automatically generates alt text for images using AltText.ai API service.',
            'author' => 'Depcore',
            'icon' => 'icon-image'
        ];
    }

    /**
     * Register plugin components and event listeners
     *
     * Sets up the webhook route and extends the File model
     * to automatically trigger alt text generation on file creation.
     *
     * @return void
     */
    public function register()
    {
        // Register webhook routes
        include_once __DIR__ . '/routes.php';

        // Extend File model to auto-generate alt text
        File::extend(function (File $model) {
            $model->bindEvent('model.afterCreate', function () use ($model) {
                // Only generate alt text if description is empty
                if (!property_exists($model, "description") || empty($model->description)) {
                    try {
                        (new AltTextApi())->promptGeneration($model);
                    } catch (\Exception $e) {
                        Log::error('AltText.ai generation request failed', [
                            'file_id' => $model->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            });
        });
    }

    /**
     * Boot method, called right before the request route
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register frontend components
     *
     * @return array Empty array - no frontend components provided
     */
    public function registerComponents()
    {
        return [];
    }

    /**
     * Register backend permissions
     *
     * @return array Empty array - no custom permissions required
     */
    public function registerPermissions()
    {
        return [];
    }

    /**
     * Register backend navigation items
     *
     * @return array Empty array - no navigation items added
     */
    public function registerNavigation()
    {
        return [];
    }

    /**
     * Register plugin settings
     *
     * Adds AltText.ai settings page to the backend Settings area
     * where administrators can configure the API key.
     *
     * @return array Settings configuration array
     */
    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'AltText.ai Settings',
                'description' => 'Configure AltText.ai API key and image alt text generation settings.',
                'icon' => 'icon-image',
                'class' => AltTextSettings::class,
                'category' => 'CMS',
            ]
        ];
    }
}
