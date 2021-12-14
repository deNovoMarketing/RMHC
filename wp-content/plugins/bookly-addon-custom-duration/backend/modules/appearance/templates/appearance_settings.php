<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Inputs;
?>
<div class="col-md-3 my-2">
    <?php Inputs::renderCheckBox( __( 'Show service price next to duration', 'bookly' ), null, get_option( 'bookly_app_service_duration_with_price' ), array( 'id' => 'bookly-service-duration-with-price' ) ) ?>
</div>