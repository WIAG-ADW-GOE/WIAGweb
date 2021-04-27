<?php
namespace App\Form\Model;

use App\Entity\Person;
use App\Entity\PlaceCount;
use App\Entity\OfficeCount;
use Symfony\Component\HttpFoundation\Request;

class CanonFormModel {
    public $name;
    public $monastery;
    public $office;
    public $place;
    public $year;
    public $someid;
    public $facetLocations;
    public $facetMonasteries;
    public $facetOffices;
    public $showAll;
    public $stateFctLoc;
    public $stateFctMon;
    public $stateFctOfc;

    public function __construct($n = null,
                                $m = null,
                                $o = null,
                                $p = null,                                
                                $y = null,
                                $id = null,
                                $flc = array(),
                                $fpl = array(),
                                $fof = array(),
                                $showAll = null,
                                $stateFctLoc = "0",
                                $stateFctMon = "0",
                                $stateFctOfc = "0") {
        $this->name = $n;
        $this->monastery = $m;
        $this->office = $o;
        $this->place = $p;                
        $this->year = $y;
        $this->someid = $id;
        $this->facetLocations = $flc;
        $this->facetMonasteries = $fpl;
        $this->facetOffices = $fof;
        $this->showAll = $showAll;
        $this->stateFctLoc = $stateFctLoc;
        $this->stateFctMon = $stateFctMon;
        $this->stateFctOfc = $stateFctOfc;
    }

    public function isEmpty() {
        return (!$this->name and
                !$this->monastery and
                !$this->office and
                !$this->place and
                !$this->year and
                !$this->someid and
                !$this->showAll);
    }

    public function getFacetLocations() {
        return $this->facetLocations;
    }

    public function setFacetLocations($fpl) {
        $this->facetLocations = $fpl;
        return $this;
    }


    public function getFacetMonasteries() {
        return $this->facetMonasteries;
    }

    public function setFacetMonasteries($fpl) {
        $this->facetMonasteries = $fpl;
        return $this;
    }

    public function setFacetOffices($fof) {
        $this->facetOffices = $fof;
        return $this;
    }

    public function setFieldsByArray(array $a) {
        $this->name = $a['name'];
        $this->monastery = $a['monastery'];
        $this->place = $a['place'];
        $this->office = $a['office'];
        $this->year = $a['year'];
        $this->someid = $a['someid'];
        $this->showAll = $a['showAll'];
        $this->stateFctLoc = $a['stateFctLoc'];
        $this->stateFctMon = $a['stateFctMon'];
        $this->stateFctOfc = $a['stateFctOfc'];


        if (array_key_exists('facetLocations', $a)) {
            $facetLocations = array();
            foreach($a['facetLocations'] as $flc) {
                // we have no count data at this point
                $facetLocations[] = new PlaceCount($flc, '',  0);
            }
            $this->facetLocations = $facetLocations;
        }

        if (array_key_exists('facetMonasteries', $a)) {
            $facetMonasteries = array();
            foreach($a['facetMonasteries'] as $fpl) {
                // we have no count data at this point
                $facetMonasteries[] = new PlaceCount($fpl, '',  0);
            }
            $this->facetMonasteries = $facetMonasteries;
        }

        if (array_key_exists('facetOffices', $a)) {
            $facetOffices = array();
            foreach($a['facetOffices'] as $foc) {
                // we have no count data at this point
                $facetOffices[] = new OfficeCount($foc, 0);
            }
            $this->facetOffices = $facetOffices;
        }

        return $this;
    }

    public function getFacetMonasteriesAsArray(): array {
        return array_map(function($el) {return $el->getName();},
                         $this->facetMonasteries);
    }

    public function getFacetOfficesAsArray(): array {
        return array_map(function($el) {return $el->getName();},
                         $this->facetOffices);
    }

    public function toArraySansFacets() {
        $qelts = array();
        if($this->name) $qelts['name'] = $this->name;
        if($this->monastery) $qelts['monastery'] = $this->monastery;
        if($this->office) $qelts['office'] = $this->office;
        if($this->place) $qelts['place'] = $this->place;
        if($this->year) $qelts['year'] = $this->year;
        if($this->someid) $qelts['someid'] = $this->someid;
        if($this->stateFctLoc) $qelts['stateFctLoc'] = $this->stateFctLoc;
        if($this->stateFctMon) $qelts['stateFctMon'] = $this->stateFctMon;
        if($this->stateFctOfc) $qelts['stateFctOfc'] = $this->stateFctOfc;

        return $qelts;
    }

    public function toArray() {
        $qelts = $this->toArraySansFacets();

        if($this->facetMonasteries) {
            $fpsc = array_map(function($el) { return $el->getName(); },
                              $this->facetMonasteries);
            $fpsstr = implode(',', $fpsc);
            $qelts['facetMonasteries'] = $fpsstr;
        }

        if($this->facetOffices) {
            $fosc = array_map(function($el) { return $el->getName(); },
                              $this->facetOffices);
            $fosstr = implode(',', $fosc);
            $qelts['facetOffices'] = $fosstr;
        }

        return $qelts;
    }

    // 2021-04-12 obsolete?
    // public function setByRequest(Request $request) {
    //     $query = $request->query;
    //     $this->name = $query->get('name');
    //     $this->place = $query->get('place');
    //     $this->office = $query->get('office');
    //     $this->year = $query->get('year');
    //     $this->someid = $query->get('someid');
    //     $this->stateFctLoc = $query->get('stateFctLoc');

    //     $fpsstr = $query->get('facetMonasteries');

    //     if($fpsstr) {
    //         $fpsoc = array();
    //         $fpsc = explode(',', $fpsstr);
    //         foreach($fpsc as $el) {
    //             $fpsoc[] = new PlaceCount($el, '1');
    //         }
    //         $this->facetMonasteries = $fpsoc;
    //     }

    //     $fosstr = $query->get('facetOffices');

    //     if($fosstr) {
    //         $fosoc = array();
    //         $fosc = explode(',', $fosstr);
    //         foreach($fosc as $el) {
    //             $fosoc[] = new PlaceCount($el, '1');
    //         }
    //         $this->facetOffices = $fosoc;
    //     }


    //     return $this;
    // }

    /**
     * strip 'Bistum' or 'Erzbistum' from search field.
     */
    public function normPlace() {
        $place = $this->place;
        foreach(['bistum', 'erzbistum', 'Bistum', 'Erzbistum'] as $bs) {
            if(!is_null($place) && str_starts_with($place, $bs)) {
                $this->place = trim(str_replace($bs, "", $place));
                return null;
            }
        }
        return null;
    }

    public function getStateFctLoc() {
        return $this->stateFctLoc;
    }

}
