<?php

namespace App\Console\Commands;

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
    protected $description = 'Generate the skeleton of your app based on the AppSkeleton.json file you provide';

    /**
     * The json object.
     *
     * @var string
     */
    protected $rows = [];

    /**
     * The Timestamp for backup.
     *
     * @var string
     */
    protected $timestamp;

    /**
     * The Progress Bar.
     *
     * @var string
     */
    protected $progressBar;

    /**
     * The json object.
     *
     * @var string
     */
    protected $jsonObj = null;

    /**
     * The json object.
     *
     * @var string
     */
    protected $optionAll = true;

    /**
     * The Action to take [create|backup|delete].
     *
     * @var string
     */
    protected $action = 'create';

    /**
     * The Controllers Directory.
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
     * The Models Directory.
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
     * The Assets Directory.
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
     * @return mixed
     */
    public function handle()
    {
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

        if (!is_file($this->argument('file'))) 
        {
            $this->error('ERROR: File not found > '.$this->argument('file'));
            return;
        }

        $this->jsonObj = json_decode(file_get_contents($this->argument('file')));

        if (is_null($this->jsonObj)) $this->error('ERROR: Invalid Json File format provided.');
        else 
        {
            if ($this->option('routes') || $this->option('controllers') || $this->option('models') || $this->option('migrations') || $this->option('views') || $this->option('assets') || $this->option('publics')) $this->optionAll = false;

            $this->progressBar = $this->output->createProgressBar($this->getCount());

            if ($this->option('backup')) $this->action = 'backup';
            else if ($this->option('clear')) $this->action = 'delete';

            if (($this->optionAll || $this->option('routes')) && $this->jsonObj->routes)
            {
                $this->parseRoutes($this->jsonObj->routes);
            }
            
            if (($this->optionAll || $this->option('controllers')) && $this->jsonObj->controllers)
            {
                $this->parseControllers($this->jsonObj->controllers);
            }

            if (($this->optionAll || $this->option('models')) && $this->jsonObj->models)
            {
                $this->parseModels($this->jsonObj->models);
            }

            if (($this->optionAll || $this->option('migrations')) &&$this->jsonObj->migrations)
            {
                $this->parseMigrations($this->jsonObj->migrations);
            }

            if (($this->optionAll || $this->option('views')) && $this->jsonObj->views)
            {
                $this->parseViews($this->jsonObj->views);
            }

            if (($this->optionAll || $this->option('assets')) && $this->jsonObj->assets)
            {
                $this->parseAssets($this->jsonObj->assets);
            }

            if (($this->optionAll || $this->option('publics')) && $this->jsonObj->publics)
            {
                $this->createPublics($this->jsonObj->publics);
            }

            $this->progressBar->finish();
        }
        
        $this->info('');
        $headers = ['Files/Directories', 'Status'];
        $this->table($headers, $this->rows);
    }

    /**
     * Parse Controllers.
     *
     * @return mixed
     */
    private function parseRoutes($routes)
    {
        //
        $method = $this->action.'Route';
        if (is_array($routes) && !empty($routes))
        {
            foreach ($routes as $route)
            {
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
     * @return mixed
     */
    private function parseControllers($controllers)
    {
        //
        $method = $this->action.'Controller';
        if (is_array($controllers) && !empty($controllers))
        {
            foreach ($controllers as $controller)
            {
                $this->$method($controller->name, isset($controller->resource)?$controller->resource:false);
                $this->progressBar->advance();
            }
        }
    }

    /**
     * Creates Models.
     *
     * @return mixed
     */
    private function parseModels($models)
    {
        //
        $method = $this->action.'Model';
        if (is_array($models) && !empty($models))
        {
            foreach ($models as $model)
            {
                $this->$method($model->name, isset($model->migration)?$model->migration:false);
                $this->progressBar->advance();
            }
        }
    }

    /**
     * Creates Migrations.
     *
     * @return mixed
     */
    private function parseMigrations($migrations)
    {
        //
        $method = $this->action.'Migration';
        if (is_array($migrations) && !empty($migrations))
        {
            foreach ($migrations as $migration)
            {
                $name = key((array)$migration);
                $schema = $migration->{$name};
                $this->$method($name, $schema);
                $this->progressBar->advance();
            }
        }
    }

    /**
     * Creates Views.
     *
     * @return mixed
     */
    private function parseViews($views)
    {
        //
        $method = $this->action.'File';
        if (is_array($views) && !empty($views))
        {
            foreach ($views as $view)
            {
                $this->$method($this->viewsDirectory.$view.'.blade.php');
                $this->progressBar->advance();
            }
        }
    }

    /**
     * Creates Assets.
     *
     * @return mixed
     */
    private function parseAssets($assets)
    {
        //
        $method = $this->action.'File';
        if (is_array($assets) && !empty($assets))
        {
            foreach ($assets as $asset)
            {
                $assetType = key((array)$asset);

                foreach ($asset->{$assetType} as $file)
                {
                    $this->$method($this->assetsDirectory.$assetType.'/'.$file);
                }
                $this->progressBar->advance();
            }
        }
    }

    /**
     * Creates Public Directories.
     *
     * @return mixed
     */
    private function createPublics($publics)
    {
        //
        $method = $this->action.'File';
        if (is_array($publics) && !empty($publics))
        {
            foreach ($publics as $public)
            {
                $this->$method($this->publicDirectory.$public);
                $this->progressBar->advance();
            }
        }
    }

    /**
     * Creates an empty file/directory.
     * If it's a file the it will try to create
     * the directory first if it doesn't already exists.
     *
     * @return void
     */
    private function createFile($filePath)
    {
        if (!file_exists($filePath))
        {
            $lastPart = basename($filePath);
            if (strpos($lastPart, '.') !== false) // it's a file
            {
                $array = explode('/', $filePath);
                array_pop($array);
                $path = implode('/', $array);

                @mkdir($path, 0777, true); // create the directory first
                file_put_contents($filePath, '');
            }
            else  @mkdir($filePath, 0777, true);
        }
        $status = file_exists($filePath)?'  OK':'NOT OK';
        $this->rows[] = array($filePath, $status);
    }
    private function deleteFile($filePath, $bLog = true)
    {
        $files = glob($filePath.($this->option('force')?'*':''));

        foreach ($files as $filePath)
        {
            $array = explode('/', $filePath);

            if (is_file($filePath)) @unlink($filePath);
            else @rmdir($filePath);

            for ($i = 0; $i < count($array); $i++)
            {
                array_pop($array);
                $path = implode('/', $array);
                @rmdir($path);
            }
        }
        if ($bLog) $this->rows[] = array($filePath, (file_exists($filePath) && (count(glob($filePath.'/*')) === 0 ))?'NOT OK':'  OK');
    }
    private function backupFile($filePath)
    {
        if (is_file($filePath))
        {
            if (@rename($filePath, $filePath.'.BAK_'.$this->timestamp))
            {
                $this->progressBar->advance();
            }
            else $this->info('Could not backup '.$filePath);
        }
        else $this->info($filePath.' Not Found !');

        $this->rows[] = array($filePath, file_exists($filePath)?'NOT OK':'  OK');
    }

    private function createController($name, $isResource = false)
    {
        $arguments = [];
        $arguments['name'] = ucfirst($name).'Controller';
        if ($isResource) $arguments['--resource'] = true;
        $this->callSilent('make:controller', $arguments);

        $filePath = $this->controllersDirectory.ucfirst($name).'Controller.php';
        if (is_file($filePath)) $status = '  OK';
        else $status = 'NOT OK';
        $this->rows[] = array($filePath, $status);
    }
    private function deleteController($name)
    {
        $filePath = $this->controllersDirectory.ucfirst($name).'Controller.php';
        $this->deleteFile($filePath);    
    }
    private function backupController($name)
    {
        $filePath = $this->controllersDirectory.ucfirst($name).'Controller.php';
        $this->backupFile($filePath);
    }

    private function createModel($name, $isMigration = false)
    {
        $arguments = [];
        $arguments['name'] = ucfirst($name);
        if ($isMigration) $arguments['--migration'] = true;
        $this->callSilent('make:model', $arguments);

        $filePath = $this->modelsDirectory.ucfirst($name).'.php';
        if (is_file($filePath)) $status = '  OK';
        else $status = 'NOT OK';
        $this->rows[] = array($filePath, $status);
    }
    private function deleteModel($name)
    {
        $filePath = $this->modelsDirectory.ucfirst($name).'.php';
        $this->deleteFile($filePath);
        
    }
    private function backupModel($name)
    {
        $filePath = $this->modelsDirectory.ucfirst($name).'.php';
        $this->backupFile($filePath);
    }

    private function createMigration($name, $schema = '')
    {
        $arguments = [];
        $arguments['name'] = 'create_'.$name.'_table';

        // To avoid duplication if "migration" is set to true for a Model
        $this->deleteMigration($name, false);
        
        if (!empty($schema))
        {
            try
            {
                $arguments['--schema'] = $schema;
                $this->callSilent('make:migration:schema', $arguments);
            }
            catch (\Exception $e)
            {
                unset($arguments['--schema']);
                $arguments['--create'] = $name;
                $this->callSilent('make:migration', $arguments);
            }
        }
        else
        {
            $arguments['--create'] = $name;
            $this->callSilent('make:migration', $arguments);
        }

        $fileName = 'create_'.$name.'_table.php';
        $array = glob($this->migrationsDirectory.'*'.$fileName);
        $filePath = (is_array($array) && !empty($array))?$array[0]:$fileName;
        if (is_file($filePath)) $status = '  OK';
        else $status = 'NOT OK';
        $this->rows[] = array($filePath, $status);
    }
    private function deleteMigration($name, $bLog = true)
    {
        $filePath = $this->migrationsDirectory.'*create_'.$name.'_table.php';
        $this->deleteFile($filePath, $bLog);
    }
    private function backupMigration($name)
    {
        $fileName = 'create_'.$name.'_table.php';
        $array = glob($this->migrationsDirectory.'*'.$fileName);
        $filePath = (is_array($array) && !empty($array))?$array[0]:$fileName;
        $this->backupFile($filePath);
    }

    private function createRoute($httpVerb, $uri, $callback)
    {
        $callback = (strpos($callback, 'function') === false)?'\''.$callback.'\'':$callback;
        $route = "\n\n".'Route::'.$httpVerb.'(\''.$uri.'\', '.$callback.');';
        
        
        $existingRoutes = file_get_contents($this->routesFile);
        if (strpos($existingRoutes, 'Route::'.$httpVerb.'(\''.$uri.'\', ') === false)
        {
            if (file_put_contents($this->routesFile, $route, FILE_APPEND)) $status = '  OK';
            else $status = 'NOT OK';
        }
        else $status = '  OK';
        
        $this->rows[] = array('Route::'.$httpVerb.'(\''.$uri.'\',...', $status);
    }
    private function deleteRoute($httpVerb, $uri, $callback)
    {
        $existingRoutes = file_get_contents($this->routesFile);
        
        $result = preg_replace("/(\n)*Route\:\:".$httpVerb."\(\'".addcslashes($uri, '/')."\'(.+)\);/", '', $existingRoutes);
        
        if (file_put_contents($this->routesFile, $result)) $status = '  OK';
        else $status = 'NOT OK';
        
        $this->rows[] = array('Route::'.$httpVerb.'(\''.$uri.'\',...', $status);
    }
    private function backupRoute($httpVerb, $uri, $callback)
    {
        $existingRoutes = file_get_contents($this->routesFile);
        
        $result = preg_replace("/Route\:\:".$httpVerb."\(\'".addcslashes($uri, '/')."\'(.+)\);/", '/*$0*/', $existingRoutes);
        
        if (file_put_contents($this->routesFile, $result)) $status = '  OK';
        else $status = 'NOT OK';
        
        $this->rows[] = array('Route::'.$httpVerb.'(\''.$uri.'\',...', $status);
    }
    
    /**
     * Get Count for Progress Bar.
     *
     * @return mixed
     */
    private function getCount()
    {
        $count = 0;

        if (($this->optionAll || $this->option('routes')) && $this->jsonObj->routes)
        {
            $count += count($this->jsonObj->routes);
        }
        
        if (($this->optionAll || $this->option('controllers')) && $this->jsonObj->controllers)
        {
            $count += count($this->jsonObj->controllers);
        }

        if (($this->optionAll || $this->option('models')) && $this->jsonObj->models)
        {
            $count += count($this->jsonObj->models);
        }

        if (($this->optionAll || $this->option('migrations')) && $this->jsonObj->migrations)
        {
            $count += count($this->jsonObj->migrations);
        }

        if (($this->optionAll || $this->option('views')) && $this->jsonObj->views)
        {
            $count += count($this->jsonObj->views);
        }

        if (($this->optionAll || $this->option('assets')) && $this->jsonObj->assets)
        {
            $count += count($this->jsonObj->assets);
        }

        if (($this->optionAll || $this->option('publics')) && $this->jsonObj->publics)
        {
            $count += count($this->jsonObj->publics);
        }

        return $count;
    }
}
