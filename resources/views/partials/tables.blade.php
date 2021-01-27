<div class="container">
    @if(session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif
    <div class="row">
        <div class="col-md-8">
            <p>From Customer <strong>{{$customer->name}}</strong></p>
            {{--                <form id="form-submit" role="form" action="{{route('customer.products.active-all',$customer->id)}}" method="POST">--}}
            {{--                    {{ csrf_field()}}--}}
            {{--                    <button class="btn btn-success"><i class="fa fa-key"></i> Activate All Products Associated With this user </button>--}}
            {{--                </form>--}}
            {{-- <a href="{{route('products.create')}}" class="btn btn-primary">Add New</a>--}}
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
            <th scope="col">{{$name}} Name</th>
            <th scope="col">Status
                <form style="display: unset" role="form"
                      action="{{route("{$routeActivateAll}",$customer->id)}}"
                      method="POST" id="form_submit_all">
                    {{ csrf_field()}}
                    <input type="checkbox" name="checkbox" value="0" onClick="toggle(this)"
                           onchange="document.getElementById('form_submit_all').submit()"/>
                    <input type="checkbox" name="checkbox" hidden value="1"/>
                </form>
            </th>
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
        @foreach($data as $item)
            <tr>
                <td>{{$count++}}</td>
                <td>{{$item->name}}</td>
                <td>
                    <button type="button"
                            class="{{$item->active  == 1 ? "btn btn-success" : "btn btn-danger"}}"
                            data-toggle="modal" data-target="#products{{$item->id}}">
                        {{$item->active == 1 ? 'Active' : 'Un-Active'}}
                    </button>
                </td>
                <td>$ {{$item->price}}</td>
                {{--                    <td>{{$item->category->name}}</td>--}}
                {{--                    <td>{{$item->created_at}}</td>--}}
                <td>
                    <a href="{{route("{$route}.edit", $item->id)}}" class="btn btn-info"><i class="fa fa-pen"></i></a>
                    <form action="{{ route("{$route}.destroy", $item->id) }}" method="POST" style="display: unset">
                        <input type="hidden" name="_method" value="DELETE">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <button class="btn btn-danger" type="submit"><i class="fa fa-trash"
                                                                        style="color: white"></i></button>
                    </form>
                </td>
            </tr>
            @include('partials.status-modal',['data' => $item, 'route' => $route])
        @endforeach
        </tbody>
    </table>
    {{--        {{$customerProduct->links()}}--}}
</div>

