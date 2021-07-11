<?php

namespace ANOITCOM\EAVBundle\Tests\Functional\Type;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\CommonFilters\FilterCriteria\FilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\BasicJsonMetaType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\TextType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypeInterface;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypes;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Meta\BasicMeta;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Type\EAVTypeInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Type\EAVTypeProperty;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Type\EAVTypePropertyInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManager;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVNamespaceRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepository;
use ANOITCOM\EAVBundle\Tests\Functional\Helpers\EntitiesFactory;
use ANOITCOM\EAVBundle\Tests\TestCases\BundleWithPostgresTestCase;
use DateTime;
use Ramsey\Uuid\Uuid;

class TypePersistenceTest extends BundleWithPostgresTestCase
{

    /** @var EAVEntityManager */
    private $em;

    private $typeRepository;

    private $valueTypes;

    private $entitiesFactory;

    private $namespaceRepository;


    protected function setUp(): void
    {
        parent::setUp();

        $this->em                  = self::$container->get(EAVEntityManager::class);
        $this->namespaceRepository = self::$container->get(EAVNamespaceRepository::class);
        $this->typeRepository      = self::$container->get(EAVTypeRepository::class);
        $this->valueTypes          = self::$container->get(ValueTypes::class);
        $this->entitiesFactory     = new EntitiesFactory($this->em, $this->namespaceRepository);
    }


    public function testPersistTypeWithProperties(): void
    {
        $type = $this->createTypeFlushAndClear();

        // test type
        $typeFromDb = $this->getFromDbById($type->getId());

        $this->compareTwoTypes($type, $typeFromDb);
    }


    public function testUpdateTypeWithProperties(): void
    {
        $type = $this->createTypeFlushAndClear();

        $typeFromDb = $this->getFromDbById($type->getId());

        $typeFromDb->setTitle($typeFromDb->getTitle() . '_updated');
        $typeFromDb->setAlias($typeFromDb->getAlias() . '_updated');

        $date      = new DateTime();
        $basicMeta = new BasicMeta([ 'created_at' => $date, 'updated_at' => $date ]);

        $typeFromDb->setMeta($basicMeta);

        foreach ($typeFromDb->getProperties() as $property) {
            $property->setAlias($property->getAlias() . '_updated');
            $property->setTitle($property->getAlias() . '_updated');
            $property->setMeta($basicMeta);
        }

        $this->em->flush();
        $this->em->clear();

        $typeFromDb2 = $this->getFromDbById($typeFromDb->getId());

        $this->compareTwoTypes($typeFromDb, $typeFromDb2);


    }


    public function testDeleteTypeWithProperties(): void
    {
        $type = $this->createTypeFlushAndClear();

        $typeFromDb = $this->getFromDbById($type->getId());

        $this->em->remove($typeFromDb);
        $this->em->flush();
        $this->em->clear();

        $typeFromDb2 = $this->getFromDbById($typeFromDb->getId());

        self::assertNull($typeFromDb2);
    }


    public function testAddDeleteChangeProperties(): void
    {
        $type = $this->createTypeFlushAndClear();

        $typeFromDb = $this->getFromDbById($type->getId());

        $properties = $type->getProperties();

        // deleted
        $deletedProperty = array_shift($properties);

        $typePropertyClass = $this->em->getEavSettings()->getClassForEntityType(EAVSettings::TYPE_PROPERTY);

        // new
        $newProperty = new $typePropertyClass(Uuid::uuid4()->toString(), $deletedProperty->getNamespace(), $type, new TextType());
        $newProperty->setAlias('property_alias');
        $newProperty->setTitle('property_title');
        $properties[] = $newProperty;

        //updated
        /** @var EAVTypeProperty $propToChange */
        $propToChange = reset($properties);
        $propToChange->setTitle('updated');
        $propToChange->setAlias('updated');

        $typeFromDb->setProperties($properties);

        $this->em->flush();
        $this->em->clear();

        $typeFromDb2 = $this->getFromDbById($typeFromDb->getId());

        $this->compareTwoTypes($typeFromDb, $typeFromDb2);
    }


    private function getFromDbById(string $id): ?EAVTypeInterface
    {
        /** @var EAVTypeInterface[] $typesFromDb */
        $typesFromDb = $this->typeRepository->findBy([ (new FilterCriteria())->where('id', '=', $id) ]);

        return count($typesFromDb) ? $typesFromDb[0] : null;
    }


    private function compareTwoTypes(EAVTypeInterface $type1, EAVTypeInterface $type2): void
    {
        self::assertNotSame($type1, $type2);
        self::assertEquals($type1->getId(), $type2->getId());
        self::assertEquals($type1->getNamespace()->getId(), $type2->getNamespace()->getId());
        self::assertEquals($type1->getAlias(), $type2->getAlias());
        self::assertEquals($type1->getTitle(), $type2->getTitle());

        self::assertTrue($this->entitiesFactory->isMetaEquals($type1->getMeta(), $type2->getMeta()));

        //test properties
        $propertiesFromDb = $type2->getProperties();
        $propertiesFromDb = array_combine(array_map(function (EAVTypePropertyInterface $property) { return $property->getId(); }, $propertiesFromDb), $propertiesFromDb);

        self::assertSameSize($type1->getProperties(), $propertiesFromDb);

        /**
         * @var string          $id
         * @var EAVTypeProperty $property
         */
        foreach ($type1->getProperties() as $property) {
            $propertyFromDB = $propertiesFromDb[$property->getId()] ?? null;
            self::assertNotNull($propertyFromDB);

            self::assertEquals($property->getId(), $propertyFromDB->getId());
            self::assertEquals($property->getTitle(), $propertyFromDB->getTitle());
            self::assertEquals($property->getAlias(), $propertyFromDB->getAlias());
            self::assertEquals($property->getTypeId(), $propertyFromDB->getTypeId());
            self::assertEquals($property->getValueType()->getCode(), $propertyFromDB->getValueType()->getCode());
        }
    }


    /**
     * @return EAVTypeInterface
     */
    private function createTypeFlushAndClear(): EAVTypeInterface
    {
        $type = $this->entitiesFactory->createType();
        $this->em->persist($type);
        $this->em->flush();
        $this->em->clear();

        return $type;
    }

}