<?php
use local_enrolmultiselect\type\department;

class local_enrolmultiselect_formelementdepartmentadd extends local_enrolmultiselect_element {

    /**
     *
     * @var department
     */
    private $department;    
    
    public function __construct($elementName=null, $elementLabel=null, $options=null, $attributes=null, department $department ) {
        
        $this->department = $department;
        parent::__construct( $department->name, $elementLabel, $attributes, $this->department );
    }

    public function toHtml()
    {
        global $OUTPUT;
        
        $allowedDepartments = $this->department->display(true);
        
        $leftArrow = $OUTPUT->larrow();
        $rightArrow = $OUTPUT->rarrow();
        $addText = get_string('add');
        $removeText = get_string('remove');
        $label = get_string('alloweddepartments', $this->department->plugin);

$html = <<<__HTML__
<table class="generaltable generalbox groupmanagementtable boxaligncenter" summary="">
    <tr>
      <td id='existingcell'>
          <p>
            <label for="removeselect">$label</label>
          </p>
          $allowedDepartments
          </td>
      <td id="buttonscell">
        <p class="arrow_button">
            <input name="departments_add_button" id="departments_add_button" type="submit" value="$leftArrow&nbsp;$addText"
                   title="$addText" class="btn btn-secondary"/><br />
            <input name="departments_remove_button" id="departments_remove_button" type="submit" value="$rightArrow&nbsp;$removeText"
                   title="$removeText" class="btn btn-secondary"/><br />
        </p>
      </td>
__HTML__;

        return $html;
        
    }
    
}
?>
