<?php

namespace App\Commands;

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(0);
        $this->task('Creating required directories if do not exist..', function(){
            $basedir = getcwd();
            $statusCache = "$basedir/cachet-data/status";
            $components = "$basedir/cachet-data/components";
            $logs = "$basedir/cachet-data/logs";
            foreach([$statusCache, $components, $logs] as $directory)
                if(!is_dir($directory))
                    mkdir($directory, 0777, true);
            return true;
        });
        $config = [];
        $this->task('Parsing configuration file..', function() use(&$config){
            $configFile = getcwd().'/config.json';
            if(!is_file($configFile))
                return false;
            if(empty($config = json_decode($configFile, true)))
                return false;
            return true;
        });
        if(empty($config))
            return 1;
        $this->task('Starting components monitoring processes..', function() use($config){
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
                `./monitor process $arguments > $logsDirectory/{$component['id']}.log &`;
                $this->info("Started monitoring component #{$component['id']}.");
            }
        });
        $this->comment("This is the master process of monitoring {$config['cachet_url']}, please don't kill this process to keep the monitoring services running..");
        while(true){
            sleep(120);
        }
    }
}
