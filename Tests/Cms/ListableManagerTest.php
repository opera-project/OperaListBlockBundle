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

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;

class ListableTestRepository implements BlockListableInterface
{
    private $qbMock;

    public function __construct(QueryBuilder $qbMock)
    {
        $this->qbMock = $qbMock;
    }

    public function listableConfiguration() : array
    {
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

    public function filterForListableBlock(Block $block) : QueryBuilder
    {
        return $this->qbMock;
    }
}

class ListableManagerTest extends TestCase
{

    private $listableManager;
    private $blockManager;

    protected function setUp()
    {
        $qbMock = $this->createMock(QueryBuilder::class);
        $listableEntityRepository = new ListableTestRepository($qbMock);

        $stub = $this->createMock(EntityManagerInterface::class);

        $mockReturnValueMap = [
            [ 'ListableTest', $listableEntityRepository ],
        ];

        $stub->method('getRepository')->will($this->returnValueMap($mockReturnValueMap));

        $this->listableManager = new ListableManager($stub);

        $this->listableManager->registerBlockListable($listableEntityRepository);

        $this->setUpBlockManager();

        $requestStack = new RequestStack();
        $requestStack->push(new Request());

        $this->blockManager->registerBlockType(new ContentList($this->listableManager, $requestStack));
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

        $this->assertEquals(count($listableEntityRepository), 1);
        $this->assertContains("ListableTest", $listableEntityRepository);
    }

    public function testGetListableEntitiesTemplates()
    {
        $listableEntityRepository = $this->listableManager->getListableEntitiesTemplates();
        $this->assertEquals(count($listableEntityRepository), 2);
    }

    public function testGetListableEntitiesOrders()
    {
        $listableEntityRepository = $this->listableManager->getListableEntitiesOrders();
        $this->assertEquals(count($listableEntityRepository), 3);
    }

    // FIXME !!
    // public function testGetContents()
    // {
    //     $listableBlock = new Block();
    //     $listableBlock->setName('test content list');
    //     $listableBlock->setType('content_list');
    //     $listableBlock->setConfiguration([
    //         'what' => 'ListableTest',
    //         'limit' => 3,
    //         'order' => 'alphabetical',
    //         'template' => 'template1.html.twig'
    //     ]);

    //     $result = $this->listableManager->getContents($listableBlock);

    //     $this->assertEquals($result->getCurrentPageResults()[0]["text"], "Hello World");
    // }

    // public function testRenderListableBlock()
    // {
    //     $listableBlock = new Block();
    //     $listableBlock->setName('test content list 2');
    //     $listableBlock->setType('content_list');
    //     $listableBlock->setConfiguration([
    //         'what' => 'ListableTest',
    //         'limit' => 3,
    //         'order' => 'alphabetical',
    //         'template' => 'template1.html.twig'
    //     ]);

    //     $this->assertEquals('Hello World Lorem Ipsum Third content ', $this->blockManager->render($listableBlock));
    // }

}