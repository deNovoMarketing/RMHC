<?php
namespace BooklyCustomDuration\Backend\Modules\Appearance\ProxyProviders;

use Bookly\Backend\Modules\Appearance\Proxy;
use BooklyCustomDuration\Lib;

/**
 * Class Local
 * @package BooklyCustomDuration\Backend\Modules\Appearance\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritdoc
     */
    public static function renderServiceStepSettings()
    {
        self::renderTemplate( 'appearance_settings' );
    }
}