# CakeMongo - MongoDB Plugin for CakePHP 3 

[![License](https://poser.pugx.org/cakephp/elastic-search/license.svg)](https://packagist.org/packages/cakephp/elastic-search)

The plugin provides an ORM-like abstraction on top of MongoDB.

## Installing CakeMongo via composer

`composer require dilab/cake-mongo:dev-master`

## Connecting the Plugin to your Application

Append `Plugin::load('Dilab/CakeMongo');` to `config/bootstrap.php`

## Defining a connection

In your `config/app.php` file, add:

```
'Datasources' => [
    //other datasources
    'cake_mongo' => [
        'className' => 'Dilab\CakeMongo\Datasource\Connection',
        'driver' => 'Dilab\CakeMongo\Datasource\Connection',
    ]
]
```


## Getting a Collection object

Collection objects are the equivalent of ORM\Table instances in MongoDB. You can use the CollectionRegistry factory to get instances, much like TableRegistry:

```
use Dilab\CakeMongo\CollectionRegistry;

$artciles = CollectionRegistry::get('Articles');
```

## RoadMap
+ [x] CakePHP ORM Functions 
+ [ ] Embed Document Support