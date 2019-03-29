<?php
namespace local_enrolmultiselect\type\available;

use \local_enrolmultiselect\type\settings\designation as settingsdesignation;
use \local_enrolmultiselect\config;
use \local_enrolmultiselect\search;
use \local_enrolmultiselect\type\basedesignation;
use \local_enrolmultiselect\traits\instance;

class potential_designation extends settingsdesignation{
    use instance;
    
    protected $enrolInstance;
    protected $field;
    
    public function __construct($name, $options) {
        
        $this->enrolInstance = $options['enrol_instance'];
        parent::__construct($name, $options);
    }

    protected function get_options() {
        global $CFG;
        $options = parent::get_options();

        $options['file'] = 'local/enrolmultiselect/classes/type/available/potential_designation.php';
        $options['plugin'] =  $this->plugin;
        $options['enrol_instance'] = $this->enrolInstance;
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
        
        $searchObject = new search( $search, $this->propertyFromConfigToDisplay, $this->searchanywhere );
        $availableDesignations = parent::find_users( $search );

        $results = $this->filterStoredValues( $availableDesignations, $searchObject, $this->field );

        return is_array( $results ) ? $results : $availableDesignations;
    }
}