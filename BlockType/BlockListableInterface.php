<?php

namespace Opera\ListBlockBundle\BlockType;

use Opera\CoreBundle\Entity\Block;
use Doctrine\ORM\QueryBuilder;

interface BlockListableInterface
{   
    /**
     * return listable configuration
     * [
     *  'templates' => [],
     *  'available_orders' => ['recents first', 'popular first', 'manual order']
     *  ...
     * ]
     */
    public function listableConfiguration() : array;

    public function filterForListableBlock(Block $block) : QueryBuilder;
}