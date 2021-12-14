<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Support;
use BooklyCustomFields\Backend\Modules\CustomFields;

/** @var string $tab */
/** @var string $services_html */
?>
<div id="bookly-tbs" class="wrap">
    <div class="form-row align-items-center mb-3">
        <h4 class="col m-0"><?php esc_html_e( 'Custom Fields', 'bookly' ) ?></h4>
        <?php Support\Buttons::render( $self::pageSlug() ) ?>
    </div>

    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs bookly-js-custom-fields-tabs flex-column flex-lg-row bookly-nav-tabs-md" role="tablist">
                <li class="nav-item text-center">
                    <a class="nav-link<?php if ( $tab === 'general' ) : ?> active<?php endif ?>" href="<?php echo add_query_arg( array( 'page' => CustomFields\Page::pageSlug() ), admin_url( 'admin.php' ) ) ?>" data-toggle="bookly-tab" data-tab="general"><?php esc_html_e( 'General', 'bookly' ) ?></a>
                </li>
                <li class="nav-item text-center">
                    <a class="nav-link<?php if ( $tab === 'conditions' ) : ?> active<?php endif ?>" href="<?php echo add_query_arg( array( 'page' => CustomFields\Page::pageSlug(), 'tab' => 'conditions' ), admin_url( 'admin.php' ) ) ?>" data-toggle="bookly-tab" data-tab="conditions"><?php esc_html_e( 'Conditions', 'bookly' ) ?></a>
                </li>
            </ul>
        </div>
        <div class="card-body bookly-js-custom-fields-wrap">
        </div>
        <div class="card-footer bg-transparent d-flex justify-content-end">
            <?php Buttons::renderSubmit( 'ajax-save-custom-fields' ) ?>
            <?php Buttons::renderReset( 'ajax-reset-custom-fields', 'ml-2' ) ?>
        </div>
    </div>
</div>