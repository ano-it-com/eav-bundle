<?php

namespace ANOITCOM\EAVBundle\Tests\App;

use Symfony\Component\Config\Loader\LoaderInterface;

class TestingCustomEntitiesKernel extends TestingKernel
{

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config.yaml', 'yaml');
        $loader->load(__DIR__ . '/config/eav_custom.yaml', 'yaml');
    }

}