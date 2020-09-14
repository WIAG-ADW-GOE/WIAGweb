<?php
namespace App\Form\Model;

class BishopQueryFormModel {
    /* Validation: not all fields altogether should be empty */
    public $name;
    public $place;
    public $office;
    public $year;
    public $someid;
    public $facetPlaces;
    public $facetOffices;

    public function __construct($n = "",
                                $p = "",
                                $o = "",
                                $y = null,
                                $id = "",
                                $fpl = array(),
                                $fof = array()) {
        $this->name = $n;
        $this->place = $p;
        $this->office = $o;
        $this->year = $y;
        $this->someid = $id;
        $this->facetPlaces = $fpl;
        $this->facetOffices = $fof;
    }

    public function isEmpty() {
        return (!$this->name and !$this->place and !$this->office and !$this->year and !$this->someid);
    }

    public function setFacetPlaces($fpl) {
        $this->facetPlaces = $fpl;
        return $this;
    }

    public function setFacetOffices($fof) {
        $this->facetOffices = $fof;
        return $this;
    }

}
