<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Repository;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespace;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;

/**
 *
 * @method EAVNamespace[] findBy(array $criteria, array $orderBy = [], $limit = null, $offset = null)
 * @method EAVNamespace|null findOneBy(array $criteria, array $orderBy = [])
 * @method EAVNamespace|null find(string $id)
 *
 */
class EAVNamespaceRepository extends EAVAbstractRepository implements EAVNamespaceRepositoryInterface
{

    public function getEntityClass(): string
    {
        return $this->em->getEavSettings()->getClassForEntityType(EAVSettings::NAMESPACE);
    }
}