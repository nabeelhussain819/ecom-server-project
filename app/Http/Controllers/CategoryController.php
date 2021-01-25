<?php

namespace App\Http\Controllers;

use App\Helpers\StringHelper;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('category.create');
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
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('category.edit', ['category' => Category::findOrFail($id)]);
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
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return back()->with('success', 'Category deleted');
    }
}
