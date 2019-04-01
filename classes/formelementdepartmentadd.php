<?php
use local_enrolmultiselect\type\basedepartment;

class local_enrolmultiselect_formelementdepartmentadd extends local_enrolmultiselect_element {

    /**
     *
     * @var basedepartment
     */
    private $department;    
    
    public function __construct($elementName=null, $elementLabel=null, $options=null, $attributes=null, basedepartment $department ) {
        
        $this->department = $department;
        parent::__construct( $this->department->name, $elementLabel, $attributes );
    }

    public function toHtml()
    {
        global $OUTPUT;
        
        $allowedDepartments = $this->department->display(true);
        
        $leftArrow = $OUTPUT->larrow();
        $rightArrow = $OUTPUT->rarrow();
        $addText = get_string('add');
        $removeText = get_string('remove');
        $label = get_string('alloweddepartments', 'local_enrolmultiselect');

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
