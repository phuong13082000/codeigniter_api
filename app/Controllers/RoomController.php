<?php

namespace App\Controllers;

use App\Models\RoomModel;
use CodeIgniter\RESTful\ResourceController;

class RoomController extends ResourceController
{
    protected $modelName = RoomModel::class;
    protected $format = 'json';
}
