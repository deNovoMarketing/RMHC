<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Inputs;
?>
<div class="col-md-3 my-2">
    <?php Inputs::renderCheckBox( __( 'Show information about group bookings', 'bookly' ), null, get_option( 'bookly_group_booking_app_show_nop' ), array( 'id' => 'bookly-show-nop-on-time-step' ) ) ?>
</div>