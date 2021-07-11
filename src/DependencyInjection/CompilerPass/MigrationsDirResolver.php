<?php

namespace ANOITCOM\EAVBundle\DependencyInjection\CompilerPass;

use Doctrine\Bundle\MigrationsBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MigrationsDirResolver
{

    private ContainerBuilder $container;


    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }


    public function resolve(): string
    {
        $configs = $this->container->getExtensionConfig('doctrine_migrations');

        $processor = new Processor();

        $config = $processor->processConfiguration(new Configuration(), $configs);

        $migrationsDir = '%kernel.project_dir%/migrations';

        if (isset($config['migrations_paths']) && is_array($config['migrations_paths'])) {
            $paths         = $config['migrations_paths'];
            $migrationsDir = reset($paths);
        } elseif (isset($config['dir_name']) && $config['dir_name']) {
            $migrationsDir = $config['dir_name'];
        }

        return $migrationsDir;
    }
}