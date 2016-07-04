# AppSkeleton-for-Laravel
AppSkeleton for Laravel is a custom artisan command that helps you speed up your application development by generating a ready structure (based on a json file) of files and direcories you'll need for your app

## Requirements

- (Optional) If you need an advanced option to create migrations with schemas, you'll need to install laracasts/generators (by Jeffrey Way). If you use Composer to install this package, it's done automatically and you have to add its service provider in app/Providers/AppServiceProvider.php 


## Usage

### Step 1: Install Through Composer

```bash
$ composer require fbnkcmaster/appskeleton-for-laravel
```

### Step 2: Register the command

This is done within the `app/Console/Kernel.php` file, like so:

```php
protected $commands = [
	// add the line below to this array
    \FBNKCMaster\AppSkeletonForLaravel\AppSkeletonCommand::class
];
```

* If you download and install it manually then you have to set the path/to/AppSkeleton/AppSkeletonCommand::class to register the command within the `app/Console/Kernel.php` file, like so:
```php
protected $commands = [
	// add the line below to this array
    path/to/where/you/put/AppSkeleton/AppSkeletonCommand::class
];
```
* Optional
- You will need to install Jeffrey Way's laracasts/generators for more advanced option with migrations.
- Then add its service provider in app/Providers/AppServiceProvider.php, like so:
```
public function register()
{
    if ($this->app->environment() == 'local') {
        $this->app->register('Laracasts\Generators\GeneratorsServiceProvider');
    }
}
```

### Step 3: That's all!

You're ready now. Run `php artisan` from the console, and you'll see the new command `AppSkeleton`.

## Examples

### Example of a json file
Put the json file whereever you can access it from the artisan command 
```json
{
	"name": "App Name",
    "description": "A Little Description of The App",
    "author": "Author Name <author@email.com>",
    "controllers": [
        {"name": "home"},
        {"name": "dashboard"},
        {"name": "users", "resource": false},
        {"name": "posts", "resource": true},
        {"name": "comments", "resource": true}
    ],
    "models": [
        {"name": "user"},
        {"name": "post", "migration": false},
        {"name": "comment", "migration": true}
    ],
    "migrations": [ // Jeffrey Way's laracasts/generators needed to take care of this, 
otherwise the schema is ignored and it will generate simple migrations files
        {"users": "username:string, email:string:unique"},
        {"posts": "id:integer:unique, title:string"},
        {"comments": "id:integer:unique, post_id:integer:unique, text:string"}
    ],
    "views": ["home", "dashboard", "pages.users", "pages.posts", "pages.comments"],
    "assets": [
        {"sass": ["file1.sass", "file2.sass", "partials/subfile1.sass"]},
        {"js": ["file1.js", "file2.js", "plugins/file1.js", "plugins/file2.js"]}
    ],
    "publics": ["folder1", "folder2", "folder2/subfolder1", "folder2/subfolder2"]
}
```

### Run commands

Generate everything in the json file (where AppSkeleton.json in the root of your Laravel application)
```bash
$ php artisan AppSkeleton
```

You can specify the path to your json file
```bash
$ php artisan AppSkeleton path/to/your/appskeleton_file.json
```

Genereate just Controllers and Views
```bash
$ php artisan AppSkeleton --controllers --views
```

Backup what was generated
```bash
$ php artisan AppSkeleton [--controllers] [--views] --backup
```

Delete what was generated
```bash
$ php artisan AppSkeleton [--controllers] [--views] --clear
```

Force delete generated files and directories even the backups
```bash
$ php artisan AppSkeleton [--controllers] [--views] --clear --f
```

### Available Arguments

```
path/to/file.json 			set the json file that contains the structure of the app
```

### Available Options

```
--routes 					parse routes
--controllers 				parse controllers
--models 					parse models
--migrations 				parse migrations
--views 					parse views
--assets 					parse assets
--publics 					parse publics
--routes 					parse routes
--b, --backup 				make backup of generated files
--c, --clear 				delete generated files
--f, --force 				force delete generated files even backups
```