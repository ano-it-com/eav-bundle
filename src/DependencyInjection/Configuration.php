<?php

namespace ANOITCOM\EAVBundle\DependencyInjection;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\BasicJsonMetaType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\BoolType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\DateTimeType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\DateType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\DecimalType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\IntType;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\TextType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntity;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntityPropertyValue;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EntityRelation\EAVEntityRelation;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EntityRelation\EAVEntityRelationType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespace;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Type\EAVType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Type\EAVTypeProperty;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Entity\EAVEntityPersister;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EntityRelation\EAVEntityRelationPersister;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\EntityRelationType\EAVEntityRelationTypePersister;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\NamespaceEntity\EAVNamespacePersister;
use ANOITCOM\EAVBundle\EAV\ORM\Persistence\Persister\Type\EAVTypePersister;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {
        $typeColumns = [
            'id'           => TextType::class,
            'namespace_id' => TextType::class,
            'alias'        => TextType::class,
            'title'        => TextType::class,
            'comment'      => TextType::class,
            'meta'         => BasicJsonMetaType::class,
        ];

        $typePropertyColumns = [
            'id'           => TextType::class,
            'namespace_id' => TextType::class,
            'type_id'      => TextType::class,
            'value_type'   => IntType::class,
            'alias'        => TextType::class,
            'title'        => TextType::class,
            'comment'      => TextType::class,
            'meta'         => BasicJsonMetaType::class,
        ];

        $relationTypeColumns = [
            'id'           => TextType::class,
            'namespace_id' => TextType::class,
            'alias'        => TextType::class,
            'title'        => TextType::class,
            'comment'      => TextType::class,
            'meta'         => BasicJsonMetaType::class,
        ];

        $isEavOntologyInstalled = class_exists('ANOITCOM\EAVSemanticsBundle\EAVSemanticsBundle', true);

        if ($isEavOntologyInstalled) {
            $typeColumns['ontology_class']         = TextType::class;
            $typePropertyColumns['ontology_class'] = TextType::class;
            $relationTypeColumns['ontology_class'] = TextType::class;
        }

        $treeBuilder = new TreeBuilder('eav');
        $rootNode    = $treeBuilder->getRootNode();
        $rootNode
            ->children()
            ->arrayNode('base_tables')
            ->children()
            ->arrayNode(EAVSettings::NAMESPACE)
            ->children()
            ->scalarNode('table')->defaultValue('eav_namespace')->end()
            ->scalarNode('class')->defaultValue(EAVNamespace::class)->end()
            ->scalarNode('persister')->defaultValue(EAVNamespacePersister::class)->end()
            ->variableNode('columns')
            ->defaultValue([
                'id'      => TextType::class,
                'iri'     => TextType::class,
                'title'   => TextType::class,
                'comment' => TextType::class,
                'meta'    => BasicJsonMetaType::class,
            ])
            ->end()
            ->end()
            ->end()
            ->arrayNode(EAVSettings::ENTITY)
            ->children()
            ->scalarNode('table')->defaultValue('eav_entity')->end()
            ->scalarNode('class')->defaultValue(EAVEntity::class)->end()
            ->scalarNode('persister')->defaultValue(EAVEntityPersister::class)->end()
            ->variableNode('columns')
            ->defaultValue([
                'id'           => TextType::class,
                'namespace_id' => TextType::class,
                'type_id'      => TextType::class,
                'meta'         => BasicJsonMetaType::class,
            ])
            ->end()
            ->end()
            ->end()
            ->arrayNode(EAVSettings::TYPE)
            ->children()
            ->scalarNode('table')->defaultValue('eav_type')->end()
            ->scalarNode('class')->defaultValue(EAVType::class)->end()
            ->scalarNode('persister')->defaultValue(EAVTypePersister::class)->end()
            ->variableNode('columns')
            ->defaultValue($typeColumns)
            ->end()
            ->end()
            ->end()
            ->arrayNode(EAVSettings::TYPE_PROPERTY)
            ->children()
            ->scalarNode('table')->defaultValue('eav_type_property')->end()
            ->scalarNode('class')->defaultValue(EAVTypeProperty::class)->end()
            ->variableNode('columns')
            ->defaultValue($typePropertyColumns)
            ->end()
            ->end()
            ->end()
            ->arrayNode(EAVSettings::VALUES)
            ->children()
            ->scalarNode('table')->defaultValue('eav_values')->end()
            ->scalarNode('class')->defaultValue(EAVEntityPropertyValue::class)->end()
            ->variableNode('columns')
            ->defaultValue([
                'id'               => TextType::class,
                'namespace_id'     => TextType::class,
                'entity_id'        => TextType::class,
                'type_property_id' => TextType::class,
                'value_text'       => TextType::class,
                'value_int'        => IntType::class,
                'value_decimal'    => DecimalType::class,
                'value_bool'       => BoolType::class,
                'value_datetime'   => [ DateType::class, DateTimeType::class ],
                'meta'             => BasicJsonMetaType::class,
            ])
            ->end()
            ->end()
            ->end()
            ->arrayNode(EAVSettings::ENTITY_RELATION)
            ->children()
            ->scalarNode('table')->defaultValue('eav_entity_relation')->end()
            ->scalarNode('class')->defaultValue(EAVEntityRelation::class)->end()
            ->scalarNode('persister')->defaultValue(EAVEntityRelationPersister::class)->end()
            ->variableNode('columns')
            ->defaultValue([
                'id'           => TextType::class,
                'namespace_id' => TextType::class,
                'type_id'      => TextType::class,
                'from_id'      => TextType::class,
                'to_id'        => TextType::class,
                'meta'         => BasicJsonMetaType::class,
            ])
            ->end()
            ->end()
            ->end()
            ->arrayNode(EAVSettings::ENTITY_RELATION_TYPE)
            ->children()
            ->scalarNode('table')->defaultValue('eav_entity_relation_type')->end()
            ->scalarNode('class')->defaultValue(EAVEntityRelationType::class)->end()
            ->scalarNode('persister')->defaultValue(EAVEntityRelationTypePersister::class)->end()
            ->variableNode('columns')
            ->defaultValue($relationTypeColumns)
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}