<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Type\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\CommonFilters\FilterCriteria\FilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\EAVHydratorInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\PersistersFactory\EAVPersistersFactoryInterface;

class EAVTypeBuilder implements EAVTypeBuilderInterface
{

    protected EAVEntityManagerInterface $em;

    protected EAVHydratorInterface $hydrator;

    protected EAVPersisterInterface $namespacePersister;


    public function __construct(EAVEntityManagerInterface $em, EAVTypeHydrator $hydrator, EAVPersistersFactoryInterface $persistersFactory)
    {
        $this->em                 = $em;
        $this->hydrator           = $hydrator;
        $this->namespacePersister = $persistersFactory->getForEntityClass($this->em->getEavSettings()->getClassForEntityType(EAVSettings::NAMESPACE), $em);
    }


    public function buildEntities(array $typeRows, array $propertyRows): array
    {
        $typeNamespaceIds     = array_values(array_unique(array_column($typeRows, 'namespace_id')));
        $propertyNamespaceIds = array_values(array_unique(array_column($typeRows, 'namespace_id')));

        $namespaceIds = array_values(array_unique(array_merge($typeNamespaceIds, $propertyNamespaceIds)));

        /** @var EAVNamespaceInterface[] $namespaces */
        $namespaces = $this->namespacePersister->loadByCriteria([ (new FilterCriteria())->whereIn('id', $namespaceIds) ]);

        $combinedTypeRows = $this->combineRows($typeRows, $propertyRows, $namespaces);

        return $this->hydrator->hydrate($combinedTypeRows);
    }


    protected function combineRows(array $typeRows, array $propertyRows, array $namespaces): array
    {
        $namespaces = array_combine(array_map(static function (EAVNamespaceInterface $namespace) { return $namespace->getId(); }, $namespaces), $namespaces);

        $propertiesByType = [];

        foreach ($propertyRows as $propertyRow) {
            if ( ! isset($propertiesByType[$propertyRow['type_id']])) {
                $propertiesByType[$propertyRow['type_id']] = [];
            }

            $propertyRow['_namespace']                   = $namespaces[$propertyRow['namespace_id']];
            $propertiesByType[$propertyRow['type_id']][] = $propertyRow;
        }

        foreach ($typeRows as &$typeRow) {
            $typeRow['_properties'] = $propertiesByType[$typeRow['id']] ?? [];
            $typeRow['_namespace']  = $namespaces[$typeRow['namespace_id']];
        }
        unset($typeRow);

        return $typeRows;
    }


    public function extractData(EAVPersistableInterface $entity): array
    {
        return $this->hydrator->extract($entity);
    }
}