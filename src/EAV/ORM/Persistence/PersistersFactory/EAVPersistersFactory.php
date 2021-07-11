<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\PersistersFactory;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;

class EAVPersistersFactory implements EAVPersistersFactoryInterface
{

    protected EAVPersistersLocator $persistersLocator;


    public function __construct(EAVPersistersLocator $persistersLocator)
    {
        $this->persistersLocator = $persistersLocator;
    }


    public function getForEntityClass(string $class, EAVEntityManagerInterface $em): EAVPersisterInterface
    {
        $persisterClass = $em->getEavSettings()->getPersisterClassForEntityClass($class);

        if ( ! $persisterClass || ! $this->persistersLocator->has($persisterClass)) {
            throw new \InvalidArgumentException('Persister \'' . $persisterClass . '\' for class \'' . $class . '\' not found!');
        }

        return $this->persistersLocator->get($persisterClass);
    }
}
