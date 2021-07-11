<?php

namespace ANOITCOM\EAVBundle\Tests\Functional\NamespaceEntity;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\CommonFilters\FilterCriteria\FilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\ValueTypes;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Meta\BasicMeta;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManager;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVNamespaceRepository;
use ANOITCOM\EAVBundle\Tests\Functional\Helpers\EntitiesFactory;
use ANOITCOM\EAVBundle\Tests\TestCases\BundleWithPostgresTestCase;
use DateTime;

class NamespacePersistenceTest extends BundleWithPostgresTestCase
{

    /** @var EAVEntityManager */
    private $em;

    private $namespaceRepository;

    private $valueTypes;

    private $entitiesFactory;


    protected function setUp(): void
    {
        parent::setUp();

        $this->em                  = self::$container->get(EAVEntityManager::class);
        $this->namespaceRepository = self::$container->get(EAVNamespaceRepository::class);
        $this->valueTypes          = self::$container->get(ValueTypes::class);
        $this->entitiesFactory     = new EntitiesFactory($this->em, $this->namespaceRepository);
    }


    public function testPersistNamespace(): void
    {
        $namespace = $this->createNamespaceFlushAndClear();

        // test type
        $namespaceFromDb = $this->getFromDbById($namespace->getId());

        $this->compareTwoNamespaces($namespace, $namespaceFromDb);
    }


    public function testUpdateNamespace(): void
    {
        $namespace = $this->createNamespaceFlushAndClear();

        $namespaceFromDb = $this->getFromDbById($namespace->getId());

        $namespaceFromDb->setTitle($namespaceFromDb->getTitle() . '_updated');
        $namespaceFromDb->setComment($namespaceFromDb->getComment() . '_updated');

        $date      = new DateTime();
        $basicMeta = new BasicMeta([ 'created_at' => $date, 'updated_at' => $date ]);

        $namespaceFromDb->setMeta($basicMeta);

        $this->em->flush();
        $this->em->clear();

        $namespaceFromDb2 = $this->getFromDbById($namespaceFromDb->getId());

        $this->compareTwoNamespaces($namespaceFromDb, $namespaceFromDb2);


    }


    public function testDeleteNamespace(): void
    {
        $namespace = $this->createNamespaceFlushAndClear();

        $namespaceFromDb = $this->getFromDbById($namespace->getId());

        $this->em->remove($namespaceFromDb);
        $this->em->flush();
        $this->em->clear();

        $namespaceFromDb2 = $this->getFromDbById($namespaceFromDb->getId());

        self::assertNull($namespaceFromDb2);
    }


    private function getFromDbById(string $id): ?EAVNamespaceInterface
    {
        /** @var EAVNamespaceInterface[] $namespaceFromDb */
        $namespaceFromDb = $this->namespaceRepository->findBy([ (new FilterCriteria())->where('id', '=', $id) ]);

        return count($namespaceFromDb) ? $namespaceFromDb[0] : null;
    }


    private function compareTwoNamespaces(EAVNamespaceInterface $namespace1, EAVNamespaceInterface $namespace2): void
    {
        self::assertNotSame($namespace1, $namespace2);
        self::assertEquals($namespace1->getId(), $namespace2->getId());
        self::assertEquals($namespace1->getIri(), $namespace2->getIri());
        self::assertEquals($namespace1->getComment(), $namespace2->getComment());
        self::assertEquals($namespace1->getTitle(), $namespace2->getTitle());

        self::assertTrue($this->entitiesFactory->isMetaEquals($namespace1->getMeta(), $namespace2->getMeta()));
    }


    private function createNamespaceFlushAndClear(): EAVNamespaceInterface
    {
        $namespace = $this->entitiesFactory->getOrCreateNamespaceAndFlush();
        $this->em->clear();

        return $namespace;
    }

}