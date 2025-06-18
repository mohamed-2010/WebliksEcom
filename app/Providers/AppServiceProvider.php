<?php

namespace App\Providers;
use App\Models\BusinessSetting;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot()
  {
      Schema::defaultStringLength(191);
      Paginator::useBootstrap();
         URL::forceScheme('https');
//
           $order_limitation = 0;
      $order_limit = BusinessSetting::where('type', 'order_limit')->first();
      $orders_day = Order::whereDate('created_at' , '=', Carbon::now())->count();
      if ($order_limit){
          $order_limit =   $order_limit->value;
          if ($order_limit != 0) {
            if ($order_limit <= $orders_day){
              $order_limitation = 1;
            }
      }
      }
      
      View::share('order_limitation', $order_limitation);
  }

  /**
   * Register any application services.
   *
   * @return void
   */
  public function register()
  {
    //

  }
}
