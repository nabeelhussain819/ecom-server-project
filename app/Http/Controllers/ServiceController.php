<?php

namespace App\Http\Controllers;

use App\Helpers\GuidHelper;
use App\Helpers\StringHelper;
use App\Models\Category;
use App\Models\Service;
use App\Models\ServicesCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('services.index', ['services' =>
            ServicesCategories::with('service')
                ->whereHas('service', function ($query) {
                    $query->where('active', true);
                })->paginate(10)]);
    }

    public function inActive()
    {
        return view('services.in-active', ['services' =>
            ServicesCategories::with('service')
                ->whereHas('service', function ($query) {
                    $query->where('active', false);
                })->orderBy('created_at', 'ASC')->paginate(10)]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('services.create', ['category' => Category::where('active', 1)->get()]);
    }

    public function search(Request $request)
    {
        $search = $request->get('search');
//        $services = ServicesCategories::with('service')->whereHas('service', function ($query) use ($search) {
//            $query->where('active', true)->where('name', 'like', '%' . $search . '%');
//        })->paginate(10);
        $services = Service::paginate(15);
        return view('services.index', ['services' => $services]);
    }

    public function searchInActive(Request $request)
    {
        $search = $request->get('search');
        $services = Category::where('active', false)->where('type', Category::SERVICE)->paginate();
        return view('services.in-active', ['services' => $services]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $service = new Service();
            $service->guid = GuidHelper::getGuid();
            $request['user_id'] = auth()->user()->getAuthIdentifier();
            $service->fill($request->all())->save();
        });
        return redirect('admin/services')->with('success', 'Service Added');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('services.edit', [
            'service' => Service::findOrFail($id),
            'category' => Category::where('active', 1)->get()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Service $service)
    {
        if ($request->get('activateOne') == "activateOnlyOne") {
            $service->update(['active' => $request->get('checkbox')]);
            return back()->with('success', "{$service->name} Status Changed Successfully.");
        } else {
            $service->fill($request->all())->update();
            ServicesCategories::where('service_id', $service->id)->update(['category_id' => $request->category_id]);
            return redirect('admin/services')->with('success', 'Service Updated');
        }
    }

    public function activateAll()
    {
        Service::query()->update(['active' => 1]);
        return back()->with('success', 'All Services Activated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Service $service)
    {
        $service->delete();
        return back()->with('success', 'Service Deleted');
    }
}
