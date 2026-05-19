<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();

        return response()->json([
            'categories' => $categories->pluck('name')->values(),
            'items' => $categories,
        ]);
    }
}
