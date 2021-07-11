<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Entity\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\CommonFilters\FilterCriteria\FilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Type\EAVTypeInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\EAVHydratorInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\PersistersFactory\EAVPersistersFactoryInterface;

class EAVEntityBuilder implements EAVEntityBuilderInterface
{

    protected EAVEntityManagerInterface $em;

    protected EAVPersisterInterface $typePersister;

    protected EAVHydratorInterface $hydrator;

    protected EAVPersisterInterface $namespacePersister;


    public function __construct(
        EAVEntityManagerInterface $em,
        EAVEntityHydrator $hydrator,
        EAVPersistersFactoryInterface $persistersFactory
    ) {
        $this->em                 = $em;
        $this->hydrator           = $hydrator;
        $this->typePersister      = $persistersFactory->getForEntityClass($this->em->getEavSettings()->getClassForEntityType(EAVSettings::TYPE), $em);
        $this->namespacePersister = $persistersFactory->getForEntityClass($this->em->getEavSettings()->getClassForEntityType(EAVSettings::NAMESPACE), $em);
    }


    public function buildEntities(array $entityRows, array $valuesRows): array
    {
        $typeIds            = array_values(array_unique(array_column($entityRows, 'type_id')));
        $entityNamespaceIds = array_values(array_unique(array_column($entityRows, 'namespace_id')));
        $valuesNamespaceIds = array_values(array_unique(array_column($valuesRows, 'namespace_id')));

        $namespaceIds = array_values(array_unique(array_merge($entityNamespaceIds, $valuesNamespaceIds)));

        /** @var EAVTypeInterface[] $types */
        $types = $this->typePersister->loadByCriteria([ (new FilterCriteria())->whereIn('id', $typeIds) ]);

        /** @var EAVNamespaceInterface[] $namespaces */
        $namespaces = $this->namespacePersister->loadByCriteria([ (new FilterCriteria())->whereIn('id', $namespaceIds) ]);

        $combinedEntityRows = $this->combineRows($entityRows, $valuesRows, $namespaces, $types);

        return $this->hydrator->hydrate($combinedEntityRows);
    }


    protected function combineRows(array $entityRows, array $entityValues, array $namespaces, array $types): array
    {
        $entityRows = array_combine(array_column($entityRows, 'id'), $entityRows);
        $namespaces = array_combine(array_map(static function (EAVNamespaceInterface $namespace) { return $namespace->getId(); }, $namespaces), $namespaces);

        $types = array_combine(array_map(static function (EAVTypeInterface $type) { return $type->getId(); }, $types), $types);

        $typePropertyIdToValueTypeMapping = [];

        /** @var EAVTypeInterface $type */
        foreach ($types as $type) {
            $properties = $type->getProperties();

            foreach ($properties as $property) {
                $typePropertyIdToValueTypeMapping[$property->getId()] = $property->getValueType()->getCode();
            }
        }

        foreach ($entityValues as $entityValue) {
            $valueType                  = $typePropertyIdToValueTypeMapping[$entityValue['type_property_id']];
            $entityValue['_value_type'] = $valueType;

            $columnName            = $this->em->getEavSettings()->getColumnNameForValueType($valueType);
            $entityValue['_value'] = $entityValue[$columnName];

            $entityValue['_namespace'] = $namespaces[$entityValue['namespace_id']];

            $entityRows[$entityValue['entity_id']]['_values'][] = $entityValue;
        }

        foreach ($entityRows as $entityId => $entityRow) {
            $entityRows[$entityId]['_type']      = $types[$entityRow['type_id']];
            $entityRows[$entityId]['_namespace'] = $namespaces[$entityRow['namespace_id']];

            if ( ! isset($entityRows[$entityId]['_values'])) {
                $entityRows[$entityId]['_values'] = [];
            }

        }

        return array_values($entityRows);
    }


    public function extractData(EAVPersistableInterface $entity): array
    {
        return $this->hydrator->extract($entity);
    }

}