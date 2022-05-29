<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $product_id
 * @property integer $requester_id
 * @property integer $user_id
 * @property float $price
 * @property integer $status_id
 * @property integer $status_name
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 * @property requester $requester
 * @property Product $product
 * @property orders[] $orders
 */
class Offer extends Model
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
    protected $fillable = ['product_id', 'requester_id', 'user_id', 'price', 'status_id', 'status_name', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requester()
    {
        return $this->belongsTo('App\Models\User', 'requester_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany('App\Models\Order');
    }
}
