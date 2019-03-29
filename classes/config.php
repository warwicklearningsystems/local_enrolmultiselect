<?php

namespace local_enrolmultiselect;

class config{
    
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
     * @param search $search
     * @return type
     */
    public function getConfig( search $search = null ){
        $configValue = get_config( $this->pluginName, $this->name );
        if(!$configValue)
            return false;
        $configMap = utils::arrayToObject( utils::JsonToArray( $configValue ) );
        
        if( $search ){
            $configArray = [];
            
            foreach( $configMap as $key=>$config){
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
        
        return $configMap;
    }
    
    /**
     * 
     * @param string $property
     * @return array
     */
    public function getFlatConfigByProperty($property = null, $forQuery = false){
        $configMap = $this->getConfig();
        
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
     * @return boolean
     */
    public function setConfig( array $values ){

        if( !$values )
            return false;
        
        $configMap = [];
        
        foreach( $values as $value ){
            $configMap[] = [
                'id' => $value,
                $this->property => $value 
            ];
        }

        return set_config( $this->name, json_encode( $configMap ), $this->pluginName );
    }
}