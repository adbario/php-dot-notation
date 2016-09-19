# Dot - PHP dot notation array access
Easy access to multidimensional arrays with dot notation.
With dot notation, your code is cleaner and handling deeper arrays is super easy.

This class implements PHP's ArrayAccess class, so Dot object can also be used same way as normal arrays but with dot notation.

With Dot you can change this:

    echo $data['info']['home']['address'];

to this:

    echo $data->get('info.home.address');

or even this:

    echo $data['info.home.address'];

## Installation

Via composer:

    composer require adbario/php-dot-notation

Or just copy the class file Dot.php and handle namespace yourself.

## Usage

This array will be used as a reference on this guide:

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

### Create Dot object

To start without any data, just create a new Dot object:

    $data = new \AdBar\Dot;

If you have an array already available, inject it to Dot object:

    $data = new \AdBar\Dot($array);
    
Set data after creating Dot object:

    $data->setData($array);
    
Set data as a reference, and all changes will be made directly to original array:

    $data->setDataAsRef($array);

### Set value

Set i.e. phone number in 'home' array:
    
    $data->set('info.home.tel', '09-123-456-789');
    
    // Array style
    $data['info.home.tel'] = '09-123-456-789';

Set multiple values at once:

    $data->set([
        'user.haircolor'    => 'blue',
        'info.home.address' => 'Private Lane 1'
    ]);

If value already exists, Dot will override it with new value.

### Get value

    echo $data->get('info.home.address');
    
    // Default value if path doesn't exist
    echo $data->get('info.home.country', 'some default value');
    
    // Array style
    echo $data['info.home.address'];

### Add value

    $data->add('info.kids', 'Amy');

Multiple values at once:

    $data->add('info.kids', [
        'Ben', 'Claire'
    ]);

### Check if value exists

    if ($data->has('info.home.address')) {
        // Do something...
    }
    
    // Array style
    if (isset($data['info.home.address'])) {
        // Do something...
    }

### Delete value

    $data->delete('info.home.address');
    
    // Array style
    unset($data['info.home.address']);

Multiple values at once:

    $data->delete([
        'user.lastname', 'info.home.address'
    ]);

### Clear values

Delete all values from path:

    $data->clear('info.home');
    
If path doesn't exist, create an empty array on it

    $data->clear('info.home.rooms', true);

Clear multiple paths at once:

    $data->clear([
        'user', 'info.home'
    ]);
    
Clear all data:

    $data->clear();

### Magic methods

Magic methods can be used to handle single level data (without dot notation). These examples are not using the same data array as examples above.

Set value:

    $data->name = 'John';

Get value:

    echo $data->name;

Check if value exists:

    if (isset($data->name)) {
        // Do something...
    }

Delete value:

    unset($data->name);
