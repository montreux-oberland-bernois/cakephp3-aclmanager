# CakePHP 3.x Acl Manager
Acl Manager For CakePHP 3.x 

## Installation

### Composer

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require montreux-oberland-bernois/cakephp3-aclmanager
```

## Getting started

* Install the CakePHP ACL plugin by running *composer require cakephp/acl*. [Read Acl plugin documentation](https://github.com/cakephp/acl).
* Include the Acl and AclManager plugins in *app/config/bootstrap.php*

```php
    Plugin::load('Acl', ['bootstrap' => true]);
    Plugin::load('AclManager', ['bootstrap' => true, 'routes' => true]);
```

## Creating ACL tables

To create ACL related tables, run the following Migrations command:

    bin/cake migrations migrate -p Acl

## Usage

Now navigate to *admin/AclManager/Acl*, update your acos and your aros or just click *Restore to default*.

For specified user or group managing, you can pass one or multiple id in params (ex. admin/AclManager/acl/Permissions/Users?id=2, 42).

This plugin is adapted for [AdminLTE Template](https://almsaeedstudio.com/themes/AdminLTE).

## About CakePHP 3.x AclManager

CakePHP 3.x - Acl Manager was inspired by the plugin of [Jean-Christophe Pires (JcPires)](https://github.com/JcPires/CakePhp3-AclManager) and [Iván Amat](https://github.com/ivanamat/cakephp3-aclmanager).

## Licensed

[MIT License](https://opensource.org/licenses/MIT)
