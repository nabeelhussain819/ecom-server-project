@component('mail::message')
<div>
  <h2>Purchased Item Details</h2>

  <table>
    <tr>
      <td><img src="{{Storage::url('users/product/$product->id')}}" height="70px" width="70px" /></td>
      <td style="float:left;">{{$product->name}}
        <br />Lorem ipsum dolor sit amet, consectetur adipiscin Lorem ipsum dolor sit amet, consectetur adipiscin
      </td>
    </tr>
  </table>
  <hr />
  <table style="width:100%">
    <tr>
      <td>Order number</td>
      <td>{{$order->id}}</td>
    </tr>
    <tr>
      <td>Invoice date</td>
      <td>{{$order->created_at}}</td>
    </tr>
    <tr>
      <td>Shipping from</td>
      <td>Bicycle</td>
    </tr>
    <tr>
      <td>Shipping to</td>
      <td>Bicycle</td>
    </tr>
  </table>
  <hr />
  <img src="{{asset('image/image.png')}}" style="z-index:7;position: absolute;left:40%" width="120px" height="120px" />
  <table style="width:95%">
    <tr>
      <td><b> Seller Name </b></td>
      <td><b>{{$product->user->name}}</b></td>
    </tr>
    <tr>
      <td><b>Payment method</b></td>
      <td><b>Bicycle</b></td>
    </tr>
  </table>
  <hr />
  <table style="width:93%">
    <tr>
      <td>Item price</td>
      <td>{{$product->price}}</td>
    </tr>
    <tr>
      <td>Shipping</td>
      <td>{{$order->actual_price}}</td>
    </tr>
    <tr>
      <td>Sales tax (estimated)</td>
      <td>$0.00</td>
    </tr>
    <tr>
      <td>
        <h4>You Pay</h4>
      </td>
      <td>
        <h4>{{$order->price}}</h4>
      </td>
    </tr>
  </table>
  <hr />
  <table style="width:135%">
    <tr>
      <td><b> Status number</b></td>
      <td><i>{{$order->status}}</i>
        <div style="color:pink">Track our Item?</div>
      </td>
    </tr>
  </table>
</div>
@endcomponent