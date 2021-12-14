<?php
namespace BooklyCustomDuration\Lib;

use Bookly\Lib as BooklyLib;

/**
 * Class Installer
 * @package BooklyBlank\Lib
 */
class Installer extends Base\Installer
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->options = array(
            'bookly_app_show_service_durations_switcher' => '0',
            'bookly_app_service_duration_with_price'     => '0',
            'bookly_l10n_label_service_duration'         => __( 'Duration', 'bookly' ),
        );
    }
}