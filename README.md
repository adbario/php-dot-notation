![PHP Dot Notation](https://user-images.githubusercontent.com/22136575/161442912-96d96b7b-9c99-4854-959c-86636093cea4.png)

<div align="center">

[![ci](https://img.shields.io/github/actions/workflow/status/adbario/php-dot-notation/ci.yml?label=ci&style=for-the-badge)](https://github.com/adbario/php-dot-notation/actions)
[![Coverage](https://img.shields.io/coveralls/github/adbario/php-dot-notation/3.x?style=for-the-badge)](https://coveralls.io/github/adbario/php-dot-notation?branch=3.x)
[![Stable](https://img.shields.io/packagist/v/adbario/php-dot-notation?label=stable&color=007ec6&style=for-the-badge)](https://packagist.org/packages/adbario/php-dot-notation)
[![PHP](https://img.shields.io/badge/php-7.4_--_8.6-777bb4?style=for-the-badge)](https://packagist.org/packages/adbario/php-dot-notation)
[![Downloads](https://img.shields.io/packagist/dt/adbario/php-dot-notation?color=007ec6&style=for-the-badge)](https://packagist.org/packages/adbario/php-dot-notation)
[![License](https://img.shields.io/packagist/l/adbario/php-dot-notation?color=67ac09&style=for-the-badge)](LICENSE)[![PHPStan](https://img.shields.io/badge/phpstan-level_8-44bb00?style=for-the-badge)](https://github.com/adbario/php-dot-notation/actions/workflows/ci.yml)

</div>

## About Dot

Dot gives you easy access to nested PHP arrays using dot notation. Instead of chaining `$array['user']['address']['city']` and checking every level with `isset()`, you write `$dot->get('user.address.city')`.

It’s lightweight and inspired by Laravel’s collections. A Dot object also implements `ArrayAccess` and `Countable`, so you can keep using it like a normal array, with dot notation on top.

```php
$dot = dot(['user' => ['name' => 'Jane']]);

$dot->get('user.name'); // 'Jane'
$dot->set('user.address.city', 'Helsinki');
$dot->has('user.address.city'); // true

$dot['user.email'] = 'jane@example.com'; // works like an array too
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

// Or with auto-parsing dot notation keys in an existing array
$dot = new \Adbar\Dot($array, true);

// You can also set a custom delimiter instead of the default dot (.)
$dot = new \Adbar\Dot($array, false, "_");
```

You can also use a helper function to create the object:

```php
$dot = dot();

// With existing array
$dot = dot($array);

// Or with auto-parsing dot notation keys in an existing array
$dot = dot($array, true);

// You can also set a custom delimiter instead of the default dot (.)
$dot = dot($array, true, "_");
```

All methods that do not return a specific value return the Dot object for chaining:

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

Sets a given key/value pair if the key doesn't exist already:

```php
$dot->add('user.name', 'John');

// Equivalent vanilla PHP
if (!isset($array['user']['name'])) {
    $array['user']['name'] = 'John';
}
```

Multiple key/value pairs:

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

Returns the number of items in a given key. A non-countable value counts as
one item and a missing key counts as zero items:

```php
$dot->count('user.siblings');
```

Items in the root of the Dot object:

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

// Equivalent vanilla PHP
echo $array['user']['name'] ?? null;
```

Returns a given default value if the given key doesn't exist:

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

// Equivalent vanilla PHP
echo $array['user']['name'] ?? null;
unset($array['user']['name']);
```

Returns a given default value if the given key doesn't exist:

```php
echo $dot->pull('user.name', 'some default value');
```

Returns all the stored items as an array and clears the Dot object:

```php
$items = $dot->pull();
```

<a name="push"></a>

### push()

Pushes a given value to the end of the array at a given key:

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

If the given key already holds a non-array, non-null value, the value is not
pushed and the Dot object is left unchanged.

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
$dot->replace('user', $array);

// Equivalent vanilla PHP
array_replace($originalArray['user'], $array);
```

`replace()` is not recursive.

<a name="set"></a>

### set()

Sets a given key/value pair:

```php
$dot->set('user.name', 'John');

// ArrayAccess
$dot['user.name'] = 'John';

// Equivalent vanilla PHP
$array['user']['name'] = 'John';
```

Multiple key/value pairs:

```php
$dot->set([
    'user.name' => 'John',
    'page.title' => 'Home'
]);
```

<a name="setarray"></a>

### setArray()

Replaces all items in the Dot object with a given array:

```php
$dot->setArray($array);
```

<a name="setreference"></a>

### setReference()

Replaces all items in the Dot object with a given array by reference, and all future changes to Dot will be made directly to the original array:

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

You can also pass [JSON encoding options](https://www.php.net/manual/en/json.constants.php):

```php
// For a given key
echo $dot->toJson('user', JSON_PRETTY_PRINT);

// For all the stored items
echo $dot->toJson(JSON_PRETTY_PRINT);
```

## Contributing

### Pull Requests

1.  Fork the Dot repository
2.  Create a new branch for each feature or improvement
3.  Send a pull request from each feature branch to the 3.x branch

Keep new features and improvements in separate feature branches, and submit a pull request for each branch. This lets us review and merge new features or improvements individually.

### Style Guide

All pull requests must adhere to the [PSR-12 standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-12-extended-coding-style-guide.md).

### Unit Testing

All pull requests must be accompanied by passing unit tests and complete code coverage. Dot uses [PHPUnit](https://github.com/sebastianbergmann/phpunit/) for testing.

### Static Analysis

All pull requests must pass static analysis using [PHPStan](https://github.com/phpstan/phpstan).

## License

[MIT license](LICENSE.md)
