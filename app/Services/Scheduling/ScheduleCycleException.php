<?php

namespace App\Services\Scheduling;

use RuntimeException;

class ScheduleCycleException extends RuntimeException
{
    /**
     * @param  list<int|string>  $nodeIds  aktivitas yang terlibat dalam urutan melingkar
     */
    public function __construct(public readonly array $nodeIds)
    {
        parent::__construct('Urutan aktivitas melingkar: sebuah aktivitas tidak boleh menjadi pendahulu bagi dirinya sendiri.');
    }
}
