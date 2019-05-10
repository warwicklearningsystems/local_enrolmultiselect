<?php

namespace local_enrolmultiselect;

use \local_enrolmultiselect\traits\config as configtrait;

class config{
    
    use configtrait;

    /**
     *
     * @var string 
     */
    private $pluginName;
    
    /**
     *
     * @var string 
     */
    private $name;
    
    /**
     *
     * @var string 
     */
    private $property;
    
    /**
     * 
     * @param string $pluginName
     * @param string $name
     * @param string $property
     */
    public function __construct( $pluginName, $name, $property ) {
        $this->pluginName = $pluginName;
        $this->name = $name;
        $this->property = $property;
    }

    /**
     * 
     * @param \local_enrolmultiselect\search $search
     * @param type $toggledItemsToInclude
     * @param type $toggledItemsToExclude
     * @return boolean
     */
    public function getConfig( search $search = null, $toggledItemsToInclude = array(), $toggledItemsToExclude = array() ){
        $configValue = get_config( $this->pluginName, $this->name );
        
        return self::processConfig( $configValue, $this->property, $search, $toggledItemsToInclude, $toggledItemsToExclude );
        /*if(!$configValue)
            return false;

        $configArrayMap = utils::JsonToArray( $configValue );

        //temporarily bind items to the config
        if( $toggledItemsToInclude ){
            $valuesToBindToConfig = $this->buildConfigValues( $toggledItemsToInclude );
            
            //filter stored config to avoid duplicates
            $configArrayMap = array_filter( $configArrayMap, function ($valueMap) use( $toggledItemsToInclude ){
                    return !in_array( $valueMap[ $this->property ], $toggledItemsToInclude );
                } 
            );

            $configArrayMap = array_merge( $configArrayMap, $valuesToBindToConfig );
        }
        
        $configMap = utils::arrayToObject( $configArrayMap );
        
        if( $search || $toggledItemsToExclude ){
            $configArray = [];
            
            foreach( $configMap as $key=>$config){

                if( $toggledItemsToExclude && in_array( $config->{$this->property}, $toggledItemsToExclude ) ){
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
        
        return $configMap;*/
    }
    
    /**
     * 
     * @param type $property
     * @param type $forQuery
     * @param type $toggledItemsToInclude
     * @return boolean|string
     */
    public function getFlatConfigByProperty($property = null, $forQuery = false, $toggledItemsToInclude = array()){
        $configMap = $this->getConfig( null, $toggledItemsToInclude );
        
        if(!$configMap)
            return false;
        
        $results = [];
        
        $property = !is_null( $property ) ? $property : $this->property;

        foreach( $configMap as $key => $config){
            $results[] = ($forQuery ? "\"" : "").$config->{$property}.($forQuery ? "\"": "");
        }
        
        return $results;
    }

    /**
     * 
     * @param array $values
     * @return array
     */
    private function buildConfigValues( array $values ){

        /*if( !$values )
            return false;

        $configMap = [];

        foreach( $values as $value ){
            $configMap[] = [
                'id' => $value,
                $this->property => $value 
            ];
        }

        return $configMap;*/
        return self::constructConfigValues($this->property, $values);
    }
    
    /**
     * 
     * @param array $values
     * @return boolean
     */
    public function setConfig( array $values ){

        $configMap = $this->buildConfigValues( $values );
        
        if( !$configMap )
            return false;

        return set_config( $this->name, json_encode( $configMap ), $this->pluginName );
    }
    
    public function removeConfig(){
        return set_config( $this->name, null, $this->pluginName );
    }
}