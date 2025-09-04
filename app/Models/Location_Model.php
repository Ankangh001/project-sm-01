<?php

namespace App\Models;

use CodeIgniter\Model;

class Location_Model extends Model
{

    protected $table = 'tbl_location';
    protected $allowedFields = ['location_name', 'latitude', 'longitude'];

}