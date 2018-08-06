<?php

namespace Opera\ListBlockBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Opera\ListBlockBundle\DependencyInjection\Compiler\ListablePass;

class OperaListBlockBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ListablePass());
    }
}