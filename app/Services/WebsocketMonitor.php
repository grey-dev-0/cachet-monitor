<?php namespace App\Services;

use Ratchet\Client\Connector;
use React\EventLoop\Loop;

class WebsocketMonitor extends Monitor{
    /**
     * @param int $componentId The ID of the cachet component represented by this monitored websocket instance.
     * @param string $url The URL to be connected to for availability.
     */
    public function __construct($componentId, $url){
        $this->componentId = $componentId;
        $this->url = $url;
        $this->statusCacheFile = getcwd().'/cachet-data/status/'.$this->componentId;
        $this->status = $this->getCachedStatus();
    }

    /**
     * @inheritDoc
     */
    public function monitor(){
        $loop = Loop::get();
        $connector = new \React\Socket\Connector($loop);
        $connector = new Connector($loop, $connector);

        $connector($this->url)->then(function ($connection) use ($loop) {
            $this->report(self::COMPONENT_OPERATIONAL);
            // Keeps pinging the socket to avoid losing connection due to inactivity.
            while(true){
                try{
                    $connection->send(json_encode(['action' => 'ping']));
                } catch(\Exception $e){
                    break;
                }
                sleep(20);
            }
            $loop->stop();
        }, function () use (&$response, $loop) {
            $loop->stop();
        });

        $loop->run();

        // Retry monitoring this websocket instance if the connection is lost.
        $this->report(self::COMPONENT_MAJOR_OUTAGE);
        sleep(3);
        $this->monitor();
    }
}
