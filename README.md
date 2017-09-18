Data Sanitize
=============

*By [endroid](http://endroid.nl/)*

[![Latest Stable Version](http://img.shields.io/packagist/v/endroid/data-sanitize.svg)](https://packagist.org/packages/endroid/data-sanitize)
[![Build Status](http://img.shields.io/travis/endroid/DataSanitize.svg)](http://travis-ci.org/endroid/DataSanitize)
[![Total Downloads](http://img.shields.io/packagist/dt/endroid/data-sanitize.svg)](https://packagist.org/packages/endroid/data-sanitize)
[![Monthly Downloads](http://img.shields.io/packagist/dm/endroid/data-sanitize.svg)](https://packagist.org/packages/endroid/data-sanitize)
[![License](http://img.shields.io/packagist/l/endroid/data-sanitize.svg)](https://packagist.org/packages/endroid/data-sanitize)

This bundle provides a user interface and some commands for merging and
cleaning entities.

[![knpbundles.com](http://knpbundles.com/endroid/DataSanitize/badge-short)](http://knpbundles.com/endroid/DataSanitize)

## Installation

Use [Composer](https://getcomposer.org/) to install the bundle.

``` bash
$ composer require endroid/data-sanitize
```

Then enable the bundle via the kernel. Only add the demo bundle if you need it.

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        // ...
        new Endroid\DataSanitize\Bundle\DataSanitizeBundle\EndroidDataSanitizeBundle(),
        new Endroid\DataSanitize\Bundle\DataSanitizeDemoBundle\EndroidDataSanitizeDemoBundle(),
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
            class: Endroid\DataSanitize\Bundle\DataSanitizeDemoBundle\Entity\Project
            fields: [ 'id', 'name' ]
        user:
            class: Endroid\DataSanitize\Bundle\DataSanitizeDemoBundle\Entity\User
            fields: [ 'id', 'name' ]
        task:
            class: Endroid\DataSanitize\Bundle\DataSanitizeDemoBundle\Entity\Task
            fields: [ 'id', 'name', 'project', 'user' ]
        tag:
            class: Endroid\DataSanitize\Bundle\DataSanitizeDemoBundle\Entity\Tag
            fields: [ 'id', 'name' ]
```

## Usage

The bundle provides some example data to demonstrate the use. Use the following
command to generate the example data.
 
``` bash
bin/console endroid:data-sanitize-demo:generate-data
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
