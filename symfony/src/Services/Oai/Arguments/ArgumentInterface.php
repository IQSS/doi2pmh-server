<?php

namespace App\Services\Oai\Arguments;

interface ArgumentInterface
{
    /**
     * @return bool     True if the argument is required for the verb
     */
    public function isRequired(): bool;

    /**
     * @return string   The argument name
     */
    public function getName(): string;

    /**
     * @return string|null      Return the argument value or null if is not in query
     */
    public function getValue(): ?string;

    /**
     * @return bool     True if the argument can be parsed in resumption token
     */
    public function isInResumptionToken(): bool;

}
