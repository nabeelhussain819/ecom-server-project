<?php

namespace App\Models;

use App\Notifications\RegistrationVerificationNotification;
use App\Observers\UserObserver;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Contracts\Providers\JWT;

/**
 * App\Models\User
 *
 * @property integer $id
 * @property string $stripe_account_id
 * @property string $name
 * @property string $email
 * @property string $email_verified_at
 * @property string $password
 * @property string $guid
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Comment[] $comments
 * @property CommentsLike[] $commentsLikes
 * @property Medium[] $media
 * @property string $profile_url
 * @property Product[] $products
 * @property Service[] $services
 * @property Vendor[] $vendors
 * @property Offer[] sellingOffers
 * @property Offer[] buyingOffers
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Client[] $clients
 * @property-read int|null $clients_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Token[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasApiTokens, Notifiable;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['name', 'email', 'email_verified_at', 'password', 'phone', 'guid', 'profile_url', 'remember_token', 'created_at', 'updated_at'];

    protected $hidden = ['password'];

    const MEDIA_UPLOAD = 'User';

    protected static function boot()
    {
        parent::boot();

        User::observe(UserObserver::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function commentsLikes()
    {
        return $this->hasMany('App\Models\CommentsLike');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function media()
    {
        return $this->hasMany(Media::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany('App\Models\Product');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function services()
    {
        return $this->hasMany('App\Models\Service');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function vendors()
    {
        return $this->hasMany('App\Models\Vendor');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public static function getUploadPath(): string
    {
        return 'users/' . \Auth::user()->id . '/';
    }

    public function getProfileUrlAttribute($profile_url)
    {
        return url(Storage::url($profile_url));
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new RegistrationVerificationNotification());
    }

    public function isVerified()
    {
        return !empty($this->email_verified_at);
    }

    public function validateEmailVerification()
    {

        if (!$this->isVerified()) {
            throw new NotAcceptableHttpException("Email not verified");
        }
    }


    public function savedProducts()
    {
        // return $this->hasManyThrough(Product::class, SavedUsersProduct::class, 'product_id', 'id', 'id', 'id');
        return $this->belongsToMany(Product::class, SavedUsersProduct::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function buyingOffers()
    {
        return $this->hasMany(Offer::class, 'requester_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sellingOffers()
    {
        return $this->hasMany(Offer::class, 'user_id');
    }
    public static function defaultSelect()
    {
        return ['id', 'name', 'profile_url'];
    }
    public function withNotifications()
    {
        return $this->load('notifications');
    }
}
