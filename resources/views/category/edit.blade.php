@extends('adminlte::page')

@section('content')

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        <form action="{{route('category.update',$category->id)}}" method="POST">
            <input type="hidden" name="_method" value="PUT">
            @csrf
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" value="{{$category->name}}" class="form-control"
                       placeholder="Enter Category Name" required>
            </div>

            <div class="form-group">
                <label>Type</label>
                <select name="type" class="form-control" required>
                    <option value="" selected>Please select...</option>
                    <option value="Product" {{$category->type == 'Product' ? 'selected' : ''}}>Product</option>
                    <option value="Service" {{$category->type == 'Service' ? 'selected' : ''}}>Service</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="active" class="form-control" required>
                    <option value="" selected>Please select...</option>
                    <option value="1" {{$category->active == 1 ? 'selected' : ''}}>Active</option>
                    <option value="0" {{$category->active == 0 ? 'selected': ''}}>In-Active</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="has_shipping" class="form-control">
                    <option value="">Please select...</option>
                    <option value=1 {{$category->has_shipping == 1 ? 'selected' : ''}}>yes</option>
                    <option value=0 {{$category->has_shipping == 0 ? 'selected' : ''}}>no</option>
                </select>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea type="text" rows="5" cols="5" class="form-control" name="description"
                          placeholder="Enter Description">{{$category->description}}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
        <br>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

@endsection
