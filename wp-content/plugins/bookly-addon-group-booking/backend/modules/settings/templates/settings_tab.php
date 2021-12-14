<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Components\Settings\Selects;
?>
<div class="tab-pane" id="bookly_settings_group_booking">
    <form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'group_booking' ) ) ?>">
        <div class="card-body">
            <?php Selects::renderSingle( 'bookly_group_booking_nop_format', __( 'Group bookings information format', 'bookly' ), __( 'Select format for displaying the time slot occupancy for group bookings.', 'bookly' ), array(
                array( 'busy', __( '[Booked/Max capacity]', 'bookly' ) ),
                array( 'free', __( '[Available left]', 'bookly' ) ),
            ) ) ?>
        </div>

        <div class="card-footer bg-transparent d-flex justify-content-end">
            <?php Inputs::renderCsrf() ?>
            <?php Buttons::renderSubmit() ?>
            <?php Buttons::renderReset( null, 'ml-2' ) ?>
        </div>
    </form>
</div>