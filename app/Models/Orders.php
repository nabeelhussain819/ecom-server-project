<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property integer $shipping_detail_id
 * @property integer $product_id
 * @property integer $type_id
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 * @property ShippingDetail $shippingDetail
 * @property Product $product
 */
class Orders extends Model
{
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'shipping_detail_id',
        'product_id',
        'type_id',
        'status',
        'created_at',
        'updated_at',
        'offer_id',
        'price',
        'actual_price'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shippingDetail()
    {
        return $this->belongsTo('App\Models\ShippingDetail');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }
}
