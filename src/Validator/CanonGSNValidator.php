<?php

namespace App\Validator;

use App\Repository\CanonGSRepository;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CanonGSNValidator extends ConstraintValidator
{
    private $canonGSRepository;

    public function __construct(CanonGSRepository $canonGSRepository) {
        $this->canonGSRepository = $canonGSRepository;
    }

    public function validate($value, Constraint $constraint) {

        /* @var $constraint \App\Validator\CanonGSN */

        if (null === $value || '' === $value) {
            return;
        }

        $qresult = $this->canonGSRepository->suggestGsn($value, 5);

        if (count($qresult) == 1) {
            return;
        }

        // TODO: implement the validation here
        // $this->context->buildViolation($constraint->message)
        //     ->setParameter('{{ value }}', $value)
        //     ->addViolation();

        $this->context->buildViolation($constraint->message)
                      ->addViolation();

    }
}
