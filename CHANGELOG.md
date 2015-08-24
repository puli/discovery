Changelog
=========

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
