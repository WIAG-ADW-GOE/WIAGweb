<?php

namespace App\Validator;

use App\Repository\PersonRepository;
use App\Entity\Person;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CanonWiagEpiscValidator extends ConstraintValidator
{
    private $personRepository;

    public function __construct(PersonRepository $personRepository) {
        $this->personRepository = $personRepository;
    }

    public function validate($value, Constraint $constraint) {

        /* @var $constraint \App\Validator\CanonGSN */

        if (null === $value || '' === $value) {
            return;
        }

        $id = Person::extractDbId($value);
        if (!is_null($id)) {
            $qresult = $this->personRepository->findOneByWiagid($id);
            dump($value, $qresult);

            if (!is_null($qresult) == 1) {
                return;
            }
        }

        // TODO: implement the validation here
        // $this->context->buildViolation($constraint->message)
        //     ->setParameter('{{ value }}', $value)
        //     ->addViolation();

        $this->context->buildViolation($constraint->message)
                      ->addViolation();

    }
}
