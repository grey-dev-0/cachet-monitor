<?php

namespace App\Components;

class ShellComponent extends Component{
    /**
     * @var string $command The shell command to execute.
     */
    public $command;

    /**
     * @var string $substring The string that needs to be matched in the command's output to consider the component operational.
     */
    public $substring;

    /**
     * @var int $interval The sleep time between each two consecutive shell executions.
     */
    public $interval = 5;

    /**
     * @var int $slowTimeout The timeout that considers the shell command performing slowly.
     */
    public $slowTimeout = 1000;
}