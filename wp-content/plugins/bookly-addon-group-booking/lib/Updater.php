<?php
namespace BooklyGroupBooking\Lib;

use Bookly\Lib as BooklyLib;

/**
 * Class Updates
 * @package BooklyGroupBooking\Lib
 */
class Updater extends BooklyLib\Base\Updater
{
    function update_1_5()
    {
        delete_option( 'bookly_group_booking_enabled' );
    }

    function update_1_1()
    {
        add_option( 'bookly_group_booking_app_show_nop', '0' );
        add_option( 'bookly_group_booking_nop_format', 'busy' );
    }
}