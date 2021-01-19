@extends('adminlte::page')

@section('content')
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        <div class="row">
            <div class="col-md-8">
                <a href="{{route('products.create')}}" class="btn btn-primary">Add New</a>
            </div>
            <div class="col-md-4 text-right">
                <form action="{{route('products.search')}}" method="GET">
                    <div class="input-group">
                        <input type="search" name="search" class="form-control" placeholder="Search"/>
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                        </span>
                    </div>
                </form>
            </div>
        </div>
        <table class="table">
            <br>
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">Status</th>
                <th scope="col">Price</th>
                <th scope="col">Category</th>
                <th scope="col">Created At</th>
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            @php
                $count = 1;
            @endphp
            @foreach($products as $item)
                <tr>
                    <td>{{$count++}}</td>
                    <td>{{$item->product->name}}</td>
                    <td><span class="{{$item->product->active == 0 ? 'badge badge-danger' : 'badge badge-success'}}">{{$item->product->active == 0 ? 'IN-ACTIVE' : 'ACTIVE' }}</span></td>
                    <td>$ {{$item->product->price}}</td>
                    <td>{{$item->category->name}}</td>
                    <td>{{$item->created_at}}</td>
                    <td>
                        <a href="{{route('products.edit', $item->product->id)}}" class="btn btn-info"><i class="fa fa-pen"></i></a>
                        <form action="{{ route('products.destroy', $item->product->id) }}" method="POST" style="display: unset">
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <button class="btn btn-danger" type="submit"><i class="fa fa-trash" style="color: white"></i></button>
                        </form>
                    </td>
                  </tr>
            @endforeach
            </tbody>
        </table>
        {{$products->links()}}
    </div>
@endsection
