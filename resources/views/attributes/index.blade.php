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
                    <a href="{{route('attribute.create')}}" class="btn btn-primary">Add New</a>
                </div>
                <div class="col-md-4 text-right">
                    <form action="{{route('category.search')}}" method="GET">
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
                <th scope="col">Created At</th>
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            @php
                $count = 1;
            @endphp
            @foreach($attributes as $attribute)
                <tr>
                    <td>{{$count++}}</td>
                    <td>{{$attribute->name}}</td>
                    <td>{{$attribute->description}}</td>
                    <td><span class="{{$attribute->active == 0 ? 'badge badge-danger' : 'badge badge-success'}}">{{$attribute->active == 0 ? 'IN-ACTIVE' : 'ACTIVE' }}</span></td>
                    <td>{{$attribute->created_at}}</td>
                    <td>
                        <a href="{{route('category.edit', $attribute->id)}}" class="btn btn-info"><i class="fa fa-pen"></i></a>
                        <form action="{{ route('category.destroy', $attribute->id) }}" method="POST" style="display: unset">
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <button class="btn btn-danger" type="submit"><i class="fa fa-trash" style="color: white"></i></button>
                        </form>
                    </td>
                  </tr>
            @endforeach
            </tbody>
        </table>
        {{$attributes->links()}}
    </div>
@endsection
