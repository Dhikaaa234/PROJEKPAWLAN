<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Status;

class StatusController extends Controller
{
    public function index()
    {
        $statuses = Status::orderBy('id')->get();

        return response()->json([
            'statuses' => $statuses->pluck('name')->values(),
            'items' => $statuses,
        ]);
    }
}
