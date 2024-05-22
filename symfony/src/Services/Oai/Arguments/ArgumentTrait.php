<?php

namespace App\Services\Oai\Arguments;

use App\Services\Oai\Verbs\OaiVerbInterface;

trait ArgumentTrait
{
    private string $name;

    private ?string $value;

    private OaiVerbInterface $verb;

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value)
    {
        if ($value !== null && strpos($value, 'amp;')) {
            $value = substr($value, 0, 4);
        }
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVerb(): OaiVerbInterface
    {
        return $this->verb;
    }
}
