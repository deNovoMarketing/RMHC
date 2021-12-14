<?php
namespace BooklyCustomerCabinet\Lib;

use Bookly\Lib;

/**
 * Class Updates
 * @package BooklyCustomerCabinet\Lib
 */
class Updater extends Lib\Base\Updater
{
    function update_1_1()
    {
        delete_option( 'bookly_customer_cabinet_enabled' );
    }
}