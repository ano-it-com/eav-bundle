<?php

namespace ANOITCOM\EAVBundle\DependencyInjection;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\BoolType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\DateTimeType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\DateType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\DecimalType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\IntType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\TextType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVEntity;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EAVType;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('eav');
        $rootNode    = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->scalarNode('base_type_class')->defaultValue(EAVType::class)->end()
                ->arrayNode('base_tables')
                    ->children()
                        ->arrayNode('eav_entity')
                            ->children()
                                ->scalarNode('table')->defaultValue('eav_entity')->end()
                                ->variableNode('columns')
                                    ->defaultValue([
                                        'id'      => TextType::class,
                                        'type_id' => TextType::class,
                                        'meta'    => TextType::class,
                                    ])
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('eav_type')
                            ->children()
                                ->scalarNode('table')->defaultValue('eav_type')->end()
                                ->variableNode('columns')
                                    ->defaultValue([
                                        'id'    => TextType::class,
                                        'alias' => TextType::class,
                                        'title' => TextType::class,
                                        'meta'  => TextType::class,
                                    ])
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('eav_type_property')
                            ->children()
                                ->scalarNode('table')->defaultValue('eav_type_property')->end()
                                ->variableNode('columns')
                                    ->defaultValue([
                                        'id'         => TextType::class,
                                        'type_id'    => TextType::class,
                                        'value_type' => IntType::class,
                                        'alias'      => TextType::class,
                                        'title'      => TextType::class,
                                        'meta'       => TextType::class,
                                    ])
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('eav_values')
                            ->children()
                                ->scalarNode('table')->defaultValue('eav_values')->end()
                                ->variableNode('columns')
                                    ->defaultValue([
                                        'id' => TextType::class,
                                        'entity_id' => TextType::class,
                                        'type_property_id' => TextType::class,
                                        'value_text' => TextType::class,
                                        'value_int' => IntType::class,
                                        'value_decimal' => DecimalType::class,
                                        'value_bool' => BoolType::class,
                                        'value_datetime' => [ DateType::class, DateTimeType::class ],
                                        'meta' => TextType::class,
                                    ])
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}