# acrosstime 

This is the centralized codebase for acrossti.me.

## Requirements

* PHP (>= 5.3.0)
* A database supported by [Propel][]
* Apache2 with mod_rewrite

## External Libraries

External libraries or tools used directly for development, not including dependencies:

* [Composer][]
* [Klein.php][] (2.0.1)
* [html5shiv][] (3.6.2)
* [jQuery][] (1.10.2)
* [Bootstrap][] (2.3.2)
* [Propel][] (1.6.8)
* [Sass][] (3.2.9)
* [Compass][] (0.12.2)

## Configuration

* Write [Propel][] `build.properties` and `runtime-conf.xml` files to the specifications of the working database. 
* Save these files to `/db` alongside `schema.xml`. 
* Run `propel-gen runtime-conf` inside `/db`. 

For more information, read the [Propel documentation on building models](http://propelorm.org/documentation/02-buildtime.html#building-the-model).

[Composer]: http://getcomposer.org/
[Klein.php]: https://github.com/chriso/klein.php
[html5shiv]: https://code.google.com/p/html5shiv/
[jQuery]: http://jquery.com/
[Bootstrap]: http://twitter.github.io/bootstrap/
[Propel]: http://propelorm.org/
[Sass]: http://sass-lang.com/
[Compass]: http://compass-style.org/