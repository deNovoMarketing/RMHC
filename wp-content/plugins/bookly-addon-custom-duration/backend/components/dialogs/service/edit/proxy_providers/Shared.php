<?php
namespace BooklyCustomDuration\Backend\Components\Dialogs\Service\Edit\ProxyProviders;

use Bookly\Backend\Components\Dialogs\Service\Edit\Proxy;
use Bookly\Lib as BooklyLib;

/**
 * Class Shared
 * @package BooklyCustomDuration\Backend\Components\Dialogs\Service\Edit\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritdoc
     */
    public static function prepareUpdateService( array $data )
    {
        if ( $data['duration'] == 'custom' ) {
            $data['duration'] = $data['unit_duration'];
        } else {
            $data['units_min'] = 1;
            $data['units_max'] = 1;
        }

        return $data;
    }
}
