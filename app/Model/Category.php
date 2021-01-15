<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property string $type
 * @property boolean $other
 * @property string $created_at
 * @property string $updated_at
 * @property ProductsCategory[] $productsCategories
 * @property ServicesCategory[] $servicesCategories
 */
class Category extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'category';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['name', 'description', 'type', 'other', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productsCategories()
    {
        return $this->hasMany('App\Model\ProductsCategory');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function servicesCategories()
    {
        return $this->hasMany('App\Model\ServicesCategory');
    }
}
