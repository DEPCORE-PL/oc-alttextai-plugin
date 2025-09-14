<?php namespace Depcore\TPayProcessor\Models;

use Model;

class AltTextSettings extends Model
{
    public $implement = [
        \System\Behaviors\SettingsModel::class
    ];

    // A unique code to identify this settings model
    public $settingsCode = 'alttextai';

    // Reference to the settings fields YAML file
    public $settingsFields = 'fields.yaml';
}
