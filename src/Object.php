<?php
namespace adrianclay\git;

interface Object
{
    public function getType();

    /** @return string */
    public function getData();
}