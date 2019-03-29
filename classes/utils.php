<?php
namespace local_enrolmultiselect;
/**
 * Defines general helper methods
 *
 */

defined('MOODLE_INTERNAL') || die();

class utils {

    /**
     * source: {@link https://www.if-not-true-then-false.com/2009/php-tip-convert-stdclass-object-to-multidimensional-array-and-convert-multidimensional-array-to-stdclass-object/}
     * 
     * @param type $d
     * @return array
     */
    public static function objectToArray($d) {
        if (is_object($d)) {
            // Gets the properties of the given object
            // with get_object_vars function
            $d = get_object_vars($d);
        }
		
        if (is_array($d)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            return array_map('self::objectToArray', $d);
        }
        else {
            // Return array
            return $d;
        }
    }
    
    /**
     * source: {@link https://www.if-not-true-then-false.com/2009/php-tip-convert-stdclass-object-to-multidimensional-array-and-convert-multidimensional-array-to-stdclass-object/}
     * 
     * @param type $d
     * @return type
     */
    public static function arrayToObject($d){
        if (is_array($d)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            return (object) array_map('self::arrayToObject', $d);
        }
        else {
            // Return object
            return $d;
        }
    }

    /**
     * 
     * @param string $json
     * @return array
     * @throws Exception
     */
    public static function JsonToArray($json){

        $decoded = json_decode( $json, true );

        if( ( !is_object( $decoded ) && !is_array( $decoded ) ) || ( JSON_ERROR_NONE !== json_last_error() ) ){
            throw new \moodle_exception( "Invalid JSON input" );
        }

        return $decoded;
    }
    
    /**
     * 
     * @param type $stringToFind
     * @param type $subjectString
     * @return type
     */
    public static function strStartsWith($stringToFind, $subjectString){
        return preg_match( "/^$stringToFind/i", $subjectString );
    }
    
    /**
     * 
     * @param type $stringToFind
     * @param type $subjectString
     * @return type
     */
    public static function strEndsWith($stringToFind, $subjectString){
        return preg_match( "/$stringToFind$/i", $subjectString );
    }
    
    /**
     * 
     * @param type $stringToFind
     * @param type $subjectString
     */
    public static function strContains($stringToFind, $subjectString){
        return preg_match( "/$stringToFind/i", $subjectString );
    }
}