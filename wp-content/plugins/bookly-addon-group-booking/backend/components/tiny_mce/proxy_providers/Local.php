<?php
namespace BooklyGroupBooking\Backend\Components\TinyMce\ProxyProviders;

use Bookly\Backend\Components\TinyMce\Proxy;

/**
 * Class Local
 * @package BooklyGroupBooking\Backend\Components\TinyMce
 */
class Local extends Proxy\GroupBooking
{
    /**
     * @inheritdoc
     */
    public static function renderStaffCabinetSettings()
    {
        self::renderTemplate( 'staff_cabinet' );
    }
}