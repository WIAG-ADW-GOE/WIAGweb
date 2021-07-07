<?php

namespace App\Validator;

use App\Repository\CanonRepository;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class MergeTargetValidator extends ConstraintValidator
{
    private $canonRepository;

    public function __construct(CanonRepository $canonRepository) {
        $this->canonRepository = $canonRepository;
    }


    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\MergeTarget */

        if (null === $value || '' === $value) {
            return;
        }

        $canon = $this->canonRepository->findOneById($value);

        if(!is_null($canon)) {
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
