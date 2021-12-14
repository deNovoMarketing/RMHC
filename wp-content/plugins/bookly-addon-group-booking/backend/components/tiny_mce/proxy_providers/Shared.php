<?php
namespace BooklyGroupBooking\Backend\Components\TinyMce\ProxyProviders;

use Bookly\Backend\Components\TinyMce\Proxy;

/**
 * Class Shared
 * @package BooklyGroupBooking\Backend\Components\TinyMce
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritdoc
     */
    public static function renderBooklyFormFields()
    {
        self::renderTemplate( 'bookly_form' );
    }
}