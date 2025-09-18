<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HandlesControllerErrors;
use App\Http\Controllers\Traits\HandlesFiltering;

abstract class Controller
{
    use HandlesControllerErrors, HandlesFiltering;
}