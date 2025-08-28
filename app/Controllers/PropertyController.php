<?php

namespace App\Controllers;

use App\Models\PropertyModel;
use CodeIgniter\RESTful\ResourceController;

class PropertyController extends ResourceController
{
    protected $modelName = PropertyModel::class;
    protected $format = 'json';

    public function index()
    {
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $limit = min(50, max(1, (int) ($this->request->getGet('limit') ?? 10)));
        $search = $this->request->getGet('search');

        $builder = $this->model;

        if ($search) {
            $builder = $builder->like('name', $search);
        }

        $data = $builder->paginate($limit, 'default', $page);

        return $this->respond($data);
    }
}
