<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class CanonWiagEpisc extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    //public $message = 'The value "{{ value }}" is not valid.';
    public $message = "Wähle eine gültige Bischof-ID.";
}
