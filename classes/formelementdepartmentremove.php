<?php

use local_enrolmultiselect\type\potential_department;

class local_enrolmultiselect_formelementdepartmentremove extends local_enrolmultiselect_element {
    
    /**
     *
     * @var potential_department
     */
    private $potentialDepartment;
    
    /**
     * 
     * @param type $elementName
     * @param type $elementLabel
     * @param type $options
     * @param type $attributes
     */
    public function __construct($elementName=null, $elementLabel=null, $options=null, $attributes=null, potential_department $potentialDepartment) {
        $this->potentialDepartment = $potentialDepartment;
        parent::__construct($this->potentialDepartment->name, $elementLabel, $attributes);
        $this->setMultiple(true);
    }

    public function toHtml()
    {
        global $OUTPUT;

        $availableDesignations = $this->potentialDepartment->display(true);
        $label = get_string('availabledepartments', $this->plugin);

$html = <<<__HTML__
    <td id="potentialcell">
        <p>
          <label for="addselect">$label</label>
        </p>
        $availableDesignations
    </td>
  </tr>
</table>

__HTML__;

        return $html;
        
    }
}
?>
