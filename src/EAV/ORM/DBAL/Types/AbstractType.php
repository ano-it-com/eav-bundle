<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types;

abstract class AbstractType
{

    abstract public function getCode(): int;

}