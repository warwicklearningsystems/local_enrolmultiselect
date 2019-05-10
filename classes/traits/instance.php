<?php
namespace local_enrolmultiselect\traits;

use \local_enrolmultiselect\utils;
use \local_enrolmultiselect\search;
use \local_enrolmultiselect\togglestore;
use \local_enrolmultiselect\traits\config as configtrait;

trait instance{
    use configtrait;
    
    protected $field;

    /**
     * 
     * @param type $enrolInstance
     * @param search $search
     * @param type $field
     * @return boolean
     */
    public static function extractFlatConfig( $enrolInstance, search $search = null, $field = 'customtext1', $toggledItemsToInclude = array(), $toggledItemsToExclude = array() ){

        if( is_null( $enrolInstance->{$field} ) && !$toggledItemsToInclude )
            return false;
        
        $classVars = get_class_vars( __CLASS__ );
        $property = $classVars[ 'propertyFromConfigToDisplay' ];
        
        //accomodate for cases where there's no config but items have been added in UI, allows searching of these items to work
        if( !is_null( $enrolInstance->{$field} ) ){
            $row = $enrolInstance->{$field};
        }else{
            $row = json_encode( self::constructConfigValues( $property, $toggledItemsToInclude ) );
        }
        
        return self::processConfig( $row, $property, $search, $toggledItemsToInclude, $toggledItemsToExclude );
    }

    /**
     * 
     * @param type $availableDesignations
     * @param search $search
     * @param type $field
     * @return boolean
     */
    protected function filterStoredValues( $availableDesignations, search $search = null, $field, $toggledItemsToInclude = array(), $toggledItemsToExclude = array()  ){

        $storedDesignations = self::extractFlatConfig( $this->enrolInstance, $search, $field);
        
        if( !$storedDesignations )
            return false;
        
        foreach( $storedDesignations as $storedDesignationkey => $storedDesignationObject ){
            foreach( $availableDesignations as $avilableDesignationGroupName => $avilableDesignationGroupMap ){
        
                foreach( $avilableDesignationGroupMap as $avilableDesignationKey => $avilableDesignationObject ){
                
                    $availableDesignation = $avilableDesignationObject->{$this->propertyFromConfigToDisplay};
                    $storedDesignation = $storedDesignationObject->{$this->propertyFromConfigToDisplay};
                    
                    if( ( $availableDesignation == $storedDesignation ) && (!$toggledItemsToInclude || ( $toggledItemsToInclude && ( !in_array($availableDesignation, $toggledItemsToInclude) )))){
                        unset($availableDesignations[ $avilableDesignationGroupName ] [$avilableDesignationKey ]);
                        
                        if( !count($availableDesignations[ $avilableDesignationGroupName ] ) ){ //if there are not more items left in this group, remove the group
                            unset( $availableDesignations[ $avilableDesignationGroupName ] );
                        }
                            
                    }
                }
            }
        }
        
        return $availableDesignations;
    }
    
    /**
     * 
     * @param type $instance
     * @param type $search
     * @param type $field
     * @param type $property
     * @return boolean
     */
    public static function hasValue( $instance, $search, $field, $property ){
        

        $storedValues = self::extractFlatConfig( $instance, null, $field );
        
        if( !$storedValues )
            return false;
        
        foreach( $storedValues as $storedValuekey => $storedValueObject ){
            if( $search == $storedValueObject->{$property}){
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 
     * @param type $instance
     * @param type $field
     * @return boolean
     */
    public static function isValueSet( $instance, $field ){
        if( is_null($instance->{$field}) )
            return false;
            
        return count( utils::JsonToArray( $instance->{$field} ) ) ? true : false;
    }
    
    public function getField(){
        return $this->field;
    }

    /**
     * 
     * @param array $designations
     * @return string
     */
    public function valuesToAdd($designations){

        $existingDesignations = [];

        if( !is_null( $this->enrolInstance->{$this->field} ) ){
            $configMap = self::extractFlatConfig( $this->enrolInstance, null, $this->field );
            foreach( $configMap as $key => $config){
                $existingDesignations[] = $config->{$this->propertyFromConfigToDisplay};
            }
        }

        $newValues = array_diff( $designations, $existingDesignations );
        $designationToAdd = array_merge( $existingDesignations, $newValues );

        return $this->buildConfigValues($designationToAdd);
    }
    
    /**
     * 
     * @param type $designations
     * @return type
     */
    public function valuesToRemove($designations){
    
        $existingDesignations = [];

        if( !is_null( $this->enrolInstance->{$this->field} ) ){
            $configMap = self::extractFlatConfig( $this->enrolInstance, null, $this->field );
            foreach( $configMap as $key => $config){
                $existingDesignations[] = $config->{$this->propertyFromConfigToDisplay};
            }
        }

        $designationsToRemove = array_diff( $existingDesignations, $designations );

        return $this->buildConfigValues( $designationsToRemove );
    }       
    
    /**
     * 
     * @param array $values
     * @return string
     */
    public function buildConfigValues( $values ){
        
        $configMap = [];
        foreach( $values as $value ){
            $configMap[] = [
                'id' => $value,
                $this->propertyFromConfigToDisplay => $value 
            ];
        }

        return json_encode( $configMap );
    }

    /**
     *
     * @param string $search
     */
    public function presentStoredValues( $search ){

        $toogledItemsToInclude = togglestore::get(
            $this->getHash(), 
            togglestore::TOGGLE_ITEMS_TO_INCLUDE_MAP_NAME
        );
        
        $toggledItemsToExclude = togglestore::get(
            $this->getHash(), 
            togglestore::TOGGLE_ITEMS_TO_EXCLUDE_MAP_NAME
        );

        $searchObject = new search( $search , $this->propertyFromConfigToDisplay, $this->searchanywhere );
        $configMap = self::extractFlatConfig( $this->enrolInstance, $search ? $searchObject : null, $this->field, $toogledItemsToInclude, $toggledItemsToExclude );

        if(!$configMap)
            return array();

        $results = array(); // The results array we are building up.
        foreach ($configMap as $key=>$config) {

            $group = $this->getGroupName( $config );
            $config->id = $config->{$this->propertyFromConfigToDisplay};
            $results[ $group ][] = $config ;
        }

        return $results;
    }

    /**
     *
     * @global type $DB
     * @param type $search
     * @return type
     */
    public function presentPotentialValues( $search ){

        global $DB;

        $searchObject = new search( $search, $this->propertyFromConfigToDisplay, $this->searchanywhere );
        $availableDesignations = parent::find_users( $search );

        $toogledItemsToInclude = togglestore::get(
            $this->getHash(), 
            togglestore::TOGGLE_ITEMS_TO_INCLUDE_MAP_NAME
        );
        
        $toggledItemsToExclude = togglestore::get(
            $this->getHash(), 
            togglestore::TOGGLE_ITEMS_TO_EXCLUDE_MAP_NAME
        );
        $results = $this->filterStoredValues( $availableDesignations, $searchObject, $this->field, $toogledItemsToInclude, $toggledItemsToExclude );

        return is_array( $results ) ? $results : $availableDesignations;
    }

    /**
     *
     * @global type $USER
     * @return boolean
     */
    public function userAllowed(){
        global $USER;

        if( self::isValueSet( $this->enrolInstance, $this->field ) ){
            if( !self::hasValue( $this->enrolInstance, $USER->{$this->propertyFromConfigToDisplay}, $this->field, $this->propertyFromConfigToDisplay ) ){
                return false;
            }
        }

        return true;
    }
}