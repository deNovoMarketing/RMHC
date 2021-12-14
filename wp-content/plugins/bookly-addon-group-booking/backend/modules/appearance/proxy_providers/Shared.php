<?php
namespace BooklyGroupBooking\Backend\Modules\Appearance\ProxyProviders;

use Bookly\Backend\Modules\Appearance\Proxy;
use BooklyGroupBooking\Lib;

/**
 * Class Shared
 * @package BooklyGroupBooking\Backend\Modules\Appearance\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritdoc
     */
    public static function prepareOptions( array $options_to_save, array $options )
    {
        $options_to_save = array_merge( $options_to_save, array_intersect_key( $options, array_flip( array (
            'bookly_group_booking_app_show_nop',
            'bookly_l10n_label_number_of_persons',
        ) ) ) );

        return $options_to_save;
    }

    /**
     * @inheritdoc
     */
    public static function renderTimeStepSettings()
    {
        self::renderTemplate( 'time_step_settings' );
    }

    /**
     * @inheritdoc
     */
    public static function prepareCodes( array $codes )
    {
        return array_merge( $codes, array(
            'number_of_persons' => array( 'description' => __( 'Number of persons', 'bookly' ) ),
        ) );
    }
}