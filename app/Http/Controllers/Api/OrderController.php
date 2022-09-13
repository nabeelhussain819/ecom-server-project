<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Fedex;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingDetail;
use App\Models\User;
use App\Notifications\OrderPlaced;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stripe\StripeClient;
use Carbon\Carbon;


class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $order = Order::orderBy('id')->first();
        $data['order'] = Order::orWhere(
            'buyer_id',
            Auth::user()->id
        )->orWhere('seller_id', Auth::user()->id)
            ->with(["product" => function (BelongsTo $hasMany) {
                $hasMany->select(Product::defaultSelect());
            }, "buyer" => function (BelongsTo $hasMany) {
                $hasMany->select(User::defaultSelect());
            }, 'shippingDetail' => function (BelongsTo $hasMany) {
                $hasMany->select(ShippingDetail::defaultSelect());
            }])->get();


        return $data['order'];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
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
        return DB::transaction(function () use ($request) {
            $order = new Order();
            $shipping = new ShippingDetail();
            //         $object = new Fedex();


            $shipping->fill($request->get("shippingDetail"));
            $shipping->user_id = Auth::user()->id;

            $shipping->save();


            $product = Product::getByGuid($request->get('product_id'));
            $offer = $product->offers()->where('requester_id', Auth::user()->id)
                ->where('status_name', Offer::$STATUS_ACCEPT)
                ->first();
            $order->seller_id = $product->user_id;
            $order->buyer_id = Auth::user()->id;
            $order->product_id = $product->id;
            $order->offer_id = $offer ? $offer->id : null;
            $order->price = $offer ? $offer->price : $product->price;
            $order->actual_price = $product->price;
            $order->shipping_detail_id = $shipping->id;
            $order->status = Order::STATUS_UNPAID;
            $order->save();

            return $order;
        });
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Order::where('id', $id)->with(["product" => function (BelongsTo $hasMany) {
            $hasMany->select(Product::defaultSelect());
        }, "buyer" => function (BelongsTo $hasMany) {
            $hasMany->select(User::defaultSelect());
        }, 'shippingDetail' => function (BelongsTo $hasMany) {
            $hasMany->select(ShippingDetail::defaultSelect());
        }])->get();
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
     * @return Order
     */
    public function update(Order $order, Request $request)
    {

        $shouldUpdate = true;
        if ($request->has('status')) {
            $stripe = new StripeClient(env('STRIPE_SK'));
            $paymentIntent = $stripe->paymentIntents->retrieve($request->get('payment_intent'));

            if ($paymentIntent->id !== $request->get('payment_intent') || $paymentIntent->status !== 'requires_capture')
                $shouldUpdate = false;
        }

        if ($shouldUpdate) {
            $buyer = User::where('id', $order->buyer_id)->first();
            $seller = User::where('id', $order->seller_id)->first();
            $buyer_shipping = ShippingDetail::where('id', $order->shipping_detail_id)->first();
            $resp = array(
                'labelResponseOptions' => "URL_ONLY",
                'requestedShipment' => array(
                    'shipper' => array(
                        'contact' => array(
                            "personName" => "Shipper Name",
                            "phoneNumber" => 1234567890,
                            // "companyName" => "Shipper Company Name"
                        ),
                        'address' => array(
                            'streetLines' => array(
                                "Shipper street address",
                            ),
                            "city" => "HARRISON",
                            "stateOrProvinceCode" => "AR",
                            "postalCode" => 72601,
                            "countryCode" => "US"
                        )
                    ),
                    'recipients' => array(
                        array(
                            'contact' => array(
                                "personName" => "BUYER NAME",
                                "phoneNumber" => 1234567890,
                                "companyName" => "Recipient Company Name"
                            ),
                            'address' => array(
                                'streetLines' => array(
                                    "Recipient street address",
                                ),
                                "city" => "Collierville", //$buyer_shipping->city,
                                "stateOrProvinceCode" => "TN", //$buyer_shipping->state,
                                "postalCode" => 38017, //$buyer_shipping->zip,
                                "countryCode" => "US"
                            )
                        ),
                    ),
                    'shippingChargesPayment' => array(
                        "paymentType" => "SENDER"
                    ),
                    "shipDatestamp" => Carbon::today()->format('Y-m-d'),
                    "serviceType" => "STANDARD_OVERNIGHT",
                    "packagingType" => "FEDEX_PAK",
                    "pickupType" => "USE_SCHEDULED_PICKUP",
                    "blockInsightVisibility" => false,
                    'labelSpecification' => array(
                        "imageType" => "PDF",
                        "labelStockType" => "PAPER_85X11_TOP_HALF_LABEL"
                    ),
                    'requestedPackageLineItems' => array(
                        array(
                            'weight' => array(
                                "value" => 10,
                                "units" => "LB"
                            )
                        ),
                    ),


                ),
                'accountNumber' => array(
                    "value" => "740561073"
                ),
            );
            $fedex_shipment = Fedex::createShipment($resp);
            // return $fedex_shipment;
            $req = $request->all();
            if (isset($fedex_shipment["errors"])) {
                throw new \Exception($fedex_shipment["errors"][0]['message'], 1);
            } else if (isset($fedex_shipment["output"]["transactionShipments"][0]["masterTrackingNumber"])) {
                $req["tracking_id"] = $fedex_shipment["output"]["transactionShipments"][0]["masterTrackingNumber"];
                $order->fill($req);
                $order->update();
                // $order["shipmentLabelUrl"] = $fedex_shipment["output"]["transactionShipments"][0]["shipmentDocuments"];

                // @Todo: create a different controller action for order confirmation
                if ($request->has('status')) {
                    /** @var User $user */
                    $user = Auth::user();
                    $user->notify(new OrderPlaced($order));
                }
            }
        }

        return $order;
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
}
