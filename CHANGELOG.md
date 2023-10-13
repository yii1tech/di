Yii1 Dependency Injection extension
===================================

1.0.4 Under Development
-----------------------

- Bug: Fixed `Module::setComponent()` unable to override component resolved from DI container (klimov-paul)


1.0.3, August 30, 2023
----------------------

- Bug: Fixed `DI::create()` unable to handle Yii-style class alias (klimov-paul)
- Bug: Fixed controller action invocation with wrong set of arguments results in 500 error instead of 400 (klimov-paul)


1.0.2, August 16, 2023
----------------------

- Bug: Fixed `ResolvesComponentViaDI` triggers PHP Error on attempt to get unexisting component (klimov-paul)
- Bug: Fixed `ResolvesComponentViaDI` triggers PHP Error on attempt to get component defined by string class name instead of array (klimov-paul)


1.0.1, August 3, 2023
---------------------

- Bug: Fixed `ContainerProxy` does not clones wrapped container on its own cloning (klimov-paul)


1.0.0, July 28, 2023
--------------------

- Initial release.
