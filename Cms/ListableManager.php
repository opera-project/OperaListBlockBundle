<?php

namespace Opera\ListBlockBundle\Cms;

use Opera\ListBlockBundle\BlockType\BlockListableInterface;
use Doctrine\ORM\EntityManagerInterface;
use Opera\CoreBundle\Entity\Block;

class ListableManager
{
    private $listables = [];
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getListableEntities(): array
    {
        $ListableEntityNameArray = [];

        foreach ($this->listables as $listableEntity) {
            $ListableEntityNameArray[] = $listableEntity->getClassName();
        }

        return $ListableEntityNameArray;
    }

    public function getListableEntitiesTemplates(): array
    {
        $templateArray = [];

        foreach ($this->listables as $listableEntity) {
            $templateArray = array_merge($templateArray, $listableEntity->listableConfiguration()['templates']);
        }

        return $templateArray;
    }

    public function getListableEntitiesOrders(): array
    {
        $availableOrderArray = [];

        foreach ($this->listables as $listableEntity) {
            $availableOrderArray = array_merge($availableOrderArray, $listableEntity->listableConfiguration()['available_orders']);
        }

        return $availableOrderArray;
    }

    public function registerBlockListable(BlockListableInterface $listableEntityRepository)
    {
        $this->listables[] = $listableEntityRepository;
    }

    public function getContents(Block $block): array
    {
        $config = $block->getConfiguration();
        $repository = $this->entityManager->getRepository($config['what']);

        return $repository->filterForListableBlock($block);
    }

}
