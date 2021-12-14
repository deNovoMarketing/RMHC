<?php
namespace BooklyCustomFields\Backend\Modules\Appearance\ProxyProviders;

use Bookly\Backend\Modules\Appearance\Proxy;

/**
 * Class Proxy
 * @package BooklyCustomFields\Backend\Modules\Appearance\ProxyProviders
 */
class Local extends Proxy\CustomFields
{
    /**
     * @inheritDoc
     */
    public static function renderShowCustomFields()
    {
        self::renderTemplate( 'show_custom_fields' );
    }

    /**
     * @inheritDoc
     */
    public static function renderCustomFields()
    {
        self::renderTemplate( 'custom_fields' );
    }
}