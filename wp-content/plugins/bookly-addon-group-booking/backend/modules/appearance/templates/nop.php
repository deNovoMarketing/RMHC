<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Editable\Elements;
?>
<div class="bookly-form-group bookly-js-nop">
    <?php Elements::renderLabel( array( 'bookly_l10n_label_number_of_persons', ) ) ?>
    <div class="bookly-form-field">
        <select class="bookly-select-mobile bookly-js-select-number-of-persons">
            <option>1</option>
            <option>2</option>
            <option>3</option>
        </select>
    </div>
</div>