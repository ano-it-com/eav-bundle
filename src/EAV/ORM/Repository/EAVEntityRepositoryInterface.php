<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Repository;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntity;

/**
 *
 * @method EAVEntity[] findBy(array $criteria, array $orderBy = [], $limit = null, $offset = null)
 * @method EAVEntity|null findOneBy(array $criteria, array $orderBy = [])
 * @method EAVEntity|null find(string $id)
 *
 */
interface EAVEntityRepositoryInterface extends EAVRepositoryInterface
{

}