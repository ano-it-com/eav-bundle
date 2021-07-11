<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractDeferredFilterCriteria;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\ParametersCounter;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\BasicFilterCriteriaClausesInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause\FilterExpression;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

abstract class AbstractDeferredFilterCriteria implements BasicFilterCriteriaClausesInterface
{

    protected array $clausesRaw = [];

    protected ParametersCounter $parameterCounter;


    public function __construct()
    {
        $this->parameterCounter = new ParametersCounter();
    }


    public function where(string $propertyId, string $operator, $value): self
    {
        $this->clausesRaw[] = [
            'method'    => 'where',
            'field'     => $propertyId,
            'arguments' => [ $operator, $value ]
        ];

        return $this;
    }


    public function orWhere(string $propertyId, string $operator, $value): self
    {
        $this->clausesRaw[] = [
            'method'    => 'orWhere',
            'field'     => $propertyId,
            'arguments' => [ $operator, $value ]
        ];

        return $this;
    }


    public function whereIn(string $propertyId, array $values): self
    {
        $this->clausesRaw[] = [
            'method'    => 'whereIn',
            'field'     => $propertyId,
            'arguments' => [ $values ]
        ];

        return $this;
    }


    public function orWhereIn(string $propertyId, array $values): self
    {
        $this->clausesRaw[] = [
            'method'    => 'orWhereIn',
            'field'     => $propertyId,
            'arguments' => [ $values ]
        ];

        return $this;
    }


    public function whereNotIn(string $propertyId, array $values): self
    {
        $this->clausesRaw[] = [
            'method'    => 'whereNotIn',
            'field'     => $propertyId,
            'arguments' => [ $values ]
        ];

        return $this;
    }


    public function orWhereNotIn(string $propertyId, array $values): self
    {
        $this->clausesRaw[] = [
            'method'    => 'orWhereNotIn',
            'field'     => $propertyId,
            'arguments' => [ $values ]
        ];

        return $this;
    }


    public function whereIsNull(string $propertyId): self
    {
        $this->clausesRaw[] = [
            'method'    => 'whereIsNull',
            'field'     => $propertyId,
            'arguments' => []
        ];

        return $this;
    }


    public function orWhereIsNull(string $propertyId): self
    {
        $this->clausesRaw[] = [
            'method'    => 'orWhereIsNull',
            'field'     => $propertyId,
            'arguments' => []
        ];

        return $this;
    }


    public function whereIsNotNull(string $propertyId): self
    {
        $this->clausesRaw[] = [
            'method'    => 'whereIsNotNull',
            'field'     => $propertyId,
            'arguments' => []
        ];

        return $this;
    }


    public function orWhereIsNotNull(string $propertyId): self
    {
        $this->clausesRaw[] = [
            'method'    => 'orWhereIsNotNull',
            'field'     => $propertyId,
            'arguments' => []
        ];

        return $this;
    }


    public function whereBetween(string $propertyId, $value1, $value2): self
    {
        $this->clausesRaw[] = [
            'method'    => 'whereBetween',
            'field'     => $propertyId,
            'arguments' => [ $value1, $value2 ]
        ];

        return $this;
    }


    public function orWhereBetween(string $propertyId, $value1, $value2): self
    {
        $this->clausesRaw[] = [
            'method'    => 'orWhereBetween',
            'field'     => $propertyId,
            'arguments' => [ $value1, $value2 ]
        ];

        return $this;
    }


    public function whereComposite(callable $innerCriteriaCallback): self
    {
        $this->clausesRaw[] = [
            'method'    => 'whereComposite',
            'field'     => null,
            'arguments' => [ $innerCriteriaCallback ]
        ];

        return $this;
    }


    public function orWhereComposite(callable $innerCriteriaCallback): self
    {
        $this->clausesRaw[] = [
            'method'    => 'orWhereComposite',
            'field'     => null,
            'arguments' => [ $innerCriteriaCallback ]
        ];

        return $this;
    }


    /**
     * @param QueryBuilder $qb
     *
     * @param EAVSettings  $eavSettings
     *
     * @return FilterExpression[]
     */
    abstract public function getExpressions(QueryBuilder $qb, EAVSettings $eavSettings): array;


    abstract public function supports(string $entityType): bool;
}