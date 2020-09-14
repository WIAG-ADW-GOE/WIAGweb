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

}
