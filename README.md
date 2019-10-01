# module-core

#### Publishing Config Files
To work with Pusher live notifications, publish the config file and add the Notification classes for which CPM should show live notifications.

```
php artisan vendor:publish --provider="CircleLinkHealth\Core\Providers\CoreServiceProvider" --tag="live-notifications"
``` 
