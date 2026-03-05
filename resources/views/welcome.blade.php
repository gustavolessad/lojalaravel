@extends('layouts.shop')

@section('title', config('app.name'))

@section('hero')
    <x-shop.banner-principal />
@endsection

@section('content')
    <x-shop.category-carousel />
@endsection
