<?php

namespace GuoJiangClub\Catering\Component\Scheduling\Schedule;

class ScheduleList
{
    protected $list;

    public function __construct()
    {
        $this->list = [];
    }

    public function add($class)
    {
        array_push($this->list, $class);
    }

    public function get()
    {
        return $this->list;
    }

}