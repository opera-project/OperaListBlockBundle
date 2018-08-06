<?php

namespace Opera\ListBlockBundle\Block;

use Opera\CoreBundle\BlockType\BlockTypeInterface;
use Opera\CoreBundle\BlockType\BaseBlock;
use Opera\CoreBundle\Entity\Block;
use Symfony\Component\Form\FormBuilderInterface;
use Opera\ListBlockBundle\Cms\ListableManager;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ContentList extends BaseBlock implements BlockTypeInterface
{
    private $listableManager;

    public function __construct(ListableManager $listableManager)
    {
        $this->listableManager = $listableManager;
    }

    public function getTemplate(Block $block) : string
    {
        $config = $block->getConfiguration();

        if (isset($config['template'])) {
            return $config['template'];
        }

        return sprintf('blocks/%s.html.twig', $this->getType());
    }
    
    public function getType() : string
    {
        return 'content_list';
    }

    public function getVariables(Block $block) : array
    {
        $config = $block->getConfiguration();
       
        return [
            'contents' => $this->listableManager->getContents($block),
        ];
    }

    public function createAdminConfigurationForm(FormBuilderInterface $builder)
    {
        $builder->add('what', ChoiceType::class, [
            'choices' => array_combine(
                $this->listableManager->getListableEntities(),
                $this->listableManager->getListableEntities()
            ),
        ]);
        
        $builder->add('template', ChoiceType::class, [
            'choices' => array_combine(
                $this->listableManager->getListableEntitiesTemplates(),
                $this->listableManager->getListableEntitiesTemplates()
            ),
        ]);

        $builder->add('order', ChoiceType::class, [
            'choices' => array_combine(
                $this->listableManager->getListableEntitiesOrders(),
                $this->listableManager->getListableEntitiesOrders()
            ),
        ]);

        $builder->add('limit', NumberType::class);
    }
}