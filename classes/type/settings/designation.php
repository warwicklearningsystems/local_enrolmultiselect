<?php
namespace local_enrolmultiselect\type\settings;

use \local_enrolmultiselect\base;
use \local_enrolmultiselect\config;
use \local_enrolmultiselect\utils;
use \local_enrolmultiselect\search;
use \local_enrolmultiselect\type\basedesignation;
use \local_enrolmultiselect\togglestore;

class designation extends basedesignation{
    
    protected $nameInverse;
    /**
     * 
     * @global type $CFG
     * @param string $name
     * @param array $options
     */
    public function __construct($name, $options) {
        parent::__construct($name, $options);
        $this->nameInverse = "";
    }

    protected function get_options() {
        global $CFG;
        $options = parent::get_options();

        $options['file'] = 'local/enrolmultiselect/classes/type/settings/designation.php';
        $options['plugin'] =  $this->plugin;
        $options['name_inverse'] =  $this->nameInverse;
        return $options;
    }

    /**
     * 
     * @global type $DB
     * @param string $search
     * @return type
     */
    public function find_users($search) {
        global $DB;

        $toogledItemsToInclude = togglestore::get(
            $this->getHash(), 
            togglestore::TOGGLE_ITEMS_TO_INCLUDE_MAP_NAME
        );
        
        $toggledItemsToExclude = togglestore::get(
            $this->getHash(), 
            togglestore::TOGGLE_ITEMS_TO_EXCLUDE_MAP_NAME
        );

        if( !$search ){
            $designationObjectMap = $this->config->getConfig( null, $toogledItemsToInclude, $toggledItemsToExclude );

            // No designations at all.
            if(!$designationObjectMap)
                return array();
        }else{
            $searchObject = new search( $search, $this->propertyFromConfigToDisplay, $this->searchanywhere );
            $designationObjectMap = $this->config->getConfig( $searchObject, $toogledItemsToInclude, $toggledItemsToExclude );
        }

        $results = array(); // The results array we are building up.

        foreach ( $designationObjectMap as $key => $designationObject ) {
            $group = $this->getGroupName( $designationObject );
            $results[ $group ][] = $designationObject;
        }

        return $results;
    }
}