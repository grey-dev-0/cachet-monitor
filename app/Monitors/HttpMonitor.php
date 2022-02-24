<?php

namespace App\Monitors;

use Illuminate\Support\Facades\Http;

class HttpMonitor extends Monitor{
    /**
     * @param \App\Components\HttpComponent $httpComponent
     */
    public function __construct($httpComponent){
        parent::__construct();
        $this->component = $httpComponent;
    }

    /**
     * @inheritDoc
     */
    public function monitor(){
        while(true){
            $this->report($this->component::COMPONENT_OPERATIONAL);
            $start = microtime(true);
            $response = Http::timeout($this->component->timeout)->get($this->component->url);
            $end = microtime(true);
            if(!in_array($response->status(), $this->component->acceptedCodes))
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
