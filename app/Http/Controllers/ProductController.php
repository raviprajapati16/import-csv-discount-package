<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('primaryImage')->paginate(12);
        return view('products', compact('products'));
    }
}
