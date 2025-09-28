@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Products</h1>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
        <i class="fas fa-arrow-left"></i> Back to Import
    </a>
</div>

<div class="row">
    @foreach($products as $product)
    <div class="col-md-4 mb-4">
        <div class="card">
            @if($product->primaryImage)
            <img src="{{ asset('storage/' . $product->primaryImage->getVariantPath('thumbnail')) }}" 
                 class="card-img-top" alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
            @else
            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                <i class="fas fa-image fa-3x text-muted"></i>
            </div>
            @endif
            <div class="card-body">
                <h5 class="card-title">{{ $product->name }}</h5>
                <p class="card-text">
                    <strong>SKU:</strong> {{ $product->sku }}<br>
                    <strong>Price:</strong> ${{ number_format($product->price, 2) }}<br>
                    <strong>Quantity:</strong> {{ $product->quantity }}
                </p>
                <p class="card-text">
                    <small class="text-muted">
                        Images: {{ $product->images->count() }}
                    </small>
                </p>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="d-flex justify-content-center">
    {{ $products->links() }}
</div>
@endsection