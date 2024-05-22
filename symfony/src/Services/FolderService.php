<?php

namespace App\Services;

use App\Entity\Folder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

class FolderService {


    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function getRootFolder(): Folder
    {
        return $this->entityManager->getRepository(Folder::class)->findOneBy(['parent' => null]);
    }

    public function getParents(Folder $folder): Collection
    {
        $parentsCollection = new ArrayCollection();
        return $this->addParents($folder, $parentsCollection);
    }

    private function addParents(Folder $folder, Collection $parentsCollection): Collection
    {
        if ($parent = $folder->getParent()) {
            $this->addParents($parent, $parentsCollection);
            $parentsCollection->add($parent);
        }
        return $parentsCollection;
    }
}
