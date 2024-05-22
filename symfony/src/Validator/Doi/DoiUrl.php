<?php


namespace App\Validator\Doi;


use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DoiUrl extends Constraint
{
    public string $messageBadHost = 'admin.constraint.doi.host';
    public string $messageDoiNotExist = 'admin.constraint.doi.notExist';
}
