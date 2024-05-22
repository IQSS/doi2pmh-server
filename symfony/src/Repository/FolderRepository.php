<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Folder;
use App\Services\FolderService;
use App\Services\Oai\Arguments\ResumptionToken;

/**
 * @method Folder|null findOneBy(array $criteria, array $orderBy = null)
 * @method Folder[]    findAll()
 * @method Folder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FolderRepository extends ServiceEntityRepository
{
    private FolderService $folderService;

    public function __construct(ManagerRegistry $registry, FolderService $folderService)
    {
        parent::__construct($registry, Folder::class);
        $this->folderService = $folderService;
    }

    /**
     * Find folder by id, if not exist return the root folder
     * @param mixed $id
     * @param null $lockMode
     * @param null $lockVersion
     * @return Folder
     */
    public function find($id, $lockMode = null, $lockVersion = null): Folder
    {
        return ($id && parent::find($id))
            ? parent::find($id)
            : $this->folderService->getRootFolder();
    }

    /**
     * Paginate results for resumption token
     *
     * @param int $first
     * @param int $numberPerPage
     * @return array
     */
    public function findAtPage(int $first, int $numberPerPage = ResumptionToken::ELEMENTS_PER_PAGE): array
    {
        return $this->createQueryBuilder('t')
            ->setMaxResults($numberPerPage)
            ->setFirstResult($first)
            ->getQuery()
            ->getResult();
    }
}
