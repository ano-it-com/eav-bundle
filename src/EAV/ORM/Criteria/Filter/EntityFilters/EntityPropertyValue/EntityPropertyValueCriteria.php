<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\EntityFilters\EntityPropertyValue;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractDeferredFilterCriteria\AbstractDeferredFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractDeferredFilterCriteria\DeferredExpressionsMaker;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\FilterExpression;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\PropertyFinder\PropertyFinder;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

class EntityPropertyValueCriteria extends AbstractDeferredFilterCriteria
{

    /**
     * @param QueryBuilder $qb
     *
     * @param EAVSettings  $eavSettings
     *
     * @return FilterExpression[]
     */
    public function getExpressions(QueryBuilder $qb, EAVSettings $eavSettings): array
    {
        $propertyIds = array_filter(array_values(array_unique(array_column($this->clausesRaw, 'field'))), function ($alias) { return $alias !== null; });

        $propertyFinder = new PropertyFinder($qb->getConnection());

        $propertyVariantsForIds = $propertyFinder->getPropertyTypeVariantsByIds($propertyIds);

        $expressions = [];

        $deferredExpressionsMaker = new DeferredExpressionsMaker($qb, $this->parameterCounter, $eavSettings, static::class);

        foreach ($this->clausesRaw as $rawClause) {
            $propertyId = $rawClause['field'];

            $propertyVariantsForId = $propertyVariantsForIds[$propertyId] ?? [];

            $expressions[] = $deferredExpressionsMaker->makeExpression($rawClause, $propertyId, $propertyVariantsForId);
        }

        return $expressions;

    }


    public function supports(string $entityType): bool
    {
        return $entityType === EAVSettings::ENTITY;
    }
}