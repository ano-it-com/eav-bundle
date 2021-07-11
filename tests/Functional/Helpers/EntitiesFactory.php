<?php

namespace ANOITCOM\EAVBundle\Tests\Functional\Helpers;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\CommonFilters\FilterCriteria\FilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\TextType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntityInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EntityRelation\EAVEntityRelation;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EntityRelation\EAVEntityRelationTypeInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Meta\BasicMeta;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Meta\JsonMetaInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Type\EAVTypeInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManager;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVNamespaceRepositoryInterface;
use Ramsey\Uuid\Uuid;

class EntitiesFactory
{

    private EAVEntityManager $em;

    private EAVNamespaceRepositoryInterface $namespaceRepository;


    public function __construct(EAVEntityManager $em, EAVNamespaceRepositoryInterface $namespaceRepository)
    {
        $this->em                  = $em;
        $this->namespaceRepository = $namespaceRepository;
    }


    public function createEntity(EAVNamespaceInterface $namespace, EAVTypeInterface $type): EAVEntityInterface
    {
        $entityClass      = $this->em->getEavSettings()->getClassForEntityType(EAVSettings::ENTITY);
        $entityValueClass = $this->em->getEavSettings()->getClassForEntityType(EAVSettings::VALUES);

        $date      = new \DateTime('2020-01-01');
        $basicMeta = new BasicMeta([ 'created_at' => $date, 'updated_at' => $date ]);

        $entity = new $entityClass(Uuid::uuid4(), $namespace, $type);

        $entity->setMeta($basicMeta);
        $values = [];
        foreach ($type->getProperties() as $property) {
            $value       = $property->getAlias() . '_value';
            $entityValue = new $entityValueClass(Uuid::uuid4(), $namespace, $property);
            $entityValue->setValue($value);
            $entityValue->setMeta($basicMeta);
            $values[] = $entityValue;
        }

        $entity->setValues($values);

        return $entity;

    }


    public function getOrCreateNamespaceAndFlush(): EAVNamespaceInterface
    {
        $namespace = $this->namespaceRepository->findOneBy([ (new FilterCriteria())->where('iri', '=', 'http://test.iri') ]);

        if ( ! $namespace) {
            $namespaceClass = $this->em->getEavSettings()->getClassForEntityType(EAVSettings::NAMESPACE);

            $date = new \DateTime('2020-01-01');

            $basicMeta = new BasicMeta([ 'created_at' => $date, 'updated_at' => $date ]);

            /** @var EAVNamespaceInterface $namespace */
            $namespace = new $namespaceClass(Uuid::uuid4()->toString(), 'http://test.iri');
            $namespace->setTitle('Title');
            $namespace->setComment('Comment');
            $namespace->setMeta($basicMeta);

            $this->em->persist($namespace);
            $this->em->flush();
        }

        return $namespace;
    }


    public function createType(): EAVTypeInterface
    {
        $namespace = $this->getOrCreateNamespaceAndFlush();

        $typeClass         = $this->em->getEavSettings()->getClassForEntityType(EAVSettings::TYPE);
        $typePropertyClass = $this->em->getEavSettings()->getClassForEntityType(EAVSettings::TYPE_PROPERTY);

        $date = new \DateTime('2020-01-01');

        $basicMeta = new BasicMeta([ 'created_at' => $date, 'updated_at' => $date ]);

        $type = new $typeClass(Uuid::uuid4()->toString(), $namespace);
        $type->setAlias('alias');
        $type->setTitle('title');
        $type->setMeta($basicMeta);

        $properties = [];

        for ($i = 0; $i <= 5; $i++) {
            $prop = new $typePropertyClass(Uuid::uuid4()->toString(), $namespace, $type, new TextType());
            $prop->setAlias('property_alias_' . $i);
            $prop->setTitle('property_title_' . $i);
            $prop->setMeta($basicMeta);

            $properties[] = $prop;
        }

        $type->setProperties(array_values($properties));

        return $type;
    }


    public function createEntityRelationType(): EAVEntityRelationTypeInterface
    {
        $namespace = $this->getOrCreateNamespaceAndFlush();

        $date = new \DateTime('2020-01-01');

        $basicMeta = new BasicMeta([ 'created_at' => $date, 'updated_at' => $date ]);

        $typeClass = $this->em->getEavSettings()->getClassForEntityType(EAVSettings::ENTITY_RELATION_TYPE);

        $relationType = new $typeClass(Uuid::uuid4(), $namespace);
        $relationType->setAlias('alias');
        $relationType->setTitle('title');
        $relationType->setMeta($basicMeta);

        return $relationType;
    }


    public function createEntityRelationAndFlush(): EAVEntityRelation
    {
        $namespace = $this->getOrCreateNamespaceAndFlush();
        // создаем тип
        $type = $this->createType();
        $this->em->persist($type);
        // создаем объекты
        $entity1 = $this->createEntity($namespace, $type);
        $entity2 = $this->createEntity($namespace, $type);
        $this->em->persist($entity1);
        $this->em->persist($entity2);
        // создаем тип релейшана
        $relationType = $this->createEntityRelationType();
        $this->em->persist($relationType);
        // создаем релейшан

        $date      = new \DateTime('2020-01-01');
        $basicMeta = new BasicMeta([ 'created_at' => $date, 'updated_at' => $date ]);

        $typeClass = $this->em->getEavSettings()->getClassForEntityType(EAVSettings::ENTITY_RELATION);

        $relation = new $typeClass(Uuid::uuid4(), $namespace, $relationType);
        $relation->setFrom($entity1);
        $relation->setTo($entity2);
        $relation->setMeta($basicMeta);

        $this->em->persist($relation);

        $this->em->flush();

        return $relation;
    }


    public function isMetaEquals(JsonMetaInterface $meta1, JsonMetaInterface $meta2): bool
    {
        $a1 = $meta1->toArray();
        $a2 = $meta2->toArray();

        $stringedMeta1 = json_decode(json_encode($a1, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        $stringedMeta2 = json_decode(json_encode($a2, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);

        return $stringedMeta1 == $stringedMeta2;
    }

}