<?php

namespace FBNKCMaster\AppSkeletonForLaravel;

use Illuminate\Console\Command;

class AppSkeletonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AppSkeleton {file=AppSkeleton.json} {--routes} {--controllers} {--models} {--migrations} {--views} {--assets} {--publics} {--b|backup} {--c|clear} {--f|force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the skeleton (structure of files and directories) you\'ll need for your app based on the json file you provide';

    /**
     * An array to handle logs output.
     *
     * @var array
     */
    protected $rows = [];

    /**
     * The timestamp for backups.
     *
     * @var timestamp
     */
    protected $timestamp;

    /**
     * The Progress Bar.
     *
     * @var object
     */
    protected $progressBar;

    /**
     * The json object we get from the file provided.
     *
     * @var object
     */
    protected $jsonObj = null;

    /**
     * All options by default.
     *
     * @var boolean
     */
    protected $optionAll = true;

    /**
     * The Action to take [create|backup|delete].
     *
     * @var string
     */
    protected $action = 'create';

    /**
     * The App Directory.
     *
     * @var string
     */
    protected $modelsDirectory = 'app/';
    
    /**
     * The Public Directory.
     *
     * @var string
     */
    protected $publicDirectory = 'public/';

    /**
     * The Routes File.
     *
     * @var string
     */
    protected $routesFile = 'app/Http/routes.php';
    
    /**
     * The Views Directory.
     *
     * @var string
     */
    protected $viewsDirectory = 'resources/views/';

    /**
     * The Assets Directory.
     *
     * @var string
     */
    protected $assetsDirectory = 'resources/assets/';

    /**
     * The Migrations Directory.
     *
     * @var string
     */
    protected $migrationsDirectory = 'database/migrations/';

    /**
     * The Controllers Directory.
     *
     * @var string
     */
    protected $controllersDirectory = 'app/Http/Controllers/';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->timestamp = time();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        /**
        * A friendly welcome message with a little help
        */
        $this->info('    _               ____  _        _ _');
        $this->info('   / \   _ __  _ __/ ___|| | _____| | |_ ___  _ __');
        $this->info('  / _ \ | \'_ \| \'_ \___ \| |/ / _ \ | __/ _ \| \'_ \ ');
        $this->info(' / ___ \| |_) | |_) |__) |   <  __/ | || (_) | | | |');
        $this->info('/_/   \_\ .__/| .__/____/|_|\_\___|_|\__\___/|_| |_|');
        $this->info('        |_|   |_| For Laravel        By @FBNKCMaster');
        $this->info('+--------------------------------------------------+');
        $this->info('|       +----------------------------------+       |');
        $this->info('|       |  WELCOME TO  AppSkeleton v1.0.0  |       |');
        $this->info('|       +----------------------------------+       |');
        $this->info('|                                                  |');
        $this->info('|     Let\'s start generate your App\'s Skeleton     |');
        $this->info('+--------------------------------------------------+');
        $this->info('|                                                  |');
        $this->info('|- Generate all what\'s in file: AppSkeleton [file] |');
        $this->info('|- Choose what to generate: AppSkeleton --routes   |');
        $this->info('|                           AppSkeleton --models   |');
        $this->info('|                           AppSkeleton --views    |');
        $this->info('|                           AppSkeleton --assets   |');
        $this->info('|- Backup generated files: AppSkeleton --backup    |');
        $this->info('|- Clear generated skeleton: AppSkeleton --clear   |');
        $this->info('|- Clear all even backups: AppSkeleton --clear --f |');
        $this->info('|                                                  |');
        $this->info('+--------------------------------------------------+');

        // Check if the json file exists
        if (!is_file($this->argument('file'))) {
            $this->error('ERROR: File not found > '.$this->argument('file'));
            return;
        }

        // Get the json object from that file
        $this->jsonObj = json_decode(file_get_contents($this->argument('file')));

