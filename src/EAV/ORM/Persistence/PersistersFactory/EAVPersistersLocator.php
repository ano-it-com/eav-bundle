<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\PersistersFactory;

use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class EAVPersistersLocator
{

    protected ServiceLocator $persistersLocator;


    public function __construct(ServiceLocator $persistersLocator)
    {
        $this->persistersLocator = $persistersLocator;
    }


    public function get(string $class): EAVPersisterInterface
    {
        return $this->persistersLocator->get($class);
    }


    public function has(string $class): bool
    {
        return $this->persistersLocator->has($class);
    }

}