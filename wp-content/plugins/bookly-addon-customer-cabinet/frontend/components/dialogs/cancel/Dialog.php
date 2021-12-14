<?php
namespace BooklyCustomerCabinet\Frontend\Components\Dialogs\Cancel;

use Bookly\Lib;

/**
 * Class Dialog
 * @package BooklyCustomerCabinet\Frontend\Components\Dialogs\Cancel
 */
class Dialog extends Lib\Base\Component
{
    public static function render( $show_reason )
    {
        static::renderTemplate( 'cancel', compact( 'show_reason' ) );
    }
}