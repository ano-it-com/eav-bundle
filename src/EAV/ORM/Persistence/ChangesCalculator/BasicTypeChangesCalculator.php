<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator;

class BasicTypeChangesCalculator implements ChangesCalculatorInterface
{

    public function getChanges(array $newValues, array $oldValues, $withoutInnerProcessing = false): array
    {
        $changes     = [];
        $typeChanges = $this->getTypeChanges($newValues, $oldValues);
        if (count($typeChanges)) {
            $changes['type'] = $typeChanges;
        }

        $propertyChanges = $this->getPropertyChanges($newValues, $oldValues);
        if (count($propertyChanges)) {
            $changes['property'] = $propertyChanges;
        }

        return $changes;
    }


    protected function getTypeChanges(array $newValues, array $oldValues): array
    {
        unset($newValues['_properties'], $oldValues['_properties']);

        $changes = [];

        foreach ($newValues as $key => $newValue) {
            if (array_key_exists($key, $oldValues)) {
                $oldValue = $oldValues[$key];

                if ($newValue !== $oldValue) {
                    $changes[$key] = [
                        'old' => $oldValue,
                        'new' => $newValue
                    ];
                }
            } else {
                $changes[$key] = [
                    'old' => null,
                    'new' => $newValue
                ];
            }
        }

        return $changes;
    }


    protected function getPropertyChanges(array $newValues, array $oldValues): array
    {
        $changes = [];

        $newValues = array_combine(array_column($newValues['_properties'], 'id'), $newValues['_properties']);
        $oldValues = array_combine(array_column($oldValues['_properties'], 'id'), $oldValues['_properties']);

        foreach ($newValues as $valueId => $newValue) {
            asort($newValue);
            if (array_key_exists($valueId, $oldValues)) {
                $oldValue = $oldValues[$valueId];
                asort($oldValue);

                if ($oldValue !== $newValue) {
                    $changes['updated'][] = [
                        'old' => $oldValue,
                        'new' => $newValue
                    ];
                }
            } else {
                $changes['added'][] = [
                    'old' => null,
                    'new' => $newValue
                ];
            }
        }

        foreach ($oldValues as $valueId => $oldValue) {
            if ( ! array_key_exists($valueId, $newValues)) {
                $changes['removed'][] = [
                    'old' => $oldValue,
                    'new' => null
                ];
            }
        }

        return $changes;
    }

}