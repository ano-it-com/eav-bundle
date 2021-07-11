<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractDeferredFilterCriteria;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\ParametersCounter;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\FilterExpression;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\EntityFilters\EntityPropertyValue\EntityPropertyValueColumn;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\PropertyFinder\PropertyInfo;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

class DeferredExpressionsMaker
{

    private QueryBuilder $qb;

    private ParametersCounter $parameterCounter;

    private EAVSettings $eavSettings;

    private string $callerClass;


    public function __construct(
        QueryBuilder $qb,
        ParametersCounter $parameterCounter,
        EAVSettings $eavSettings,
        string $callerClass
    ) {
        $this->qb               = $qb;
        $this->parameterCounter = $parameterCounter;
        $this->eavSettings      = $eavSettings;
        $this->callerClass      = $callerClass;
    }


    /**
     * @param array          $rawClause
     * @param string|null    $field
     * @param PropertyInfo[] $propertyVariantsForField
     *
     * @return FilterExpression
     */
    public function makeExpression(array $rawClause, ?string $field, array $propertyVariantsForField): FilterExpression
    {
        $expressionsBuilder = new ExpressionsBuilder($this->qb, $this->parameterCounter, $this->eavSettings, $this->callerClass);

        $methodString = $rawClause['method'];

        [ $method, $isAnd ] = $expressionsBuilder->analyzeMethod($methodString);

        if ( ! $field) {
            // composite
            $arguments = $expressionsBuilder->makeArgumentsForComposite($rawClause, $isAnd);

            return $expressionsBuilder->{$method}(...$arguments);
        }

        if ( ! count($propertyVariantsForField)) {
            return $expressionsBuilder->buildNeverTrueExpression($isAnd);
        }

        $expressions = [];

        $joinAlias = 'alias_' . md5($field);
        foreach ($propertyVariantsForField as $propertyInfo) {
            $valueColumn        = new EntityPropertyValueColumn($this->eavSettings->getColumnNameForValueType($propertyInfo->getValueType()), $joinAlias);
            $propertyTypeColumn = new EntityPropertyValueColumn('type_property_id', $joinAlias);

            if ( ! method_exists($expressionsBuilder, $method)) {
                throw new \InvalidArgumentException('Method ' . $method . ' not found');
            }

            $arguments = $expressionsBuilder->makeArgumentsForRegularClause($rawClause, $valueColumn, $forceIsAnd = true);

            $valueExpression        = $expressionsBuilder->{$method}(...$arguments);
            $propertyTypeExpression = $expressionsBuilder->buildWhereExpression($propertyTypeColumn, '=', $propertyInfo->getId(), true);

            $expressions[] = $expressionsBuilder->buildWhereCompositeClauseFromArray([ $valueExpression, $propertyTypeExpression ], false);
        }

        return $expressionsBuilder->buildWhereCompositeClauseFromArray($expressions, $isAnd);
    }

}