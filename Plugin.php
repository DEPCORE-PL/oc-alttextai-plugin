<?php namespace Depcore\AltTextAi;

use Backend;
use Depcore\AltTextAi\Classes\AltTextApi;
use Event;
use Media\Widgets\MediaManager;
use System\Classes\PluginBase;

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
        Event::listen('media.file.upload', function (MediaManager $mediaWidget, string &$path, \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile) {
            $uploadedFile->
            debug((new AltTextApi())->generateFromFile($path));
        });
    }

    /**
     * boot method, called right before the request route.
     */
    public function boot()
    {
        //
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
}
