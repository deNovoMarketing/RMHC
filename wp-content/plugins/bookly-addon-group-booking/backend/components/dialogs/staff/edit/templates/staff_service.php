<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/** @var Bookly\Lib\Entities\Service $service */
?>
<div class="col-6">
    <div class="form-group bookly-js-capacity-form-group">
        <div class="d-lg-none"><?php esc_html_e( 'Capacity (min and max)', 'bookly' ) ?></div>
        <div class="d-flex">
            <div class="flex-fill mr-2">
                <input class="form-control bookly-js-capacity bookly-js-capacity-min" type="number" min="1" <?php disabled( ! array_key_exists( $service->getId(), $services_data ) ) ?>
                       name="capacity_min[<?php echo $service->getId() ?>]"
                       value="<?php echo array_key_exists( $service->getId(), $services_data ) ? $services_data[ $service->getId() ]['capacity_min'] : $service->getCapacityMin() ?>"
                       <?php if ( $read_only ) : ?>readonly<?php endif ?>
                >
            </div>
            <div class="flex-fill">
                <input class="form-control bookly-js-capacity bookly-js-capacity-max" type="number" min="1" <?php disabled( ! array_key_exists( $service->getId(), $services_data ) ) ?>
                       name="capacity_max[<?php echo $service->getId() ?>]"
                       value="<?php echo array_key_exists( $service->getId(), $services_data ) ? $services_data[ $service->getId() ]['capacity_max'] : $service->getCapacityMax() ?>"
                       <?php if ( $read_only ) : ?>readonly<?php endif ?>
                >
            </div>
        </div>
    </div>
</div>