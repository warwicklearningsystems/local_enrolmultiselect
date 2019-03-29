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
        
        $leftArrow = $OUTPUT->larrow();
        $rightArrow = $OUTPUT->rarrow();
        $addText = get_string('add');
        $removeText = get_string('remove');
        $label = get_string('alloweddesignations', 'local_enrolmultiselect');

$html = <<<__HTML__
<table class="generaltable generalbox groupmanagementtable boxaligncenter" summary="">
    <tr>
      <td id='existingcell'>
          <p>
            <label for="removeselect">$label</label>
          </p>
          $allowedDesignations
          </td>
      <td id="buttonscell">
        <p class="arrow_button">
            <input name="designations_add_button" id="designations_add_button" type="submit" value="$leftArrow&nbsp;$addText"
                   title="$addText" class="btn btn-secondary"/><br />
            <input name="designations_remove_button" id="designations_remove_button" type="submit" value="$rightArrow&nbsp;$removeText"
                   title="$removeText" class="btn btn-secondary"/><br />
        </p>
      </td>
__HTML__;

        return $html;
        
    }
}
?>
