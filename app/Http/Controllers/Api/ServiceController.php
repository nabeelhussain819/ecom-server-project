<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use App\Models\ServicesAttribute;
use App\Models\ServicesCategories;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
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
        DB::beginTransaction();
        try {
            $service = new Service();
            //temporary 1
            $request['user_id'] = \Auth::user()->id;
            $service->fill($request->all())->save();
            $serviceCategories = new ServicesCategories($request->all());
            $service->categories()->saveMany([$serviceCategories]);

            //@todo inherit attribute functionality
            foreach ($request->get('attributes', []) as $attribute) {
                $data = [
                    'attribute_id' => $attribute['id'],
                    'service_id' => $service->id,
                    'value' => $attribute['value']
                ];

                $serviceAttribute = new ServicesAttribute($data);
                $serviceAttribute->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

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
        DB::beginTransaction();
        try {
            $service->fill($request->all())->update();

            $attributes = ($postedAttributes = $request->get('attributes')) ? array_combine(array_column($postedAttributes, 'id'), array_column($postedAttributes, 'value')) : [];
            // @TODO: create relations to avoid where query
            ServicesAttribute::where('product_id', $service->id)
                ->get()
                ->each(function (ServicesAttribute $attribute) use ($attributes) {
                    $attribute->value = $attributes[$attribute->attribute_id];
                    $attribute->save();
                });

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

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

    public function media(Service $service, Request $request)
    {
        return $service->images();
    }

    public function search(Request $request)
    {
        $services = Service::from('services as s')
            ->select(DB::raw('s.*, sc.category_id'))
            ->join('services_categories as sc', 's.id', '=', 'sc.service_id')
            ->where('s.name', 'LIKE', "%{$request->get('query')}%")
            ->when($request->get('category_id'), function (Builder $builder, $category) use ($request) {
                $builder->where('sc.category_id', $category)
                    ->when(json_decode($request->get('filters'), true), function (Builder $builder, $filters) {
                        $having = [];

                        foreach ($filters as $id => $value) {
                            if (is_bool($value)) {
                                $value = $value ? 'true' : 'false';
                            }

                            if (is_array($value)) {
                                $value = implode('","', $value);
                                $having[] = "sum(case when sa.attribute_id = $id and json_overlaps(sa.value, '[\"$value\"]') then 1 else 0 end) > 0";
                            } else {
                                $having[] = "sum(case when sa.attribute_id = $id and json_contains(sa.value, '\"$value\"') then 1 else 0 end) > 0";
                            }
                        }

                        $having = implode(' and ', $having);
                        $builder->whereRaw("
                            s.id in
                            (select s.id
                            from services s
                            inner join services_attributes sa on s.id = sa.service_id
                            group by s.id
                            having $having)
                        ");
                    });
            })
            ->distinct()
            ->get();

        $categories = Category::when($request->get('category_id'), function (Builder $builder, $category) {
            $builder->where('id', $category)
                ->with('attributes');
        })
            ->get();

        return [
            'results' => $services,
            'categories' => $categories
        ];
    }
}
