# EloquentExport
A Laravel package that allows you to export data from database through Eloquent model.

# Installation

Run the following command.

    composer require sukohi/eloquent-export:1.*
    
# Usage

Your Eloquent model has `export()` method after installation.

## The simplest way

Use `export()` method in your controller.  
Only filename is required.

    $users = \App\User::get();
    
    // csv
    return $users->export('test.csv');
    
    // xlsx
    return $users->export('test.xlsx');
    
    // xls
    return $users->export('test.xls');
    
## Select column

Downloading data will be narrowed by using `select()` method.

    $users = \App\User::select('id', 'name', 'email')->get();
    $users->export('test.csv);  // Only id, name and email data.

## with filters

You can change data using callback(s) as follows.

    $users = \App\User::get();

    $filters = [
        function($user) {

            return 'Sir '. $user->name;

        },
        function($user) {

            return $user->created_at->format('Y.m.d');

        }
    ];

    return $users->export('test.xls', $filters);

In this case, only two column data is available.

# Encoding

You can specify an encoding to convert data only when downloading csv file.

    return $users->export('test.csv', [], 'sjis-win');

# License
This package is licensed under the MIT License.

Copyright 2019 Sukohi Kuhoh
