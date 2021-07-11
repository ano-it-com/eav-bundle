<?php

namespace ANOITCOM\EAVBundle;

use ANOITCOM\EAVBundle\DependencyInjection\CompilerPass\EAVMigrationsDirPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EAVBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new EAVMigrationsDirPass());
    }
}