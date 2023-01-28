<?php
namespace adrianclay\git;


interface SHAReference {

    public function getSHA(): string;

}