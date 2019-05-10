<?php

use local_enrolmultiselect\type\basedesignation;

class local_enrolmultiselect_formelementdesignationremove extends local_enrolmultiselect_element {
    
    /**
     *
     * @var basedesignation 
     */
    private $potentialDesignation;
    
    /**
     * 
     * @param type $elementName
     * @param type $elementLabel
     * @param type $options
     * @param type $attributes
     * @param potential_designation $potentialDesignation
     */
    public function __construct($elementName=null, $elementLabel=null, $options=null, $attributes=null, basedesignation $potentialDesignation) {
        $this->potentialDesignation = $potentialDesignation;
        parent::__construct($this->potentialDesignation->name, $elementLabel, $attributes);
        $this->setMultiple(true);
    }

    public function toHtml()
    {
        global $OUTPUT;

        $availableDesignations = $this->potentialDesignation->display(true);
        $label = get_string('availabledesignations', 'local_enrolmultiselect');

$html = <<<__HTML__
    <td id="potentialcell">
        <p>
          <label class="multiselect-label" for="addselect">$label</label>
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
