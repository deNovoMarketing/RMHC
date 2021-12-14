<?php  if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * @var array $duration_options
 * @var array $service
 */

$service_id = $service['id'];
?>

<div class="form-row bookly-js-units-block border-left ml-4 pl-3" style="display: <?php echo $service['units_max'] > 1 ? 'block' : 'none' ?>">
    <div class="col-sm-4 bookly-js-service bookly-js-service-simple">
        <div class="form-group">
            <label for="unit_duration_<?php echo $service_id ?>">
                <?php esc_html_e( 'Unit duration', 'bookly' ) ?>
            </label>

            <select id="unit_duration_<?php echo $service_id ?>" class="bookly-js-unit-duration form-control custom-select" name="unit_duration">
                <?php foreach ( $duration_options as $option ): ?>
                    <option value="<?php echo $option['value'] ?>" <?php echo $option['selected'] ?> >
                        <?php echo $option['label'] ?>
                    </option>
                <?php endforeach ?>
            </select>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="form-group">
            <label for="units_min_<?php echo $service_id ?>"><?php esc_html_e( 'Minimum units', 'bookly' ) ?></label>
            <input id="units_min_<?php echo $service_id ?>"
                   class="form-control" type="number" min="1" step="1" name="units_min"
                   value="<?php echo esc_attr( $service['units_min'] ?: 1 ) ?>">
        </div>
    </div>

    <div class="col-sm-4">
        <div class="form-group">
            <label for="units_max_<?php echo $service_id ?>"><?php esc_html_e( 'Maximum units', 'bookly' ) ?></label>
            <input id="units_max_<?php echo $service_id ?>"
                   class="form-control" type="number" min="1" step="1" name="units_max"
                   value="<?php echo esc_attr( $service['units_max'] ?: 1 ) ?>">
        </div>
    </div>

</div>