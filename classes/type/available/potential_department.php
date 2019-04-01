<?php
namespace local_enrolmultiselect\type\available;

use \local_enrolmultiselect\type\settings\department as settingsdepartment;
use \local_enrolmultiselect\config;
use \local_enrolmultiselect\search;
use \local_enrolmultiselect\traits\instance;

class potential_department extends settingsdepartment{
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

        $options['file'] = 'local/enrolmultiselect/classes/type/available/potential_department.php';
        $options['plugin'] =  $this->plugin;
        $options['enrol_instance'] = $this->enrolInstance;
        return $options;
    }
    
    /**
     * 
     * @param string $search
     */
    public function find_users( $search ) {
        return $this->presentPotentialValues( $search );
    }
}