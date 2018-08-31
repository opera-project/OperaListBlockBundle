<?php

namespace Opera\ListBlockBundle\Tests\Block;

use Opera\CoreBundle\Tests\TestCase;
use Opera\ListBlockBundle\Block\ContentList;
use Opera\ListBlockBundle\Cms\ListableManager;
use Opera\CoreBundle\Entity\Block;
use Symfony\Component\HttpFoundation\RequestStack;

class ContentListTest extends TestCase
{
    private $listableManagerMock;

    public function testGetType()
    {
        $this->listableManagerMock = $this->createMock(ListableManager::class);

        $contentListBlock = new ContentList($this->listableManagerMock, $this->createMock(RequestStack::class));

        $this->assertEquals('content_list', $contentListBlock->getType());
    }
}