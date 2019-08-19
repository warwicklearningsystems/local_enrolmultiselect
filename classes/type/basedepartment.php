<?php

namespace local_enrolmultiselect\type;

use \local_enrolmultiselect\config;
use \local_enrolmultiselect\base;

abstract class basedepartment extends base{

    const DEFAULT_GROUP = 'Departments';//'Other';

    const GROUPS = [];//['Centre', 'School', 'Warwick', 'WMS', 'Studies', 'Institute', 'Office', 'Service'];

    /**
     *
     * @var string 
     */
    protected $propertyFromConfigToDisplay = 'department';

    /**
     *
     * @var config 
     */
    protected $config;
    
    /**
     *
     * @var string 
     */
    protected $configName = 'departments';

    public function __construct( $name, $options ) {
        global $CFG;
        
        require_once($CFG->dirroot . '/lib/accesslib.php');
        
        $this->config = new config( $options['plugin'], $this->configName, $this->propertyFromConfigToDisplay );
        
        parent::__construct($name, $options);
    }

    public function getGroups() {
        return self::GROUPS;
    }
    
    public function getDefaultGroup() {
        return self::DEFAULT_GROUP;
    }
}

