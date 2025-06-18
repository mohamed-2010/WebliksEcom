<?php

namespace App\Models;

use App\Notifications\EmailVerificationNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Guest extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasApiTokens;

    public function sendEmailVerificationNotification()
    {
        $this->notify(new EmailVerificationNotification());
    }

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = [
        'name', 'email',  'address', 'phone', 'country_id', 'state_id', 'city_id', 'longitude', 'latitude', 'postal_code', 'bloc', 'avenue', 'street', 'house'
    ];



    public function wishlists()
    {
    return $this->hasMany(Wishlist::class);
    }

    public function customer()
    {
    return $this->hasOne(Customer::class);
    }

    public function seller()
    {
    return $this->hasOne(Seller::class);
    }

    public function affiliate_user()
    {
    return $this->hasOne(AffiliateUser::class);
    }

    public function affiliate_withdraw_request()
    {
    return $this->hasMany(AffiliateWithdrawRequest::class);
    }

    public function products()
    {
    return $this->hasMany(Product::class);
    }

    public function shop()
    {
    return $this->hasOne(Shop::class);
    }

    public function staff()
    {
    return $this->hasOne(Staff::class);
    }

    public function orders()
    {
    return $this->hasMany(Order::class);
    }

    public function wallets()
    {
    return $this->hasMany(Wallet::class)->orderBy('created_at', 'desc');
    }

    public function club_point()
    {
    return $this->hasOne(ClubPoint::class);
    }

    public function customer_package()
    {
        return $this->belongsTo(CustomerPackage::class);
    }

    public function customer_package_payments()
    {
        return $this->hasMany(CustomerPackagePayment::class);
    }

    public function customer_products()
    {
        return $this->hasMany(CustomerProduct::class);
    }

    public function seller_package_payments()
    {
        return $this->hasMany(SellerPackagePayment::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function affiliate_log()
    {
        return $this->hasMany(AffiliateLog::class);
    }
}
