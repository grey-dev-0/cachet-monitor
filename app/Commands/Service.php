<?php

namespace App\Commands;

use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

class Service extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'start';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Starts the monitoring service for configured targets and reports updates to a cachet instance';

    /**
     * @var int $fork The monitoring service processes fork flag.
     */
    private $fork;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(0);
        $this->fork = getmypid();
        $this->task('Creating required directories if do not exist..', function(){
            $basedir = getcwd();
            $statusCache = "$basedir/cachet-data/status";
            $logs = "$basedir/cachet-data/logs";
            foreach([$statusCache, $logs] as $directory)
                if(!is_dir($directory))
                    mkdir($directory, 0777, true);
            return true;
        });
        $config = [];
        $this->task('Parsing configuration file..', function() use(&$config){
            $configFile = getcwd().'/config.json';
            if(!is_file($configFile))
                return false;
            if(empty($config = json_decode(File::get($configFile), true)))
                return false;
            return true;
        });
        if(empty($config))
            return 1;
        $this->info('Starting components monitoring processes..');
        $this->warn("This is the master process of monitoring {$config['cachet_url']}.\nPlease don't kill this process to keep the monitoring services running..");
        $logsDirectory = getcwd().'/cachet-data/logs';
        foreach($config['components'] as $component){
            $arguments = [];
            foreach($component as $property => $value)
                if(!is_array($value))
                    $arguments[] = "--$property=$value";
                else
                    foreach($value as $subValue)
                        $arguments[] = "--$property=$subValue";
            $arguments = implode(' ', $arguments);
            if($this->fork != 0)
                $this->fork = pcntl_fork();
            if($this->fork == 0){
                $command = "./monitor process $arguments > $logsDirectory/{$component['id']}.log";
                $this->info("Started monitoring component #{$component['id']}:");
                $this->comment($command);
                `$command`;
                // The loop must be broken for the forked child process to avoid re-running duplicate monitoring
                // processes which were previously run by other forked children processes.
                break;
            } elseif($this->fork == -1)
                $this->error("Monitor service process of component #{$component['id']} has failed to start!");
        }
        if($this->fork > 0)
            while(true){
                sleep(120);
            }
        return 0;
    }
}
