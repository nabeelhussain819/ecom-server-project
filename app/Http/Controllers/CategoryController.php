<?php

namespace App\Http\Controllers;

use App\Helpers\GuidHelper;
use App\Helpers\StringHelper;
use App\Http\Requests\CategoryRequest;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\CategoryAttributes;
use App\Models\Product;
use App\Models\ProductsAttribute;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('category.index', ['category' =>
            Category::where('active', true)
                ->orderBy('created_at', 'ASC')
                ->paginate(10)]);
    }

    public function inActive()
    {
        return view('category.in-active', ['category' =>
            Category::where('active', false)
                ->orderBy('created_at', 'ASC')
                ->paginate(10)]);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        $categories = Category::where('active', true)->get();

        return view('category.create', ['categories' => $categories]);
    }

    public function search(Request $request)
    {
        $search = $request->get('search');
        return view('category.index', ['category' =>
            Category::where('active', true)
                ->where('name', 'like', '%' . $search . '%')
                ->paginate(10)]);
    }

    public function searchInActive(Request $request)
    {
        $search = $request->get('search');
        return view('category.in-active', ['category' => Category::where('active', 0)
            ->where('name', 'like', '%' . $search . '%')
            ->paginate(10)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(CategoryRequest $request)
    {
        //
        $category = new Category();
        $category->guid = GuidHelper::getGuid();
        $category->fill($request->all())->save();
        return redirect('admin/category')->with('success', 'Category Added.');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return view('category.show', ['category' => Category::findOrFail($id)]);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $categories = Category::where('active', true)->get();
        return view('category.edit', ['category' => Category::findOrFail($id), 'categories' => $categories]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        if ($request->get('activateOne') == "activateOnlyOne") {
            $category->update(['active' => StringHelper::isValueTrue($request->get('active'))]);
            return back()->with('success', "{$category->name} Activated Successfully.");
        }
        $category->fill($request->all())->update();
        return back()->with('success', 'Category Updated');
    }

    public function activateAll()
    {
        Category::query()->update(['active' => 1]);
        return back()->with('success', 'All Categories Activated');
    }

    /**
     * @param Category $category
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     * @todo check the change please this is how would you bind the model
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Category deleted');
    }

    /**
     * showing the view of add properties
     * @param Category $category
     */
    public function showAttributes(Category $category)
    {
        return view('category.add-properties',
            ['category' => $category,
                'attributes' => Attribute::getAll()->get()
            ]
        );
    }

    public function addAttributes(Category $category, Request $request)
    {
        $categoryAttributes = new CategoryAttributes($request->all());

        $category->categoryAttributes()->saveMany([$categoryAttributes]);
        return back()->with('success', 'All Categories Activated');
    }

    public function showAttributesList(Category $category)
    {
        return view('category.show-properties', ['category' => $category]);
    }

    public function attributes(Category $category, ?Product $product)
    {
        $defaults = [];
        if ($product->exists) {
            // @TODO: create relations to avoid where query
            $defaults = ProductsAttribute::where('product_id', $product->id)
                ->pluck('value', 'attribute_id')
                ->all();
        }

        return view('products.attributes', ['attributes' => $category->attributes()->get(), 'defaults' => $defaults]);
    }
}
