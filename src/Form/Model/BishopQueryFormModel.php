<?php
namespace App\Form\Model;

use App\Entity\Person;

class BishopQueryFormModel {
    public $name;
    public $place;
    public $office;
    public $year;
    public $someid;
    public $facetPlaces;
    public $facetOffices;

    public function __construct($n = null,
                                $p = null,
                                $o = null,
                                $y = null,
                                $id = null,
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

    public function setTextFields(array $a) {
        $this->name = $a['name'];
        $this->place = $a['place'];
        $this->office = $a['office'];
        $this->year = $a['year'];
        $this->someid = $a['someid'];
        $this->facetPlaces = null;
        $this->facetOffices = null;
        return null;
    }

    public function copy(BishopQueryFormModel $a) {
        $this->name = $a->name;
        $this->place = $a->place;
        $this->office = $a->office;
        $this->year = $a->year;
        $this->someid = $a->someid;
        $this->facetPlaces = $a->facetPlaces;
        $this->facetOffices = $a->facetOffices;
        return null;
    }

    public function getQueryArray() {
        $qelts = array();
        if($this->name) $qelts['name'] = $this->name;
        if($this->place) $qelts['place'] = $this->place;
        if($this->office) $qelts['office'] = $this->office;
        if($this->year) $qelts['year'] = $this->year;
        if($this->someid) $qelts['someid'] = $this->someid;
        
        return $qelts;
    }

    public function updateSomeid() {
        $someid = $this->someid;

        if($someid and Person::isWiagidLong($someid)) {
            $this->someid = Person::wiagidLongToWiagid($someid);
        }
        dump($this->someid);
        return null;
    }



}
