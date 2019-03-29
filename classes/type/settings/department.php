<?php
namespace local_enrolmultiselect\type\settings;

use \local_enrolmultiselect\base;
use \local_enrolmultiselect\config;
use \local_enrolmultiselect\utils;
use \local_enrolmultiselect\search;
use \local_enrolmultiselect\type\basedepartment; 

class department extends basedepartment{        
    /**
     * 
     * @global type $CFG
     * @param string $name
     * @param array $options
     */
    public function __construct($name, $options) {
        parent::__construct($name, $options);
    }

    protected function get_options() {
        global $CFG;
        $options = parent::get_options();

        $options['file'] = 'local/enrolmultiselect/classes/type/settings/department.php';
        $options['plugin'] =  $this->plugin;
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

        if( !$search ){
            $designationObjectMap = $this->config->getConfig();

            // No designations at all.
            if(!$designationObjectMap)
                return array();
        }else{
            $searchObject = new search( $search, $this->propertyFromConfigToDisplay, $this->searchanywhere );
            $designationObjectMap = $this->config->getConfig( $searchObject );
        }

        $results = array(); // The results array we are building up.

        foreach ( $designationObjectMap as $key => $designationObject ) {
            $group = $this->getGroupName( $designationObject );
            $results[ $group ][] = $designationObject;
        }

        return $results;
    }
    
    public function getGroups() {
        return self::GROUPS;
    }
    
    public function getDefaultGroup() {
        return self::DEFAULT_GROUP;
    }
}