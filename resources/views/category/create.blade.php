@extends('adminlte::page')

@section('content')
    <div class="container">
        <form action="{{route('category.store')}}" method="POST">
            @csrf
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" placeholder="Enter Category Name">
            </div>

            <div class="form-group">
                <label>Type</label>
                <select name="type" class="form-control">
                    <option value="" selected>Please select...</option>
                    <option value="Product">Product</option>
                    <option value="Service">Service</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="has_shipping" class="form-control">
                    <option value="" selected>Please select...</option>
                    <option value=1>Active</option>
                    <option value=0>In-Active</option>
                </select>
            </div>
            <div class="form-group">
                <label>Has Shiping</label>
                <select name="active" class="form-control">
                    <option value="" selected>Please select...</option>
                    <option value=1>yes</option>
                    <option value=0>false</option>
                </select>
            </div>
            <div class="form-group">
                <label>Related Parent</label>
                <select name="parent_id" class="form-control">
                    <option value="" selected>Please select...</option>
                    @foreach($categories as $category)
                        <option value={{$category->id}}>{{$category->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea type="text" rows="5" cols="5" class="form-control" name="description"
                          placeholder="Enter Description">
            </textarea>
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
