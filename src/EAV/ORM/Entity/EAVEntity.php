<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Entity;

use Ramsey\Uuid\Uuid;

class EAVEntity implements EAVPersistableInterface
{

    /** @var string */
    protected $id;

    /** @var EAVType */
    protected $type;

    /** @var string|null */
    protected $meta;

    /** @var EAVEntityPropertyValue[] */
    protected $values;


    public function __construct(string $id)
    {
        $this->id = $id;
    }


    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }


    /**
     * @return EAVType
     */
    public function getType(): EAVType
    {
        return $this->type;
    }


    /**
     * @param EAVType $type
     */
    public function setType(EAVType $type): void
    {
        $this->type = $type;
    }


    /**
     * @return string|null
     */
    public function getMeta(): ?string
    {
        return $this->meta;
    }


    /**
     * @param string|null $meta
     */
    public function setMeta(?string $meta): void
    {
        $this->meta = $meta;
    }


    /**
     * @return EAVEntityPropertyValue[]
     */
    public function getValues(): array
    {
        return $this->values;
    }


    /**
     * @param EAVEntityPropertyValue[] $values
     */
    public function setValues(array $values): void
    {
        $this->values = $values;
    }


    public function addPropertyValueByAlias(string $alias, $value): void
    {
        foreach ($this->type->getProperties() as $property) {
            if ($property->getAlias() === $alias) {
                $propertyValue = new EAVEntityPropertyValue(Uuid::uuid4()->toString());
                $propertyValue->setValue($value);
                $propertyValue->setValueType($property->getValueType()->getCode());
                $propertyValue->setTypePropertyId($property->getId());
                $this->values[] = $propertyValue;

                return;
            }
        }

        throw new \InvalidArgumentException('Entity type does not have property with alias ' . $alias);
    }


    public function removeProperty(EAVEntityPropertyValue $propertyValue): void
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