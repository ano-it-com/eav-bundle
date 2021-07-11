<?php

namespace ANOITCOM\EAVBundle\Tests\Functional\EntityManager;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\CommonFilters\FilterCriteria\FilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManager;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVNamespaceRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepository;
use ANOITCOM\EAVBundle\Tests\Functional\Helpers\EntitiesFactory;
use ANOITCOM\EAVBundle\Tests\TestCases\BundleWithPostgresTestCase;

class EntityManagerTest extends BundleWithPostgresTestCase
{

    /** @var EAVEntityManager */
    protected $em;

    /** @var EAVNamespaceRepository */
    protected $namespaceRepository;

    /** @var EAVTypeRepository */
    protected $typeRepository;

    /** @var EAVEntityRepository */
    protected $entityRepository;

    /** @var EAVEntityRelationTypeRepository */
    protected $relationTypeRepository;

    /** @var EAVEntityRelationRepository */
    protected $relationRepository;

    private $entitiesFactory;


    protected function setUp(): void
    {
        parent::setUp();

        $this->em                     = self::$container->get(EAVEntityManager::class);
        $this->namespaceRepository    = self::$container->get(EAVNamespaceRepository::class);
        $this->entityRepository       = self::$container->get(EAVEntityRepository::class);
        $this->typeRepository         = self::$container->get(EAVTypeRepository::class);
        $this->relationTypeRepository = self::$container->get(EAVEntityRelationTypeRepository::class);
        $this->relationRepository     = self::$container->get(EAVEntityRelationRepository::class);
        $this->entitiesFactory        = new EntitiesFactory($this->em, $this->namespaceRepository);
    }


    public function testEmReturnsNewNamespaceAfterClear(): void
    {
        $namespace = $this->entitiesFactory->getOrCreateNamespaceAndFlush();
        $this->em->clear();

        $namespacesFromDb = $this->namespaceRepository->findBy([ (new FilterCriteria())->where('id', '=', $namespace->getId()) ]);

        self::assertCount(1, $namespacesFromDb);

        $namespaceFromDb = reset($namespacesFromDb);

        self::assertInstanceOf($this->em->getEavSettings()->getClassForEntityType(EAVSettings::NAMESPACE), $namespaceFromDb);
        self::assertEquals($namespace->getId(), $namespaceFromDb->getId());
        self::assertNotSame($namespace, $namespaceFromDb);

    }


    public function testEmReturnsNewTypeAfterClear(): void
    {
        $type = $this->entitiesFactory->createType();

        $this->em->persist($type);
        $this->em->flush();
        $this->em->clear();

        $typesFromDb = $this->typeRepository->findBy([ (new FilterCriteria())->where('id', '=', $type->getId()) ]);

        self::assertCount(1, $typesFromDb);

        $typeFromDb = reset($typesFromDb);

        self::assertInstanceOf($this->em->getEavSettings()->getClassForEntityType(EAVSettings::TYPE), $typeFromDb);
        self::assertEquals($type->getId(), $typeFromDb->getId());
        self::assertNotSame($type, $typeFromDb);

    }


    public function testEmReturnsNewEntityAfterClear(): void
    {
        $namespace = $this->entitiesFactory->getOrCreateNamespaceAndFlush();
        $type      = $this->entitiesFactory->createType();
        $entity    = $this->entitiesFactory->createEntity($namespace, $type);

        $this->em->persist($type);
        $this->em->persist($entity);
        $this->em->flush();
        $this->em->clear();

        $entitiesFromDb = $this->entityRepository->findBy([ (new FilterCriteria())->where('id', '=', $entity->getId()) ]);

        self::assertCount(1, $entitiesFromDb);

        $entityFromDb = reset($entitiesFromDb);

        self::assertInstanceOf($this->em->getEavSettings()->getClassForEntityType(EAVSettings::ENTITY), $entityFromDb);
        self::assertEquals($entity->getId(), $entityFromDb->getId());
        self::assertNotSame($entity, $entityFromDb);

    }


    public function testEmReturnsNewRelationTypeAfterClear(): void
    {
        $relationType = $this->entitiesFactory->createEntityRelationType();

        $this->em->persist($relationType);
        $this->em->flush();
        $this->em->clear();

        $entitiesFromDb = $this->relationTypeRepository->findBy([ (new FilterCriteria())->where('id', '=', $relationType->getId()) ]);

        self::assertCount(1, $entitiesFromDb);

        $entityFromDb = reset($entitiesFromDb);

        self::assertInstanceOf($this->em->getEavSettings()->getClassForEntityType(EAVSettings::ENTITY_RELATION_TYPE), $entityFromDb);
        self::assertEquals($relationType->getId(), $entityFromDb->getId());
        self::assertNotSame($relationType, $entityFromDb);

    }


