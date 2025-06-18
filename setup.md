# Setup of ga4

change credentials in .env
composer require freshbitsweb/laravel-google-analytics-4-measurement-protocol
php artisan vendor:publish --tag="ga4-config"
add google_analytics_enabled to bussiness settings

# setup of free delivery after

add free_delivery_after to bussiness settings
add free_delivery_after_enabled to bussiness settings


# setup for version of google merchent and facebook commerce

composer require wearepixel/laravel-google-shopping-feed