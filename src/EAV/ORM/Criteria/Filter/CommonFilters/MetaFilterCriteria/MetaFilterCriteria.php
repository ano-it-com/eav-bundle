<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\CommonFilters\MetaFilterCriteria;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\AbstractFilterCriteria\AbstractFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\BasicFilterCriteriaClausesInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\ColumnInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\CommonFilters\MetaFilterCriteria\BasicMetaColumn;

class MetaFilterCriteria extends AbstractFilterCriteria implements BasicFilterCriteriaClausesInterface
{

    protected function getColumn(string $field): ColumnInterface
    {
        return new BasicMetaColumn($field);
    }


    public function supports(string $entityType): bool
    {
        return true;
    }

}