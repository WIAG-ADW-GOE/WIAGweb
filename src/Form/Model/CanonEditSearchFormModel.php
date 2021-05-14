<?php
namespace App\Form\Model;

use App\Entity\PlaceCount;
use App\Entity\OfficeCount;
use Symfony\Component\HttpFoundation\Request;

class CanonEditSearchFormModel {
    public $name;
    public $monastery;
    public $office;
    public $place;
    public $year;
    public $someid;
    public $reference;    
    public $filterStatus;

    public function __construct($n = null,
                                $m = null,
                                $o = null,
                                $p = null,
                                $y = null,
                                $id = null,
                                $filterStatus = array()) {
        $this->name = $n;
        $this->monastery = $m;
        $this->office = $o;
        $this->place = $p;
        $this->year = $y;
        $this->someid = $id;
        $this->filterStatus = $filterStatus;
    }


}
