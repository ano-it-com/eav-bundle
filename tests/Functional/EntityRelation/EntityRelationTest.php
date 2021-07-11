<?php

namespace ANOITCOM\EAVBundle\Tests\Functional\EntityRelation;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\CommonFilters\FilterCriteria\FilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypes;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EntityRelation\EAVEntityRelationInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Meta\BasicMeta;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManager;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVNamespaceRepository;
use ANOITCOM\EAVBundle\Tests\Functional\Helpers\EntitiesFactory;
use ANOITCOM\EAVBundle\Tests\TestCases\BundleWithPostgresTestCase;
use DateTime;

class EntityRelationTest extends BundleWithPostgresTestCase
{

    private $em;

    private $relationRepository;

    private $valueTypes;

    private $entitiesFactory;

    private $namespaceRepository;


    protected function setUp(): void
    {
        parent::setUp();

        $this->em                  = self::$container->get(EAVEntityManager::class);
        $this->namespaceRepository = self::$container->get(EAVNamespaceRepository::class);
        $this->relationRepository  = self::$container->get(EAVEntityRelationRepository::class);
        $this->valueTypes          = self::$container->get(ValueTypes::class);
        $this->entitiesFactory     = new EntitiesFactory($this->em, $this->namespaceRepository);
    }


    public function testPersistRelation(): void
    {
        $relation = $this->entitiesFactory->createEntityRelationAndFlush();

        $this->em->clear();

        $relationFromDb = $this->getFromDbById($relation->getId());

        $this->compareTwoRelations($relation, $relationFromDb);
    }


    public function testUpdateRelation(): void
    {
        $relation = $this->entitiesFactory->createEntityRelationAndFlush();

        $newNamespace    = $this->entitiesFactory->getOrCreateNamespaceAndFlush();
        $newType         = $this->entitiesFactory->createType();
        $newRelationType = $this->entitiesFactory->createEntityRelationType();
        $newObject1      = $this->entitiesFactory->createEntity($newNamespace, $newType);
        $newObject2      = $this->entitiesFactory->createEntity($newNamespace, $newType);

        $this->em->persist($newNamespace);
        $this->em->persist($newType);
        $this->em->persist($newRelationType);
        $this->em->persist($newObject1);
        $this->em->persist($newObject2);

        $this->em->flush();
        $this->em->clear();

        $relationFromDb = $this->getFromDbById($relation->getId());

        $relationFromDb->setFrom($newObject1);
        $relationFromDb->setTo($newObject2);

        $date      = new DateTime();
        $basicMeta = new BasicMeta([ 'created_at' => $date, 'updated_at' => $date ]);

        $relationFromDb->setMeta($basicMeta);

        $this->em->flush();
        $this->em->clear();

        $relationFromDb2 = $this->getFromDbById($relationFromDb->getId());

        $this->compareTwoRelations($relationFromDb, $relationFromDb2);


    }


    public function testDeleteRelation(): void
    {
        $relation = $this->entitiesFactory->createEntityRelationAndFlush();

        $this->em->clear();

        $relationFromDb = $this->getFromDbById($relation->getId());

        $this->em->remove($relationFromDb);
        $this->em->flush();
        $this->em->clear();

        $relationFromDb2 = $this->getFromDbById($relationFromDb->getId());

        self::assertNull($relationFromDb2);
    }


    private function getFromDbById(string $id): ?EAVEntityRelationInterface
    {
        /** @var EAVEntityRelationInterface [] $typesFromDb */
        $relationsFromDb = $this->relationRepository->findBy([ (new FilterCriteria())->where('id', '=', $id) ]);

        return count($relationsFromDb) ? $relationsFromDb[0] : null;
    }


    private function compareTwoRelations(EAVEntityRelationInterface $relation1, EAVEntityRelationInterface $relation2): void
    {
        self::assertNotSame($relation1, $relation2);
        self::assertEquals($relation1->getId(), $relation2->getId());
        self::assertEquals($relation1->getType()->getId(), $relation2->getType()->getId());
        self::assertEquals($relation1->getNamespace()->getId(), $relation2->getNamespace()->getId());
        self::assertEquals($relation1->getFrom()->getId(), $relation2->getFrom()->getId());
        self::assertEquals($relation1->getTo()->getId(), $relation2->getTo()->getId());

        self::assertTrue($this->entitiesFactory->isMetaEquals($relation1->getMeta(), $relation2->getMeta()));

    }

}