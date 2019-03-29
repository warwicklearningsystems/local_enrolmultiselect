<?php
namespace local_enrolmultiselect;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class search{
    
    /**
     *
     * @var string the string to find
     */
    private $stringToFind;
    
    /**
     *
     * @var string the field to search for 
     */
    private $field;
    
    /**
     *
     * @var string whether to search the whole string or not
     */
    private $searchAnyWhere;
    
    /**
     * 
     * @param type $stringToFind
     * @param type $field
     * @param type $searchAnyWhere
     */
    public function __construct( $stringToFind, $field, $searchAnyWhere ) {
        $this->stringToFind = $stringToFind;
        $this->field = $field;
        $this->searchAnyWhere = $searchAnyWhere;
    }
    
    public function getStringToFind(){
        return $this->stringToFind;
    }

    public function getField(){
        return $this->field;
    }
    
    public function getSearchAnyWhere(){
        return $this->searchAnyWhere;
    }
}