<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EntityRelationType\Builder;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\AbstractSimpleHydrator;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Hydrator\EAVHydratorInterface;

class EAVEntityRelationTypeHydrator extends AbstractSimpleHydrator implements EAVHydratorInterface
{

    public function getEntityClass(): string
    {
        return $this->em->getEavSettings()->getClassForEntityType(EAVSettings::ENTITY_RELATION_TYPE);
    }


    protected function getDbExcludeFields(): array
    {
        return [ 'namespace_id' ];
    }


    protected function getHydrationCallback(): ?callable
    {
        return static function (object $entity, array $entityData) {

            $entity->namespace = $entityData['_namespace'];
        };
    }


    protected function getExtractionCallback(): ?callable
    {
        return static function (array &$data, object $object) {
            $data['namespace_id'] = $object->getNamespace()->getId();
        };
    }


    protected function removeTemporaryKeys(array $data): array
    {
        unset($data['_namespace']);

        return $data;
    }
}