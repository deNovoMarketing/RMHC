<?php
namespace BooklyGroupBooking\Frontend\Modules\Booking\ProxyProviders;

use Bookly\Lib as BooklyLib;


/**
 * Class Shared
 * @package BooklyGroupBooking\Frontend\Modules\Booking\ProxyProviders
 */
class Shared extends \Bookly\Frontend\Modules\Booking\Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function stepOptions( $options, $step )
    {
        if ( $step == 'service' ) {
            $options['l10n']['nop_label'] = BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_number_of_persons' );
        }

        return $options;
    }
}