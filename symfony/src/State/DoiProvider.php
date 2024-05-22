<?php

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use App\Entity\Doi;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

final class DoiProvider implements ProviderInterface
{
    public function __construct(private ProviderInterface $itemProvider, private ProviderInterface $collectionProvider)
    {
    }
    
    /** Excludes DOI marked as deleted from the API. */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface){
            $data = $this->collectionProvider->provide($operation, $uriVariables, $context);
            return array_filter($data, function($doi) {
                return !$doi->isDeleted();
            });
        } else {
            $data = $this->itemProvider->provide($operation, $uriVariables, $context);
            if (isset($data) && $data->isDeleted()){
                    return null;
            }
            return $data;
        }
    }
}
