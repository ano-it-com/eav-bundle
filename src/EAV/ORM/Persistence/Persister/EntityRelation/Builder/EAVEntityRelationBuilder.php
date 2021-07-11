<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EntityRelation\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\CommonFilters\FilterCriteria\FilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntity;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EntityRelation\EAVEntityRelationType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\EAVHydratorInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\PersistersFactory\EAVPersistersFactoryInterface;

class EAVEntityRelationBuilder implements EAVEntityRelationBuilderInterface
{

    protected EAVEntityManagerInterface $em;

    protected EAVPersisterInterface $typePersister;

    protected EAVPersisterInterface $entityPersister;

    protected EAVPersisterInterface $namespacePersister;

    protected EAVHydratorInterface $hydrator;


    public function __construct(
        EAVEntityManagerInterface $em,
        EAVEntityRelationHydrator $hydrator,
        EAVPersistersFactoryInterface $persistersFactory
    ) {
        $this->em                 = $em;
        $this->hydrator           = $hydrator;
        $this->typePersister      = $persistersFactory->getForEntityClass($this->em->getEavSettings()->getClassForEntityType(EAVSettings::ENTITY_RELATION_TYPE), $em);
        $this->entityPersister    = $persistersFactory->getForEntityClass($this->em->getEavSettings()->getClassForEntityType(EAVSettings::ENTITY), $em);
        $this->namespacePersister = $persistersFactory->getForEntityClass($this->em->getEavSettings()->getClassForEntityType(EAVSettings::NAMESPACE), $em);
    }


    public function buildEntities(array $entityRows): array
    {
        $typeIds      = array_values(array_unique(array_column($entityRows, 'type_id')));
        $namespaceIds = array_values(array_unique(array_column($entityRows, 'namespace_id')));

        $entityFromIds = array_values(array_unique(array_column($entityRows, 'from_id')));
        $entityToIds   = array_values(array_unique(array_column($entityRows, 'to_id')));

        $entityIds = array_values(array_unique(array_merge($entityFromIds, $entityToIds)));

        $entities = $this->entityPersister->loadByCriteria([ (new FilterCriteria())->whereIn('id', $entityIds) ]);

        /** @var \ANOITCOM\EAVBundle\EAV\ORM\Entity\EntityRelation\EAVEntityRelationType[] $types */
        $types = $this->typePersister->loadByCriteria([ (new FilterCriteria())->whereIn('id', $typeIds) ]);

        /** @var EAVNamespaceInterface[] $namespaces */
        $namespaces = $this->namespacePersister->loadByCriteria([ (new FilterCriteria())->whereIn('id', $namespaceIds) ]);

        $combinedEntityRows = $this->combineRows($entityRows, $namespaces, $types, $entities);

        return $this->hydrator->hydrate($combinedEntityRows);
    }


    protected function combineRows(array $entityRows, array $namespaces, array $types, array $entities): array
    {
        $entityRows = array_combine(array_column($entityRows, 'id'), $entityRows);

        $types      = array_combine(array_map(static function (EAVEntityRelationType $type) { return $type->getId(); }, $types), $types);
        $entities   = array_combine(array_map(static function (EAVEntity $entity) { return $entity->getId(); }, $entities), $entities);
        $namespaces = array_combine(array_map(static function (EAVNamespaceInterface $namespace) { return $namespace->getId(); }, $namespaces), $namespaces);

        //TODO - обработку ненайденных моделей
        foreach ($entityRows as $entityId => $entityRow) {
            $entityRows[$entityId]['_type']      = $types[$entityRow['type_id']];
            $entityRows[$entityId]['_from']      = $entities[$entityRow['from_id']];
            $entityRows[$entityId]['_to']        = $entities[$entityRow['to_id']];
            $entityRows[$entityId]['_namespace'] = $namespaces[$entityRow['namespace_id']];
        }

        return array_values($entityRows);
    }


    public function extractData(EAVPersistableInterface $entity): array
    {
        return $this->hydrator->extract($entity);
    }

}