<?php


namespace App\Validator\Doi;

use App\Services\DoiService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DoiUrlValidator extends ConstraintValidator
{
    const DOI_HOST_NAME = "doi.org";

    private DoiService $doiService;

    public function __construct(DoiService $doiService)
    {
        $this->doiService = $doiService;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof DoiUrl) {
            throw new UnexpectedTypeException($constraint, DoiUrl::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        // Verify if host name is doi.org
        if (parse_url($value, PHP_URL_HOST) !== self::DOI_HOST_NAME) {
            $this->context->buildViolation($constraint->messageBadHost)->addViolation();
        }

        // Verify if doi exist in doi.org
        if (!$this->doiService->doiExist($value)) {
            $this->context->buildViolation($constraint->messageDoiNotExist)->addViolation();
        }
    }
}
