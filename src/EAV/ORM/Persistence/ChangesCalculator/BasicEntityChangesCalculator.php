<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator;

class BasicEntityChangesCalculator implements ChangesCalculatorInterface
{

    public function getChanges(array $newValues, array $oldValues, $withoutInnerProcessing = false): array
    {
        $changes       = [];
        $entityChanges = $this->getEntityChanges($newValues, $oldValues);
        if (count($entityChanges)) {
            $changes['entity'] = $entityChanges;
        }

        $valuesChanges = $this->getValuesChanges($newValues, $oldValues);
        if (count($valuesChanges)) {
            $changes['values'] = $valuesChanges;
        }

        return $changes;
    }


    protected function getEntityChanges(array $newValues, array $oldValues): array
    {
        unset($newValues['_values'], $oldValues['_values']);

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


    protected function getValuesChanges(array $newValues, array $oldValues): array
    {
        $changes = [];

        $newValues = array_combine(array_column($newValues['_values'], 'id'), $newValues['_values']);
        $oldValues = array_combine(array_column($oldValues['_values'], 'id'), $oldValues['_values']);

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