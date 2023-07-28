<p align="center">
    <a href="https://github.com/yii1tech" target="_blank">
        <img src="https://avatars.githubusercontent.com/u/134691944" height="100px">
    </a>
    <h1 align="center">Yii1 Dependency Injection Extension</h1>
    <br>
</p>

This extension provides Dependency Injection support for Yii1 application.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://img.shields.io/packagist/v/yii1tech/di.svg)](https://packagist.org/packages/yii1tech/di)
[![Total Downloads](https://img.shields.io/packagist/dt/yii1tech/di.svg)](https://packagist.org/packages/yii1tech/di)
[![Build Status](https://github.com/yii1tech/di/workflows/build/badge.svg)](https://github.com/yii1tech/di/actions)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yii1tech/di
```

or add

```json
"yii1tech/di": "*"
```

to the "require" section of your composer.json.


Usage
-----

This extension provides Dependency Injection support for Yii1 application using [PSR-11](https://www.php-fig.org/psr/psr-11/) compatible container.

This extension introduces static facade class `\yii1tech\di\DI`, which provides global access to PSR container and injector.
All dependency injection features provided in this extension relies on this facade.
It provides easy way for container entity retrieval and dependency injection.
For example:

```php
<?php

use yii1tech\di\DI;

class Foo
{
    /**
     * @var CDbConnection
     */
    public $db;
    /**
     * @var string 
     */
    public $name = 'default';
    
    public function __construct(CDbConnection $db)
    {
        $this->db = $db;
    }
    
    public function format(CFormatter $formatter, string $value): string
    {
        return $formatter->formatDate($value);
    }
}

$psrContainer = DI::container(); // retrieve related PSR compatible container
var_dump($psrContainer instanceof \Psr\Container\ContainerInterface); // outputs `true`

$db = DI::get(CDbConnection::class); // retrieve entity from PSR compatible container

$object = DI::make(Foo::class); // instantiates object, resolving constructor arguments from PSR compatible container based on type-hints
var_dump($object->db instanceof CDbConnection); // outputs `true`

$date = DI::invoke([$object, 'format'], ['value' => time()]); // invokes given callback, resolving its arguments from PSR compatible container based on type-hints
var_dump($date); // outputs '2023/07/28'

$object = DI::create([ // instantiates object from Yii-style configuration, resolving constructor arguments from PSR compatible container based on type-hints
    'class' => Foo::class,
    'name' => 'custom',
]);
var_dump($object->db instanceof CDbConnection); // outputs `true`
var_dump($object->name); // outputs `custom`
```


### Container Setup <span id="container-setup"></span>

The actual dependency injection container should be set via `\yii1tech\di\DI::setContainer()` method.
It should be done at the Yii bootstrap stage **before** the application is created.
For example:

```php
<?php
// file '/public/index.php'
require __DIR__ . '../vendor/autoload.php';
// ...

use yii1tech\di\Container;
use yii1tech\di\DI;

// setup DI container:
DI::setContainer(
    Container::new()
        ->config(CDbConnection::class, [
            'connectionString' => 'sqlite::memory:',
        ])
        ->lazy(ICache::class, function (Container $container) {
            $cache = new CDbCache();
            $cache->setDbConnection($container->get(CDbConnection::class))
            
            $cache->init();
            
            return $cache;
        })
        // ...
);

// create and run Yii application:
Yii::createWebApplication($config)->run();
```

Instead of creating container instance right away, you may specify a PHP callback, which will instantiate it.
For example:

```php
<?php
// file '/public/index.php'
require __DIR__ . '../vendor/autoload.php';
// ...
use yii1tech\di\Container;
use yii1tech\di\DI;

// setup DI container:
DI::setContainer(function () {
    return ContainerFactory::create();
});

// create and run Yii application:
Yii::createWebApplication($config)->run();

// ...
class ContainerFactory
{
    public static function create(): \Psr\Container\ContainerInterface
    {
        $container = Container::new();
        // fill up container
        
        return $container;
    }
}
```


### Dependency Injection for Application Components <span id="di-for-application-components"></span>

This extension allows configuration of the Application (Service Locator) components via DI container.
In order to make it work, you need to use one of application classes provided within this extension - feature will not function
automatically on the standard ones.

Following classes are provided:

- `\yii1tech\di\web\WebApplication` - DI aware Web Application.
- `\yii1tech\di\console\ConsoleApplication` - DI aware Console Application.
- `\yii1tech\di\base\Module` - DI aware Module.
- `\yii1tech\di\web\WebModule` - DI aware Web Module.

If you use your own custom application class, you may apply DI component resolution to it using `\yii1tech\di\base\ResolvesComponentViaDI` trait.

For example:

```php
<?php
// file '/public/index.php'
require __DIR__ . '../vendor/autoload.php';
// ...
use yii1tech\di\DI;
use yii1tech\di\web\WebApplication;

// setup DI container:
DI::setContainer(/* ... */);

// create and run Yii DI-aware application:
Yii::createApplication(WebApplication::class, $config)->run();
```

Being DI-aware, application will resolve configured components via DI Container according to their configured class.
This allows you moving configuration into DI Container from Service Locator without breaking anything.
For example:

```php
<?php
// ...
use yii1tech\di\Container;
use yii1tech\di\DI;
use yii1tech\di\web\WebApplication;

// setup DI container:
DI::setContainer(
    Container::new()
        ->lazy(CDbConnection::class, function () {
            $db = new CDbConnection();
            $db->connectionString = 'mysql:host=127.0.0.1;dbname=example';
            $db->username = 'container_user';
            $db->password = 'secret';
            
            $db->init();
            
            return $db;
        })
        ->lazy(ICache::class, function (Container $container) {
            $cache = new CDbCache();
            $cache->setDbConnection($container->get(CDbConnection::class));
            
            $cache->init();
            
            return $cache;
        })
);

$config = [
    'components' => [
        'db' => [ // component 'db' will be fetched from container using ID 'CDbConnection'
            'class' => CDbConnection::class,
        ],
        'cache' => [ // component 'cache' will be fetched from container using ID 'ICache'
            'class' => ICache::class,
        ],
        'format' => [ // if component has no matching definition in container - it will be resolved in usual way
            'class' => CFormatter::class,
            'dateFormat' => 'Y/m/d',
        ],
    ],
];

// create and run Yii DI-aware application:
Yii::createApplication(WebApplication::class, $config)->run();
//...
$db = Yii::app()->getComponent('db');
var_dump($db->username); // outputs 'container_user'

$cache = Yii::app()->getComponent('cache');
var_dump(get_class($cache)); // outputs 'CDbCache'
```

**Heads up!** Be careful while moving Yii component definitions into DI container: remember to invoke method `init()` manually on them.
Yii often places crucial initialization logic into component's `init()`, if you omit its invocation you may receive broken component instance,
while fetching it directly from the container.

**Heads up!** If you move application component config to DI container, make sure to clean up its original configuration at Service Locator.
Any remaining "property-value" definition will be applied to the object fetched from container. It may be useful, when you define container
entity via factory, but in most cases it will cause you trouble.

> Tip: component placed into Yii Application (Service Locator) via DI container is not required to implement `\IApplicationComponent` interface,
  since method `init()` will never be automatically invoked on it.


### Dependency Injection in Web Application <span id="di-in-web-application"></span>

This extension allows injection of DI container entities into controller constructors and action methods based on arguments' type-hints.
However, this feature will not work on standard controllers extended directly from `CController` - you'll need to extend `\yii1tech\di\web\Controller`
class or use `\yii1tech\di\web\ResolvesActionViaDI` trait.
For example:

```php
<?php

use yii1tech\di\web\Controller;

class ItemController extends Controller
{
    /**
     * @var CDbConnection 
     */
    protected $db;
    
    // injects `CDbConnection` from DI container at constructor level
    public function __construct(CDbConnection $db, $id, $module = null)
    {
        parent::__construct($id, $module); // do not forget to invoke parent constructor
        
        $this->db = $db;
    }
    
    // injects `ICache` from DI container at action level
    public function actionIndex(ICache $cache)
    {
        // ...
    }
    
    // injects `ICache` from DI container at action level, populates `$id` from `$_GET`
    public function actionView(ICache $cache, $id)
    {
        // ...
    }
}
```

**Heads up!** While declaring your own controller's constructor, make sure it accepts parent's arguments `$id` and `$module`
and passes them to the parent constructor. Otherwise, controller may not function properly.


### Dependency Injection in Console Application <span id="di-in-console-application"></span>

This extension allows injection of DI container entities into console commands constructors and action methods based on arguments' type-hints.
However, this feature will not work on standard console commands extended directly from `CConsoleCommand` - you'll need to extend
`\yii1tech\di\console\ConsoleCommand` class or use `\yii1tech\di\console\ResolvesActionViaDI` trait.
For example:

```php
<?php

use yii1tech\di\console\ConsoleCommand;

class ItemCommand extends ConsoleCommand
{
    /**
     * @var CDbConnection 
     */
    protected $db;
    
    // injects `CDbConnection` from DI container at constructor level
    public function __construct(CDbConnection $db, $name, $runner)
    {
        parent::__construct($name, $runner); // do not forget to invoke parent constructor

        $this->db = $db;
    }
    
    // injects `ICache` from DI container at action level
    public function actionIndex(ICache $cache)
    {
        // ...
    }
    
    // injects `CFormatter` from DI container at action level, populates `$date` from shell arguments
    public function actionFormat(CFormatter $formatter, $date)
    {
        // ...
    }
}
```

**Heads up!** While declaring your own console command's constructor, make sure it accepts parent's arguments `$name` and `$runner`
and passes them to the parent constructor. Otherwise, console command may not function properly.


### External (3rd party) Container Usage <span id="external-contatiner-usage"></span>

Container `\yii1tech\di\Container` is very basic, and you may want to use something more sophisticated like [PHP-DI](https://php-di.org/) instead of it.
Any PSR-11 compatible container can be used within this extension. All you need is pass your container to `\yii1tech\di\DI::setContainer()`.
For example:

```php
<?php
// file '/public/index.php'
require __DIR__ . '../vendor/autoload.php';
// ...
use DI\ContainerBuilder;
use yii1tech\di\DI;
use yii1tech\di\web\WebApplication;

// use 'PHP-DI' for the container:
DI::setContainer(function () {
    $builder = new ContainerBuilder();
    
    $builder->useAutowiring(true);
    // ...
    
    return $builder->build();
});

// create and run Yii DI-aware application:
Yii::createApplication(WebApplication::class, $config)->run();
```

Many existing PSR-11 compatible solutions are coming with built-in injection mechanism, which you may want to utilize instead of the one,
provided by this extension. In order to do this, you should create your own injector, implementing `\yii1tech\di\InjectorContract` interface,
and pass it to `\yii1tech\di\DI::setInjector()`.

Many of the existing PSR-11 containers (including the 'PHP-DI') already implements dependency injection methods `make()` and `call()`.
You can use `\yii1tech\di\external\ContainerBasedInjector` to utilize these methods. For example:

```php
<?php
// file '/public/index.php'
require __DIR__ . '../vendor/autoload.php';
// ...
use DI\ContainerBuilder;
use yii1tech\di\DI;
use yii1tech\di\external\ContainerBasedInjector;
use yii1tech\di\web\WebApplication;

// use 'PHP-DI' for the container:
DI::setContainer(function () {
    $builder = new ContainerBuilder();
    // ...
    
    return $builder->build();
})
    ->setInjector(new ContainerBasedInjector()); // use `\DI\Container::make()` and `\DI\Container::call()` for dependency injection

// create and run Yii DI-aware application:
Yii::createApplication(WebApplication::class, $config)->run();
```

**Heads up!** Many of the existing PSR-11 containers, which provide built-in injection methods, implements method `\Psr\Container\ContainerInterface::has()`
in inconsistent way. While standard demands: it should return `true` only, if there is explicit binding inside the container - many solutions return
`true` even, if binding does not exist, but requested class **can be** resolved, e.g. its constructor arguments can be filled up using other bindings
from container. Such check is always comes with extra class and method reflection creation, which reduces the overall performance. This may cause a
significant impact, while resolving Yii application components via DI container.

It is recommended to resolve any inconsistency in `\Psr\Container\ContainerInterface::has()` implementation before using container with this extension.
This can be easily achieved using `\yii1tech\di\external\ContainerProxy` class. For example:

```php
<?php

// file '/public/index.php'
require __DIR__ . '../vendor/autoload.php';
// ...

use DI\Container;
use DI\ContainerBuilder;
use yii1tech\di\DI;
use yii1tech\di\external\ContainerProxy;

// use 'PHP-DI' for the container:
DI::setContainer(function () {
    $builder = new ContainerBuilder();
    // ...
    
    return ContainerProxy::new($builder->build())
        ->setCallbackForHas(function (Container $container, string $id) {
            return in_array($id, $container->getKnownEntryNames(), true);
        });
});
// ...
```