        // Check if is a valide json object
        if (is_null($this->jsonObj)) {
            $this->error('ERROR: Invalid Json File format provided.');
        } else {
            // Check for what to parse, if nothing specified parse all
            if ($this->option('routes') || $this->option('controllers') || $this->option('models') || $this->option('migrations') || $this->option('views') || $this->option('assets') || $this->option('publics')) {
                $this->optionAll = false;
            }

            // Initiate the Progress Bar
            $this->progressBar = $this->output->createProgressBar($this->getCount());

            // Decide what action to do
            if ($this->option('backup')) {
                $this->action = 'backup';
            } elseif ($this->option('clear')) {
                $this->action = 'delete';
            }

            // Launch executions...
            if (($this->optionAll || $this->option('routes')) && $this->jsonObj->routes) {
                $this->parseRoutes($this->jsonObj->routes);
            }
            
            if (($this->optionAll || $this->option('controllers')) && $this->jsonObj->controllers) {
                $this->parseControllers($this->jsonObj->controllers);
            }

            if (($this->optionAll || $this->option('models')) && $this->jsonObj->models) {
                $this->parseModels($this->jsonObj->models);
            }

            if (($this->optionAll || $this->option('migrations')) &&$this->jsonObj->migrations) {
                $this->parseMigrations($this->jsonObj->migrations);
            }

            if (($this->optionAll || $this->option('views')) && $this->jsonObj->views) {
                $this->parseViews($this->jsonObj->views);
            }

            if (($this->optionAll || $this->option('assets')) && $this->jsonObj->assets) {
                $this->parseAssets($this->jsonObj->assets);
            }

            if (($this->optionAll || $this->option('publics')) && $this->jsonObj->publics) {
                $this->parsePublics($this->jsonObj->publics);
            }

            $this->progressBar->finish();
        }
        
        // A little hack for a new line...
        $this->info('');

