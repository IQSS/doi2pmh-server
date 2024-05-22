<?php

namespace App\EventListener;

use App\Entity\Doi;
use DateTime;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * Listener for preUpdate events on DOI.
 */
class DoiChangedListener
{
    /**
     * @inheritdoc
     * Updates the created_at, updated_at and deleted_at timestamps if necessary.
     */
    public function preUpdate(Doi $doi, PreUpdateEventArgs $event): void
    {
        if ($event->hasChangedField("deleted"))
        {
            if ($event->getNewValue("deleted"))
            {
                $doi->setDeletedAt(new DateTime('now'));
            } else
            {
                $doi->setCreatedAt(new DateTime('now'));
                $doi->setUpdatedAt(new DateTime('now'));
            }
        }
        if ($event->hasChangedField("folder")
            || $event->hasChangedField("citation")
            || $event->hasChangedField("jsonContent")
            || $event->hasChangedField("toIgnore")
        )
        {
            $doi->setUpdatedAt(new DateTime('now'));
        }
    }
}
