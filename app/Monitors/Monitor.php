<?php namespace App\Monitors;

use Illuminate\Support\Facades\DB;

abstract class Monitor{
    /**
     * @var int $id The ID of the monitor.
     */
    protected $id;

    /**
     * @var \App\Components\Component $component The component to be monitored.
     */
    protected $component;

    /**
     * @var \DivineOmega\CachetPHP\Objects\CachetInstance $cachet The Cachet API client.
     */
    protected $cachet;

    /**
     * Start monitoring the designated component.
     */
    abstract public function monitor();

    public function __construct(){
        $this->id = getmypid();
        $this->cachet = app('cachet');
    }

    /**
     * Gets the currently cached cachet's component status.
     *
     * @return string
     */
    protected function getCachedStatus(){
        if(DB::table('components')->where('monitor_id', $this->id)->count() == 0)
            DB::table('components')->insert(['id' => $this->component->id, 'monitor_id' => $this->id,
                'status' => $this->component::COMPONENT_OPERATIONAL]);
        return DB::table('components')->where('id', $this->component->id)->max('status');
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

        // Notifying Catchet only if this is the highest - worst - status of a component among all of its monitors.
        if(DB::table('components')->where('id', $this->component->id)->where('monitor_id', '!=', $this->id)
                ->where('status', '>', $status)->count() == 0){
            $component = $this->cachet->getComponentById($this->component->id);
            $component->status = $status;
            $component->save();
        }

        // Caching new status.
        if(is_null($cachedStatus = DB::table('components')->where('monitor_id', $this->id)->first()))
            DB::table('components')->insert(compact('status') + ['id' => $this->component->id, 'monitor_id' => $this->id]);
        elseif($cachedStatus->status != $status)
            DB::table('components')->where('monitor_id', $this->id)->update(compact('status'));
    }
}
