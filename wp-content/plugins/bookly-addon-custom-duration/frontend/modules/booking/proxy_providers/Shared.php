<?php
namespace BooklyCustomDuration\Frontend\Modules\Booking\ProxyProviders;

use Bookly\Frontend\Modules\Booking\Proxy;
use Bookly\Lib as BooklyLib;

/**
 * Class Shared
 * @package BooklyCustomDuration\Frontend\Modules\Booking\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function stepOptions( $options, $step )
    {
        if ( $step == 'service' ) {
            $options['l10n']['duration_label'] = BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_service_duration' );
        }

        return $options;
    }
}
