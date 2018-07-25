# CakeMongo - MongoDB Plugin for CakePHP 3 

[![License](https://poser.pugx.org/imdad/cake-mongo/license)](https://packagist.org/packages/imdad/cake-mongo) [![Total Downloads](https://poser.pugx.org/imdad/cake-mongo/downloads)](https://packagist.org/packages/imdad/cake-mongo)

The plugin provides an ORM-like abstraction on top of MongoDB.

## Installation

To install the CakeMongo plugin, you can use composer. From your application’s ROOT directory (where composer.json file is located) run the following:

```composer require imdad/cake-mongo:dev-master```

You will need to add the following line to your application's `config/bootstrap.php` file:

```Plugin::load('Imdad/CakeMongo');```

Additionally, you will need to configure the 'cake_mongo' datasource connection in your `config/app.php` file. 
An example configuration would be:

```
'Datasources' => [
    //other datasources
    'cake_mongo' => [
        'className' => 'Imdad\CakeMongo\Datasource\Connection',
        'driver' => 'Imdad\CakeMongo\Datasource\Connection',
    ]
]
```

## Overview

The CakeMongo plugin makes it easier to interact with an MongoDB collections and provides an interface similar to the Database Access & ORM. 
To get started you should create a **Collection** object. **Collection** objects are the "Repository" or table-like class in CakeMongo:

```php
// in src/Model/Collection/ArticlesCollection.php

namespace App\Model\Collection;

use Imdad\CakeMongo\Collection;

class ArticlesCollection extends Collection
{

}
```

Do not confuse CakeMongo's Collection class with CakePHP's Collection class.

You can then use your Collection class in your controllers:

```php

public function beforeFilter(Event $event)
{
    parent::beforeFilter($event);
    // Load the Collection using the 'Mongo' provider.
    $this->loadModel('Articles', 'Mongo');
}


public function add()
{
    $article = $this->Articles->newEntity();
    if ($this->request->is('post')) {
        $article = $this->Articles->patchEntity($article, $this->request->getData());
        if ($this->Articles->save($article)) {
            $this->Flash->success(__('The article has been saved.'));

            return $this->redirect(['action' => 'index']);
        }
        $this->Flash->error(__('The article could not be saved. Please, try again.'));
    }
    $this->set(compact('article'));
}

```

We would also need to create a basic view for our indexed articles:

```php
<?= $this->Form->create($article); ?>
<?= $this->Form->control('title', ['empty' => true]); ?>
<?= $this->Form->control('body', ['type' => 'textarea']); ?>
<?= $this->Form->button(__('Submit')) ?>
<?= $this->Form->end() ?>
```

You should now be able to submit the form and have a new document added to MongoDB.

Alternatively you can load Collection anywhere you want using CollectionRegistry:
 
```php
use Imdad\CakeMongo\CollectionRegistry;

$this->Articles = CollectionRegistry::get('Articles');
```

## Support
[GitHub Issues](https://github.com/imdad/cake-mongo/issues) - Submit bug/issue here!
Please supply as much information as possible when submitting a bug. It will the best if you 
could create a Unit Test.

