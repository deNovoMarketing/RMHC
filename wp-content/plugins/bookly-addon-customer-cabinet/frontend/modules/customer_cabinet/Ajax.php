<?php
namespace BooklyCustomerCabinet\Frontend\Modules\CustomerCabinet;

use Bookly\Lib as BooklyLib;

/**
 * Class Ajax
 * @package BooklyCustomerCabinet\Frontend\Modules\CustomerCabinet
 */
class Ajax extends BooklyLib\Base\Ajax
{
    /** @var BooklyLib\Entities\Customer */
    protected static $customer;

    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( '_default' => 'customer', );
    }

    /**
     * Get a list of appointments
     */
    public static function getAppointments()
    {
        $columns = self::parameter( 'columns' );
        $appointment_columns = self::parameter( 'appointment_columns' );
        $order = self::parameter( 'order', array() );
        $postfix_any = sprintf( ' (%s)', get_option( 'bookly_l10n_option_employee' ) );
        $client_diff = get_option( 'gmt_offset' ) * MINUTE_IN_SECONDS;
        $date_filter = self::parameter( 'date' );
        $service_filter = self::parameter( 'service' );
        $staff_filter = self::parameter( 'staff' );

        $query = BooklyLib\Entities\Appointment::query( 'a' )
            ->select( 'ca.id AS ca_id,
                    s.category_id,
                    c.name AS category,
                    COALESCE(ca.compound_service_id,ca.collaborative_service_id,a.service_id) AS service_id,
                    a.staff_id,
                    a.location_id,
                    a.custom_service_name,
                    a.online_meeting_provider,
                    a.online_meeting_id,
                    a.online_meeting_data,
                    COALESCE(s.title, a.custom_service_name) AS service_title,
                    st.full_name AS staff_name,
                    a.staff_any,
                    ca.series_id,
                    ca.status,
                    ca.extras,
                    ca.compound_token,
                    ca.collaborative_token,
                    ca.number_of_persons,
                    ca.custom_fields,
                    ca.appointment_id,
                    ca.time_zone,
                    ca.time_zone_offset,
                    p.id AS payment_id,
                    COALESCE(p.total, IF (ca.compound_service_id IS NULL AND ca.collaborative_service_id IS NULL, COALESCE(ss.price, ss_no_location.price, a.custom_service_price), s.price)) AS price,
                    IF (ca.time_zone_offset IS NULL,
                        a.start_date,
                        DATE_SUB(a.start_date, INTERVAL ' . $client_diff . ' + ca.time_zone_offset MINUTE)
                    ) AS start_date,
                    IF (ca.time_zone_offset IS NULL,
                        a.end_date,
                        DATE_SUB(a.end_date, INTERVAL ' . $client_diff . ' + ca.time_zone_offset MINUTE)
                    ) AS end_date,
                    ca.token' )
            ->leftJoin( 'Staff', 'st', 'st.id = a.staff_id' )
            ->innerJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
            ->leftJoin( 'Service', 's', 's.id = COALESCE(ca.compound_service_id, ca.collaborative_service_id, a.service_id)' )
            ->leftJoin( 'Category', 'c', 'c.id = s.category_id' )
            ->leftJoin( 'StaffService', 'ss', 'ss.staff_id = a.staff_id AND ss.service_id = a.service_id AND ss.location_id <=> a.location_id' )
            ->leftJoin( 'StaffService', 'ss_no_location', 'ss_no_location.staff_id = a.staff_id AND ss_no_location.service_id = a.service_id AND ss_no_location.location_id IS NULL' )
            ->leftJoin( 'Payment', 'p', 'p.id = ca.payment_id' )
            ->where( 'ca.customer_id', self::$customer->getId() )
            ->groupBy( 'COALESCE(compound_token, collaborative_token, ca.id)' );

        BooklyLib\Proxy\Locations::prepareAppointmentsQuery( $query );

        $sub_query = BooklyLib\Proxy\Files::getSubQueryAttachmentExists();
        if ( ! $sub_query ) {
            $sub_query = '0';
        }
        $query->addSelect( '(' . $sub_query . ') AS attachment' );
        if ( $date_filter !== null ) {
            if ( $date_filter == 'any' ) {
                $query->whereNot( 'a.start_date', null );
            } elseif ( $date_filter == 'null' ) {
                $query->where( 'a.start_date', null );
            } else {
                list ( $start, $end ) = explode( ' - ', $date_filter, 2 );
                $end = date( 'Y-m-d', strtotime( $end ) + DAY_IN_SECONDS );
                $query->whereBetween( 'a.start_date', $start, $end );
            }
        }

        if ( $staff_filter !== null && $staff_filter !== '' ) {
            $query->where( 'a.staff_id', $staff_filter );
        }

        if ( $service_filter !== null && $service_filter !== '' ) {
            $query->where( 'a.service_id', $service_filter ?: null );
        }

        foreach ( $order as $sort_by ) {
            $query->sortBy( str_replace( '.', '_', $columns[ $sort_by['column'] ]['data'] ) )
                ->order( $sort_by['dir'] == 'desc' ? BooklyLib\Query::ORDER_DESCENDING : BooklyLib\Query::ORDER_ASCENDING );
        }

        $total = $query->count( true );
        $query->limit( self::parameter( 'length' ) )->offset( self::parameter( 'start' ) );

        $data = array();
        $location_active = BooklyLib\Config::locationsActive();
        foreach ( $query->fetchArray() as $row ) {
            // Appointment status.
            $row['appointment_status_text'] = BooklyLib\Entities\CustomerAppointment::statusToString( $row['status'] );
            // Custom fields
            $customer_appointment = new BooklyLib\Entities\CustomerAppointment();
            $customer_appointment->load( $row['ca_id'] );
            $custom_fields = array();
            $fields_data = (array) BooklyLib\Proxy\CustomFields::getWhichHaveData();
            $staff_name = BooklyLib\Entities\Staff::find( $row['staff_id'] )->getTranslatedName();
            $category_name = $row['category_id']
                ? BooklyLib\Entities\Category::find( $row['category_id'] )->getTranslatedName()
                : '';
            $service_title = $row['service_id'] === null
                ? $row['custom_service_name']
                : BooklyLib\Entities\Service::find( $row['service_id'] )->getTranslatedTitle();
            $location = $location_active && isset( $row['location_id'] )
                ? BooklyLib\Proxy\Locations::findById( $row['location_id'] )->getTranslatedName()
                : '';

            foreach ( $fields_data as $field_data ) {
                $custom_fields[ $field_data->id ] = '';
            }
            foreach ( (array) BooklyLib\Proxy\CustomFields::getForCustomerAppointment( $customer_appointment ) as $custom_field ) {
                $custom_fields[ $custom_field['id'] ] = $custom_field['value'];
            }
            $allow_cancel_time = current_time( 'timestamp' ) + (int) BooklyLib\Proxy\Pro::getMinimumTimePriorCancel( $row['service_id'] );

            $allow_cancel = 'blank';
            if ( ! in_array( $row['status'], BooklyLib\Proxy\CustomStatuses::prepareFreeStatuses( array(
                BooklyLib\Entities\CustomerAppointment::STATUS_CANCELLED,
                BooklyLib\Entities\CustomerAppointment::STATUS_REJECTED,
                BooklyLib\Entities\CustomerAppointment::STATUS_DONE,
            ) ) ) ) {
                if ( in_array( $row['status'], BooklyLib\Proxy\CustomStatuses::prepareBusyStatuses( array(
                        BooklyLib\Entities\CustomerAppointment::STATUS_APPROVED,
                        BooklyLib\Entities\CustomerAppointment::STATUS_PENDING,
                    ) ) ) && $row['start_date'] === null ) {
                    $allow_cancel = 'allow';
                } else {
                    if ( $row['start_date'] > current_time( 'mysql' ) ) {
                        if ( $allow_cancel_time < strtotime( $row['start_date'] ) ) {
                            $allow_cancel = 'allow';
                        } else {
                            $allow_cancel = 'deny';
                        }
                    } else {
                        $allow_cancel = 'expired';
                    }
                }
            }
            $allow_reschedule = 'blank';
            if ( ! in_array( $row['status'], BooklyLib\Proxy\CustomStatuses::prepareFreeStatuses( array(
                    BooklyLib\Entities\CustomerAppointment::STATUS_CANCELLED,
                    BooklyLib\Entities\CustomerAppointment::STATUS_REJECTED,
                    BooklyLib\Entities\CustomerAppointment::STATUS_WAITLISTED,
                    BooklyLib\Entities\CustomerAppointment::STATUS_DONE,
                ) ) ) && $row['start_date'] !== null ) {
                if ( $row['start_date'] > current_time( 'mysql' ) ) {
                    if ( $allow_cancel_time < strtotime( $row['start_date'] ) && BooklyLib\Proxy\SpecialHours::isNotInSpecialHour( $row['start_date'], $row['end_date'], $row['service_id'], $row['staff_id'], $row['location_id'], null ) ) {
                        $allow_reschedule = 'allow';
                    } else {
                        $allow_reschedule = 'deny';
                    }
                } else {
                    $allow_reschedule = 'expired';
                }
            }
            $price = null;
            if ( $row['series_id'] && ! $row['payment_id'] ) {
                $payment_query = BooklyLib\Entities\CustomerAppointment::query( 'ca' )
                    ->select( 'p.total' )
                    ->leftJoin( 'Payment', 'p', 'p.id = ca.payment_id' )
                    ->where( 'series_id', $row['series_id'] )
                    ->whereNot( 'payment_id', null )
                    ->limit( 1 );
                if ( $payment = $payment_query->fetchCol( 'total' ) ) {
                    $price = BooklyLib\Utils\Price::format( reset( $payment ) );
                }
            }
            if ( $price === null ) {
                $price = $row['price'] !== null
                    ? BooklyLib\Utils\Price::format( $row['price'] + BooklyLib\Proxy\ServiceExtras::getTotalPrice( (array) json_decode( $row['extras'], true ), $row['number_of_persons'] ) )
                    : __( 'N/A', 'bookly' );
            }
            $appointment = new BooklyLib\Entities\Appointment();
            $appointment->setOnlineMeetingData( $row['online_meeting_data'] )
                ->setOnlineMeetingProvider( $row['online_meeting_provider'] )
                ->setOnlineMeetingId( $row['online_meeting_id'] );

            $data[] = array(
                'ca_id' => $row['ca_id'],
                'date' => strtotime( $row['start_date'] ),
                'raw_start_date' => $row['start_date'],
                'location' => $location,
                'start_date' => $row['start_date'] === null ? __( 'N/A', 'bookly' ) : ( ( in_array( 'timezone', $appointment_columns ) && $timezone = BooklyLib\Proxy\Pro::getCustomerTimezone( $row['time_zone'], $row['time_zone_offset'] ) ) ? sprintf( '%s<br/>(%s)', BooklyLib\Utils\DateTime::formatDateTime( $row['start_date'] ), $timezone ) : BooklyLib\Utils\DateTime::formatDateTime( $row['start_date'] ) ),
                'staff_name' => $staff_name . ( $row['staff_any'] ? $postfix_any : '' ),
                'service_title' => $service_title . '<br/>' . implode( '<br/>', array_map( function ( $extras ) { return $extras['title']; }, (array) BooklyLib\Proxy\ServiceExtras::getCAInfo( $row['ca_id'], true ) ) ),
                'category' => $category_name,
                'status' => $row['appointment_status_text'],
                'price' => $price,
                'payment_id' => $row['payment_id'],
                'join_online_meeting_url' => BooklyLib\Proxy\Shared::buildOnlineMeetingJoinUrl( '', $appointment ),
                'online_meeting_provider' => $row['online_meeting_provider'],
                'online_meeting_url' => BooklyLib\Proxy\Shared::buildOnlineMeetingUrl( '', $appointment ),
                'allow_cancel' => $allow_cancel,
                'allow_reschedule' => $allow_reschedule,
                'custom_fields' => $custom_fields,
            );
        }

        $data = array_values( $data );

        wp_send_json( array(
            'draw' => (int) self::parameter( 'draw' ),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data,
        ) );
    }

    public static function saveProfile()
    {
        $columns = explode( ',', self::parameter( 'columns' ) );
        $profile_data = self::parameters();
        $response = array( 'success' => true, 'errors' => array() );

        // Customer Information
        $info_fields = array();
        foreach ( $profile_data as $field => $parameter ) {
            if ( strpos( $field, 'info_field_checkbox' ) === 0 ) {
                $field_id = substr( $field, 20 );
                $info_fields[ $field_id ]['id'] = $field_id;
                $info_fields[ $field_id ]['value'][] = $parameter;
            } elseif ( strpos( $field, 'info_field' ) === 0 ) {
                $field_id = substr( $field, 11 );
                $info_fields[ $field_id ] = array(
                    'id' => $field_id,
                    'value' => $parameter,
                );
            }
        }

        // Check errors
        $info_errors = BooklyLib\Proxy\CustomerInformation::validate( $response['errors'], $info_fields );
        if ( isset( $info_errors['info_fields'] ) ) {
            foreach ( $info_errors['info_fields'] as $field_id => $error ) {
                if ( in_array( 'customer_information_' . $field_id, $columns ) ) {
                    $response['errors']['info_fields'][ $field_id ] = $error;
                }
            }
        }
        foreach ( $profile_data as $field => $value ) {
            $errors = array();
            switch ( $field ) {
                case 'last_name':
                case 'first_name':
                case 'full_name':
                    $errors = self::_validateProfile( 'name', $profile_data );
                    break;
                case 'email':
                    $errors = self::_validateProfile( 'email', $profile_data );
                    break;
                case 'phone':
                    $errors = self::_validateProfile( 'phone', $profile_data );
                    break;
                case 'birthday':
                    $errors = self::_validateProfile( 'birthday', $profile_data );
                    break;
                case 'country':
                case 'state':
                case 'postcode':
                case 'city':
                case 'street':
                case 'street_number':
                case 'additional_address':
                    $errors = self::_validateProfile( 'address', $profile_data );
                    break;

            }
            $response['errors'] = array_merge( $response['errors'], $errors );
        }

        if ( empty( $response['errors'] ) && $profile_data['current_password'] ) {
            // Update wordpress password
            $user = get_userdata( self::$customer->getWpUserId() );
            if ( $user ) {
                if ( ! wp_check_password( $profile_data['current_password'], $user->data->user_pass ) ) {
                    $response['errors']['current_password'][] = __( 'Wrong current password', 'bookly' );
                }
            }
            if ( $profile_data['new_password_1'] == '' ) {
                $response['errors']['new_password_1'][] = __( 'Required', 'bookly' );
            }
            if ( $profile_data['new_password_2'] == '' ) {
                $response['errors']['new_password_2'][] = __( 'Required', 'bookly' );
            }
            if ( $profile_data['new_password_1'] != $profile_data['new_password_2'] ) {
                $response['errors']['new_password_2'][] = __( 'Passwords mismatch', 'bookly' );
            }
            if ( empty( $response['errors'] ) ) {
                wp_set_password( $profile_data['new_password_1'], self::$customer->getWpUserId() );
            }
        }

        if ( empty( $response['errors'] ) ) {
            // Save profile
            foreach ( $columns as $column ) {
                switch ( $column ) {
                    case 'name':
                        if ( BooklyLib\Config::showFirstLastName() ) {
                            self::$customer
                                ->setFirstName( $profile_data['first_name'] )
                                ->setLastName( $profile_data['last_name'] );
                        } else {
                            self::$customer->setFullName( $profile_data['full_name'] );
                        }
                        break;
                    case 'email':
                        self::$customer->setEmail( $profile_data['email'] );
                        break;
                    case 'phone':
                        self::$customer->setPhone( $profile_data['phone'] );
                        break;
                    case 'birthday':
                        $day = isset( $profile_data['birthday']['day'] ) ? (int) $profile_data['birthday']['day'] : 1;
                        $month = isset( $profile_data['birthday']['month'] ) ? (int) $profile_data['birthday']['month'] : 1;
                        $year = isset( $profile_data['birthday']['year'] ) ? (int) $profile_data['birthday']['year'] : date( 'Y' );

                        self::$customer->setBirthday( sprintf( '%04d-%02d-%02d', $year, $month, $day ) );
                        break;
                    case 'address':
                        self::$customer
                            ->setCountry( isset( $profile_data['country'] ) ? $profile_data['country'] : self::$customer->getCountry() )
                            ->setState( isset( $profile_data['state'] ) ? $profile_data['state'] : self::$customer->getState() )
                            ->setPostcode( isset( $profile_data['postcode'] ) ? $profile_data['postcode'] : self::$customer->getPostcode() )
                            ->setCity( isset( $profile_data['city'] ) ? $profile_data['city'] : self::$customer->getCity() )
                            ->setStreet( isset( $profile_data['street'] ) ? $profile_data['street'] : self::$customer->getStreet() )
                            ->setStreetNumber( isset( $profile_data['street_number'] ) ? $profile_data['street_number'] : self::$customer->getStreetNumber() )
                            ->setAdditionalAddress( isset( $profile_data['additional_address'] ) ? $profile_data['additional_address'] : self::$customer->getAdditionalAddress() );
                        break;

                }
            }
            if ( ! empty( $info_fields ) ) {
                self::$customer->setInfoFields( json_encode( BooklyLib\Proxy\CustomerInformation::prepareInfoFields( $info_fields ) ?: array() ) );
            }
            self::$customer->save();
        } else {
            $response['success'] = false;
        }

        wp_send_json( $response );
    }

    /**
     * Validate profile data
     *
     * @param string $field
     * @param array $profile_data
     * @return array
     */
    private static function _validateProfile( $field, $profile_data )
    {
        $validator = new BooklyLib\Validator();
        switch ( $field ) {
            case 'email':
                $validator->validateEmail( 'email', $profile_data );
                break;
            case 'name':
                if ( BooklyLib\Config::showFirstLastName() ) {
                    $validator->validateName( 'first_name', $profile_data['first_name'] );
                    $validator->validateName( 'last_name', $profile_data['last_name'] );
                } else {
                    $validator->validateName( 'full_name', $profile_data['full_name'] );
                }
                break;
            case 'phone':
                $validator->validatePhone( 'phone', $profile_data['phone'], BooklyLib\Config::phoneRequired() );
                break;
            case 'birthday':
                $validator->validateBirthday( $field, $profile_data[ $field ] );
                break;
            case 'address':
                $address_show_fields = (array) get_option( 'bookly_cst_address_show_fields', array() );
                foreach ( $address_show_fields as $field_name => $data ) {
                    if ( $data['show'] ) {
                        $validator->validateAddress( $field_name, $profile_data[ $field_name ], BooklyLib\Config::addressRequired() );
                    }
                }
                break;
        }

        return $validator->getErrors();
    }

    /**
     * @inheritDoc
     */
    protected static function hasAccess( $action )
    {
        if ( parent::hasAccess( $action ) ) {
            self::$customer = BooklyLib\Entities\Customer::query()->where( 'wp_user_id', get_current_user_id() )->findOne();

            return self::$customer->isLoaded();
        }

        return false;
    }
}