Changelog
=========

* 1.0.0-beta9 (2016-01-14)

 * decoupled from puli/repository
 * decoupled from webmozart/glob
 * added `JsonDiscovery`

* 1.0.0-beta8 (2015-10-05)

 * removed `$repo` argument from the constructors of `Discovery` implementations
 * added `$initializers` argument to the constructors of `Discovery` 
   implementations
 * renamed `ResourceDiscovery` to `Discovery`
 * renamed `ResourceDiscovery::findByType()` to `Discovery::findBindings()`
 * renamed `ResourceDiscovery::isTypeDefined()` to `Discovery::hasBindingType()`
 * renamed `ResourceDiscovery::getDefinedType()` to `Discovery::getBindingType()`
 * renamed `ResourceDiscovery::getDefinedTypes()` to `Discovery::getBindingTypes()`
 * removed `ResourceDiscovery::findByPath()`
 * added `Discovery::hasBindings()`
 * added `Discovery::hasBinding()`
 * added `Discovery::getBinding()`
 * added `Discovery::hasBindingTypes()`
 * renamed `EditableDiscovery::bind()` to `EditableDiscovery::addBinding()`
 * renamed `EditableDiscovery::unbind()` to `EditableDiscovery::removeBinding()`
 * renamed `EditableDiscovery::defineType()` to `EditableDiscovery::addBindingType()`
 * renamed `EditableDiscovery::undefineType()` to `EditableDiscovery::removeBindingType()`
 * renamed `EditableDiscovery::clear()` to `EditableDiscovery::removeBindingTypes()`
 * added `EditableDiscovery::removeBindings()`
 * added `Binding`
 * removed `ResourceBinding` interface
 * added `ResourceBinding` class
 * added `BindingInitializer`
 * added `NotInitializedException`
 * added `NoSuchBindingException`
 * added `BindingNotAcceptedException`
 * moved `BindingParameter` to `Puli\Discovery\Api\Type` namespace
 * moved `BindingType` to `Puli\Discovery\Api\Type` namespace
 * moved `DuplicateTypeException` to `Puli\Discovery\Api\Type` namespace
 * moved `MissingParameterException` to `Puli\Discovery\Api\Type` namespace
 * moved `NoSuchParameterException` to `Puli\Discovery\Api\Type` namespace
 * moved `NoSuchTypeException` to `Puli\Discovery\Api\Type` namespace
 * added parameter `$acceptedBindings` to constructor of `BindingType`
 * added `BindingType::hasParameters()`
 * added `BindingType::acceptsBinding()`
 * added `BindingType::getAcceptedBindings()`
 * removed `ParameterValidator` interface
 * renamed `SimpleParameterValidator` class to `ParameterValidator`
 * changed `AbstractBinding` to implement `Binding`
 * added `ClassBinding`
 * removed `EagerBinding`
 * removed `LazyBinding`
 * added `ResourceBindingInitializer`
 * adapted data structures stored by `KeyValueStoreDiscovery`
 * added support for search/removal using `Expression` instances

* 1.0.0-beta7 (2015-08-24)

 * fixed minimum package versions in composer.json

* 1.0.0-beta6 (2015-08-12)

 * upgraded to webmozart/glob 3.0

* 1.0.0-beta5 (2015-05-29)R

 * fixed: no exception is thrown by `KeyValueStoreDiscovery::findByPath()` if
   the discovery contains the requested type, but no bindings
 * fixed: no exception is thrown by `InMemoryDiscovery::findByPath()` if
   the discovery contains the requested type, but no bindings
 * `ResourceDiscovery::findByPath()` now throws an exception if the path does
   not exist

* 1.0.0-beta4 (2015-04-13)

 * `LazyBinding` does not cache resources anymore in case the repository 
   contents changed since the last call
 * removed `NoQueryMatchesException`
 * changed boolean parameter `$required` to integer parameter `$flags` in
   `BindingParameter::__construct()`
 * removed `$code` arguments from static exception factory methods
 * upgraded to webmozart/glob 2.0

* 1.0.0-beta3 (2015-03-19)

 * replaced `Assert` by webmozart/assert
 * renamed `ResourceDiscovery::find()` to `findByType()`
 * split `ResourceDiscovery::getBindings()` into `findByPath()` and
   `getBindings()` without arguments
 
* 1.0.0-beta2 (2015-01-27)

 * added `NullDiscovery`
 * changed `ResourceDiscovery::find()` and `getBindings()` to throw a
   `NoSuchTypeException` if the type has not been defined
 * removed dependency to beberlei/assert

* 1.0.0-beta (2015-01-12)

 * first release
