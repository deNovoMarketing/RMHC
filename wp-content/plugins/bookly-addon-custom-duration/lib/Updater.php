<?php
namespace BooklyCustomDuration\Lib;

use Bookly\Lib;

/**
 * Class Updates
 * @package BooklyCustomDuration\Lib
 */
class Updater extends Lib\Base\Updater
{
    function update_1_5()
    {
        delete_option( 'bookly_custom_duration_enabled' );
    }
}