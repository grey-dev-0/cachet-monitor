<?php namespace App\Monitors;

use Ratchet\Client\Connector;
use React\EventLoop\Loop;

class WebsocketMonitor extends Monitor{
    /**
     * @param \App\Components\WebsocketComponent $websocketComponent
     */
    public function __construct($websocketComponent){
        parent::__construct();
        $this->component = $websocketComponent;
    }

    /**
     * @inheritDoc
     */
    public function monitor(){
        monitor_start:
        $loop = Loop::get();
        $connector = new \React\Socket\Connector($loop);
        $connector = new Connector($loop, $connector);

        $connector($this->component->url)->then(function ($connection) use ($loop) {
            $this->report($this->component::COMPONENT_OPERATIONAL);
            $connection->on('close', function() use($loop){
                $loop->stop();
            });
            // Keeps pinging the socket to avoid losing connection due to inactivity
            $loop->addPeriodicTimer(20, function() use($connection){
                $connection->send(json_encode(['action' => 'ping']));
            });
        }, function () use ($loop) {
            $loop->stop();
        });

        $loop->run();

        // Retry monitoring this websocket instance if the connection is lost.
        $this->report($this->component::COMPONENT_MAJOR_OUTAGE);
        sleep(3);
        goto monitor_start;
    }
}
