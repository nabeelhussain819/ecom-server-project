<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServicesAttribute;
use App\Models\ServicesCategories;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return ServicesCategories::with('service', 'category')->whereHas('service', function ($query) {
            $query->where('active', 1);
        })->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $service = new Service();
        //temporary 1
        $request['user_id'] = 1;
        $service->fill($request->all())->save();
        $serviceCategories = new ServicesCategories($request->all());
        $service->categories()->saveMany([$serviceCategories]);

        $attributes = [];
        foreach ($request->get('attributes', []) as $attribute) {
            $attributes[] = [
                'attribute_id' => $attribute['id'],
                'service_id' => $service->id,
                'value' => $attribute['value']
            ];
        }

        ServicesAttribute::insert($attributes);

        return $this->genericResponse(true, 'Service Created', 200, ['service' => $service->withCategories()]);
    }

    /**
     * Display the specified resource.
     *
     * @param Service $service
     * @return Service
     */
    public function show(Service $service)
    {
        return $service->withCategories()->withServicesAttributes();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request, Service $service)
    {
        //
        $service->fill($request->all())->update();

        $attributes = ($postedAttributes = $request->get('attributes')) ? array_combine(array_column($postedAttributes, 'id'), array_column($postedAttributes, 'value')) : [];
        // @TODO: create relations to avoid where query
        ServicesAttribute::where('product_id', $service->id)
            ->get()
            ->each(function (ServicesAttribute $attribute) use ($attributes) {
                $attribute->value = $attributes[$attribute->attribute_id];
                $attribute->save();
            });

//        ServicesCategories::where('service_id', $service->id)->update(['category_id' => $request->category_id]);
        return $this->genericResponse(true, "$service->name Updated", 200, ['service' => $service->withCategories()]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        Service::destroy($id);
        return response()->json(['message', 'Service deleted']);
    }
}
