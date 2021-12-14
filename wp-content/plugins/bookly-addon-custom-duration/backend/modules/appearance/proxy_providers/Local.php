<?php
namespace BooklyCustomDuration\Backend\Modules\Appearance\ProxyProviders;

use Bookly\Backend\Modules\Appearance\Proxy;
use BooklyCustomDuration\Lib;

/**
 * Class Local
 * @package BooklyCustomDuration\Backend\Modules\Appearance\ProxyProviders
 */
class Local extends Proxy\CustomDuration
{
    public static function renderServiceDuration()
    {
        self::renderTemplate( 'appearance' );
    }
}