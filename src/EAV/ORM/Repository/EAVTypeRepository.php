<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Repository;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\Type\EAVType;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;

/**
 *
 * @method EAVType[] findBy(array $criteria, array $orderBy = [], $limit = null, $offset = null)
 * @method EAVType|null findOneBy(array $criteria, array $orderBy = [])
 * @method EAVType|null find(string $id)
 *
 */
class EAVTypeRepository extends EAVAbstractRepository implements EAVTypeRepositoryInterface
{

    public function getEntityClass(): string
    {
        return $this->em->getEavSettings()->getClassForEntityType(EAVSettings::TYPE);
    }
}