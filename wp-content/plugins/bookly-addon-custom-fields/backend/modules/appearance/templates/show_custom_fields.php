<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Inputs;
?>
<div class="col-md-3 my-2">
    <?php Inputs::renderCheckBox( __( 'Show custom fields', 'bookly' ), null, get_option( 'bookly_custom_fields_enabled' ), array( 'id' => 'bookly-show-custom-fields' ) ) ?>
</div>