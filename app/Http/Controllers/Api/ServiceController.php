<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\Service;
use App\Model\servicesCategory;
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
        return servicesCategory::with('service','category')->whereHas('service',function($query){
           $query->where('active',1);
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $serviceCat = new servicesCategory();
        $service = new Service();
        $service->guid = \Illuminate\Support\Str::uuid();
        //temporary 1
        $request['user_id'] = 1;
        $service->fill($request->all())->save();
        $serviceCat->service_id = $service->id;
        $serviceCat->category_id = $request->category_id;
        $serviceCat->save();
        return response()->json([
            'message' => 'Service added successfully'
        ],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $service = Service::find($id);
        $service->fill($request->all())->update();
        servicesCategory::where('service_id',$service->id)->update(['category_id' => $request->category_id]);
        return response()->json(['message','Service Updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        Service::destroy($id);
        return response()->json(['message','Service deleted']);
    }
}
