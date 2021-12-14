<?php
namespace BooklyCustomDuration\Backend\Components\TinyMce\ProxyProviders;

use Bookly\Backend\Components\TinyMce\Proxy;

/**
 * Class ProxyProvider
 * @package BooklyCustomDuration\Backend\Components\TinyMce
 */
class Shared extends Proxy\Shared
{
    public static function renderBooklyFormFields()
    {
        self::renderTemplate( 'bookly_form' );
    }
}