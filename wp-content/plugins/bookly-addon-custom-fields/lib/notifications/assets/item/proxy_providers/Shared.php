<?php
namespace BooklyCustomFields\Lib\Notifications\Assets\Item\ProxyProviders;

use Bookly\Lib\Notifications\Assets\Item\Codes;
use Bookly\Lib\Notifications\Assets\Item\Proxy;
use BooklyCustomFields\Lib\ProxyProviders\Local;

/**
 * Class Shared
 *
 * @package BooklyCustomFields\Lib\Notifications\Assets\Item\ProxyProviders
 */
abstract class Shared extends Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function prepareCodes( Codes $codes )
    {
        $codes->custom_fields = Local::getFormatted( $codes->getItem()->getCA(), 'text' );
        $codes->custom_fields_2c = Local::getFormatted( $codes->getItem()->getCA(), 'html' );
        $codes->custom_fields_data = array();
        foreach ( (array) json_decode( $codes->getItem()->getCA()->getCustomFields(), true ) as $custom_field ) {
            $codes->custom_fields_data[ 'custom_field#' . $custom_field['id'] ] = is_array( $custom_field['value'] ) ? implode( ',', $custom_field['value'] ) : $custom_field['value'];
        }
    }

    /**
     * @inheritDoc
     */
    public static function prepareReplaceCodes( array $replace_codes, Codes $codes, $format )
    {
        $replace_codes['custom_fields'] = $codes->custom_fields;
        $replace_codes['custom_fields_2c'] = $format == 'html' ? $codes->custom_fields_2c : $codes->custom_fields;
        if ( $codes->custom_fields_data !== null ) {
            foreach ( $codes->custom_fields_data as $key => $value ) {
                $replace_codes[ $key ] = $value;
            }
        }

        return $replace_codes;
    }
}