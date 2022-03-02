<?php

namespace App\Monitors;

class ShellMonitor extends Monitor{
    /**
     * @param \App\Components\ShellComponent $shellComponent
     */
    public function __construct($shellComponent){
        parent::__construct();
        $this->component = $shellComponent;
    }

    /**
     * @inheritDoc
     */
    public function monitor(){
        while(true){
            $this->report($this->component::COMPONENT_OPERATIONAL);
            $start = microtime(true);
            $output = `{$this->component->command}`;
            $end = microtime(true);
            if(strpos($output, $this->component->substring) === false)
                $this->report($this->component::COMPONENT_MAJOR_OUTAGE);
            else{
                $time = ($end - $start) / 1000000.0;
                if($time >= $this->component->slowTimeout)
                    $this->report($this->component::COMPONENT_BAD_PERFORMANCE);
            }
            sleep($this->component->interval);
        }
    }
}