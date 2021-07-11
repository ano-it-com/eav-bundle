<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Repository;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EntityRelation\EAVEntityRelationType;

/**
 *
 * @method EAVEntityRelationType[] findBy(array $criteria, array $orderBy = [], $limit = null, $offset = null)
 * @method EAVEntityRelationType|null findOneBy(array $criteria, array $orderBy = [])
 * @method EAVEntityRelationType|null find(string $id)
 *
 */
interface EAVEntityRelationTypeRepositoryInterface extends EAVRepositoryInterface
{

}