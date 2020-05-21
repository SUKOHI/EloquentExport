# EloquentExport
A Laravel package that allows you to export data from your database through Eloquent model.
This package is maintained under Laravel 6.x.

# Installation

Execute the following command.

    composer require sukohi/eloquent-export:2.*
    
Your Eloquent model already has `export()` method after installation.

# Usage

## The simplest way

Call `export()` in your controller.  
Only filename required.

    $users = \App\User::get();
    
    // csv
    return $users->export('test.csv');
    
    // xlsx
    return $users->export('test.xlsx');
    
    // xls
    return $users->export('test.xls');
    
## Select column

Downloading data will be narrowed with `select()` method.

    $users = \App\User::select('id', 'name', 'email')->get();
    $users->export('test.csv);  // Only id, name and email data.

## Options

### Rendering

You can change downloading data with key, dot-notation-key, null, callback(s) as follows.

    $users = \App\User::get();
    $options = [
        'renders' => [
            'id', // $user->id
            'company.name', // $user->company->name
            null, // skipping column
            function($user) {
    
                return $user->created_at->format('Y.m.d');
    
            }
        ]
    ];
    return $users->export('test.xls', $options);

### Encoding

You can specify an encoding to convert data just when downloading csv file.

    $options = ['encoding' => 'sjis-win'];
    return $users->export('test.csv', $options);

### Additional data

    $users = \App\User::get();
    $options = [
        'prepend' => [
            ['header - 1', 'header - 2', 'header - 3']
        ],
        'append' => [
            ['footer - 1', 'footer - 2', 'footer - 3']
        ]
    ];
    return $users->export('test.xls', $options);

# License
This package is licensed under the MIT License.

Copyright 2019 Sukohi Kuhoh
