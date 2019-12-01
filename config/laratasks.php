<?php

return [
    'default_priority' => 100,
    'default_max_attempts' => 3,
    'default_queue' => 'default',
    'default_parent_strategy' => \Laratasks\Laratasks\Task::PARENT_STRATEGY_ALL_SUCCEED,
];