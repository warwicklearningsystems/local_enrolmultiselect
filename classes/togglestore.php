<?php

namespace local_enrolmultiselect;

class togglestore{

    const TOGGLE_ITEMS_TO_INCLUDE_MAP_NAME = 'toggle_items_to_include';
    const TOGGLE_ITEMS_TO_EXCLUDE_MAP_NAME = 'toggle_items_to_exclude';
    const TOGGLE_POTENTIAL_MAP_NAME        = 'toggle_to_potential';
    const TOGGLE_CURRENT_MAP_NAME          = 'toggle_to_current';
    const TOGGLE_REVERSE_MAP = [ 
        self::TOGGLE_ITEMS_TO_INCLUDE_MAP_NAME => self::TOGGLE_ITEMS_TO_EXCLUDE_MAP_NAME,
        self::TOGGLE_ITEMS_TO_EXCLUDE_MAP_NAME => self::TOGGLE_ITEMS_TO_INCLUDE_MAP_NAME
    ];

    /**
     * 
     * @global \local_enrolmultiselect\type $USER
     * @param type $selectorHash
     * @param type $itemMapName
     * @param type $toggleValueMap
     */
    public static function set( $selectorHash, $itemMapName, $toggleValueMap ){
        global $USER;
        
        if( !isset( $USER->userselectors[ $selectorHash ][ $itemMapName ] ) ){
            $USER->userselectors[ $selectorHash ][ $itemMapName ] = json_decode( $toggleValueMap );
        }else{
            $USER->userselectors[ $selectorHash ][ $itemMapName ] = array_merge(
                $USER->userselectors[ $selectorHash ][ $itemMapName ],
                json_decode( $toggleValueMap )
            );
        }
        
        //now ensure that these items are removed from the converse toggle map
        self::remove( $selectorHash, self::TOGGLE_REVERSE_MAP[ $itemMapName ], json_decode( $toggleValueMap ) );
    }
    
    /**
     * 
     * @param type $selectorHash
     * @param type $itemMapName
     */
    public static function remove( $selectorHash, $itemMapName, $toggleValueMap ){
        global $USER;

        if( !isset( $USER->userselectors[ $selectorHash ][ $itemMapName ] ) )
            return false;
            
        $USER->userselectors[ $selectorHash ][ $itemMapName ] = array_filter( $USER->userselectors[ $selectorHash ][ $itemMapName ], function( $val ) use( $toggleValueMap ){
                return !in_array( $val, $toggleValueMap );
            }
        );
        
        if( 0 == count( $USER->userselectors[ $selectorHash ][ $itemMapName ] ) )
            unset( $USER->userselectors[ $selectorHash ][ $itemMapName ] );
    }

    /**
     * 
     * @global type $USER
     * @param type $selectorHash
     * @param type $itemMapName
     * @return array
     */
    public static function get( $selectorHash, $itemMapName ){
        global $USER;

        if( isset( $USER->userselectors[ $selectorHash ][ $itemMapName ] ) )
            return $USER->userselectors[ $selectorHash ][ $itemMapName ];

        return false;
    }
}