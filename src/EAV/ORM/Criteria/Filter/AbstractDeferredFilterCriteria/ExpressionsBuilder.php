<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractDeferredFilterCriteria;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\ParametersCounter;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\FilterExpression;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereBetween;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereClause;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereComposite;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereInClause;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereIsNotNullClause;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereIsNullClause;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\WhereNotInClause;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\FilterCriteriaInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

class ExpressionsBuilder
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


    public function analyzeMethod(string $methodString): array
    {
        $isAnd = true;

        if (strpos($methodString, 'or') === 0) {
            $methodString = substr($methodString, 2);
            $isAnd        = false;
        }

        return [ 'build' . ucfirst($methodString) . 'Expression', $isAnd ];
    }


    public function makeArgumentsForRegularClause(array $rawClause, ColumnInterface $column, bool $isAnd): array
    {
        $arguments = $rawClause['arguments'];
        array_unshift($arguments, $column);

        $arguments[] = $isAnd;

        return $arguments;
    }


    public function makeArgumentsForComposite(array $rawClause, bool $isAnd): array
    {
        $arguments   = $rawClause['arguments'];
        $arguments[] = $isAnd;

        return $arguments;
    }


    public function buildNeverTrueExpression(bool $isAnd): FilterExpression
    {
        return new FilterExpression('false', [], [], $isAnd);
    }


    public function buildWhereExpression(ColumnInterface $column, string $operator, $value, bool $isAnd): FilterExpression
    {

        return (new WhereClause($column, $operator, $value, $this->parameterCounter, $isAnd))->getExpression($this->qb, $this->eavSettings);
    }


    public function buildWhereCompositeClauseFromArray(array $expressions, $isAnd): FilterExpression
    {
        $joinParams = [];
        $parameters = [];

        $andExpressions = [];
        $orExpressions  = [];

        foreach ($expressions as $expression) {
            foreach ($expression->getJoinTableParams() as $joinTableParam) {
                $joinParams[] = $joinTableParam;
            }

            $parameters = [ ...$parameters, ...$expression->getParameters() ];

            if ($expression->isAnd()) {
                $andExpressions[] = $expression->getExpression();
            } else {
                $orExpressions[] = $expression->getExpression();
            }
        }

        $and = $this->qb->expr()->andX()->addMultiple($andExpressions);
        $or  = $this->qb->expr()->orX()->addMultiple($orExpressions);

        $composite = $this->qb->expr()->andX()->add($and)->add($or);

        return new FilterExpression($composite, $parameters, $joinParams, $isAnd);
    }


    public function buildWhereInExpression(ColumnInterface $column, array $values, bool $isAnd): FilterExpression
    {
        return (new WhereInClause($column, $values, $this->parameterCounter, $isAnd))->getExpression($this->qb, $this->eavSettings);
    }


    public function buildWhereNotInExpression(ColumnInterface $column, array $values, bool $isAnd): FilterExpression
    {
        return (new WhereNotInClause($column, $values, $this->parameterCounter, $isAnd))->getExpression($this->qb, $this->eavSettings);
    }


    public function buildWhereIsNullExpression(ColumnInterface $column, bool $isAnd): FilterExpression
    {
        return (new WhereIsNullClause($column, $this->parameterCounter, $isAnd))->getExpression($this->qb, $this->eavSettings);
    }


    public function buildWhereIsNotNullExpression(ColumnInterface $column, bool $isAnd): FilterExpression
    {
        return (new WhereIsNotNullClause($column, $this->parameterCounter, $isAnd))->getExpression($this->qb, $this->eavSettings);
    }


    public function buildWhereBetweenExpression(ColumnInterface $column, $value1, $value2, bool $isAnd): FilterExpression
    {
        return (new WhereBetween($column, $value1, $value2, $this->parameterCounter, $isAnd))->getExpression($this->qb, $this->eavSettings);
    }


    public function buildWhereCompositeExpression(callable $innerCriteriaCallback, bool $isAnd): FilterExpression
    {
        /** @var FilterCriteriaInterface $freshCriteria */
        $freshCriteria = new $this->callerClass();

        $innerCriteriaCallback($freshCriteria);

        return (new WhereComposite($freshCriteria, $isAnd))->getExpression($this->qb, $this->eavSettings);
    }
}