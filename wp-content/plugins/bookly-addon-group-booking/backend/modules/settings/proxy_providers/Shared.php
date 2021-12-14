<?php
namespace BooklyGroupBooking\Backend\Modules\Settings\ProxyProviders;

use Bookly\Backend\Modules\Settings\Proxy;
use Bookly\Backend\Components\Settings\Menu;

/**
 * Class Shared
 * @package BooklyGroupBooking\Backend\Modules\Settings\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function renderMenuItem()
    {
        Menu::renderItem( __( 'Group Booking', 'bookly' ), 'group_booking' );
    }

    /**
     * @inheritDoc
     */
    public static function renderTab()
    {
        self::renderTemplate( 'settings_tab' );
    }

    /**
     * @inheritDoc
     */
    public static function saveSettings( array $alert, $tab, array $params )
    {
        if ( $tab == 'group_booking' ) {
            $options = array( 'bookly_group_booking_nop_format' );
            foreach ( $options as $option_name ) {
                if ( array_key_exists( $option_name, $params ) ) {
                    update_option( $option_name, $params[ $option_name ] );
                }
            }
            $alert['success'][] = __( 'Settings saved.', 'bookly' );
        }

        return $alert;
    }

    /**
     * @inheritDoc
     */
    public static function prepareCalendarAppointmentCodes( array $codes, $participants )
    {
        if ( $participants == 'one' ) {
            $codes['number_of_persons'] = __( 'Number of persons', 'bookly' );
        } else {
            $codes['signed_up'] = __( 'Number of persons already in the list', 'bookly' );
            $codes['service_capacity'] = __( 'Capacity of service', 'bookly' );
        }

        return $codes;
    }

    /**
     * @inheritDoc
     */
    public static function prepareCodes( array $codes, $section )
    {
        switch ( $section ) {
            case 'calendar_one_participant' :
                $codes['number_of_persons'] = array( 'description' => __( 'Number of persons', 'bookly' ) );
                break;
            case 'calendar_many_participants' :
                $codes['signed_up'] = array( 'description' => __( 'Number of persons already in the list', 'bookly' ) );
                $codes['service_capacity'] = array( 'description' => __( 'Capacity of service', 'bookly' ) );
                break;
            case 'google_calendar' :
            case 'outlook_calendar' :
                $codes = array_merge_recursive( $codes, array(
                    'number_of_persons' => array( 'description' => __( 'Number of persons', 'bookly' ) ),
                    'participants' => array(
                        'loop' => array(
                            'codes' => array(
                                'signed_up' => array( 'description' => __( 'Number of persons already in the list', 'bookly' ) ),
                                'service_capacity' => array( 'description' => __( 'Capacity of service', 'bookly' ) ),
                            ),
                        ),
                    ),
                ) );
                break;
        }

        return $codes;
    }
}