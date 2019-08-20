<?php
namespace local_enrolmultiselect\type\settings;

use \local_enrolmultiselect\type\basedesignation;
use \local_enrolmultiselect\togglestore;

class potential_designation extends basedesignation{
    
    public function __construct($name, $options) {
        parent::__construct($name, $options);
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

        $existingDesignations = $this->config->getFlatConfigByProperty( null, true, $toggledItemsToExclude );
        
        if( $toogledItemsToInclude ){
            $existingDesignations = array_filter( $existingDesignations, function( $val ) use ( $toogledItemsToInclude ){
                    $val = str_replace('"', '', $val);
                    return !in_array( $val, $toogledItemsToInclude );
                }
            );
        }
        
        list($wherecondition, $params) = $this->search_sql($search, 'u');

        $sql = "SELECT distinct($this->propertyFromConfigToDisplay) FROM {user} u WHERE {$this->propertyFromConfigToDisplay} is not null AND {$this->propertyFromConfigToDisplay} not like \"\"";        

        if( $existingDesignations )
            $sql.=" AND $this->propertyFromConfigToDisplay not in (".implode(",",$existingDesignations).")";

        if($search)
            $sql.= " AND ".$wherecondition;
        
        $designations = $DB->get_records_sql( $sql ." ORDER BY $this->propertyFromConfigToDisplay ASC", $params );
        
        $results = array(); // The results array we are building up.
        foreach ($designations as $key=>$designation) {
        
            $group = $this->getGroupName( $designation );
            $designation->id = $designation->{$this->propertyFromConfigToDisplay};
            $results[ $group ][] = $designation ;
        }

        return $results;
    }

    protected function get_options() {
        global $CFG;
        $options = parent::get_options();

        $options['file'] = 'local/enrolmultiselect/classes/type/settings/potential_designation.php';
        $options['plugin'] =  $this->plugin;
        return $options;
    }
}

