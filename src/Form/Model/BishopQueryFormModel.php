<?php
namespace App\Form\Model;

class BishopQueryFormModel {
    /* Validation: not all fields altogether should be empty */
    public $name;
    public $place;
    public $year;
    public $someid;
    public $placefacet;

    public function __construct($n, $p, $y, $id) {
        $this->name = $n;
        $this->place = $p;
        $this->year = $y;
        $this->someid = $id;
        $this->placefacet = array();
    }

}
