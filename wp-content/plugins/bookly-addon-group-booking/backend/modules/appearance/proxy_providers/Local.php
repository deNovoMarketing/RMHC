<?php
namespace BooklyGroupBooking\Backend\Modules\Appearance\ProxyProviders;

use Bookly\Backend\Modules\Appearance\Proxy;

/**
 * Class Local
 * @package BooklyGroupBooking\Backend\Modules\Appearance\ProxyProviders
 */
class Local extends Proxy\GroupBooking
{
    /**
     * @inheritdoc
     */
    public static function renderNOP()
    {
        self::renderTemplate( 'nop' );
    }
}