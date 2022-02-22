<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Process extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'process {--id : The ID of the component} {--type : The type of monitored component "http", "ws" or, "shell"}
                                    {--interval= : Between HTTP pings or shell checks sleep interval} {--timeout= : HTTP ping timeout limit}
                                    {--slow_timeout= : HTTP ping slow request time limit} {--accepted_codes=* | Success HTTP ping status codes}
                                    {--url= : The URL of the required HTTP or WS ping / test.}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Starts a monitoring process for a specific cachet component.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $monitor = $this->getMonitorClass();
        $monitor = new $monitor(...$this->getMonitorArguments());
        $this->info(date('[d/m/Y h:i:s A]').'Started component #'.$this->option('id').' monitoring process.');
        $monitor->monitor();
        $this->info(date('[d/m/Y h:i:s A]').'Component #'.$this->option('id').' monitoring process has crashed!');
        return 0;
    }

    /**
     * Get the monitor class that corresponds to the submitted component type.
     *
     * @return ?string
     */
    private function getMonitorClass(){
        switch($this->option('type')){
            case 'http': return \App\Services\HttpMonitor::class;
            case 'ws': return \App\Services\WebsocketMonitor::class;
            // TODO: case 'shell': return \App\Services\ShellMonitor::class;
        }
        return null;
    }

    /**
     * Get the corresponding monitoring class arguments according to the submitted cachet component type.
     *
     * @return array
     */
    private function getMonitorArguments(){
        $arguments = [$this->option('id')];
        switch($this->option('type')){
            case 'http':
                $arguments[] = $this->option(['url']);
                $arguments[] = (!empty($interval = $this->option('interval')))? $interval : null;
                $arguments[] = (!empty($acceptedCodes = $this->option('accepted_codes')))? $acceptedCodes : null;
                $arguments[] = (!empty($timeout = $this->option('timeout')))? $timeout : null;
                $arguments[] = (!empty($slowTimeout = $this->option('slow_timeout')))? $slowTimeout : null;
                break;
            case 'ws':
                $arguments[] = $this->option(['url']);
                break;
            // case 'shell': // TODO: Handling shell monitor.
        }
        return $arguments;
    }
}
