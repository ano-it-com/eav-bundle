<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EntityRelation\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\AbstractSimpleHydrator;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\EAVHydratorInterface;

class EAVEntityRelationHydrator extends AbstractSimpleHydrator implements EAVHydratorInterface
{

    public function getEntityClass(): string
    {
        return $this->em->getEavSettings()->getClassForEntityType(EAVSettings::ENTITY_RELATION);
    }


    protected function removeTemporaryKeys(array $data): array
    {
        unset($data['_type'], $data['_from'], $data['_to'], $data['_namespace']);

        return $data;
    }


    protected function getHydrationCallback(): ?callable
    {
        return static function (object $object, array $data) {
            $object->from      = $data['_from'];
            $object->to        = $data['_to'];
            $object->type      = $data['_type'];
            $object->namespace = $data['_namespace'];
        };
    }


    protected function getExtractionCallback(): ?callable
    {
        return static function (array &$data, object $object) {
            $data['from_id']      = $object->getFrom()->getId();
            $data['to_id']        = $object->getTo()->getId();
            $data['type_id']      = $object->getType()->getId();
            $data['namespace_id'] = $object->getNamespace()->getId();
        };
    }


    protected function getDbExcludeFields(): array
    {
        return [
            'type_id',
            'from_id',
            'to_id',
            'namespace_id',
        ];
    }

}