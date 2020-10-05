<?php
namespace App\Entity;

class OfficeCount {
    public $name;
    public $count;

    public function __construct($n, $c) {
        $this->name = $n;
        $this->count = $c;
    }

    public function getLabel(): string {
        return $this->name.' ('.$this->count.')';
    }

    public static function find($name, array $a) {
        foreach($a as $oc) {
            if ($name == $oc->name) return true;
        }
        return false;
    }

}
