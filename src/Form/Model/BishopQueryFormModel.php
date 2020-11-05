<?php
namespace App\Form\Model;

use App\Entity\Person;
use App\Entity\PlaceCount;
use App\Entity\OfficeCount;
use Symfony\Component\HttpFoundation\Request;

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

    public function toArraySansFacets() {
        $qelts = array();
        if($this->name) $qelts['name'] = $this->name;
        if($this->place) $qelts['place'] = $this->place;
        if($this->office) $qelts['office'] = $this->office;
        if($this->year) $qelts['year'] = $this->year;
        if($this->someid) $qelts['someid'] = $this->someid;

        return $qelts;
    }

    public function toArray() {
        $qelts = $this->toArraySansFacets();

        if($this->facetPlaces) {
            $fpsc = array_map(function($el) { return $el->getName(); },
                              $this->facetPlaces);
            $fpsstr = implode(',', $fpsc);
            $qelts['facetPlaces'] = $fpsstr;
        }

        if($this->facetOffices) {
            $fosc = array_map(function($el) { return $el->getName(); },
                              $this->facetOffices);
            $fosstr = implode(',', $fosc);
            $qelts['facetOffices'] = $fosstr;
        }

        return $qelts;
    }

    public function setByRequest(Request $request) {
        $query = $request->query;
        $this->name = $query->get('name');
        $this->place = $query->get('place');
        $this->office = $query->get('office');
        $this->year = $query->get('year');
        $this->someid = $query->get('someid');

        $fpsstr = $query->get('facetPlaces');

        if($fpsstr) {
            $fpsoc = array();
            $fpsc = explode(',', $fpsstr);
            foreach($fpsc as $el) {
                $fpsoc[] = new PlaceCount($el, '1');
            }
            $this->facetPlaces = $fpsoc;
        }

        $fosstr = $query->get('facetOffices');

        if($fosstr) {
            $fosoc = array();
            $fosc = explode(',', $fosstr);
            foreach($fosc as $el) {
                $fosoc[] = new PlaceCount($el, '1');
            }
            $this->facetOffices = $fosoc;
        }


        return $this;
    }

}
