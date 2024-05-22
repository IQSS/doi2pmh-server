<?php
namespace App\State;

use ApiPlatform\Metadata\DeleteOperationInterface;
use App\Entity\Doi;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\FolderRepository;
use App\Services\DoiService;
use App\Services\FolderService;
use Doctrine\ORM\EntityManagerInterface;

class DoiProcessor implements ProcessorInterface
{

    public function __construct(
        private DoiService $doiService,
        private ProcessorInterface $persistProcessor
    )
    {
    }

    /**
     * {@inheritDoc}
     * @var Doi $data
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []) : mixed
    {
        if ($operation instanceof DeleteOperationInterface){
            $this->doiService->deleteDoi($data);
            return null;
        }
        $folder = $data->getFolder();
        $data = $this->doiService->replaceIfDeleted($data);
        $data->setFolder($folder);
        $data = $this->doiService->fillDoiData($data);
        $data->setCitation($this->doiService->getCitation($data));
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
