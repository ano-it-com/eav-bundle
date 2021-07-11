# EAV

EAV (Entity-Attribute-Value) bundle для Symfony. ORM для работы с динамическими типами объектов.

## Основные возможности
* ORM с использованием принципов Doctrine
* Реализация Identity Map и Unit-of-Work
* Возможность расширения моделей полями

## Установка

```
php bin/console eav:install [config] [migrations]
```

config - установка только конфигурационных файлов  
migrations - установка файлов-миграций


## Основные сущности

`Пространство имен` - (namespace), любая сущность, связь, значение привязываются к какому-либо пространству имен. Используются для разделения данных, в том числе для разграничения прав доступа.

`Тип сущности` - (entity type), тип объекта, например, "Персона", "Организация". Определяет набор доступных полей сущности.

`Свойства типа сущности` - (entity type properties), типы свойств типа сущности, определяют тип значения свойства. Например, "Фамилия", "Дата рождения". 

`Тип связи` - (entity relation type), тип связи между двумя сущностями. Например, "знает о", "работает в".

`Сущность` - (entity), сущность, объект, конкретная сущность определённого  типа.

`Значение свойства сущности` - (entity values), значения свойств сущности, например, "Иванов", "12-11-1982".

`Связь` - (entity relation), связь между двумя сущностями.


## Примеры

### Создание пространства имен
```

/** @var EAVNamespaceInterface $namespace */
$namespace = new EAVNamespace(Uuid::uuid4()->toString(), 'http://test.iri');
$namespace->setTitle('Test Namespace');
$namespace->setComment('Comment');

// необязательная мета-информация
$date = new \DateTime('2020-01-01');
$basicMeta = new BasicMeta([ 'created_at' => $date, 'updated_at' => $date ]);

$namespace->setMeta($basicMeta);

/** @var EAVEntityManagerInterface $em */
$em->persist($namespace);
$em->flush();
```

### Создание типа сущности
```
$typePerson = new EAVType(Uuid::uuid4()->toString(), $namespace);
$typePerson->setAlias('person');
$typePerson->setTitle('Персона');

$fioPropertyType = new EAVTypeProperty(Uuid::uuid4()->toString(), $namespace, $typePerson, new TextType());
$fioPropertyType->setAlias('fio');
$fioPropertyType->setTitle('ФИО');

$birthdayPropertyType = new EAVTypeProperty(Uuid::uuid4()->toString(), $namespace, $typePerson, new TextType());
$birthdayPropertyType->setAlias('birthday');
$birthdayPropertyType->setTitle('Дата рождения');

$typePerson->setProperties( [$fio, $birthday]);

/** @var EAVEntityManagerInterface $em */
$em->persist($typePerson);
$em->flush();

```

### Создание типа связи
```
$relationType = new EAVEntityRelationType(Uuid::uuid4(), $namespace);
$relationType->setAlias('knows_about');
$relationType->setTitle('Знает о');

/** @var EAVEntityManagerInterface $em */
$em->persist($relationType);
$em->flush();
```

### Создание объектов и связей
```
$entityIvanov = new EAVEntity(Uuid::uuid4(), $namespace, $typePerson);

$fioValue = new EAVEntityPropertyValue(Uuid::uuid4(), $namespace, $fioPropertyType);
$fioValue->setValue('Иванов Иван Иванович');
$birthdayValue = new EAVEntityPropertyValue(Uuid::uuid4(), $namespace, $birthdayPropertyType);
$birthdayValue->setValue(new \DateTime('1985-12-11'));

$entityIvanov->setValues([$fioValue, $birthdayValue]);

/** @var EAVEntityManagerInterface $em */
$em->persist($entityIvanov);

$entityPetrov = new EAVEntity(Uuid::uuid4(), $namespace, $typePerson);

$fioValue1 = new EAVEntityPropertyValue(Uuid::uuid4(), $namespace, $fioPropertyType);
$fioValue1->setValue('Петров Петр Петрович');
$birthdayValue1 = new EAVEntityPropertyValue(Uuid::uuid4(), $namespace, $birthdayPropertyType);
$birthdayValue1->setValue(new \DateTime('1977-01-14'));

$entityPetrov->setValues([$fioValue1, $birthdayValue1]);

$em->persist($entityPetrov);

$relation = new EAVEntityRelation(Uuid::uuid4(), $namespace, $relationType);
$relation->setFrom($entityIvanov);
$relation->setTo($entityPetrov);

$em->persist($entityPetrov);
$en->flush();
```

### Получение сущностей
```
// по идентификатору
$namespace = $this->namespaceRepository->find('c5d4915c-8e4f-4d22-a8b0-8859be567594');
// по фильтру
$namespace = $this->namespaceRepository->findOneBy([ (new FilterCriteria())->where('iri', '=', 'http://test.iri') ]);

// по разным фильтрам
$entity = $this->entityRepository->findBy([
    (new EntityPropertyValueCriteria())
        ->where($fioPropertyType->getId(), '=', 'Иванов Иван Иванович')
        ->where($birthayPropertyType->getId(), '=', '1985-12-11'),
    (new FilterCriteria())->where('type_id', '=', $typePerson->getId())
]);
```

### Удаление сущностей
```
$this->em->remove($entity);
$this->em->flush();
```