<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\CommonFilters\FilterCriteria;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\AbstractFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\BasicFilterCriteriaClausesInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;

class FilterCriteria extends AbstractFilterCriteria implements BasicFilterCriteriaClausesInterface
{

    protected function getColumn(string $field): ColumnInterface
    {
        return new BasicColumn($field);
    }


    public function supports(string $entityType): bool
    {
        return true;
    }
}