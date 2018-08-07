<?php

namespace Opera\ListBlockBundle\Tests\Block;

use Opera\CoreBundle\Tests\TestCase;
use Opera\ListBlockBundle\Block\ContentList;
use Opera\ListBlockBundle\Cms\ListableManager;
use Opera\ListBlockBundle\BlockType\BlockListableInterface;
use Opera\CoreBundle\Entity\Block;

use Doctrine\ORM\EntityManagerInterface;
use Opera\CoreBundle\Cms\BlockManager;
use Symfony\Component\Form\FormFactory;


class ListableTestRepository implements BlockListableInterface {

    public function listableConfiguration() : array {
        return [
            'templates' => [
                'name' => 'template1.html.twig',
                'name2' => 'template2.html.twig',
            ],
            'available_orders' => [
                'alphabetical',
                'recent first',
                'popularity',
            ]
        ];
    }

    public function getClassName()
    {
        return "ListableTest";
    }

    public function filterForListableBlock(Block $block) : array {
        return [
            [ "text" => "Hello World" ],
            [ "text" => "Lorem Ipsum" ],
            [ "text" => "Third content" ],
        ];
    }
}

class OtherListableTestRepository implements BlockListableInterface {

    public function listableConfiguration() : array {
        return [
            'templates' => [
                'name3' => 'template3.html.twig',
                'name4' => 'template4.html.twig',
            ],
            'available_orders' => [
                'name',
                'popularity',
            ]
        ]; 
    }

    public function getClassName()
    {
        return "OtherListable";
    }

    public function filterForListableBlock(Block $block) : array {
        return [];
    }
}

class ListableManagerTest extends TestCase
{

    private $listableManager;
    private $blockManager;

    protected function setUp()
    {
        $listableEntityRepository = new ListableTestRepository();
        $otherListableEntityRepository = new OtherListableTestRepository();

        $stub = $this->createMock(EntityManagerInterface::class);

        $mockReturnValueMap = [
            [ 'ListableTest', $listableEntityRepository ],
            [ 'ListableTest', $otherListableEntityRepository ],
        ];

        $stub->method('getRepository')->will($this->returnValueMap($mockReturnValueMap));

        $this->listableManager = new ListableManager($stub);

        $this->listableManager->registerBlockListable($listableEntityRepository);
        $this->listableManager->registerBlockListable($otherListableEntityRepository);

        $this->setUpBlockManager();

        $this->blockManager->registerBlockType(new ContentList($this->listableManager));
    }


    private function setUpBlockManager()
    {
        $formFactory = $this->getMockBuilder(FormFactory::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->blockManager = new BlockManager(
            new \Twig_Environment(
            new \Twig_Loader_Filesystem(__DIR__.'/../templates')
            ),
            $formFactory
        );
    }

    public function testGetListableEntities()
    {
        $listableEntityRepository = $this->listableManager->getListableEntities();

        $this->assertEquals(count($listableEntityRepository), 2);
        $this->assertContains("ListableTest", $listableEntityRepository);
        $this->assertContains("OtherListable", $listableEntityRepository);
    }

    public function testGetListableEntitiesTemplates()
    {
        $listableEntityRepository = $this->listableManager->getListableEntitiesTemplates();
        $this->assertEquals(count($listableEntityRepository), 4);
    }

    public function testGetListableEntitiesOrders()
    {
        $listableEntityRepository = $this->listableManager->getListableEntitiesOrders();
        $this->assertEquals(count($listableEntityRepository), 5);
    }

    public function testGetContents()
    {
        $listableBlock = new Block();
        $listableBlock->setName('test content list');
        $listableBlock->setType('content_list');
        $listableBlock->setConfiguration([
            'what' => 'ListableTest',
            'limit' => 3,
            'order' => 'alphabetical',
            'template' => 'template1.html.twig'
        ]);

        $result = $this->listableManager->getContents($listableBlock);

        $this->assertEquals($result[0]["text"], "Hello World");
    }

    public function testRenderListableBlock()
    {
        $listableBlock = new Block();
        $listableBlock->setName('test content list 2');
        $listableBlock->setType('content_list');
        $listableBlock->setConfiguration([
            'what' => 'ListableTest',
            'limit' => 3,
            'order' => 'alphabetical',
            'template' => 'template1.html.twig'
        ]);

        $this->assertEquals('Hello World Lorem Ipsum Third content ', $this->blockManager->render($listableBlock));
    }
    
    /**
     * @expectedException \LogicException
     */
    public function testRenderUnListableBlock()
    {
        $listableBlock = new Block();
        $listableBlock->setName('test content list 2');
        $listableBlock->setType('content_list');
        $listableBlock->setConfiguration([
            'what' => 'NotListableItem',
            'limit' => 3,
            'order' => 'alphabetical',
            'template' => 'template1.html.twig'
        ]);

        $this->blockManager->render($listableBlock);
    }

}