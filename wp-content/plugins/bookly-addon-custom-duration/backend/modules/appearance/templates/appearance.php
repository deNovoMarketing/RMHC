<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Utils\Price;
use Bookly\Lib\Utils\DateTime;
use Bookly\Backend\Components\Editable\Elements;
?>
<div class="bookly-form-group bookly-js-custom-duration">
    <?php Elements::renderLabel( array( 'bookly_l10n_label_service_duration', ) ) ?>
    <div>
        <select class="bookly-select-mobile bookly-js-select-duration bookly-animate">
            <option value="0">-</option>
            <option value="1" data-text-0="<?php echo DateTime::secondsToInterval( 3600 * 2 ) ?>" data-text-1="<?php echo DateTime::secondsToInterval( 3600 * 2 ) ?> (<?php echo Price::format( 40 * 2 ) ?>)"></option>
            <option value="1" data-text-0="<?php echo DateTime::secondsToInterval( 3600 * 3 ) ?>" data-text-1="<?php echo DateTime::secondsToInterval( 3600 * 3 ) ?> (<?php echo Price::format( 40 * 3 ) ?>)"></option>
            <option value="1" data-text-0="<?php echo DateTime::secondsToInterval( 3600 * 4 ) ?>" data-text-1="<?php echo DateTime::secondsToInterval( 3600 * 4 ) ?> (<?php echo Price::format( 40 * 4 ) ?>)"></option>
            <option value="1" data-text-0="<?php echo DateTime::secondsToInterval( 3600 * 5 ) ?>" data-text-1="<?php echo DateTime::secondsToInterval( 3600 * 5 ) ?> (<?php echo Price::format( 40 * 5 ) ?>)"></option>
        </select>
    </div>
</div>