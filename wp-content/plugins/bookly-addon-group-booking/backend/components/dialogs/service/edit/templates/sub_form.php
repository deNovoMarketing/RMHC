<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * @var array $service
 */
?>
<div class="form-group">
    <label for="capacity_<?php echo $service['id'] ?>"><?php esc_html_e( 'Capacity (min and max)', 'bookly' ) ?></label>
    <div class="form-row">
        <div class="col-6">
            <input id="capacity_min_<?php echo $service['id'] ?>" class="form-control bookly-js-question bookly-js-capacity" type="number" min="1" step="1" name="capacity_min" value="<?php echo esc_attr( $service['capacity_min'] ) ?>" />
        </div>
        <div class="col-6">
            <input id="capacity_max_<?php echo $service['id'] ?>" class="form-control bookly-js-question bookly-js-capacity" type="number" min="1" step="1" name="capacity_max" value="<?php echo esc_attr( $service['capacity_max'] ) ?>" />
        </div>
    </div>
    <small class="form-text text-muted"><?php esc_html_e( 'The minimum and maximum number of customers allowed to book the service for the certain time period.', 'bookly' ) ?></small>
</div>
<div class="form-group">
    <label for="one_booking_per_slot"><?php esc_html_e( 'One booking per time slot', 'bookly' ) ?></label>
    <div class="custom-control custom-radio">
        <input type="radio" id="bookly-one-booking-per-slot-0" name="one_booking_per_slot" value="0"<?php checked( $service['one_booking_per_slot'], 0 ) ?> class="custom-control-input" />
        <label for="bookly-one-booking-per-slot-0" class="custom-control-label"><?php esc_html_e( 'Disabled', 'bookly' ) ?></label>
    </div>
    <div class="custom-control custom-radio">
        <input type="radio" id="bookly-one-booking-per-slot-1" name="one_booking_per_slot" value="1"<?php checked( $service['one_booking_per_slot'], 1 ) ?> class="custom-control-input" />
        <label for="bookly-one-booking-per-slot-1" class="custom-control-label"><?php esc_html_e( 'Enabled', 'bookly' ) ?></label>
    </div>
    <small class="form-text text-muted"><?php esc_html_e( 'Enable this option if you want to limit the possibility of booking within the service capacity to one time.', 'bookly' ) ?></small>
</div>