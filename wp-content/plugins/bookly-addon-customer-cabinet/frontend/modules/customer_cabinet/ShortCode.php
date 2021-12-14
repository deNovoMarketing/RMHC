<?php
namespace BooklyCustomerCabinet\Frontend\Modules\CustomerCabinet;

use BooklyCustomerCabinet\Lib;
use Bookly\Lib as BooklyLib;
use Bookly\Backend\Modules as BooklyModules;

/**
 * Class Controller
 * @package BooklyCustomerCabinet\Frontend\Modules\CustomerCabinet
 */
class ShortCode extends BooklyLib\Base\Component
{
    /**
     * Init component.
     */
    public static function init()
    {
        // Register shortcodes.
        add_shortcode( 'bookly-customer-cabinet', array( __CLASS__, 'render' ) );

        // Assets.
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'linkStyles' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'linkScripts' ) );
    }

    /**
     * Link styles.
     */
    public static function linkStyles()
    {
        if (
            get_option( 'bookly_gen_link_assets_method' ) == 'enqueue' ||
            BooklyLib\Utils\Common::postsHaveShortCode( 'bookly-customer-cabinet' )
        ) {
            if ( get_option( 'bookly_cst_phone_default_country' ) != 'disabled' ) {
                self::enqueueStyles( array(
                    'bookly' => array( 'frontend/resources/css/intlTelInput.css' ),
                ) );
            }
            self::enqueueStyles( array(
                'bookly' => array( 'backend/resources/css/fontawesome-all.min.css' => array( 'bookly-backend-globals' ) ),
                'module' => array( 'css/customer-cabinet.css' ),
            ) );
        }
    }

    /**
     * Link scripts.
     */
    public static function linkScripts()
    {
        if (
            get_option( 'bookly_gen_link_assets_method' ) == 'enqueue' ||
            BooklyLib\Utils\Common::postsHaveShortCode( 'bookly-customer-cabinet' )
        ) {
            if ( get_option( 'bookly_cst_phone_default_country' ) != 'disabled' ) {
                self::enqueueScripts( array(
                    'bookly' => array( '/frontend/resources/js/intlTelInput.min.js' => array( 'jquery' ), ),
                ) );
            }
            self::enqueueScripts( array(
                'module' => array( 'js/customer-cabinet.js' => array( 'bookly-backend-globals' ) ),
                'bookly' => array( 'backend/resources/js/select2.min.js' => array( 'bookly-customer-cabinet.js' ), ),
            ) );

            wp_localize_script( 'bookly-customer-cabinet.js', 'BooklyCustomerCabinetL10n', array(
                'zeroRecords' => __( 'No appointments.', 'bookly' ),
                'minDate' => 0,
                'maxDate' => BooklyLib\Config::getMaximumAvailableDaysForBooking(),
                'datePicker' => BooklyLib\Utils\DateTime::datePickerOptions(),
                'dateRange' => BooklyLib\Utils\DateTime::dateRangeOptions( array( 'anyTime' => __( 'Any time', 'bookly' ) ) ),
                'tasks' => array(
                    'enabled' => BooklyLib\Config::tasksActive(),
                    'title' => BooklyModules\Appointments\Proxy\Tasks::getFilterText(),
                ),
                'expired_appointment' => __( 'Expired', 'bookly' ),
                'deny_cancel_appointment' => __( 'Not allowed', 'bookly' ),
                'cancel' => __( 'Cancel', 'bookly' ),
                'payment' => __( 'Payment', 'bookly' ),
                'reschedule' => __( 'Reschedule', 'bookly' ),
                'noTimeslots' => __( 'There are no time slots for selected date.', 'bookly' ),
                'profile_update_success' => __( 'Profile updated successfully.', 'bookly' ),
                'processing' => __( 'Processing...', 'bookly' ),
                'errors' => array(
                    'cancel' => __( 'Unfortunately, you\'re not able to cancel the appointment because the required time limit prior to canceling has expired.', 'bookly' ),
                    'reschedule' => __( 'The selected time is not available anymore. Please, choose another time slot.', 'bookly' ),
                ),
            ) );
        }
    }

    /**
     * Render Customer Services shortcode.
     *
     * @param array $attributes
     * @return string
     */
    public static function render( $attributes )
    {
        global $sitepress;

        // Disable caching.
        BooklyLib\Utils\Common::noCache();

        // Prepare URL for AJAX requests.
        $ajax_url = admin_url( 'admin-ajax.php' );

        // Support WPML.
        if ( $sitepress instanceof \SitePress ) {
            $ajax_url = add_query_arg( array( 'lang' => $sitepress->get_current_language() ), $ajax_url );
        }

        $customer = new BooklyLib\Entities\Customer();
        if ( is_user_logged_in() && $customer->loadBy( array( 'wp_user_id' => get_current_user_id() ) ) ) {
            $titles = array(
                'category' => BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_category' ),
                'service' => BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_service' ),
                'staff' => BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_employee' ),
                'location' => BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_location' ),
                'date' => __( 'Date', 'bookly' ),
                'time' => __( 'Time', 'bookly' ),
                'price' => __( 'Price', 'bookly' ),
                'online_meeting' => __( 'Online meeting', 'bookly' ),
                'join_online_meeting' => __( 'Join online meeting', 'bookly' ),
                'cancel' => __( 'Cancel', 'bookly' ),
                'reschedule' => __( 'Reschedule', 'bookly' ),
                'status' => __( 'Status', 'bookly' ),
            );
            if ( BooklyLib\Config::customFieldsActive() ) {
                foreach ( (array) BooklyLib\Proxy\CustomFields::getTranslated() as $field ) {
                    if ( ! in_array( $field->type, array( 'captcha', 'text-content', 'file' ) ) ) {
                        $titles[ 'custom_field_' . $field->id ] = $field->label;
                    }
                }
            }

            $customer_address = array(
                'country' => $customer->getCountry(),
                'state' => $customer->getState(),
                'postcode' => $customer->getPostcode(),
                'city' => $customer->getCity(),
                'street' => $customer->getStreet(),
                'street_number' => $customer->getStreetNumber(),
                'additional_address' => $customer->getAdditionalAddress(),
            );

            $appointment_columns = isset( $attributes['appointments'] ) ? explode( ',', $attributes['appointments'] ) : array();
            $filters = in_array( 'filters', $appointment_columns );
            $show_reason = in_array( 'reason', $appointment_columns );
            foreach ( $appointment_columns as $pos => $column ) {
                if ( ! array_key_exists( $column, $titles ) ) {
                    unset( $appointment_columns[ $pos ] );
                }
            }
            $services = BooklyLib\Entities\Service::query( 's' )
                ->select( 's.id, s.title' )
                ->where( 'type', BooklyLib\Entities\Service::TYPE_SIMPLE )
                ->where( 'visibility', BooklyLib\Entities\Service::VISIBILITY_PUBLIC )
                ->fetchArray();

            $staff_members = BooklyLib\Entities\Staff::query( 's' )->select( 's.id, s.full_name' )->where( 'visibility', 'public' )->fetchArray();

            return self::renderTemplate( 'short_code', array(
                'ajax_url' => $ajax_url,
                'appointment_columns' => $appointment_columns,
                'filters' => $filters,
                'show_reason' => $show_reason,
                'attributes' => (array) $attributes,
                'customer' => $customer,
                'customer_address' => $customer_address,
                'form_id' => uniqid( 'bookly-js-customer-cabinet-' ),
                'titles' => $titles,
                'services' => $services,
                'staff_members' => $staff_members,
            ), false );
        }

        return self::renderTemplate( 'permission', array(), false );
    }
}