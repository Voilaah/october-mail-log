<?php

namespace Voilaah\MailLog\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Model;

class Settings extends Model
{
    public $implement = [
        'System.Behaviors.SettingsModel',
    ];

    public $settingsCode = 'voilaah_maillog_settings';
    public $settingsFields = 'fields.yaml';
}
