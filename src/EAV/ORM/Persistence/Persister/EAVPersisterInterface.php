<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;

interface EAVPersisterInterface
{

    public static function getSupportedClass(): string;


    public function loadByCriteria(array $criteria = [], int $refDepth = 0, array $orderBy = [], $limit = null, $offset = null);


    public function getChanges(EAVPersistableInterface $entity, array $oldValues): array;


    public function update(EAVPersistableInterface $entity, array $changeSet): void;


    public function insert(EAVPersistableInterface $entity): void;


    public function delete(EAVPersistableInterface $entity): void;


    public function getCurrentState(EAVPersistableInterface $entity): array;

}