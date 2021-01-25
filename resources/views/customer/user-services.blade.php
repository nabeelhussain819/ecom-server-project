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
                <p>From Customer <strong>{{$customer->name}}</strong></p>
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
                <th scope="col">Status
                    <form style="display: unset" role="form" action="{{route('customer.services.active-all',$customer->id)}}"
                          method="POST" id="form_submit_all" >
                        {{ csrf_field()}}
                        <input type="checkbox" name="checkbox" value="0" onClick="toggle(this)" onchange="document.getElementById('form_submit_all').submit()"/>
                        <input type="checkbox" name="checkbox" hidden value="1"/>
                    </form></th>
                <th scope="col">Price</th>
                {{--<th scope="col">Category</th>--}}
                {{--<th scope="col">Created At</th>--}}
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            @php
                $count = 1;
            @endphp
            @foreach($userServices as $item)
                <tr>
                    <td>{{$count++}}</td>
                    <td>{{$item->name}}</td>
                    <td>{{$item->active == 1 ? 'Active' : 'Un-Active'}}
                        <form style="display: unset" action="{{route('services.update',$item->id)}}"
                              method="POST" id="form-submit{{$item->id}}">
                            {{ method_field('PATCH') }}
                            {{ csrf_field()}}
                            <input type="hidden" name="activateOne" value="activateOnlyOne">
                            @csrf
                            <input type="checkbox" value="1" {{$item->active == 1 ? 'checked' : ''}} name="checkbox"
                                   onchange="document.getElementById('form-submit{{$item->id}}').submit()"
                            />
                        </form>
                    </td>
                    <td>${{$item->price}}</td>
                    {{--                    <td>{{$item->category->name}}</td>--}}
                    {{--                    <td>{{$item->created_at}}</td>--}}
                    <td>
                        <a href="{{route('services.edit', $item->id)}}" class="btn btn-info"><i
                                class="fa fa-pen"></i></a>
                        <form action="{{ route('services.destroy', $item->id) }}" method="POST" style="display: unset">
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button class="btn btn-danger" type="submit"><i class="fa fa-trash"
                                                                            style="color: white"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{--{{$customerProduct->links()}}--}}
    </div>
@endsection

<script>
    function toggle(source) {
        checkboxes = document.getElementsByName('checkbox');
        for (var i = 0, n = checkboxes.length; i < n; i++) {
            checkboxes[i].checked = source.checked;
        }
    }
</script>
