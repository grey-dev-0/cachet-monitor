<?php namespace App\Components;

class HttpComponent extends Component{
    /**
     * @var string $url The URL to be pinged or connected to for availability.
     */
    public $url;

    /**
     * @var int $interval The sleep time between each response and the next request i.e. ping interval.
     */
    public $interval = 5;

    /**
     * @var int[] $acceptedCodes HTTP status codes that are considered successful for the pinged URL.
     */
    public $acceptedCodes = [200, 201, 204, 301];

    /**
     * @var int $timeout The URL maximum ping timeout.
     */
    public $timeout = 30;

    /**
     * @var int $slowTimeout The timeout that considers the URL performing slowly.
     */
    public $slowTimeout = 15;
}