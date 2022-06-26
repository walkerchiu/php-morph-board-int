# php-morph-board-int

php-morph-board-int is a Laravel library for dealing with board management.

## Installation

Use the package manager [composer](https://getcomposer.org/download/) to install php-morph-board-int.

``` bash
composer require walkerchiu/php-morph-board-int
```

## Usage

### Package settings

``` bash
# CLI

# Publish this package settings
php artisan vendor:publish

# Overwrite default settings
vi config/wk-morph-board.php

# Overwrite translations
cd resources/lang/vendor/php-morph-board
vi ...

# Overwrite views
cd resources/views/vendor/php-morph-board
vi ...

# See migrations
cd database/migrations
cat ...
```

### Core settings

``` bash
# CLI

# Overwrite default settings
vi config/wk-core.php

# See class section
# See table section
```

### Migrations

``` bash
# CLI

# Generate a database migration
php artisan make:migration

# Run all of your outstanding migrations
php artisan migrate

# See which migrations have run thus far
php artisan migrate:status
```

### How to use

#### Entity

In fact, this usage is not limited to Entity, and other usages such as Repository and Service are also similar.

You can view the source code to understand the methods provided by these classes.

``` php
# PHP

# Use directly
# You can find more settings in config/wk-core.php
use WalkerChiu\MorphBoard\Models\Entities\Board

Board::all();
```

``` php
# PHP

# Use core setting
# You can find more settings in config/wk-core.php
use Illuminate\Support\Facades\App;

App::make(config('wk-core.class.morph-board.board'));
```

### Useful commands

``` bash
# CLI

# Truncate all tables of this package
php artisan command:MorphBoardCleaner
```

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
