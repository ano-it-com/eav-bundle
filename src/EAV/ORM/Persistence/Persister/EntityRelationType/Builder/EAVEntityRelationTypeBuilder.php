<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EntityRelationType\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\CommonFilters\FilterCriteria\FilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVPersistableInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\EAVHydratorInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EAVPersisterInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\PersistersFactory\EAVPersistersFactoryInterface;

class EAVEntityRelationTypeBuilder implements EAVEntityRelationTypeBuilderInterface
{

    protected EAVEntityManagerInterface $em;

    protected EAVHydratorInterface $hydrator;

    protected EAVPersisterInterface $namespacePersister;


    public function __construct(
        EAVEntityManagerInterface $em,
        EAVEntityRelationTypeHydrator $hydrator,
        EAVPersistersFactoryInterface $persistersFactory
    ) {
        $this->em                 = $em;
        $this->hydrator           = $hydrator;
        $this->namespacePersister = $persistersFactory->getForEntityClass($this->em->getEavSettings()->getClassForEntityType(EAVSettings::NAMESPACE), $em);
    }


    public function buildEntities(array $entityRows): array
    {
        $namespaceIds = array_values(array_unique(array_column($entityRows, 'namespace_id')));

        /** @var EAVNamespaceInterface[] $namespaces */
        $namespaces = $this->namespacePersister->loadByCriteria([ (new FilterCriteria())->whereIn('id', $namespaceIds) ]);

        $combinedEntityRows = $this->combineRows($entityRows, $namespaces);

        return $this->hydrator->hydrate($combinedEntityRows);
    }


    protected function combineRows(array $entityRows, array $namespaces): array
    {
        $namespaces = array_combine(array_map(static function (EAVNamespaceInterface $namespace) { return $namespace->getId(); }, $namespaces), $namespaces);

        foreach ($entityRows as &$entityRow) {
            $entityRow['_namespace'] = $namespaces[$entityRow['namespace_id']];
        }
        unset($entityRow);

        return $entityRows;
    }


    public function extractData(EAVPersistableInterface $entity): array
    {
        return $this->hydrator->extract($entity);
    }

}