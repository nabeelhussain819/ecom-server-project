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
                <a href="{{route('services.create')}}" class="btn btn-primary">Add New</a>
            </div>
            <div class="col-md-4 text-right">
                <form action="{{route('services.search')}}" method="GET">
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
                <th scope="col">Service Name</th>
                <th scope="col">Status</th>
                <th scope="col">Price</th>
                <th scope="col">Created By</th>
                {{-- <th scope="col">Category</th>--}}
                {{-- <th scope="col">Created At</th>--}}
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            @php
                $count = 1;
            @endphp
            @forelse($services as $item)
                <tr>
                    <td>{{$count++}}</td>
                    <td>{{$item->service->name}}</td>
                    <td>{{$item->service->active == 1 ? 'Active' : 'Un-Active'}}
                        <form style="display: unset" action="{{route('services.update',$item->service->id)}}"
                              method="POST" id="form-submit{{$item->id}}">
                            {{ method_field('PATCH') }}
                            {{ csrf_field()}}
                            <input type="hidden" name="activateOne" value="activateOnlyOne">
                            @csrf
                            <input type="checkbox" value="1" {{$item->service->active == 1 ? 'checked' : ''}} name="checkbox"
                                   onchange="document.getElementById('form-submit{{$item->id}}').submit()"
                            />
                        </form>
                    </td>
                    <td>$ {{$item->service->price}}</td>
                    <td>
                        <a href="{{route('customer.services',$item->service->user->id)}}">{{$item->service->user->name}}</a>
                    </td>

                    {{--                    <td>{{$item->category->name}}</td>--}}
                    {{--                    <td>{{$item->created_at}}</td>--}}
                    <td>
                        <a href="{{route('services.edit', $item->service->id)}}" class="btn btn-info"><i
                                class="fa fa-pen"></i></a>
                        <form action="{{ route('services.destroy', $item->service->id) }}" method="POST"
                              style="display: unset">
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button class="btn btn-danger" type="submit"><i class="fa fa-trash"
                                                                            style="color: white"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <p>No Active Services</p>
            @endforelse
            </tbody>
        </table>
        {{$services->links()}}
    </div>
@endsection
