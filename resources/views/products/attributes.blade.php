@foreach($attributes as $attribute)
    <div class="form-group">
        <label>{{ucfirst($attribute->name)}}</label>
        <input class="form-control" name="attributes[{{$attribute->id}}]" value="{{$defaults[$attribute->id] ?? null}}"/>
    </div>
@endforeach
