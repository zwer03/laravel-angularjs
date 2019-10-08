<?php

namespace App\Helpers\Interfaces;

Interface UserInterface
{

    public function create($request);
    public function audit($request);
}

?>