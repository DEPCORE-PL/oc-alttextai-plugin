<?php namespace Depcore\AltTextAi;

use Depcore\AltTextAi\Classes\AltTextApi;
use Depcore\AltTextAi\Models\AltTextSettings;
use Log;
use System\Classes\PluginBase;
use System\Models\File;

/**
 * Plugin Information File
 *
 * @link https://docs.octobercms.com/3.x/extend/system/plugins.html
 */
class Plugin extends PluginBase
{
    /**
     * pluginDetails about this plugin.
     */
    public function pluginDetails()
    {
        return [
            'name' => 'AltTextAi',
            'description' => 'No description provided yet...',
            'author' => 'Depcore',
            'icon' => 'icon-leaf'
        ];
    }

    /**
     * register method, called when the plugin is first registered.
     */
    public function register()
    {
        include_once __DIR__ . '/routes.php';

        File::extend(function (File $model) {
            $model->bindEvent('model.afterCreate', function () use ($model) {
                if (!property_exists($model, "description") || count($model->description) == 0) {
                    (new AltTextApi())->promptGeneration($model);

                }
            });
        });
    }

    /**
     * boot method, called right before the request route.
     */
    public function boot()
    {
    }

    /**
     * registerComponents used by the frontend.
     */
    public function registerComponents()
    {
        return []; // Remove this line to activate

        return [
            'Depcore\AltTextAi\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * registerPermissions used by the backend.
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

        return [
            'depcore.alttextai.some_permission' => [
                'tab' => 'AltTextAi',
                'label' => 'Some permission'
            ],
        ];
    }

    /**
     * registerNavigation used by the backend.
     */
    public function registerNavigation()
    {
        return []; // Remove this line to activate

        return [
            'alttextai' => [
                'label' => 'AltTextAi',
                'url' => Backend::url('depcore/alttextai/mycontroller'),
                'icon' => 'icon-leaf',
                'permissions' => ['depcore.alttextai.*'],
                'order' => 500,
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'Altext Settings',
                'description' => 'Manage altext.ai generation settings.',
                'icon' => 'icon-image',
                'class' => AltTextSettings::class,
            ]
        ];
    }
}
