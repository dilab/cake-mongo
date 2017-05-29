<?php

use Cake\Collection\Collection;
use Cake\Event\EventManager;
use Dilab\CakeMongo\Document;
use Dilab\CakeMongo\View\Form\DocumentContext;

$listener = function ($event) {
    $controller = false;
    if (isset($event->data['controller'])) {
        $controller = $event->data['controller'];
    }
    if ($controller) {
        $callback = ['Dilab\CakeMongo\CollectionRegistry', 'get'];
        $controller->modelFactory('MongoDb', $callback);
        $controller->modelFactory('Mongo', $callback);
    }
};

// Attach the TypeRegistry into controllers.
EventManager::instance()->on('Dispatcher.invokeController', $listener);
EventManager::instance()->on('Dispatcher.beforeDispatch', ['priority' => 99], $listener);
unset($listener);

// Attach the document context into FormHelper.
EventManager::instance()->on('View.beforeRender', function ($event) {
    $view = $event->subject();
    $view->Form->addContextProvider('mongo', function ($request, $data) {
        $first = null;
        if (is_array($data['entity']) || $data['entity'] instanceof Traversable) {
            $first = (new Collection($data['entity']))->first();
        }
        if ($data['entity'] instanceof Document || $first instanceof Document) {
            return new DocumentContext($request, $data);
        }
    });
});
