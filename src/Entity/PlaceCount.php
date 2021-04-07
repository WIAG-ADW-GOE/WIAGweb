<?php
namespace App\Entity;

class PlaceCount {
    public $id;
    public $name;
    public $count;

    public function __construct($id, $n, $c) {
        $this->id = $id;
        $this->name = $n;
        $this->count = $c;
    }

    public function getId() {
        return $this->id;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function getLabel(): string {
        $label = $this->name.' ('.$this->count.')';
        return $label;
    }

    public function getValue(): string {
        return $this->id;
    }

    public static function find($name, array $a) {
        foreach($a as $pc) {
            if ($name == $pc->name) return true;
        }
        return false;
    }

    public static function isless(PlaceCount $a, PlaceCount $b) {
        if($a->name == $b->name) {
            return 0;
        }
        return $a->name < $b->name ? -1 : 1;
    }

}
