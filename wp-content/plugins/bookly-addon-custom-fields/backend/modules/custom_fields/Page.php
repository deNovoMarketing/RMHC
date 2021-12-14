<?php
namespace BooklyCustomFields\Backend\Modules\CustomFields;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Utils\DateTime;

/**
 * Class Page
 * @package BooklyCustomFields\Backend\Modules\CustomFields
 */
class Page extends BooklyLib\Base\Component
{
    /**
     *  Render page.
     */
    public static function render()
    {
        $tab = self::parameter( 'tab', 'general' );

        self::enqueueStyles( array(
            'alias' => array( 'bookly-backend-globals', ),
        ) );

        self::enqueueScripts( array(
            'bookly' => array( 'backend/resources/js/sortable.min.js' => array( 'bookly-backend-globals' ), ),
            'module' => array( 'js/custom_fields.js' => array( 'bookly-sortable.min.js' ) ),
        ) );

        wp_localize_script( 'bookly-custom_fields.js', 'BooklyCustomFieldsL10n', array(
            'saved' => __( 'Settings saved.', 'bookly' ),
            'datePicker' => DateTime::datePickerOptions(),
            'dateRange' => DateTime::dateRangeOptions(),
        ) );

        self::renderTemplate( 'index', compact( 'tab' ) );
    }
}