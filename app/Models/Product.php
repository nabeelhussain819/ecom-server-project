<?php

namespace App\Models;

use App\Core\Base;
use App\Interfaces\IMediaInteraction;
use App\Traits\InteractWithMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * App\Models\Product
 *
 * @property integer $id
 * @property integer $category_id
 * @property integer $user_id
 * @property string $name
 * @property string $description
 * @property float $price
 * @property float $sale_price
 * @property string $location
 * @property string $google_address
 * @property string $postal_address
 * @property float $longitude
 * @property float $latitude
 * @property boolean $active
 * @property string $guid
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 * @property ProductsCategories[] $productsCategories
 * @property Media[] media
 * @property Rating[] $ratings
 * @property-read int|null $ratings_count
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereGoogleAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereGuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePostalAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSalePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUserId($value)
 * @mixin \Eloquent
 */
class Product extends Base implements IMediaInteraction
{
    use InteractWithMedia;

    protected $autoBlame = false; //@todo temp
    public const MEDIA_UPLOAD = "PRODUCT";

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    public static function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub
    }

    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['category_id', 'user_id', 'name', 'description', 'price', 'sale_price', 'location', 'google_address', 'postal_address', 'longitude', 'latitude', 'active', 'guid', 'created_at', 'updated_at'];

    protected $appends = ['cover_image', 'is_owner'];

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
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function productsAttributes()
    {
        return $this->hasMany(ProductsAttribute::class);
    }


    public function savedUsers()
    {
        return $this->hasMany(SavedUsersProduct::class, 'product_id');
    }

    public function withCategory()
    {
        return $this->load(['category' => function (BelongsTo $query) {
            $query->with('attributes');
        }]);
    }

    public function withProductsAttributes()
    {
        return $this->load('productsAttributes');
    }

    /**
     * this is temp fix for the Demo its should  be field in the product table
     * @return |null
     */
    public function getCoverImageAttribute()
    {
        $media = $this->media->first();
        if (!empty($media)) {
            return $media->url;
        }
        return null;
    }

    public function getIsOwnerAttribute()
    {
        if (!empty(\Auth::user())) {
            return $this->user_id === \Auth::user()->id;
        }
        return false;
    }

    /**
     *User that method as in Trait so what so ever we also used in service just passing the same name in relation
     */
    public function attachOrDetachSaved()
    {
        if (Auth::check()) {
            $authenticatedUserId = \Auth::user()->id;

            $savedItem = $this->savedUsers()->where('user_id', $authenticatedUserId)->first();

            if (!empty($savedItem)) {
                return $savedItem->delete();
            }
            $this->savedUsers()->save(new SavedUsersProduct(["user_id" => $authenticatedUserId]));
        }
    }

    public function getIsSavedAttribute(): bool
    {
        if (auth('api')->check()) {
            return $this->savedUsers->contains('user_id', auth('api')->user()->id);
        }
        return false;
    }

    public function appendDetailAttribute()
    {
        $this->append(['isSaved']);
        return $this;
    }
}
