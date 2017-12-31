Data Sanitize Bundle
====================

*By [endroid](https://endroid.nl/)*

[![Latest Stable Version](http://img.shields.io/packagist/v/endroid/data-sanitize-bundle.svg)](https://packagist.org/packages/endroid/data-sanitize-bundle)
[![Build Status](http://img.shields.io/travis/endroid/EndroidDataSanitizeBundle.svg)](http://travis-ci.org/endroid/EndroidDataSanitizeBundle)
[![Total Downloads](http://img.shields.io/packagist/dt/endroid/data-sanitize-bundle.svg)](https://packagist.org/packages/endroid/data-sanitize-bundle)
[![Monthly Downloads](http://img.shields.io/packagist/dm/endroid/data-sanitize-bundle.svg)](https://packagist.org/packages/endroid/data-sanitize-bundle)
[![License](http://img.shields.io/packagist/l/endroid/data-sanitize-bundle.svg)](https://packagist.org/packages/endroid/data-sanitize-bundle)

This bundle provides a user interface and some commands for merging and
cleaning entities.

[![knpbundles.com](http://knpbundles.com/endroid/EndroidDataSanitizeBundle/badge-short)](http://knpbundles.com/endroid/EndroidDataSanitizeBundle)

## Requirements

* Symfony

## Installation

Use [Composer](https://getcomposer.org/) to install the bundle.

``` bash
$ composer require endroid/data-sanitize-bundle
```

Then enable the bundle via the kernel.

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        // ...
        new Endroid\Bundle\DataSanitizeBundle\EndroidDataSanitizeBundle(),
    ];
}
```

## Routing

Add the following section to your routing to be able to visit the routes
provided by this bundle.

``` yml
EndroidDataSanitizeBundle:
    resource: "@EndroidDataSanitizeBundle/Controller/"
    type:     annotation
    prefix:   /data-sanitize
```

## Configuration

By default entities and relations are derived from their doctrine class meta
data. In the configuration you define the entities and properties to list.

``` yml
endroid_data_sanitize:
    entities:
        project:
            class: Endroid\Bundle\DataSanitizeBundle\Entity\Project
            fields: [ 'id', 'name' ]
        user:
            class: Endroid\Bundle\DataSanitizeBundle\Entity\User
            fields: [ 'id', 'name' ]
        task:
            class: Endroid\Bundle\DataSanitizeBundle\Entity\Task
            fields: [ 'id', 'name', 'project', 'user' ]
        tag:
            class: Endroid\Bundle\DataSanitizeBundle\Entity\Tag
            fields: [ 'id', 'name' ]
```

## Usage

The bundle provides some example data to demonstrate the use. Use the following
command to load the example data. Then import or copy the
[example configuration](https://github.com/endroid/EndroidDataSanitizeBundle/blob/master/src/Resources/config/example.yml).
 
``` bash
bin/console endroid:data-sanitize:load-example-data
```

## ORM Mappings

In case you have auto mapping enabled and don't want the entities provided by
this bundle to be included update your doctrine.orm mapping accordingly.

``` yml
doctrine:
    orm:
        mappings:
            EndroidDataSanitizeBundle:
                mapping: false
```

## Development

The production version makes use of built assets. Use the following commands to
install dependencies and create a new build.

``` bash
npm install
NODE_ENV=production node_modules/.bin/webpack
```

## Versioning

Version numbers follow the MAJOR.MINOR.PATCH scheme. Backwards compatibility
breaking changes will be kept to a minimum but be aware that these can occur.
Lock your dependencies for production and test your code when upgrading.

## License

This bundle is under the MIT license. For the full copyright and license
information please view the LICENSE file that was distributed with this source code.
