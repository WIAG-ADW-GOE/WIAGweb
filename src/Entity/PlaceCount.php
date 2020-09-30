<?php
namespace App\Entity;

class PlaceCount {
    public $name;
    public $count;

    public function __construct($n, $c) {
        $this->name = $n;
        $this->count = $c;
    }

    public function getLabel(): string {
        $label = $this->name.' ('.$this->count.')';
        return $label;
    }

    public function getAttr() {
        $attr = array();
        if ($this->name == 'Pedena') {
            $attr[] = ['checked' => 'checked'];
        }            
        return $attr;
    }

}
