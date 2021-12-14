<?php
namespace BooklyGroupBooking\Backend\Components\Dialogs\Service\Edit\ProxyProviders;

use Bookly\Backend\Components\Dialogs\Service\Edit\Proxy;

/**
 * Class Shared
 * @package BooklyGroupBooking\Backend\Components\Dialogs\Service\Edit\ProxyProviders
 */
class Local extends Proxy\GroupBooking
{
    /**
     * @inheritdoc
     */
    public static function renderSubForm( array $service )
    {
        self::renderTemplate( 'sub_form', compact( 'service' ) );
    }
}