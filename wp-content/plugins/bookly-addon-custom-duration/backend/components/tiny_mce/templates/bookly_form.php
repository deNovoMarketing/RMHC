<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<tr>
    <td>
        <label for="bookly-hide-service-duration"><?php echo esc_html( get_option( 'bookly_l10n_label_service_duration' ) ) ?></label>
    </td>
    <td>
        <div class="checkbox">
            <label>
                <input type="checkbox" id="bookly-hide-service-duration">
                <?php esc_html_e( 'Hide this field', 'bookly' ) ?>
            </label>
        </div>
    </td>
</tr>