    public function testEmReturnsNewRelationAfterClear(): void
    {
        $relation = $this->entitiesFactory->createEntityRelationAndFlush();

        $this->em->clear();

        $entitiesFromDb = $this->relationRepository->findBy([ (new FilterCriteria())->where('id', '=', $relation->getId()) ]);

        self::assertCount(1, $entitiesFromDb);

        $entityFromDb = reset($entitiesFromDb);

        self::assertInstanceOf($this->em->getEavSettings()->getClassForEntityType(EAVSettings::ENTITY_RELATION), $entityFromDb);
        self::assertEquals($relation->getId(), $entityFromDb->getId());
        self::assertNotSame($relation, $entityFromDb);

    }


    public function testEmReturnsOldTypeWithoutClear(): void
    {
        $type = $this->entitiesFactory->createType();

        $this->em->persist($type);
        $this->em->flush();

        $typesFromDb = $this->typeRepository->findBy([ (new FilterCriteria())->where('id', '=', $type->getId()) ]);

        self::assertCount(1, $typesFromDb);

        $typeFromDb = reset($typesFromDb);

        self::assertInstanceOf($this->em->getEavSettings()->getClassForEntityType(EAVSettings::TYPE), $typeFromDb);
        self::assertEquals($type->getId(), $typeFromDb->getId());
        self::assertSame($type, $typeFromDb);

    }


    public function testEmReturnsOldNamespaceWithoutClear(): void
    {
        $namespace = $this->entitiesFactory->getOrCreateNamespaceAndFlush();

        $namespacesFromDb = $this->namespaceRepository->findBy([ (new FilterCriteria())->where('id', '=', $namespace->getId()) ]);

        self::assertCount(1, $namespacesFromDb);

        $namespaceFromDb = reset($namespacesFromDb);

        self::assertInstanceOf($this->em->getEavSettings()->getClassForEntityType(EAVSettings::NAMESPACE), $namespaceFromDb);
        self::assertEquals($namespace->getId(), $namespaceFromDb->getId());
        self::assertSame($namespace, $namespaceFromDb);

    }


    public function testEmReturnsOldEntityWithoutClear(): void
    {
        $namespace = $this->entitiesFactory->getOrCreateNamespaceAndFlush();
        $type      = $this->entitiesFactory->createType();
        $entity    = $this->entitiesFactory->createEntity($namespace, $type);

        $this->em->persist($type);
        $this->em->persist($entity);
        $this->em->flush();

        $entitiesFromDb = $this->entityRepository->findBy([ (new FilterCriteria())->where('id', '=', $entity->getId()) ]);

        self::assertCount(1, $entitiesFromDb);

        $entityFromDb = reset($entitiesFromDb);

        self::assertInstanceOf($this->em->getEavSettings()->getClassForEntityType(EAVSettings::ENTITY), $entityFromDb);
        self::assertEquals($entity->getId(), $entityFromDb->getId());
        self::assertSame($entity, $entityFromDb);

    }


    public function testEmReturnsOldRelationTypeWithoutClear(): void
    {
        $relationType = $this->entitiesFactory->createEntityRelationType();

        $this->em->persist($relationType);
        $this->em->flush();

        $entitiesFromDb = $this->relationTypeRepository->findBy([ (new FilterCriteria())->where('id', '=', $relationType->getId()) ]);

        self::assertCount(1, $entitiesFromDb);

        $entityFromDb = reset($entitiesFromDb);

        self::assertInstanceOf($this->em->getEavSettings()->getClassForEntityType(EAVSettings::ENTITY_RELATION_TYPE), $entityFromDb);
        self::assertEquals($relationType->getId(), $entityFromDb->getId());
        self::assertSame($relationType, $entityFromDb);

    }


    public function testEmReturnsOldRelationWithoutClear(): void
    {
        $relation = $this->entitiesFactory->createEntityRelationAndFlush();

        $entitiesFromDb = $this->relationRepository->findBy([ (new FilterCriteria())->where('id', '=', $relation->getId()) ]);

        self::assertCount(1, $entitiesFromDb);

        $entityFromDb = reset($entitiesFromDb);

        self::assertInstanceOf($this->em->getEavSettings()->getClassForEntityType(EAVSettings::ENTITY_RELATION), $entityFromDb);
        self::assertEquals($relation->getId(), $entityFromDb->getId());
        self::assertSame($relation, $entityFromDb);

    }

}