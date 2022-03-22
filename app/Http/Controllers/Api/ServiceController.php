<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ArrayHelper;
use App\Helpers\GuidHelper;
use App\Helpers\StringHelper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServicesAttribute;
use App\Models\ServicesCategories;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
            $service->fill(ArrayHelper::merge($request->all(), ['status' => "DRAFT"]))->save();


            //@todo inherit attribute functionality
            foreach ($request->get('attributes', []) as $attribute) {
                $data = [
                    'attribute_id' => $attribute['id'],
                    'service_id' => $service->id,
                    'value' => $attribute['value'],

                ];

                $serviceAttribute = new ServicesAttribute($data);
                $serviceAttribute->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $this->genericResponse(true, 'Service Created', 200, ['service' => $service->withCategory()]);
    }

    /**
     * Display the specified resource.
     *
     * @param Service $service
     * @return Service
     */
    public function show(Service $service)
    {
        return $service->withMedia()->withCategory()->withServicesAttributes();
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

        return $this->genericResponse(true, "$service->name Updated", 200, ['service' => $service->withCategory()]);
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
        $services = Service::where('name', 'LIKE', "%{$request->get('query')}%")
            ->where($this->applyFilters($request))
            ->when($request->get('category_id'), function (Builder $builder, $category) use ($request) {
                $builder->where('category_id', $category)
                    ->when(json_decode($request->get('filters'), true), function (Builder $builder, $filters) {
                        $having = [];

                        foreach ($filters as $id => $value) {
                            if (is_bool($value)) {
                                $value = $value ? 'true' : 'false';
                            }

                            if (is_array($value)) {
                                $value = implode('","', $value);
                                $having[] = "sum(case when services_attributes.attribute_id = $id and json_overlaps(services_attributes.value, '[\"$value\"]') then 1 else 0 end) > 0";
                            } else {
                                $having[] = "sum(case when services_attributes.attribute_id = $id and json_contains(services_attributes.value, '\"$value\"') then 1 else 0 end) > 0";
                            }
                        }

                        $having = implode(' and ', $having);
                        $builder->whereRaw("
                            id in
                            (select services.id
                            from services
                            inner join services_attributes on services.id = services_attributes.service_id
                            group by services.id
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
            ->where('type', Category::SERVICE)
            ->get();

        return [
            'results' => $services,
            'categories' => $categories
        ];
    }

    public function upload(Service $service, Request $request)
    {
        return DB::transaction(function () use (&$request, &$service) {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $guid = GuidHelper::getGuid();
            $path = User::getUploadPath() . StringHelper::trimLower(Media::SERVICE_IMAGES);
            $name = "{$path}/{$guid}.{$extension}";
            $media = new Media();
            $media->fill([
                'name' => $name,
                'extension' => $extension,
                'type' => Media::SERVICE_IMAGES,
                'user_id' => \Auth::user()->id,
                'service_id' => $service->id,
                'active' => true,
            ]);

            $media->save();

            Storage::putFileAs(
                'public/' . $path, $request->file('file'), "{$guid}.{$extension}"
            );

            return [
                'uid' => $media->id,
                'name' => $media->url,
                'status' => 'done',
                'url' => $media->url,
            ];
        });
    }

    public function self()
    {
        return Service::where('user_id', Auth::user()->id)
            ->with(['category', 'media'])
            ->paginate($this->pageSize);
    }
}
