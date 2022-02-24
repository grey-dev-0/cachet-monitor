<?php namespace App\Components;

use Illuminate\Support\Str;

abstract class Component{
    const COMPONENT_OPERATIONAL     = 1;
    const COMPONENT_BAD_PERFORMANCE = 2;
    const COMPONENT_PARTIAL_OUTAGE  = 3;
    const COMPONENT_MAJOR_OUTAGE    = 4;

    /**
     * @var int $id The ID of the cachet component to monitor.
     */
    public $id;

    /**
     * @var int $status Current component status.
     */
    public $status = self::COMPONENT_OPERATIONAL;

    /**
     * @param array $properties Component's properties read from the service configuration file.
     */
    public function __construct($properties){
        foreach($properties as $property => $value){
            $property = Str::camel($property);
            $this->$property = $value;
        }
    }
}