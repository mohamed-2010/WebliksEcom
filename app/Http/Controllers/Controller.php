<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Order;
use App\Models\User;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Override callAction to add order counts to views.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function callAction($method, $parameters)
    {
        $response = parent::callAction($method, $parameters);

        // Check if the response is a view
        if ($response instanceof \Illuminate\View\View) {
            $orderCounts = $this->getOrderCounts();

            // Share the counts with the view
            $response->with($orderCounts);
        }

        return $response;
    }

    /**
     * Get order counts.
     *
     * @return array
     */
    protected function getOrderCounts()
    {
        $adminUserId = User::where('user_type', 'admin')->value('id');

        return [
            'all_orders_count' => Order::where('viewed', 0)->count(),
            'in_house_orders_count' => Order::where('seller_id', $adminUserId)
            ->where('viewed', 0)->count(),
            'seller_orders_count' => Order::where('seller_id', '!=', $adminUserId)
            ->where('viewed', 0)->count(),
            'pickup_point_orders_count' => Order::where('shipping_type', 'pickup_point')
            ->where('viewed', 0)->count(),
        ];
    }
}
