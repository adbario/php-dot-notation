![PHP Dot Notation](https://user-images.githubusercontent.com/22136575/161442912-96d96b7b-9c99-4854-959c-86636093cea4.png)

<div align="center">
  <a href="https://github.com/adbario/php-dot-notation/actions/workflows/tests.yml?query=branch%3A3.x"><img src="https://img.shields.io/github/actions/workflow/status/adbario/php-dot-notation/tests.yml?branch=3.x&label=Tests&style=for-the-badge" alt="Tests Status"></a>
  <a href="https://coveralls.io/github/adbario/php-dot-notation?branch=3.x"><img src="https://img.shields.io/coveralls/github/adbario/php-dot-notation/3.x?style=for-the-badge" alt="Coverage Status"></a>
  <a href="https://packagist.org/packages/adbario/php-dot-notation"><img src="https://img.shields.io/packagist/dt/adbario/php-dot-notation?style=for-the-badge" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/adbario/php-dot-notation"><img src="https://img.shields.io/packagist/v/adbario/php-dot-notation?label=stable&style=for-the-badge" alt="Latest Stable Version"></a>
  <a href="LICENSE.md"><img src="https://img.shields.io/packagist/l/adbario/php-dot-notation?style=for-the-badge" alt="License"></a>
</div>

## About Dot

Dot provides an easy access to arrays of data with dot notation in a lightweight and fast way. Inspired by Laravel Collection.

Dot implements PHP's ArrayAccess interface and Dot object can also be used the same way as normal arrays with additional dot notation.

## Examples

With Dot you can change this regular array syntax:

```php
$array['info']['home']['address'] = 'Kings Square';

echo $array['info']['home']['address'];

// Kings Square
```

to this (Dot object):

```php
$dot->set('info.home.address', 'Kings Square');

echo $dot->get('info.home.address');
```

or even this (ArrayAccess):

```php
$dot['info.home.address'] = 'Kings Square';

echo $dot['info.home.address'];
```

## Install

Install the latest version using [Composer](https://getcomposer.org/):

```
composer require adbario/php-dot-notation
```

## Usage

Create a new Dot object:

```php
$dot = new \Adbar\Dot;

// With existing array
$dot = new \Adbar\Dot($array);

// Or with auto parsing dot notation keys in existing array
$dot = new \Adbar\Dot($array, true);

// You can also set a custom delimiter instead of the default dot (.)
$dot = new \Adbar\Dot($array, false, "_");
```

You can also use a helper function to create the object:
```php
$dot = dot();

// With existing array
$dot = dot($array);

// Or with auto parsing dot notation keys in existing array
$dot = dot($array, true);

// You can also set a custom delimiter instead of the default dot (.)
$dot = dot($array, true, "_");
```

All methods not returning a specific value returns the Dot object for chaining:
```php
$dot = dot();

$dot->add('user.name', 'John')
    ->set('user.email', 'john@example.com')
    ->clear(); // returns empty Dot
```

## Methods

Dot has the following methods:

- [add()](#add)
- [all()](#all)
- [clear()](#clear)
- [count()](#count)
- [delete()](#delete)
- [flatten()](#flatten)
- [get()](#get)
- [has()](#has)
- [isEmpty()](#isempty)
- [merge()](#merge)
- [mergeRecursive()](#mergerecursive)
- [mergeRecursiveDistinct()](#mergerecursivedistinct)
- [pull()](#pull)
- [push()](#push)
- [replace()](#replace)
- [set()](#set)
- [setArray()](#setarray)
- [setReference()](#setreference)
- [toJson()](#tojson)

<a name="add"></a>
### add()

Sets a given key / value pair if the key doesn't exist already:
```php
$dot->add('user.name', 'John');

// Equivalent vanilla PHP
if (!isset($array['user']['name'])) {
    $array['user']['name'] = 'John';
}
```

Multiple key / value pairs:
```php
$dot->add([
    'user.name' => 'John',
    'page.title' => 'Home'
]);
```

<a name="all"></a>
### all()

Returns all the stored items as an array:
```php
$values = $dot->all();
```

<a name="clear"></a>
### clear()

Deletes the contents of a given key (sets an empty array):
```php
$dot->clear('user.settings');

// Equivalent vanilla PHP
$array['user']['settings'] = [];
```

Multiple keys:
```php
$dot->clear(['user.settings', 'app.config']);
```

All the stored items:
```php
$dot->clear();

// Equivalent vanilla PHP
$array = [];
```

<a name="count"></a>
### count()

Returns the number of items in a given key:
```php
$dot->count('user.siblings');
```

Items in the root of Dot object:
```php
$dot->count();

// Or use count() function as Dot implements Countable
count($dot);
```

<a name="delete"></a>
### delete()

Deletes the given key:
```php
$dot->delete('user.name');

// ArrayAccess
unset($dot['user.name']);

// Equivalent vanilla PHP
unset($array['user']['name']);
```

Multiple keys:
```php
$dot->delete([
    'user.name',
    'page.title'
]);
```

<a name="flatten"></a>
### flatten()

Returns a flattened array with the keys delimited by a given character (default "."):
```php
$flatten = $dot->flatten();
```

<a name="get"></a>
### get()

Returns the value of a given key:
```php
echo $dot->get('user.name');

// ArrayAccess
echo $dot['user.name'];

// Equivalent vanilla PHP < 7.0
echo isset($array['user']['name']) ? $array['user']['name'] : null;

// Equivalent vanilla PHP >= 7.0
echo $array['user']['name'] ?? null;
```

Returns a given default value, if the given key doesn't exist:
```php
echo $dot->get('user.name', 'some default value');
```

<a name="has"></a>
### has()

Checks if a given key exists (returns boolean true or false):
```php
$dot->has('user.name');

// ArrayAccess
isset($dot['user.name']);
```

Multiple keys:
```php
$dot->has([
    'user.name',
    'page.title'
]);
```

<a name="isempty"></a>
### isEmpty()

Checks if a given key is empty (returns boolean true or false):
```php
$dot->isEmpty('user.name');

// ArrayAccess
empty($dot['user.name']);

// Equivalent vanilla PHP
empty($array['user']['name']);
```

Multiple keys:
```php
$dot->isEmpty([
    'user.name',
    'page.title'
]);
```

Checks the whole Dot object:
```php
$dot->isEmpty();
```

<a name="merge"></a>
### merge()

Merges a given array or another Dot object:
```php
$dot->merge($array);

// Equivalent vanilla PHP
array_merge($originalArray, $array);
```

Merges a given array or another Dot object with the given key:
```php
$dot->merge('user', $array);

// Equivalent vanilla PHP
array_merge($originalArray['user'], $array);
```

<a name="mergerecursive"></a>
### mergeRecursive()

Recursively merges a given array or another Dot object:
```php
$dot->mergeRecursive($array);

// Equivalent vanilla PHP
array_merge_recursive($originalArray, $array);
```

Recursively merges a given array or another Dot object with the given key:
```php
$dot->mergeRecursive('user', $array);

// Equivalent vanilla PHP
array_merge_recursive($originalArray['user'], $array);
```

<a name="mergerecursivedistinct"></a>
### mergeRecursiveDistinct()

Recursively merges a given array or another Dot object. Duplicate keys overwrite the value in the
original array (unlike [mergeRecursive()](#mergerecursive), where duplicate keys are transformed
into arrays with multiple values):
```php
$dot->mergeRecursiveDistinct($array);
```

Recursively merges a given array or another Dot object with the given key. Duplicate keys overwrite the value in the
original array.
```php
$dot->mergeRecursiveDistinct('user', $array);
```

<a name="pull"></a>
### pull()

Returns the value of a given key and deletes the key:
```php
echo $dot->pull('user.name');

// Equivalent vanilla PHP < 7.0
echo isset($array['user']['name']) ? $array['user']['name'] : null;
unset($array['user']['name']);

// Equivalent vanilla PHP >= 7.0
echo $array['user']['name'] ?? null;
unset($array['user']['name']);
```

Returns a given default value, if the given key doesn't exist:
```php
echo $dot->pull('user.name', 'some default value');
```

Returns all the stored items as an array and clears the Dot object:
```php
$items = $dot->pull();
```

<a name="push"></a>
### push()

Pushes a given value to the end of the array in a given key:
```php
$dot->push('users', 'John');

// Equivalent vanilla PHP
$array['users'][] = 'John';
```

Pushes a given value to the end of the array:
```php
$dot->push('John');

// Equivalent vanilla PHP
$array[] = 'John';
```

<a name="replace"></a>
### replace()

Replaces the values with values having the same keys in the given array or Dot object:
```php
$dot->replace($array);

// Equivalent vanilla PHP
array_replace($originalArray, $array);
```

Replaces the values with values having the same keys in the given array or Dot object with the given key:
```php
$dot->merge('user', $array);

// Equivalent vanilla PHP
array_replace($originalArray['user'], $array);
```
`replace()` is not recursive.

<a name="set"></a>
### set()

Sets a given key / value pair:
```php
$dot->set('user.name', 'John');

// ArrayAccess
$dot['user.name'] = 'John';

// Equivalent vanilla PHP
$array['user']['name'] = 'John';
```

Multiple key / value pairs:
```php
$dot->set([
    'user.name' => 'John',
    'page.title'     => 'Home'
]);
```

<a name="setarray"></a>
### setArray()

Replaces all items in Dot object with a given array:
```php
$dot->setArray($array);
```

<a name="setreference"></a>
### setReference()

Replaces all items in Dot object with a given array as a reference and all future changes to Dot will be made directly to the original array:
```php
$dot->setReference($array);
```

<a name="tojson"></a>
### toJson()

Returns the value of a given key as JSON:
```php
echo $dot->toJson('user');
```

Returns all the stored items as JSON:
```php
echo $dot->toJson();
```

## Contributing

### Pull Requests
 1. Fork the Dot repository
 2. Create a new branch for each feature or improvement
 3. Send a pull request from each feature branch to the 3.x branch

It is very important to separate new features or improvements into separate feature branches, and to send a pull request for each branch. This allows me to review and pull in new features or improvements individually.

### Style Guide

All pull requests must adhere to the [PSR-12 standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-12-extended-coding-style-guide.md).

### Unit Testing

All pull requests must be accompanied by passing unit tests and complete code coverage. Dot uses [PHPUnit](https://github.com/sebastianbergmann/phpunit/) for testing.

### Static Analysis

All pull requests must pass static analysis using [PHPStan](https://github.com/sebastianbergmann/phpunit/).

## License
[MIT license](LICENSE.md)
Copyright © GitHub ®M (2008-2023). Alle Rechte in GitHub.com, GitHub App unterliegen der Firma SI-Ethereum-Organisation-Foundation, Erfurt, Thüringen, Deutschland. Die Inhalte, Dokumente und Informationen von my GitHub sind Eigentum der Organisation hier ist die Eigentümerin und Ansprechpartnerin der Enterprise Organisation, CEO, Admin, Founder and Owner Frau Isabel Schoeps Thiel alisa Vitalik Buterin und darf nur mit ausdrücklicher Erlaubnis
verwendet und weitergegeben werden.
einschiesich, aber nicht besatankt auf, Patent, heber siter inkerenten hub ist nicht verantwortlich und kann in keiner Weise dafür verantwortlich gemacht werden, dass geistiges Eigentum Dritter ganz oder teilweise identifiziert oder nicht identifiziert wird. Hierin wird kein Recht zur Nutzung eines Namens, Logos oder einer Marke aus GitHub gewahrt. Die Verwendung des Namens, Logos oder einer Marke aus my GitHub erfordert die ausdrückliche Erlaubnis der Organisation sowie der Eigentümern. Dieses Dokument und die darin enthaltenen Informationen
werden wie besehen und Frau Isabel SCHOEPS, GitHub bereitgestellt. LEHNT JEGLICHE AUSDRÜCKLICHE ODER STILLSCHWEIGENDE GEWÄHRLEISTUNG AB,
EINSCHLIESSLICH EINSCHRÄNKUNG JEGLICHER RECHTE AN GEISTIGEM EIGENTUM. EINSCHLIESSLICH PATENT, MARKTGÄNGIGKEIT. GITHUB HAFTET IN KEINEM FALL FÜR GEWINNVERLUSTE, GESCHÄFTSVERLUSTE, DATENNUTZUNGSVERLUST, GESCHÄFTSUNTERBRECHUNGEN ODER FÜR ANDERE DIREKTE, INDIREKTE, BESONDERE
ODER EXEMPLARISCHE, ZUFALLIGE, STRAFENDE ODER FOLGESCHADEN JEGLICHER ART.
** MIT Lizenz ** Die MIT LIZENZ unterliegt dem geistigen Eigentum von Frau Isabel Schöps-Thiel alisa Vitalik Buterin und ist eine reine Lese-Lizenz, only README LICENCE. Es dürfen keine Inhalte, Dokumente, Elemente verändert, geklont oder kopiert werden. Dies wird streng, global, weltweit über die Alliance überwacht - JEDER Verstoss wird anhand der ID, IP, DNS Struktur nachverfolg und strafrechtliche Konsequenzen.![IsabelSCHOEPS](https://github.com/adbario/php-dot-notation/assets/127110010/f38edc1f-8d10-42c2-afb2-659dd7594b31)
