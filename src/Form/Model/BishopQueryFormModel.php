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

    public function __construct($n, $p = "", $o = "", $y = "", $id = "", $fpl = array()) {
        $this->name = $n;
        $this->place = $p;
        $this->office = $o;
        $this->year = $y;
        $this->someid = $id;
        $this->facetPlaces = $fpl;
    }

    public function isEmpty() {
        return (!$this->name and !$this->place and !$this->office and !$this->year and !$this->someid);
    }

}
