<?php
namespace BooklyGroupBooking\Backend\Components\Dialogs\Staff\Edit\ProxyProviders;

use Bookly\Lib as BooklyLib;
use Bookly\Backend\Components\Dialogs\Staff\Edit\Proxy;

/**
 * Class Shared
 * @package BooklyGroupBooking\Backend\Components\Dialogs\Staff\Edit\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function renderStaffServiceLabels()
    {
        self::renderTemplate( 'staff_service_label' );
    }

    /**
     * @inheritDoc
     */
    public static function renderStaffService( $staff_id, $service, array $services_data, $attributes = array() )
    {
        $read_only = false;
        if ( ( $service->getType() == BooklyLib\Entities\Service::TYPE_PACKAGE )
             || ( isset( $attributes['read-only']['capacity'] ) && $attributes['read-only']['capacity'] ) ) {
            $read_only = true;
        }

        self::renderTemplate( 'staff_service', compact( 'service', 'services_data', 'read_only' ) );
    }
}