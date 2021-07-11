<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\Meta\BasicMeta;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Meta\JsonMetaInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Type\EAVTypeInterface;
use Ramsey\Uuid\Uuid;

class EAVEntity implements EAVEntityInterface
{

    protected string $id;

    protected EAVTypeInterface $type;

    protected $meta;

    protected EAVNamespaceInterface $namespace;

    /** @var EAVEntityPropertyValue[] */
    protected array $values = [];


    public function __construct(string $id, EAVNamespaceInterface $namespace, EAVTypeInterface $type)
    {
        $this->id        = $id;
        $this->type      = $type;
        $this->namespace = $namespace;

        $this->meta = new BasicMeta();
    }


    public function getId(): string
    {
        return $this->id;
    }


    public function getType(): EAVTypeInterface
    {
        return $this->type;
    }


    public function getNamespace(): EAVNamespaceInterface
    {
        return $this->namespace;
    }


    public function setNamespace(EAVNamespaceInterface $namespace): void
    {
        $this->namespace = $namespace;
    }


    public function getMeta(): JsonMetaInterface
    {
        return $this->meta;
    }


    public function setMeta(JsonMetaInterface $meta): void
    {
        $this->meta = $meta;
    }


    /**
     * @return EAVEntityPropertyValueInterface[]
     */
    public function getValues(): array
    {
        return $this->values;
    }


    /**
     * @param EAVEntityPropertyValueInterface[] $values
     */
    public function setValues(array $values): void
    {
        $this->values = $values;
    }


    public function addPropertyValueByAlias(string $alias, $value, $meta = null): void
    {
        foreach ($this->type->getProperties() as $property) {
            if ($property->getAlias() === $alias) {
                $propertyValue = new EAVEntityPropertyValue(Uuid::uuid4()->toString(), $this->namespace, $property);
                $propertyValue->setValue($value);
                if ($meta !== null) {
                    $propertyValue->setMeta($meta);
                }
                $this->values[] = $propertyValue;

                return;
            }
        }

        throw new \InvalidArgumentException('Entity type does not have property with alias ' . $alias);
    }


    public function addPropertyValueByPropertyTypeId(string $id, $value, $meta = null): void
    {
        foreach ($this->type->getProperties() as $property) {
            if ($property->getId() === $id) {
                $propertyValue = new EAVEntityPropertyValue(Uuid::uuid4()->toString(), $this->namespace, $property);
                $propertyValue->setValue($value);
                if ($meta !== null) {
                    $propertyValue->setMeta($meta);
                }
                $this->values[] = $propertyValue;

                return;
            }
        }

        throw new \InvalidArgumentException('Entity type does not have property with type id ' . $id);
    }


    public function removeProperty(EAVEntityPropertyValueInterface $propertyValue): void
    {
        foreach ($this->values as $key => $value) {
            if ($value->getId() === $propertyValue->getId()) {
                unset($this->values[$key]);

                return;
            }
        }

        throw new \InvalidArgumentException('Property does not exist');

    }

}