<?php

namespace ANOITCOM\EAVBundle\Tests\Functional\Entity;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\CommonFilters\FilterCriteria\FilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\BasicJsonMetaType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypes;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntityInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntityPropertyValue;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntityPropertyValueInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Meta\BasicMeta;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManager;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVNamespaceRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepository;
use ANOITCOM\EAVBundle\Tests\Functional\Helpers\EntitiesFactory;
use ANOITCOM\EAVBundle\Tests\TestCases\BundleWithPostgresTestCase;
use DateTime;
use Ramsey\Uuid\Uuid;

class EntityPersistenceTest extends BundleWithPostgresTestCase
{

    /** @var EAVEntityManager */
    private $em;

    private $entityRepository;

    private $typeRepository;

    private $valueTypes;

    private $entitiesFactory;

    private $namespaceRepository;


    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }


    protected function setUp(): void
    {
        parent::setUp();

        $this->em                  = self::$container->get(EAVEntityManager::class);
        $this->namespaceRepository = self::$container->get(EAVNamespaceRepository::class);
        $this->entityRepository    = self::$container->get(EAVEntityRepository::class);
        $this->typeRepository      = self::$container->get(EAVTypeRepository::class);
        $this->valueTypes          = self::$container->get(ValueTypes::class);
        $this->entitiesFactory     = new EntitiesFactory($this->em, $this->namespaceRepository);
    }


    public function testPersistEntityWithValues(): void
    {
        $entity = $this->createEntityFlushAndClear();

        // test entity
        $entityFromDb = $this->getFromDbById($entity->getId());

        $this->compareTwoEntities($entity, $entityFromDb);
    }


    public function testUpdateEntityWithValues(): void
    {
        $entity = $this->createEntityFlushAndClear();

        // test entity
        $entityFromDb = $this->getFromDbById($entity->getId());

        $date      = new DateTime();
        $basicMeta = new BasicMeta([ 'created_at' => $date, 'updated_at' => $date ]);

        $entityFromDb->setMeta($basicMeta);

        foreach ($entityFromDb->getValues() as $value) {
            $value->setValue($value->getValue() . '_updated');
        }

        $this->em->flush();
        $this->em->clear();

        $entityFromDb2 = $this->getFromDbById($entityFromDb->getId());

        $this->compareTwoEntities($entityFromDb, $entityFromDb2);


    }


    public function testDeleteEntityWithProperties(): void
    {
        $entity = $this->createEntityFlushAndClear();

        // test entity
        $entityFromDb = $this->getFromDbById($entity->getId());

        $this->em->remove($entityFromDb);
        $this->em->flush();
        $this->em->clear();

        $entityFromDb2 = $this->getFromDbById($entityFromDb->getId());

        self::assertNull($entityFromDb2);
    }


    public function testAddDeleteChangeValues(): void
    {
        $entity = $this->createEntityFlushAndClear();

        // test entity
        $entityFromDb = $this->getFromDbById($entity->getId());

        $values = $entityFromDb->getValues();

        // deleted
        $deletedValue = array_shift($values);

        $deletedPropertyType = null;
        $type                = $entity->getType();
        $property            = $type->getPropertyById($deletedValue->getTypePropertyId());

        $propertyValueClass = $this->em->getEavSettings()->getClassForEntityType(EAVSettings::VALUES);

        // new
        $newValue = new $propertyValueClass(Uuid::uuid4(), $entity->getNamespace(), $property);
        $newValue->setValue('new value');

        $values[] = $newValue;

        //updated
        /** @var EAVEntityPropertyValue $valueToChange */
        $valueToChange = reset($values);
        $valueToChange->setValue('updated');

        $entityFromDb->setValues($values);

        $this->em->flush();
        $this->em->clear();

        $entityFromDb2 = $this->getFromDbById($entityFromDb->getId());

        $this->compareTwoEntities($entityFromDb, $entityFromDb2);
    }


    private function getFromDbById(string $id): ?EAVEntityInterface
    {
        /** @var EAVEntityInterface [] $entitiesFromDb */
        $entitiesFromDb = $this->entityRepository->findBy([ (new FilterCriteria())->where('id', '=', $id) ]);

        return count($entitiesFromDb) ? $entitiesFromDb[0] : null;
    }


    private function compareTwoEntities(EAVEntityInterface $entity1, EAVEntityInterface $entity2): void
    {
        self::assertNotSame($entity1, $entity2);
        self::assertEquals($entity1->getId(), $entity2->getId());
        self::assertEquals($entity1->getNamespace()->getId(), $entity2->getNamespace()->getId());
        self::assertEquals($entity1->getType()->getId(), $entity2->getType()->getId());

        self::assertTrue($this->entitiesFactory->isMetaEquals($entity1->getMeta(), $entity2->getMeta()));

        //test values
        $valuesFromDb = $entity2->getValues();
        $valuesFromDb = array_combine(array_map(function (EAVEntityPropertyValueInterface $value) { return $value->getId(); }, $valuesFromDb), $valuesFromDb);

        self::assertSameSize($entity1->getValues(), $valuesFromDb);

        /**
         * @var string                          $id
         * @var EAVEntityPropertyValueInterface $value
         */
        foreach ($entity1->getValues() as $value) {
            $valueFromDB = $valuesFromDb[$value->getId()] ?? null;
            self::assertNotNull($valueFromDB);

            self::assertEquals($value->getId(), $valueFromDB->getId());
            self::assertEquals($value->getTypePropertyId(), $valueFromDB->getTypePropertyId());
            self::assertEquals($value->getValue(), $valueFromDB->getValue());
            self::assertEquals($value->getValueTypeCode(), $valueFromDB->getValueTypeCode());
        }
    }


    /**
     * @return array
     */
    private function createEntityFlushAndClear(): EAVEntityInterface
    {
        $namespace = $this->entitiesFactory->getOrCreateNamespaceAndFlush();
        $type      = $this->entitiesFactory->createType();
        $entity    = $this->entitiesFactory->createEntity($namespace, $type);

        $this->em->persist($type);
        $this->em->persist($entity);

        $this->em->flush();
        $this->em->clear();

        return $entity;
    }
}