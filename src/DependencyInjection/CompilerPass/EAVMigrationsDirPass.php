<?php

namespace ANOITCOM\EAVBundle\DependencyInjection\CompilerPass;

use ANOITCOM\EAVBundle\Install\InstallCommand;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EAVMigrationsDirPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        $resolver = new MigrationsDirResolver($container);

        $container
            ->getDefinition(InstallCommand::class)
            ->setArgument('$migrationsDir', $resolver->resolve());
    }
}