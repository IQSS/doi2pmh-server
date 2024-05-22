<?php

namespace App\Services\Oai\Arguments;

use App\Entity\Folder;
use App\Services\Oai\Verbs\OaiVerbInterface;

class Set implements ArgumentInterface
{
    use ArgumentTrait;

    public function __construct(OaiVerbInterface $verb)
    {
        $this->verb = $verb;
        $this->name = 'set';
        $this->setValue($verb->getRequest()->get('set')?? $verb->getRequest()->get('amp;set'));
    }

    /**
     * @see ArgumentInterface
     * @return bool
     */
    public function isRequired(): bool
    {
        return false;
    }

    /**
     * @see ArgumentInterface
     * @return bool
     */
    public function isInResumptionToken(): bool
    {
        return true;
    }

    /**
     * Parse folder slug to get its real name
     * @return string
     */
    public function unslug(): string
    {
        return str_replace('_', ' ', $this->value);
    }

    /**
     * @return Folder|null  Return set's folder
     */
    public function getFolder(): ?Folder
    {
        return $this->verb->getEntityManager()->getRepository(Folder::class)->findOneBy(['name' => $this->unslug()]);
    }
}
