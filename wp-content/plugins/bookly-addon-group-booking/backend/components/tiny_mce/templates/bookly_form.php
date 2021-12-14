<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<tr>
    <td>
        <label for="bookly-hide-number-of-persons"><?php echo esc_html( get_option( 'bookly_l10n_label_number_of_persons' ) ) ?></label>
    </td>
    <td>
        <div class="checkbox">
            <label>
                <input type="checkbox" id="bookly-hide-number-of-persons" checked>
                <?php esc_html_e( 'Hide this field', 'bookly' ) ?>
            </label>
        </div>
    </td>
</tr>