        // Display log as a table
        $headers = ['Files/Directories', 'Status'];
        $this->table($headers, $this->rows);
    }

    /**
     * Parse Routes.
     *
     * @param  json array  $routes
     * @return void
     */
    private function parseRoutes($routes)
    {
        $method = $this->action.'Route';
        if (is_array($routes) && !empty($routes)) {
            foreach ($routes as $route) {
                $httpVerb = key((array)$route);
                $array = explode(':', $route->{$httpVerb});
                $uri = $array[0];
                $callback = $array[1];
                $this->$method($httpVerb, $uri, $callback);
                $this->progressBar->advance();
            }
        }
    }
    
    /**
     * Parse Controllers.
     *
     * @param  json array  $controllers
     * @return void
     */
    private function parseControllers($controllers)
    {
        $method = $this->action.'Controller';
        if (is_array($controllers) && !empty($controllers)) {
            foreach ($controllers as $controller) {
                $this->$method($controller->name, isset($controller->resource)?$controller->resource:false);
                $this->progressBar->advance();
            }
        }
    }

    /**
     * Parse Models.
     *
     * @param  json array  $models
     * @return void
     */
    private function parseModels($models)
    {
        $method = $this->action.'Model';
        if (is_array($models) && !empty($models)) {
            foreach ($models as $model) {
                $this->$method($model->name, isset($model->migration)?$model->migration:false);
                $this->progressBar->advance();
            }
        }
    }

    /**
     * Parse Migrations.
     *
     * @param  json array  $migrations
     * @return void
     */
    private function parseMigrations($migrations)
    {
        $method = $this->action.'Migration';
        if (is_array($migrations) && !empty($migrations)) {
            foreach ($migrations as $migration) {
                $name = key((array)$migration);
                $schema = $migration->{$name};
                $this->$method($name, $schema);
                $this->progressBar->advance();
            }
        }
    }

    /**
     * Parse Views.
     *
     * @param  json array  $views
     * @return void
     */
    private function parseViews($views)
    {
        $method = $this->action.'File';
        if (is_array($views) && !empty($views)) {
            foreach ($views as $view) {
                $view = str_replace('.', '/', $view);
                $this->$method($this->viewsDirectory.$view.'.blade.php');
                $this->progressBar->advance();
            }
        }
    }

    /**
     * Parse Assets.
     *
     * @param  json array  $assets
     * @return void
     */
    private function parseAssets($assets)
    {
        $method = $this->action.'File';
        if (is_array($assets) && !empty($assets)) {
            foreach ($assets as $asset) {
                $assetType = key((array)$asset);

                foreach ($asset->{$assetType} as $file) {
                    $this->$method($this->assetsDirectory.$assetType.'/'.$file);
                }
                $this->progressBar->advance();
            }
        }
    }

    /**
     * Parse Public Files/Directories.
     *
     * @param  json array  $publics
     * @return void
     */
    private function parsePublics($publics)
    {
        $method = $this->action.'File';
        if (is_array($publics) && !empty($publics)) {
            foreach ($publics as $public) {
                $this->$method($this->publicDirectory.$public);
                $this->progressBar->advance();
            }
        }
    }

    /**
     * Creates an empty file/directory.
     *
     * @param  string  $filePath
     * @return void
     */
    private function createFile($filePath)
    {
        if (!file_exists($filePath)) {
            $lastPart = basename($filePath);
            if (strpos($lastPart, '.') !== false) {

                // it's a file, so create the directory first
                $array = explode('/', $filePath);
                array_pop($array);
                $path = implode('/', $array);
                @mkdir($path, 0777, true);

                // then create the empty file
                file_put_contents($filePath, '');
            } else {
                // it's a directory, so create it
                @mkdir($filePath, 0777, true);
            }
        }

        $status = file_exists($filePath)?'  OK':'NOT OK';
        $this->rows[] = array($filePath, $status);
    }

    /**
     * Deletes the file/directory.
     *
     * @param  string  $filePath
     * @param  boolean  $bLog
     * @return void
     */
    private function deleteFile($filePath, $bLog = true)
    {
        $files = glob($filePath.($this->option('force')?'*':''));

        foreach ($files as $filePath) {
            $array = explode('/', $filePath);

            if (is_file($filePath)) {
                @unlink($filePath);
            } else {
                @rmdir($filePath);
            }

            for ($i = 0; $i < count($array); $i++) {
                array_pop($array);
                $path = implode('/', $array);
                @rmdir($path);
            }
        }
        if ($bLog) {
            $this->rows[] = array($filePath, (file_exists($filePath) && (count(glob($filePath.'/*')) === 0))?'NOT OK':'  OK');
        }
    }

    /**
     * Backups the file.
     *
     * @param  string  $filePath
     * @return void
     */
    private function backupFile($filePath)
    {
        if (is_file($filePath)) {
            if (@rename($filePath, $filePath.'.BAK_'.$this->timestamp)) {
                $this->progressBar->advance();
            } else {
                $this->info('Could not backup '.$filePath);
            }
        } else {
            $this->info($filePath.' Not Found !');
        }

        $this->rows[] = array($filePath, file_exists($filePath)?'NOT OK':'  OK');
    }

    /**
     * Creates a Controller.
     *
     * @param  string  $name
     * @param  boolean  $isResource
     * @return void
     */
    private function createController($name, $isResource = false)
    {
        $arguments = [];
        $arguments['name'] = ucfirst($name).'Controller';
        if ($isResource) {
            $arguments['--resource'] = true;
        }
        $this->callSilent('make:controller', $arguments);

        $filePath = $this->controllersDirectory.ucfirst($name).'Controller.php';
        if (is_file($filePath)) {
            $status = '  OK';
        } else {
            $status = 'NOT OK';
        }
        $this->rows[] = array($filePath, $status);
    }

    /**
     * Deletes a Controller.
     *
     * @param  string  $name
     * @return void
     */
    private function deleteController($name)
    {
        $filePath = $this->controllersDirectory.ucfirst($name).'Controller.php';
        $this->deleteFile($filePath);
    }

    /**
     * Backups a Controller.
     *
     * @param  string  $name
     * @return void
     */
    private function backupController($name)
    {
        $filePath = $this->controllersDirectory.ucfirst($name).'Controller.php';
        $this->backupFile($filePath);
    }

    /**
     * Creates a Model.
     *
     * @param  string  $name
     * @param  boolean  $isMigration
     * @return void
     */
    private function createModel($name, $isMigration = false)
    {
        $arguments = [];
        $arguments['name'] = ucfirst($name);
        if ($isMigration) {
            $arguments['--migration'] = true;
        }
        $this->callSilent('make:model', $arguments);

        $filePath = $this->modelsDirectory.ucfirst($name).'.php';
        if (is_file($filePath)) {
            $status = '  OK';
        } else {
            $status = 'NOT OK';
        }
        $this->rows[] = array($filePath, $status);
    }

    /**
     * Deletes a Model.
     *
     * @param  string  $name
     * @return void
     */
    private function deleteModel($name)
    {
        $filePath = $this->modelsDirectory.ucfirst($name).'.php';
        $this->deleteFile($filePath);
    }

    /**
     * Backups a Model.
     *
     * @param  string  $name
     * @return void
     */
    private function backupModel($name)
    {
        $filePath = $this->modelsDirectory.ucfirst($name).'.php';
        $this->backupFile($filePath);
    }

    /**
     * Creates a Migration.
     *
     * @param  string  $name
     * @param  string  $schema
     * @return void
     */
    private function createMigration($name, $schema = '')
    {
        $arguments = [];
        $arguments['name'] = 'create_'.$name.'_table';

        // To avoid duplication if "migration" is set to true for a Model
        $this->deleteMigration($name, false);
        
        if (!empty($schema)) {
            try {
                $arguments['--schema'] = $schema;
                $this->callSilent('make:migration:schema', $arguments);
            } catch (\Exception $e) {
                unset($arguments['--schema']);
                $arguments['--create'] = $name;
                $this->callSilent('make:migration', $arguments);
            }
        } else {
            $arguments['--create'] = $name;
            $this->callSilent('make:migration', $arguments);
        }

        $fileName = 'create_'.$name.'_table.php';
        $array = glob($this->migrationsDirectory.'*'.$fileName);
        $filePath = (is_array($array) && !empty($array))?$array[0]:$fileName;
        if (is_file($filePath)) {
            $status = '  OK';
        } else {
            $status = 'NOT OK';
        }
        $this->rows[] = array($filePath, $status);
    }

    /**
     * Deletes a Migration.
     *
     * @param  string  $name
     * @param  boolean  $bLog
     * @return void
     */
    private function deleteMigration($name, $bLog = true)
    {
        $filePath = $this->migrationsDirectory.'*create_'.$name.'_table.php';
        $this->deleteFile($filePath, $bLog);
    }

    /**
     * Backups a Migration.
     *
     * @param  string  $name
     * @return void
     */
    private function backupMigration($name)
    {
        $fileName = 'create_'.$name.'_table.php';
        $array = glob($this->migrationsDirectory.'*'.$fileName);
        $filePath = (is_array($array) && !empty($array))?$array[0]:$fileName;
        $this->backupFile($filePath);
    }

    /**
     * Creates a Route.
     *
     * @param  string  $httpVerb
     * @param  string  $uri
     * @param  string  $callback
     * @return void
     */
    private function createRoute($httpVerb, $uri, $callback)
    {
        $callback = (strpos($callback, 'function') === false)?'\''.$callback.'\'':$callback;
        $route = "\n\n".'Route::'.$httpVerb.'(\''.$uri.'\', '.$callback.');';
        
        $existingRoutes = file_get_contents($this->routesFile);

        if (strpos($existingRoutes, 'Route::'.$httpVerb.'(\''.$uri.'\', ') === false) {
            if (file_put_contents($this->routesFile, $route, FILE_APPEND)) {
                $status = '  OK';
            } else {
                $status = 'NOT OK';
            }
        } else {
            $status = '  OK';
        }
        
        $this->rows[] = array('Route::'.$httpVerb.'(\''.$uri.'\',...', $status);
    }

    /**
     * Deletes a Route.
     *
     * @param  string  $httpVerb
     * @param  string  $uri
     * @param  string  $callback
     * @return void
     */
    private function deleteRoute($httpVerb, $uri, $callback)
    {
        $existingRoutes = file_get_contents($this->routesFile);
        
        $result = preg_replace("/(\n)*Route\:\:".$httpVerb."\(\'".addcslashes($uri, '/')."\'(.+)\);/", '', $existingRoutes);
        
        if (file_put_contents($this->routesFile, $result)) {
            $status = '  OK';
        } else {
            $status = 'NOT OK';
        }
        
        $this->rows[] = array('Route::'.$httpVerb.'(\''.$uri.'\',...', $status);
    }

    /**
     * Backups a Route. Actually, it comments it.
     *
     * @param  string  $httpVerb
     * @param  string  $uri
     * @param  string  $callback
     * @return void
     */
    private function backupRoute($httpVerb, $uri, $callback)
    {
        $existingRoutes = file_get_contents($this->routesFile);
        
        $result = preg_replace("/Route\:\:".$httpVerb."\(\'".addcslashes($uri, '/')."\'(.+)\);/", '/*$0*/', $existingRoutes);
        
        if (file_put_contents($this->routesFile, $result)) {
            $status = '  OK';
        } else {
            $status = 'NOT OK';
        }
        
        $this->rows[] = array('Route::'.$httpVerb.'(\''.$uri.'\',...', $status);
    }
    
    /**
     * Get Count for Progress Bar.
     *
     * @return integer
     */
    private function getCount()
    {
        $count = 0;

        if (($this->optionAll || $this->option('routes')) && $this->jsonObj->routes) {
            $count += count($this->jsonObj->routes);
        }
        
        if (($this->optionAll || $this->option('controllers')) && $this->jsonObj->controllers) {
            $count += count($this->jsonObj->controllers);
        }

        if (($this->optionAll || $this->option('models')) && $this->jsonObj->models) {
            $count += count($this->jsonObj->models);
        }

        if (($this->optionAll || $this->option('migrations')) && $this->jsonObj->migrations) {
            $count += count($this->jsonObj->migrations);
        }

        if (($this->optionAll || $this->option('views')) && $this->jsonObj->views) {
            $count += count($this->jsonObj->views);
        }

        if (($this->optionAll || $this->option('assets')) && $this->jsonObj->assets) {
            $count += count($this->jsonObj->assets);
        }

        if (($this->optionAll || $this->option('publics')) && $this->jsonObj->publics) {
            $count += count($this->jsonObj->publics);
        }

        return $count;
    }
}
