<?php
namespace local_enrolmultiselect\traits;

use \local_enrolmultiselect\utils;
use \local_enrolmultiselect\search;
use \local_enrolmultiselect\togglestore;

trait config{
    
    /**
     * 
     * @param type $config
     * @param type $property
     * @param search $search
     * @param type $toggledItemsToInclude
     * @param type $toggledItemsToExclude
     * @return type
     */
    public static function processConfig( $config, $property, search $search = null, $toggledItemsToInclude = array(), $toggledItemsToExclude = array() ){

        if( !$config )
            return false;

        $configArrayMap = utils::JsonToArray( $config );
        
        //temporarily bind items to the config
        if( $toggledItemsToInclude ){
            $valuesToBindToConfig = self::constructConfigValues( $property, $toggledItemsToInclude );
            
            //filter stored config to avoid duplicates
            $configArrayMap = array_filter( $configArrayMap, function ($valueMap) use( $property, $toggledItemsToInclude ){
                    return !in_array( $valueMap[ $property ], $toggledItemsToInclude );
                } 
            );

            $configArrayMap = array_merge( $configArrayMap, $valuesToBindToConfig );
        }
        
        //sort values alphabetically
        usort( $configArrayMap, function( $a, $b ) use ( $property ){
            return strcasecmp( $a[ $property ], $b[ $property ] );
        });
        
        $configMap = utils::arrayToObject( $configArrayMap );
        
        if( $search || $toggledItemsToExclude ){
            $configArray = [];
            
            foreach( $configMap as $key=>$config){

                if( $toggledItemsToExclude && in_array( $config->{$property}, $toggledItemsToExclude ) ){
                    unset( $configMap->$key );
                }

                if( $search ){
                    if( $search->getSearchAnyWhere() ){

                        if( !utils::strContains( $search->getStringToFind(), $config->{$search->getField()} ) ){
                            unset( $configMap->$key );
                        }
                    }else{
                        if( !utils::strStartsWith( $search->getStringToFind(), $config->{$search->getField()} ) ){
                            unset( $configMap->$key );
                        }
                    }
                }
            }
        }
        
        return $configMap;
    }
 
    /**
     * 
     * @param type $values
     * @return boolean
     */
    public static function constructConfigValues( $property, $values ){
        if( !$values )
            return false;

        $configMap = [];

        foreach( $values as $value ){
            $configMap[] = [
                'id' => $value,
                $property => $value 
            ];
        }

        return $configMap;
    }
}