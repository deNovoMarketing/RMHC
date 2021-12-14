<?php
namespace BooklyCustomerCabinet\Frontend\Components\Dialogs\Delete;

use Bookly\Lib;

/**
 * Class Dialog
 * @package BooklyCustomerCabinet\Frontend\Components\Dialogs\Delete
 */
class Dialog extends Lib\Base\Component
{
    public static function render()
    {
        static::renderTemplate( 'delete' );
    }
}