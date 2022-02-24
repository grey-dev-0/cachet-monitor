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
    protected $signature = 'process {--id= : The ID of the component} {--type= : The type of monitored component "http", "ws" or, "shell"}
                                    {--interval= : Between HTTP pings or shell checks sleep interval} {--timeout= : HTTP ping timeout limit}
                                    {--slow_timeout= : HTTP ping slow request time limit} {--accepted_codes=* : Success HTTP ping status codes}
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
        $component = $this->getComponentClass();
        $monitor = new $monitor(new $component($this->options()));
        $this->info(date('[d/m/Y h:i:s A]').' Started component #'.$this->option('id').' monitoring process.');
        $monitor->monitor();
        $this->info(date('[d/m/Y h:i:s A]').' Component #'.$this->option('id').' monitoring process has crashed!');
        return 0;
    }

    /**
     * Get the component class that corresponds to its submitted type.
     *
     * @return ?string
     */
    private function getComponentClass(){
        switch($this->option('type')){
            case 'http': return \App\Components\HttpComponent::class;
            case 'ws': return \App\Components\WebsocketComponent::class;
            // TODO: case 'shell': return \App\Components\ShellComponent::class;
        }
        return null;
    }

    /**
     * Get the monitor class that corresponds to the submitted component type.
     *
     * @return ?string
     */
    private function getMonitorClass(){
        switch($this->option('type')){
            case 'http': return \App\Monitors\HttpMonitor::class;
            case 'ws': return \App\Monitors\WebsocketMonitor::class;
            // TODO: case 'shell': return \App\Monitors\ShellMonitor::class;
        }
        return null;
    }
}
