<?php
namespace BooklyCustomDuration\Backend\Components\Dialogs\Service\Edit\ProxyProviders;

use Bookly\Backend\Components\Dialogs\Service\Edit\Proxy;
use Bookly\Lib as BooklyLib;

/**
 * Class Local
 * @package BooklyCustomDuration\Backend\Components\Dialogs\Service\Edit\ProxyProviders
 */
class Local extends Proxy\CustomDuration
{
    /**
     * @inheritdoc
     */
    public static function renderServiceDurationFields( array $service )
    {
        $duration_options = BooklyLib\Utils\Common::getDurationSelectOptions( $service['duration'] );

        self::renderTemplate( 'service_duration_fields', compact( 'duration_options', 'service' ) );
    }

    /**
     * @inheritdoc
     */
    public static function prepareServiceDurationOptions( array $options, array $service )
    {
        $options[] = array(
            'value'    => 'custom',
            'label'    => __( 'Custom', 'bookly' ),
            'selected' => $service['units_max'] > 1 ? 'selected' : '',
        );

        return $options;
    }

    /**
     * @inheritdoc
     */
    public static function renderServicePriceLabel( $service_id )
    {
        self::renderTemplate( 'service_price_label', compact( 'service_id' ) );
    }

    /**
     * @inheritdoc
     */
    public static function renderServiceDurationHelp()
    {
        self::renderTemplate( 'service_duration_help' );
    }
}
