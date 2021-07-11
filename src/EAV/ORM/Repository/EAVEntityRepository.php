<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Repository;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntity;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;

/**
 *
 * @method EAVEntity[] findBy(array $criteria, array $orderBy = [], $limit = null, $offset = null)
 * @method EAVEntity|null findOneBy(array $criteria, array $orderBy = [])
 * @method EAVEntity|null find(string $id)
 *
 */
class EAVEntityRepository extends EAVAbstractRepository implements EAVEntityRepositoryInterface
{

    public function getEntityClass(): string
    {
        return $this->em->getEavSettings()->getClassForEntityType(EAVSettings::ENTITY);
    }
}