<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class HttpMonitor extends Monitor{
    /**
     * @var int $interval The sleep time between each response and the next request i.e. ping interval.
     */
    private $interval = 5;

    /**
     * @var int[] $acceptedCodes HTTP status codes that are considered successful for the pinged URL.
     */
    private $acceptedCodes = [200, 201, 204, 301];

    /**
     * @var int $timeout The URL maximum ping timeout.
     */
    private $timeout = 30;

    /**
     * @var int $slowTimeout The timeout that considers the URL performing slowly.
     */
    private $slowTimeout = 15;

    /**
     * @param int $componentId The ID of the cachet component represented by this monitored URL.
     * @param string $url The URL to be pinged for availability.
     * @param ?int $interval The sleep time between each response and the next request i.e. ping interval.
     * @param ?int[] $acceptedCodes HTTP status codes that are considered successful for the pinged URL.
     */
    public function __construct($componentId, $url, $interval = null, $acceptedCodes = null, $timeout = null, $slowTimeout = null){
        $this->componentId = $componentId;
        $this->url = $url;
        $this->interval = $interval?? $this->interval;
        $this->acceptedCodes = $acceptedCodes?? $this->acceptedCodes;
        $this->timeout = $timeout?? $this->timeout;
        $this->slowTimeout = $slowTimeout?? $this->slowTimeout;
        $this->statusCacheFile = getcwd().'/cachet-data/status/'.$this->componentId;
        $this->status = $this->getCachedStatus();
    }

    /**
     * @inheritDoc
     */
    public function monitor(){
        while(true){
            $this->report(self::COMPONENT_OPERATIONAL);
            $start = microtime(true);
            $response = Http::timeout($this->timeout)->get($this->url);
            $end = microtime(true);
            if(!in_array($response->status(), $this->acceptedCodes))
                $this->report(self::COMPONENT_MAJOR_OUTAGE);
            else{
                $time = ($end - $start) / 1000000.0;
                if($time >= $this->slowTimeout)
                    $this->report(self::COMPONENT_BAD_PERFORMANCE);
            }
            sleep($this->interval);
        }
    }
}
