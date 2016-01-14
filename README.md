The Puli Discovery Component
============================

[![Build Status](https://travis-ci.org/puli/discovery.svg?branch=1.0.0-beta9)](https://travis-ci.org/puli/discovery)
[![Build status](https://ci.appveyor.com/api/projects/status/wmg14bydks4xwqs2/branch/master?svg=true)](https://ci.appveyor.com/project/webmozart/discovery/branch/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/puli/discovery/badges/quality-score.png?b=1.0.0-beta9)](https://scrutinizer-ci.com/g/puli/discovery/?branch=1.0.0-beta9)
[![Latest Stable Version](https://poser.pugx.org/puli/discovery/v/stable.svg)](https://packagist.org/packages/puli/discovery)
[![Total Downloads](https://poser.pugx.org/puli/discovery/downloads.svg)](https://packagist.org/packages/puli/discovery)
[![Dependency Status](https://www.versioneye.com/php/puli:discovery/1.0.0/badge.svg)](https://www.versioneye.com/php/puli:discovery/1.0.0)

Latest release: [1.0.0-beta9](https://packagist.org/packages/puli/discovery#1.0.0-beta9)

PHP >= 5.3.9

The [Puli] Discovery Component supports binding of Puli resources to *binding
types*. Binding types can be defined with the `addBindingType()` method of the 
[`EditableDiscovery`] interface:

```php
use Puli\Discovery\Api\Type\BindingType;
use Puli\Discovery\Binding\Initializer\ResourceBindingInitializer;
use Puli\Discovery\InMemoryDiscovery;

$discovery = new InMemoryDiscovery(array(
    // $repo is a Puli\Repository\Api\ResourceRepository instance
    new ResourceBindingInitializer($repo),
));

$discovery->addBindingType(new BindingType('doctrine/xml-mapping'));
```

Resource Bindings
-----------------

Resources in the repository can then be bound to the defined type by passing a
`ResourceBinding` to `addBinding()`:

```php
use Puli\Discovery\Binding\ResourceBinding;

$discovery->addBinding(new ResourceBinding('/app/config/doctrine/*.xml', 'doctrine/xml-mapping'));
```

With `findBindings()`, you can later retrieve all the bindings for the type:

```php
foreach ($discovery->findBindings('doctrine/xml-mapping') as $binding) {
    foreach ($binding->getResources() as $resource) {
        // do something...
    }
}
```

The following [`Discovery`] implementations are currently supported:

* [`InMemoryDiscovery`]
* [`KeyValueStoreDiscovery`]
* [`NullDiscovery`]

Read the [Resource Discovery] guide in the Puli documentation to learn more
about resource discovery.

Class Bindings
--------------

You can also bind classes to binding types. By convention, the common interface
of all bound classes is used as binding type:

```php
$discovery->addBindingType(new BindingType(Plugin::class));
```

Classes can be bound by adding `ClassBinding` instances:

```php
use Puli\Discovery\Binding\ClassBinding;

$discovery->addBinding(new ClassBinding(MyPlugin::class, Plugin::class));
```

As before, use `findBindings()` to find all bindings for a binding type:

```php
foreach ($discovery->findBindings(Plugin::class) as $binding) {
    $pluginClass = $binding->getClassName();
    $plugin = new $pluginClass();
    
    // do something...
}
```

Authors
-------

* [Bernhard Schussek] a.k.a. [@webmozart]
* [The Community Contributors]

Installation
------------

Follow the [Installation guide] guide to install Puli in your project.

Documentation
-------------

Read the [Puli Documentation] to learn more about Puli.

Contribute
----------

Contributions to Puli are always welcome!

* Report any bugs or issues you find on the [issue tracker].
* You can grab the source code at Puli’s [Git repository].

Support
-------

If you are having problems, send a mail to bschussek@gmail.com or shout out to
[@webmozart] on Twitter.

License
-------

All contents of this package are licensed under the [MIT license].

[Puli]: http://puli.io
[Bernhard Schussek]: http://webmozarts.com
[The Community Contributors]: https://github.com/puli/discovery/graphs/contributors
[Resource Discovery]: http://docs.puli.io/en/latest/discovery/introduction.html
[Installation guide]: http://docs.puli.io/en/latest/installation.html
[Puli Documentation]: http://docs.puli.io/en/latest/index.html
[issue tracker]: https://github.com/puli/issues/issues
[Git repository]: https://github.com/puli/discovery
[@webmozart]: https://twitter.com/webmozart
[MIT license]: LICENSE
[`EditableDiscovery`]: http://api.puli.io/latest/class-Puli.Discovery.Api.EditableDiscovery.html
[`Discovery`]: http://api.puli.io/latest/class-Puli.Discovery.Api.Discovery.html
[`InMemoryDiscovery`]: http://api.puli.io/latest/class-Puli.Discovery.InMemoryDiscovery.html
[`KeyValueStoreDiscovery`]: http://api.puli.io/latest/class-Puli.Discovery.KeyValueStoreDiscovery.html
[`NullDiscovery`]: http://api.puli.io/latest/class-Puli.Discovery.NullDiscovery.html
