<?php

namespace local_enrolmultiselect\type;

use \local_enrolmultiselect\config;
use \local_enrolmultiselect\base;

abstract class basedesignation extends base{

    const DEFAULT_GROUP = 'Other';
    
    const GROUPS = ['User', 'Academic', 'Partner', 'Staff'];
    
    /**
     *
     * @var string 
     */
    protected $propertyFromConfigToDisplay = 'phone2';

    /**
     *
     * @var config 
     */
    protected $config;
    
    /**
     *
     * @var string 
     */
    protected $configName = 'designations';

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

