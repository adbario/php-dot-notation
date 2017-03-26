# Dot - PHP dot notation array access
Easy access to multidimensional arrays with dot notation.
With dot notation, your code is cleaner and handling deeper arrays is super easy.

This class implements PHP's ArrayAccess class, so Dot object can also be used the same way as normal arrays with additional dot notation.

With Dot you can change this:

```php
echo $data['info']['home']['address'];
```

to this:

```php
echo $data->get('info.home.address');
```

or even this:

```php
echo $data['info.home.address'];
```

## Installation

Via composer:

```
composer require adbario/php-dot-notation
```

Or just copy the class file Dot.php and handle namespace yourself.

#### With [Composer](https://getcomposer.org/):

```
composer require adbario/php-dot-notation
```

#### Manual installation:
1. Download the latest release
2. Extract the files into your project
3. require_once '/path/to/php-dot-notation/src/Dot.php';

## Usage

This array will be used as a reference on this guide:

```php
$array = [
    'user' => [
        'firstname' => 'John',
        'lastname'  => 'Smith'
    ],
    'info' => [
        'kids' => [
            0 => 'Laura',
            1 => 'Chris',
            2 => 'Little Johnny'
        ],
        'home' => [
            'address' => 'Rocky Road 3'
        ]
    ]
];
```

### Create a Dot object

To start with an empty array, just create a new Dot object:

```php
$data = new \Adbar\Dot;
```

If you have an array already available, inject it to the Dot object:

```php
$data = new \Adbar\Dot($array);
```

Set an array after creating the Dot object:

```php
$data->setArray($array);
```

Set an array as a reference, and all changes will be made directly to the original array:

```php
$data->setReference($array);
```

### Set a value

Set i.e. a phone number in the 'home' array:

```php
$data->set('info.home.tel', '09-123-456-789');

// Array style
$data['info.home.tel'] = '09-123-456-789';
```

Set multiple values at once:

```php
$data->set([
    'user.haircolor'    => 'blue',
    'info.home.address' => 'Private Lane 1'
]);
```

If the value already exists, Dot will override it with a new value.

### Get a value

```php
echo $data->get('info.home.address');

// Default value if the path doesn't exist
echo $data->get('info.home.country', 'some default value');

// Array style
echo $data['info.home.address'];
```

Get all the stored values:

```php
$values = $data->all();
```

Get a value from a path and remove it:

```php
$address = $data->pull('home.address');
```

Get all the stored values and remove them:

```php
$values = $data->pull();
```

### Add a value

```php
$data->add('info.kids', 'Amy');
```

Multiple values at once:

```php
$data->add('info.kids', [
    'Ben', 'Claire'
]);
```

### Check if a value exists

```php
if ($data->has('info.home.address')) {
    // Do something...
}

// Array style
if (isset($data['info.home.address'])) {
    // Do something...
}
```

### Delete a value

```php
$data->delete('info.home.address');

// Array style
unset($data['info.home.address']);
```

Multiple values at once:

```php
$data->delete([
    'user.lastname', 'info.home.address'
]);
```

### Clear values

Delete all the values from a path:

```php
$data->clear('info.home');
```

Clear multiple paths at once:

```php
$data->clear([
    'user', 'info.home'
]);
```

Clear all data:

```php
$data->clear();
```

### Sort the values

You can sort the values of a given path or all the stored values.

Sort the values of a path:

```php
$kids = $data->sort('info.kids');

// Sort recursively
$info = $data->sort('info');
```

Sort all the values

```php
$sorted = $data->sort();

// Sort recursively
$sorted = $data->sort();
```

### Magic methods

Magic methods can be used to handle single level data (without dot notation). These examples are not using the same data array as examples above.

Set a value:

```php
$data->name = 'John';
```

Get a value:

```php
echo $data->name;
```

Check if a value exists:

```php
if (isset($data->name)) {
    // Do something...
}
```

Delete a value:

```php
unset($data->name);
```
