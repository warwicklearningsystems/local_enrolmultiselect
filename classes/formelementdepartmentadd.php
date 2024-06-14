<?php
use local_enrolmultiselect\type\basedepartment;

class local_enrolmultiselect_formelementdepartmentadd extends local_enrolmultiselect_element {

    /**
     *
     * @var basedepartment
     */
    private $department;    
    
    // public function __construct($elementName=null, $elementLabel=null, $options=null, $attributes=null, basedepartment $department ) {
    public function __construct(basedepartment $department,$elementName=null, $elementLabel=null, $options=null, $attributes=null) {

        $this->department = $department;
        parent::__construct( $this->department->name, $elementLabel, $attributes );
    }

    public function toHtml()
    {
        global $OUTPUT;
        
        $allowedDepartments = $this->department->display(true);
        $label = get_string('alloweddepartments', 'local_enrolmultiselect');
        $toggleButtons = $this->department->renderToggleButtons( $this->department->name, true );

$html = <<<__HTML__
<table class="generaltable generalbox groupmanagementtable boxaligncenter" summary="">
    <tr>
        <td id='existingcell'>
            <p>
                <label class="multiselect-label" for="removeselect">$label</label>
            </p>
            $allowedDepartments
        </td>
        <td id="buttonscell">
            $toggleButtons
        </td>
__HTML__;

        return $html;
        
    }
    
}
?>
