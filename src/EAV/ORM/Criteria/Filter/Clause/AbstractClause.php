<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\ParametersCounter;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

abstract class AbstractClause
{

    protected ColumnInterface $column;

    protected ParametersCounter $parametersCounter;


    public function getExpression(QueryBuilder $qb, EAVSettings $eavSettings): FilterExpression
    {
        $parameterName = $this->makePlaceholderParam();

        $fromPart = $qb->getQueryPart('from');

        if (count($fromPart) !== 1) {
            throw new \InvalidArgumentException('Multiple from tables doesn\'t supports!');
        }

        $tableName = $fromPart[0]['table'];

        return $this->makeExpression($qb, $tableName, $this->column, $eavSettings, $parameterName);

    }


    protected function makePlaceholderParam(): string
    {
        return $this->parametersCounter->getNext();
    }


    abstract protected function makeExpression(QueryBuilder $qb, string $tableName, ColumnInterface $column, EAVSettings $eavSettings, string $parameterName);

}