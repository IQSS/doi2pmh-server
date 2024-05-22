<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\Security\Core\Security;

final class FolderProvider implements ProviderInterface
{
    public function __construct(private ProviderInterface $collectionProvider, private Security $security)
    {
    }
    
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $data = $this->collectionProvider->provide($operation, $uriVariables, $context);
        if ($operation->getName() == "api_folder_all") {
            return array_filter($data, function($folder) {
                return $folder->isRootFolderApp();
            });
        }

        if ($operation->getName() == "api_folder_me") {
            return $this->security->getUser()->getRootFolder();
        }
        return $data;
    }
}
