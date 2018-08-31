<?php

namespace Opera\ListBlockBundle\Cms;

use Opera\ListBlockBundle\BlockType\BlockListableInterface;
use Doctrine\ORM\EntityManagerInterface;
use Opera\CoreBundle\Entity\Block;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

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
        $listableEntityNameArray = [];

        foreach ($this->listables as $listableEntity) {
            $listableEntityNameArray[] = $listableEntity->getClassName();
        }

        return $listableEntityNameArray;
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

    public function getContents(Block $block, ?int $page = 1): Pagerfanta
    {
        $config = $block->getConfiguration();
        $repository = $this->entityManager->getRepository($config['what']);

        $queryBuilder = $repository->filterForListableBlock($block);

        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(isset($config['limit']) ? $config['limit'] : 10);
        $pagerfanta->setCurrentPage($page);

        return $pagerfanta;
    }

}
