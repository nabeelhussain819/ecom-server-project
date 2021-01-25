@extends('adminlte::page')

@section('content')

@include('partials.tables', ['data' => $customerProduct, 'customer' => $customer])

@endsection

<script>
    function toggle(source) {
        checkboxes = document.getElementsByName('checkbox');
        for (var i = 0, n = checkboxes.length; i < n; i++) {
            checkboxes[i].checked = source.checked;
        }
    }
</script>
