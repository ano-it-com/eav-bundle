<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\Clause;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\ParametersCounter;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use Doctrine\DBAL\Query\QueryBuilder;

class WhereBetween extends AbstractClause implements ClauseInterface
{

    private $value1;

    private $value2;

    private bool $isAnd;


    public function __construct(ColumnInterface $column, $value1, $value2, ParametersCounter $parametersCounter, bool $isAnd)
    {
        $this->column            = $column;
        $this->value1            = $value1;
        $this->value2            = $value2;
        $this->parametersCounter = $parametersCounter;
        $this->isAnd             = $isAnd;
    }


    protected function makeExpression(QueryBuilder $qb, string $tableName, ColumnInterface $column, EAVSettings $eavSettings, string $parameterName): FilterExpression
    {
        $expr = $qb->expr()->andX(
            $expr1 = $qb->expr()->gte($column->getFullName($tableName), ':' . $parameterName . '1'),
            $expr2 = $qb->expr()->lte($column->getFullName($tableName), ':' . $parameterName . '2')
        );

        return new FilterExpression(
            $expr,
            [
                [ $parameterName . '1', $this->value1 ],
                [ $parameterName . '2', $this->value2 ],
            ],
            $this->column->getJoinTables($eavSettings), $this->isAnd);
    }
}