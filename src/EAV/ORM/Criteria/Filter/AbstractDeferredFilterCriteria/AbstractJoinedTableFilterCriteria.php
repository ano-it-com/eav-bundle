<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractDeferredFilterCriteria;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\FilterExpression;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

abstract class AbstractJoinedTableFilterCriteria extends AbstractDeferredFilterCriteria
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
        $expressionsBuilder = new ExpressionsBuilder($qb, $this->parameterCounter, $eavSettings, static::class);

        $expressions = [];

        foreach ($this->clausesRaw as $rawClause) {
            $field        = $rawClause['field'];
            $methodString = $rawClause['method'];

            [ $method, $isAnd ] = $expressionsBuilder->analyzeMethod($methodString);

            if ( ! $field) {
                // composite
                $arguments = $expressionsBuilder->makeArgumentsForComposite($rawClause, $isAnd);

                return $expressionsBuilder->{$method}(...$arguments);
            }

            $column = $this->makeColumn($field, $eavSettings);

            if ( ! method_exists($expressionsBuilder, $method)) {
                throw new \InvalidArgumentException('Method ' . $method . ' not found');
            }

            $arguments = $expressionsBuilder->makeArgumentsForRegularClause($rawClause, $column, $isAnd);

            $metaExpression = $expressionsBuilder->{$method}(...$arguments);

            $expressions[] = $metaExpression;
        }

        return $expressions;

    }


    abstract protected function makeColumn($field, EAVSettings $eavSettings): ColumnInterface;
}