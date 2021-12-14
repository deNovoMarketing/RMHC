<?php
namespace BooklyGroupBooking\Lib;

use Bookly\Lib as BooklyLib;

/**
 * Class Installer
 * @package BooklyGroupBooking\Lib
 */
class Installer extends Base\Installer
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->options = array(
            'bookly_l10n_label_number_of_persons' => __( 'Persons', 'bookly' ),
            'bookly_group_booking_app_show_nop'   => 0,
            'bookly_group_booking_nop_format'     => 'busy',
        );
    }

}