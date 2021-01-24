<?php

namespace App\Http\Controllers;

use App\Model\Category;
use App\Model\Service;
use App\Model\ServicesCategories;
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
        return view('services.index',['services' =>
            ServicesCategories::with('service')
                ->whereHas('service',function ($query){
                    $query->where('active',1);
                })->orderBy('created_at','ASC')->paginate(10)]);
    }

    public function inActive()
    {
        return view('services.in-active',['services' =>
            ServicesCategories::with('service')
                ->whereHas('service',function ($query){
                    $query->where('active',0);
                })->orderBy('created_at','ASC')->paginate(10)]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('services.create',['category' => Category::where('active',1)->get()]);
    }

    public function search(Request $request)
    {
        $search = $request->get('search');
        $services = ServicesCategories::with('service')->whereHas('service', function ($query) use ($search){
            $query->where('active',1)->where('name','like','%' . $search . '%');
        })->paginate(10);
        return view('services.index',['services' => $services]);
    }
    public function searchInActive(Request $request)
    {
        $search = $request->get('search');
        $services = ServicesCategories::with('service')->whereHas('service', function ($query) use ($search){
            $query->where('active',0)->where('name','like','%' . $search . '%');
        })->paginate(10);
        return view('services.in-active',['services' => $services]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $serviceCat = new ServicesCategories();
        $service = new Service();
        $service->guid = \Illuminate\Support\Str::uuid();
        $request['user_id'] = auth()->user()->getAuthIdentifier();
        $service->fill($request->all())->save();
        $serviceCat->service_id = $service->id;
        $serviceCat->category_id = $request->category_id;
        $serviceCat->save();
        return redirect('admin/services')->with('success','Service Added');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('services.edit',[
            'service' => Service::findOrFail($id),
            'category' => Category::where('active',1)->get()
        ]);
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
        $service = Service::find($id);
        $service->fill($request->all())->update();
        ServicesCategories::where('service_id',$service->id)->update(['category_id' => $request->category_id]);
        return redirect('admin/services')->with('success','Service Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();
        return back()->with('success','Service Deleted');
    }
}
