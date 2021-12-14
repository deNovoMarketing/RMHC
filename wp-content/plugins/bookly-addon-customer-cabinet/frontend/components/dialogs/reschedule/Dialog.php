<?php
namespace BooklyCustomerCabinet\Frontend\Components\Dialogs\Reschedule;

use Bookly\Lib;

/**
 * Class Dialog
 * @package BooklyCustomerCabinet\Frontend\Components\Dialogs\Reschedule
 */
class Dialog extends Lib\Base\Component
{
    public static function render()
    {
        static::renderTemplate( 'reschedule' );
    }
}