<?php

use local_enrolmultiselect\type\basedesignation;

class local_enrolmultiselect_formelementdesignationadd extends local_enrolmultiselect_element {
    
    /**
     *
     * @var basedesignation
     */
    private $designation;
    
    /**
     * 
     * @param type $elementName
     * @param type $elementLabel
     * @param type $options
     * @param type $attributes
     * @param designation $designation
     */
    public function __construct($elementName=null, $elementLabel=null, $options=null, $attributes=null, basedesignation $designation ) {

        $this->designation = $designation;
        parent::__construct($this->designation->name, $elementLabel, $attributes);
        $this->setMultiple(true);
    }

    public function toHtml()
    {
        global $OUTPUT;

        $allowedDesignations = $this->designation->display(true);
        $label = get_string('alloweddesignations', 'local_enrolmultiselect');
        $toggleButtons = $this->designation->renderToggleButtons( $this->designation->name, true );

$html = <<<__HTML__
<table class="generaltable generalbox groupmanagementtable boxaligncenter" summary="">
    <tr>
        <td id='existingcell'>
            <p>
                <label class="multiselect-label" for="removeselect">$label</label>
            </p>
            $allowedDesignations
        </td>
        <td id="buttonscell">
            $toggleButtons
        </td>
__HTML__;

        return $html;
        
    }
}
?>
