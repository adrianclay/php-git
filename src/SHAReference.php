<?php
namespace adrianclay\git;


interface SHAReference {

    /**
     * @return string
     */
    public function getSHA();

}