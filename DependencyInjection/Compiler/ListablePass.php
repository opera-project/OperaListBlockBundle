<?php

namespace Opera\ListBlockBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Opera\ListBlockBundle\Cms\ListableManager;
use Symfony\Component\DependencyInjection\Reference;

class ListablePass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        $manager = $container->getDefinition(ListableManager::class);

        // or processing tagged services:
        foreach ($container->findTaggedServiceIds('cms.block_listable') as $id => $tags) {
            $manager->addMethodCall('registerBlockListable', [ new Reference($id) ]);   
        }
    }
}