<?php

namespace App\Controllers;

use App\Models\BookingModel;
use App\Models\RoomModel;
use CodeIgniter\RESTful\ResourceController;

class BookingController extends ResourceController
{
    protected $format = 'json';
    protected $modelName = BookingModel::class;

    public function create($id = null)
    {
        $rules = [
            'room_id' => 'required|is_natural_no_zero',
            'check_in' => 'required|valid_date',
            'check_out' => 'required|valid_date',
            'guests' => 'required|integer',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);
        $room = (new RoomModel())->find($data['room_id']);

        if (!$room) {
            return $this->failNotFound('Room not found');
        }

        $overlap = $this->model->where('room_id', $data['room_id'])
            ->where('status !=', 'cancelled')
            ->groupStart()
            ->where('check_in <', $data['check_out'])
            ->where('check_out >', $data['check_in'])
            ->groupEnd()->first();

        if ($overlap) {
            return $this->failResourceExists('Room is not available in this period');
        }

        $nights = (strtotime($data['check_out']) - strtotime($data['check_in'])) / 86400;

        if ($nights <= 0) {
            return $this->failValidationErrors(['date' => 'check_out must be after check_in']);
        }

        $total = $nights * (float)$room['price'];

        $uid = $this->request->user['id'] ?? null;

        $id = $this->model->insert([
            'user_id' => $uid,
            'room_id' => $data['room_id'],
            'check_in' => $data['check_in'],
            'check_out' => $data['check_out'],
            'guests' => $data['guests'],
            'status' => 'pending',
            'total_price' => $total,
        ]);

        return $this->respondCreated([
            'id' => $id,
            'total_price' => $total,
        ]);
    }
}