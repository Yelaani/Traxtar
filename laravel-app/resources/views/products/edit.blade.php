@extends('layouts.traxtar')

@section('content')
<a class="btn mb-4 inline-block" href="{{ route('admin.products.index') }}">← Back to products</a>

@livewire('product-form', ['productId' => $product->id])
@endsection
