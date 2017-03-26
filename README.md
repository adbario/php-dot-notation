# Dot - PHP dot notation array access
Easy access to multidimensional arrays with dot notation.
With dot notation, your code is cleaner and handling deeper arrays is super easy.

This class implements PHP's ArrayAccess class, so Dot object can also be used same way as normal arrays but with dot notation.

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

### Create Dot object

To start without any data, just create a new Dot object:

```php
$data = new \AdBar\Dot;
```

If you have an array already available, inject it to Dot object:

```php
$data = new \AdBar\Dot($array);
```

Set data after creating Dot object:

```php
$data->setData($array);
```

Set data as a reference, and all changes will be made directly to original array:

```php
$data->setDataAsRef($array);
```

### Set value

Set i.e. phone number in 'home' array:

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

If value already exists, Dot will override it with new value.

### Get value

```php
echo $data->get('info.home.address');

// Default value if path doesn't exist
echo $data->get('info.home.country', 'some default value');

// Array style
echo $data['info.home.address'];
```

### Add value

```php
$data->add('info.kids', 'Amy');
```

Multiple values at once:

```php
$data->add('info.kids', [
    'Ben', 'Claire'
]);
```

### Check if value exists

```php
if ($data->has('info.home.address')) {
    // Do something...
}

// Array style
if (isset($data['info.home.address'])) {
    // Do something...
}
```

### Delete value

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

Delete all values from path:

```php
$data->clear('info.home');
```

If path doesn't exist, create an empty array on it

```php
$data->clear('info.home.rooms', true);
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

### Magic methods

Magic methods can be used to handle single level data (without dot notation). These examples are not using the same data array as examples above.

Set value:

```php
$data->name = 'John';
```

Get value:

```php
echo $data->name;
```

Check if value exists:

```php
if (isset($data->name)) {
    // Do something...
}
```

Delete value:

```php
unset($data->name);
``
