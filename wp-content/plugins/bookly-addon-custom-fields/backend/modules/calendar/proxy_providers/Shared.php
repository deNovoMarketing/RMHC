<?php
namespace BooklyCustomFields\Backend\Modules\Calendar\ProxyProviders;

use Bookly\Lib as BooklyLib;
use Bookly\Backend\Modules\Calendar\Proxy;
use BooklyCustomFields\Lib;

/**
 * Class Shared
 * @package BooklyCustomFields\Backend\Modules\Calendar\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function prepareAppointmentCodesData( array $codes, $appointment_data, $participants )
    {
        if ( $participants == 'one' ) {
            if ( $appointment_data['custom_fields'] != '[]' ) {
                $ca = new BooklyLib\Entities\CustomerAppointment();
                $ca->setCustomFields(  $appointment_data['custom_fields'] );
                $ca->setAppointmentId( $appointment_data['id'] );
                foreach ( Lib\ProxyProviders\Local::getForCustomerAppointment( $ca ) as $custom_field ) {
                    $codes['custom_fields'] .= sprintf( '<div>%s: %s</div>', wp_strip_all_tags( $custom_field['label'] ), nl2br( esc_html( $custom_field['value'] ) ) );
                }
            }
        }

        return $codes;
    }
}