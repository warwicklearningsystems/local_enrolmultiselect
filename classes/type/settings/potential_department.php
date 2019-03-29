<?php
namespace local_enrolmultiselect\type\settings;

use \local_enrolmultiselect\base;
use \local_enrolmultiselect\config;
use \local_enrolmultiselect\utils;
use \local_enrolmultiselect\search;
use \local_enrolmultiselect\type\basedepartment;

class potential_department extends basedepartment{
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

        $options['file'] = 'local/enrolmultiselect/classes/type/settings/potential_department.php';
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
        $existingDesignations = $this->config->getFlatConfigByProperty( null, true );
        
        list($wherecondition, $params) = $this->search_sql($search, 'u');

        $sql = "SELECT distinct($this->propertyFromConfigToDisplay) FROM {user} u WHERE {$this->propertyFromConfigToDisplay} is not null AND {$this->propertyFromConfigToDisplay} not like \"\"";        

        if( $existingDesignations )
            $sql.=" AND $this->propertyFromConfigToDisplay not in (".implode(",",$existingDesignations).")";

        if($search)
            $sql.= " AND ".$wherecondition;
        
        $designations = $DB->get_records_sql( $sql, $params );
        
        $results = array(); // The results array we are building up.
        foreach ($designations as $key=>$designation) {
        
            $group = $this->getGroupName( $designation );
            $designation->id = $designation->{$this->propertyFromConfigToDisplay};
            $results[ $group ][] = $designation ;
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

