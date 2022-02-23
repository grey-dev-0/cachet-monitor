<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

abstract class Monitor{
    const COMPONENT_OPERATIONAL     = 1;
    const COMPONENT_BAD_PERFORMANCE = 2;
    const COMPONENT_PARTIAL_OUTAGE  = 3;
    const COMPONENT_MAJOR_OUTAGE    = 4;

    /**
     * @var int $componentId The ID of the cachet component
     */
    protected $componentId;

    /**
     * @var string $url The URL to be pinged or connected to for availability.
     */
    protected $url;

    /**
     * @var int $status Current component status.
     */
    protected $status;

    /**
     * @var \DivineOmega\CachetPHP\Objects\CachetInstance $cachet The Cachet API client.
     */
    protected $cachet;

    /**
     * @var string $statusCacheFile The component's status cached file.
     */
    protected $statusCacheFile;

    /**
     * Start monitoring the designated component.
     */
    abstract public function monitor();

    public function __construct(){
        $this->cachet = app('cachet');
    }

    /**
     * Gets the currently cached cachet's component status.
     *
     * @return string
     */
    protected function getCachedStatus(){
        if(!file_exists($this->statusCacheFile))
            File::put($this->statusCacheFile, '1');
        return File::get($this->statusCacheFile);
    }

    /**
     * Reports a status update of the monitored component to cachet.
     * Duplicate reports are automatically avoided.
     *
     * @param $status
     * @return void
     */
    protected function report($status){
        if($status == $this->getCachedStatus())
            return;
        $component = $this->cachet->getComponentById($this->componentId);
        $component->status = $status;
        $component->save();

        // Caching new status.
        File::put($this->statusCacheFile, "$status");
    }
}
