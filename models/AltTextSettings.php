<?php namespace Depcore\AltTextAi\Models;

use Model;

/**
 * AltText.ai Settings Model
 *
 * Stores plugin configuration including the API key required
 * for communicating with the AltText.ai service.
 *
 * Settings are accessible via the backend Settings page.
 *
 * @package Depcore\AltTextAi\Models
 * @property string $apiKey The AltText.ai API key for authentication
 */
class AltTextSettings extends Model
{
    /**
     * Behaviors implemented by this model
     *
     * @var array
     */
    public $implement = [
        \System\Behaviors\SettingsModel::class
    ];

    /**
     * Unique code to identify this settings model
     *
     * @var string
     */
    public $settingsCode = 'alttextai';

    /**
     * Reference to the settings fields YAML file
     *
     * @var string
     */
    public $settingsFields = 'fields.yaml';
}
