<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class PreFlight extends BaseController
{
    public function index()
    {
        $this->response->setHeader('Allow:', 'OPTIONS, GET, POST, PUT, PATCH, DELETE');
        return $this->response->setStatusCode(200);
    }
}
