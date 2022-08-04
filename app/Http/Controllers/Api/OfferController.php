<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfferController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

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
    }

    public function statusHandler(Request $request, Offer $offer)
    {
        $status = $request->get('status') ? Offer::$STATUS_ACCEPT : Offer::$STATUS_REJECT;
        $offer->update(["status_name" => $status]);
        return $this->genericResponse(true, "request updated");
    }

    public function pendingOffer($id){
        return Offer::where('id', $id)->with(["product" => function (BelongsTo $hasMany) {
            $hasMany->select(Product::defaultSelect());
        } , "requester" => function (BelongsTo $hasMany) {
            $hasMany->select(User::defaultSelect());
        }, "user" => function (BelongsTo $hasMany) {
            $hasMany->select(Product::getUser());
        }])->get();
    }
}
