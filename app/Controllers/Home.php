<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Home extends BaseController
{
    use ResponseTrait;
    public function index(): string
    {
        return view('pages/home', [
            'env_check' => getenv('JWT_SECRET') ? 'ada' : "tidak ada",
        ]);
    }
    public function notFound()
    {
        $path = $this->request->getPath();
        $isApi = str_starts_with($path, 'api/');
        if ($isApi) {
            return $this->respond([
                'error' => true,
                'message' => 'Endpoint tidak ditemukan'
            ], 404);
        }
        return view('errors/html/error_404', [
            'message' => 'Page not found'
        ]);
    }
}
