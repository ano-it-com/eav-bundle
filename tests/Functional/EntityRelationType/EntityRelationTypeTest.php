<?php

namespace ANOITCOM\EAVBundle\Tests\Functional\EntityRelationType;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\CommonFilters\FilterCriteria\FilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypes;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EntityRelation\EAVEntityRelationTypeInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Meta\BasicMeta;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManager;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVNamespaceRepository;
use ANOITCOM\EAVBundle\Tests\Functional\Helpers\EntitiesFactory;
use ANOITCOM\EAVBundle\Tests\TestCases\BundleWithPostgresTestCase;
use DateTime;

class EntityRelationTypeTest extends BundleWithPostgresTestCase
{

    private $em;

    private $relationTypeRepository;

    private $valueTypes;

    private $namespaceRepository;

    private $entitiesFactory;


    protected function setUp(): void
    {
        parent::setUp();

        $this->em                     = self::$container->get(EAVEntityManager::class);
        $this->namespaceRepository    = self::$container->get(EAVNamespaceRepository::class);
        $this->relationTypeRepository = self::$container->get(EAVEntityRelationTypeRepository::class);
        $this->valueTypes             = self::$container->get(ValueTypes::class);
        $this->entitiesFactory        = new EntitiesFactory($this->em, $this->namespaceRepository);
    }


    public function testPersistType(): void
    {
        $type = $this->createEntityRelationTypeFlushEndClear();

        // test type
        $typeFromDb = $this->getFromDbById($type->getId());

        $this->compareTwoTypes($type, $typeFromDb);
    }


    public function testUpdateType(): void
    {
        $type = $this->createEntityRelationTypeFlushEndClear();

        // test type
        $typeFromDb = $this->getFromDbById($type->getId());

        $typeFromDb->setTitle($typeFromDb->getTitle() . '_updated');
        $typeFromDb->setAlias($typeFromDb->getAlias() . '_updated');

        $date      = new DateTime();
        $basicMeta = new BasicMeta([ 'created_at' => $date, 'updated_at' => $date ]);

        $typeFromDb->setMeta($basicMeta);

        $this->em->flush();
        $this->em->clear();

        $typeFromDb2 = $this->getFromDbById($typeFromDb->getId());

        $this->compareTwoTypes($typeFromDb, $typeFromDb2);


    }


    public function testDeleteType(): void
    {
        $type = $this->createEntityRelationTypeFlushEndClear();

        $typeFromDb = $this->getFromDbById($type->getId());

        $this->em->remove($typeFromDb);
        $this->em->flush();
        $this->em->clear();

        $typeFromDb2 = $this->getFromDbById($typeFromDb->getId());

        self::assertNull($typeFromDb2);
    }


    private function getFromDbById(string $id): ?EAVEntityRelationTypeInterface
    {
        /** @var EAVEntityRelationTypeInterface [] $typesFromDb */
        $typesFromDb = $this->relationTypeRepository->findBy([ (new FilterCriteria())->where('id', '=', $id) ]);

        return count($typesFromDb) ? $typesFromDb[0] : null;
    }


    private function compareTwoTypes(EAVEntityRelationTypeInterface $type1, EAVEntityRelationTypeInterface $type2): void
    {
        self::assertNotSame($type1, $type2);
        self::assertEquals($type1->getId(), $type2->getId());
        self::assertEquals($type1->getNamespace()->getId(), $type2->getNamespace()->getId());
        self::assertEquals($type1->getAlias(), $type2->getAlias());
        self::assertEquals($type1->getTitle(), $type2->getTitle());

        self::assertTrue($this->entitiesFactory->isMetaEquals($type1->getMeta(), $type2->getMeta()));

    }


    private function createEntityRelationTypeFlushEndClear(): EAVEntityRelationTypeInterface
    {
        $type = $this->entitiesFactory->createEntityRelationType();

        $this->em->persist($type);
        $this->em->flush();
        $this->em->clear();

        return $type;
    }

}