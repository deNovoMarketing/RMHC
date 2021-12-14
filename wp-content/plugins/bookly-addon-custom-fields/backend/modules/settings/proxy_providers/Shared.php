<?php
namespace BooklyCustomFields\Backend\Modules\Settings\ProxyProviders;

use Bookly\Backend\Modules\Settings\Proxy;

/**
 * Class Shared
 * @package BooklyCustomFields\Backend\Modules\Settings\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function prepareCalendarAppointmentCodes( array $codes, $participants )
    {
        if ( $participants == 'one' ) {
            $codes['custom_fields'] = __( 'Combined values of all custom fields', 'bookly' );
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
            case 'woocommerce' :
                $codes['custom_fields'] = array( 'description' => __( 'Combined values of all custom fields', 'bookly' ) );
                break;
            case 'google_calendar' :
            case 'outlook_calendar' :
                $codes = array_merge_recursive( $codes, array(
                    'participants' => array(
                        'loop' => array(
                            'codes' => array(
                                'custom_fields' => array( 'description' => __( 'Combined values of all custom fields', 'bookly' ) ),
                            ),
                        ),
                    ),
                ) );
                break;
        }

        return $codes;
    }
}