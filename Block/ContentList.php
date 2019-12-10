<?php

namespace Opera\ListBlockBundle\Block;

use Opera\CoreBundle\BlockType\BlockTypeInterface;
use Opera\CoreBundle\BlockType\BaseBlock;
use Opera\CoreBundle\Entity\Block;
use Symfony\Component\Form\FormBuilderInterface;
use Opera\ListBlockBundle\Cms\ListableManager;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Opera\TaxonomyBundle\Entity\Tag;
use Opera\CoreBundle\Form\Type\OperaAdminAutocompleteType;
use Symfony\Component\HttpFoundation\RequestStack;
use Opera\CoreBundle\BlockType\CacheableBlockInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Symfony\Component\HttpFoundation\Response;

class ContentList extends BaseBlock implements BlockTypeInterface, CacheableBlockInterface
{
    private $listableManager;
    private $requestStack;

    public function __construct(ListableManager $listableManager, RequestStack $requestStack)
    {
        $this->listableManager = $listableManager;
        $this->requestStack = $requestStack;
    }

    public function getTemplate(Block $block): string
    {
        $config = $block->getConfiguration();

        if (isset($config['template'])) {
            return $config['template'];
        }

        return sprintf('blocks/%s.html.twig', $this->getType());
    }

    public function getType(): string
    {
        return 'content_list';
    }

    public function execute(Block $block)
    {
        $config = $block->getConfiguration();
        $pageParameterName = $config['page_parameter_name'] ?? 'page_' . $block->getId();

        try {
            $pagerfanta = $this->listableManager->getContents(
                $block,
                $this->requestStack->getCurrentRequest()->get($pageParameterName, 1)
            );
        } catch (OutOfRangeCurrentPageException $e) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        return [
            'page_parameter_name' => $pageParameterName,
            'contents' => $pagerfanta,
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

        $builder->add('tags', OperaAdminAutocompleteType::class, [
            'class' => Tag::class,
            'multiple' => true,
            'required' => false,
        ]);

        $builder->add('filters', TextareaType::class, [
            'required' => false,
        ]);

        $builder->add('limit', NumberType::class);

        $builder->add('page_parameter_name', [
            'required' => false,
        ]);
    }

    public function configure(NodeDefinition $rootNode)
    {
        $rootNode
            ->children()
            ->enumNode('what')
            ->values($this->listableManager->getListableEntities())
            ->defaultValue($this->listableManager->getListableEntities()[0])
            ->end()
            ->arrayNode('tags')
            ->treatNullLike(array())
            ->prototype('scalar')->end()
            ->end()
            ->scalarNode('filters')->defaultValue('')->end()
            ->floatNode('limit')
            ->min(1)
            ->defaultValue(5)
            ->end()
            ->scalarNode('template')->end()
            ->scalarNode('order')->end()
            ->scalarNode('page_parameter_name')->end()
            ->end();;
    }

    public function getCacheConfig(OptionsResolver $resolver, Block $block)
    {
        $resolver->setDefaults([
            // Set your configs for cache
            'vary' => 'cookie',
            // 'expires_after' => \DateInterval::createFromDateString('1 hour'),
        ]);
    }
}
