<?php

namespace App\Models;

use App\Core\Base;
use App\Helpers\StringHelper;

/**
 * App\Models\Attribute
 *
 * @property integer $id
 * @property string $name
 * @properties AttributesValue[] $attributesValues
 * @properties ProductAttribute[] $productAttributes
 * @method static \Illuminate\Database\Eloquent\Builder|Attribute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attribute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attribute query()
 * @method static \Illuminate\Database\Eloquent\Builder|Attribute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attribute whereName($value)
 * @mixin \Eloquent
 */
class Attribute extends Base
{
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */

//    protected $hasGuid = false;
    protected $autoBlame = false;
    /**
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attributesValues()
    {
        return $this->hasMany('App\AttributesValue');
    }

    public function setNameAttribute($name)
    {
        $this->attributes['name'] = StringHelper::trimLower($name);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productAttributes()
    {
        return $this->hasMany('App\ProductAttribute');
    }
}
