<?php
namespace local_enrolmultiselect\type\available;

use \local_enrolmultiselect\base;
use \local_enrolmultiselect\config;
use \local_enrolmultiselect\utils;
use \local_enrolmultiselect\search;
use \local_enrolmultiselect\type\basedepartment;
use \local_enrolmultiselect\traits\instance;


class department extends basedepartment{
    
    use instance;
    
    /**
     *
     * @var type 
     */
    protected $enrolInstance;
    protected $field;


    /**
     * 
     * @global type $CFG
     * @param string $name
     * @param array $options
     */
    public function __construct($name, $options) {
        $this->enrolInstance = $options['enrol_instance'];
        parent::__construct($name, $options);
    }

    protected function get_options() {
        global $CFG;
        $options = parent::get_options();

        $options['file'] = 'local/enrolmultiselect/classes/type/available/department.php';
        $options['plugin'] =  $this->plugin;
        $options['enrol_instance'] = $this->enrolInstance;
        return $options;
    }
    
    /**
     * 
     * @param string $search
     * @return type
     */
    public function find_users($search) {
        return $this->presentStoredValues( $search );
    }
}