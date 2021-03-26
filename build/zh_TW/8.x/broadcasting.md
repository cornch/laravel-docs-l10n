# Broadcasting

- [Introduction](#introduction)
- [Server Side Installation](#server-side-installation)
    - [Configuration](#configuration)
    - [Pusher Channels](#pusher-channels)
    - [Ably](#ably)
    - [Open Source Alternatives](#open-source-alternatives)
- [Client Side Installation](#client-side-installation)
    - [Pusher Channels](#client-pusher-channels)
    - [Ably](#client-ably)
- [Concept Overview](#concept-overview)
    - [Using An Example Application](#using-example-application)
- [Defining Broadcast Events](#defining-broadcast-events)
    - [Broadcast Name](#broadcast-name)
    - [Broadcast Data](#broadcast-data)
    - [Broadcast Queue](#broadcast-queue)
    - [Broadcast Conditions](#broadcast-conditions)
    - [Broadcasting & Database
      Transactions](#broadcasting-and-database-transactions)
- [Authorizing Channels](#authorizing-channels)
    - [Defining Authorization Routes](#defining-authorization-routes)
    - [Defining Authorization Callbacks](#defining-authorization-callbacks)
    - [Defining Channel Classes](#defining-channel-classes)
- [Broadcasting Events](#broadcasting-events)
    - [Only To Others](#only-to-others)
- [Receiving Broadcasts](#receiving-broadcasts)
    - [Listening For Events](#listening-for-events)
    - [Leaving A Channel](#leaving-a-channel)
    - [Namespaces](#namespaces)
- [Presence Channels](#presence-channels)
    - [Authorizing Presence Channels](#authorizing-presence-channels)
    - [Joining Presence Channels](#joining-presence-channels)
    - [Broadcasting To Presence
      Channels](#broadcasting-to-presence-channels)
- [Client Events](#client-events)
- [Notifications](#notifications)

<a name="introduction"></a>
## Introduction

In many modern web applications, WebSockets are used to implement realtime,
live-updating user interfaces. When some data is updated on the server, a
message is typically sent over a WebSocket connection to be handled by the
client. WebSockets provide a more efficient alternative to continually
polling your application's server for data changes that should be reflected
in your UI.

For example, imagine your application is able to export a user's data to a
CSV file and email it to them. However, creating this CSV file takes several
minutes so you choose to create and mail the CSV within a [queued
job](/docs/{{version}}/queues). When the CSV has been created and mailed to
the user, we can use event broadcasting to dispatch a
`App\Events\UserDataExported` event that is received by our application's
JavaScript. Once the event is received, we can display a message to the user
that their CSV has been emailed to them without them ever needing to refresh
the page.

To assist you in building these types of features, Laravel makes it easy to
"broadcast" your server-side Laravel [events](/docs/{{version}}/events) over
a WebSocket connection. Broadcasting your Laravel events allows you to share
the same event names and data between your server-side Laravel application
and your client-side JavaScript application.

<a name="supported-drivers"></a>
#### Supported Drivers

By default, Laravel includes two server-side broadcasting drivers for you to
choose from: [Pusher Channels](https://pusher.com/channels) and
[Ably](https://ably.io). However, community driven packages such as
[laravel-websockets](https://beyondco.de/docs/laravel-websockets/getting-started/introduction)
provide additional broadcasting drivers that do not require commercial
broadcasting providers.

> {tip} Before diving into event broadcasting, make sure you have read Laravel's documentation on [events and listeners](/docs/{{version}}/events).

<a name="server-side-installation"></a>
## Server Side Installation

To get started using Laravel's event broadcasting, we need to do some
configuration within the Laravel application as well as install a few
packages.

Event broadcasting is accomplished by a server-side broadcasting driver that
broadcasts your Laravel events so that Laravel Echo (a JavaScript library)
can receive them within the browser client. Don't worry - we'll walk through
each part of the installation process step-by-step.

<a name="configuration"></a>
### Configuration

All of your application's event broadcasting configuration is stored in the
`config/broadcasting.php` configuration file. Laravel supports several
broadcast drivers out of the box: [Pusher
Channels](https://pusher.com/channels), [Redis](/docs/{{version}}/redis),
and a `log` driver for local development and debugging. Additionally, a
`null` driver is included which allows you to totally disable broadcasting
during testing. A configuration example is included for each of these
drivers in the `config/broadcasting.php` configuration file.

<a name="broadcast-service-provider"></a>
#### Broadcast Service Provider

Before broadcasting any events, you will first need to register the
`App\Providers\BroadcastServiceProvider`. In new Laravel applications, you
only need to uncomment this provider in the `providers` array of your
`config/app.php` configuration file. This `BroadcastServiceProvider`
contains the code necessary to register the broadcast authorization routes
and callbacks.

<a name="queue-configuration"></a>
#### Queue Configuration

You will also need to configure and run a [queue
worker](/docs/{{version}}/queues). All event broadcasting is done via queued
jobs so that the response time of your application is not seriously affected
by events being broadcast.

<a name="pusher-channels"></a>
### Pusher Channels

If you plan to broadcast your events using [Pusher
Channels](https://pusher.com/channels), you should install the Pusher
Channels PHP SDK using the Composer package manager:

    composer require pusher/pusher-php-server "^5.0"

Next, you should configure your Pusher Channels credentials in the
`config/broadcasting.php` configuration file. An example Pusher Channels
configuration is already included in this file, allowing you to quickly
specify your key, secret, and application ID. Typically, these values should
be set via the `PUSHER_APP_KEY`, `PUSHER_APP_SECRET`, and `PUSHER_APP_ID`
[environment
variables](/docs/{{version}}/configuration#environment-configuration):

    PUSHER_APP_ID=your-pusher-app-id
    PUSHER_APP_KEY=your-pusher-key
    PUSHER_APP_SECRET=your-pusher-secret
    PUSHER_APP_CLUSTER=mt1

The `config/broadcasting.php` file's `pusher` configuration also allows you
to specify additional `options` that are supported by Channels, such as the
cluster.

Next, you will need to change your broadcast driver to `pusher` in your
`.env` file:

    BROADCAST_DRIVER=pusher

Finally, you are ready to install and configure [Laravel
Echo](#client-side-installation), which will receive the broadcast events on
the client-side.

<a name="pusher-compatible-laravel-websockets"></a>
#### Pusher Compatible Laravel Websockets

The [laravel-websockets](https://github.com/beyondcode/laravel-websockets)
package is a pure PHP, Pusher compatible WebSocket package for Laravel. This
package allows you to leverage the full power of Laravel broadcasting
without a commercial WebSocket provider. For more information on installing
and using this package, please consult its [official
documentation](https://beyondco.de/docs/laravel-websockets).

<a name="ably"></a>
### Ably

If you plan to broadcast your events using [Ably](https://ably.io), you
should install the Ably PHP SDK using the Composer package manager:

    composer require ably/ably-php

Next, you should configure your Ably credentials in the
`config/broadcasting.php` configuration file. An example Ably configuration
is already included in this file, allowing you to quickly specify your
key. Typically, this value should be set via the `ABLY_KEY` [environment
variable](/docs/{{version}}/configuration#environment-configuration):

    ABLY_KEY=your-ably-key

Next, you will need to change your broadcast driver to `ably` in your `.env`
file:

    BROADCAST_DRIVER=ably

Finally, you are ready to install and configure [Laravel
Echo](#client-side-installation), which will receive the broadcast events on
the client-side.

<a name="open-source-alternatives"></a>
### Open Source Alternatives

The [laravel-websockets](https://github.com/beyondcode/laravel-websockets)
package is a pure PHP, Pusher compatible WebSocket package for Laravel. This
package allows you to leverage the full power of Laravel broadcasting
without a commercial WebSocket provider. For more information on installing
and using this package, please consult its [official
documentation](https://beyondco.de/docs/laravel-websockets).

<a name="client-side-installation"></a>
## Client Side Installation

<a name="client-pusher-channels"></a>
### Pusher Channels

Laravel Echo is a JavaScript library that makes it painless to subscribe to
channels and listen for events broadcast by your server-side broadcasting
driver. You may install Echo via the NPM package manager. In this example,
we will also install the `pusher-js` package since we will be using the
Pusher Channels broadcaster:

```bash
npm install --save-dev laravel-echo pusher-js
```

Once Echo is installed, you are ready to create a fresh Echo instance in
your application's JavaScript. A great place to do this is at the bottom of
the `resources/js/bootstrap.js` file that is included with the Laravel
framework. By default, an example Echo configuration is already included in
this file - you simply need to uncomment it:

```js
import Echo from 'laravel-echo';

window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true
});
```

Once you have uncommented and adjusted the Echo configuration according to
your needs, you may compile your application's assets:

    npm run dev

> {tip} To learn more about compiling your application's JavaScript assets, please consult the documentation on [Laravel Mix](/docs/{{version}}/mix).

<a name="using-an-existing-client-instance"></a>
#### Using An Existing Client Instance

If you already have a pre-configured Pusher Channels client instance that
you would like Echo to utilize, you may pass it to Echo via the `client`
configuration option:

```js
import Echo from 'laravel-echo';

const client = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'your-pusher-channels-key',
    client: client
});
```

<a name="client-ably"></a>
### Ably

Laravel Echo is a JavaScript library that makes it painless to subscribe to
channels and listen for events broadcast by your server-side broadcasting
driver. You may install Echo via the NPM package manager. In this example,
we will also install the `pusher-js` package.

You may wonder why we would install the `pusher-js` JavaScript library even
though we are using Ably to broadcast our events. Thankfully, Ably includes
a Pusher compatibility mode which lets us use the Pusher protocol when
listening for events in our client-side application:

```bash
npm install --save-dev laravel-echo pusher-js
```

**Before continuing, you should enable Pusher protocol support in your Ably application settings. You may enable this feature within the "Protocol Adapter Settings" portion of your Ably application's settings dashboard.**

Once Echo is installed, you are ready to create a fresh Echo instance in
your application's JavaScript. A great place to do this is at the bottom of
the `resources/js/bootstrap.js` file that is included with the Laravel
framework. By default, an example Echo configuration is already included in
this file; however, the default configuration in the `bootstrap.js` file is
intended for Pusher. You may copy the configuration below to transition your
configuration to Ably:

```js
import Echo from 'laravel-echo';

window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_ABLY_PUBLIC_KEY,
    wsHost: 'realtime-pusher.ably.io',
    wsPort: 443,
    disableStats: true,
    encrypted: true,
});
```

Note that our Ably Echo configuration references a `MIX_ABLY_PUBLIC_KEY`
environment variable. This variable's value should be your Ably public
key. Your public key is the portion of your Ably key that occurs before the
`:` character.

Once you have uncommented and adjusted the Echo configuration according to
your needs, you may compile your application's assets:

    npm run dev

> {tip} To learn more about compiling your application's JavaScript assets, please consult the documentation on [Laravel Mix](/docs/{{version}}/mix).

<a name="concept-overview"></a>
## Concept Overview

Laravel's event broadcasting allows you to broadcast your server-side
Laravel events to your client-side JavaScript application using a
driver-based approach to WebSockets. Currently, Laravel ships with [Pusher
Channels](https://pusher.com/channels) and [Ably](https://ably.io)
drivers. The events may be easily consumed on the client-side using the
[Laravel Echo](#client-side-installation) JavaScript package.

Events are broadcast over "channels", which may be specified as public or
private. Any visitor to your application may subscribe to a public channel
without any authentication or authorization; however, in order to subscribe
to a private channel, a user must be authenticated and authorized to listen
on that channel.

> {tip} If you would like to use an open source, PHP driven alternative to Pusher, check out the [laravel-websockets](https://github.com/beyondcode/laravel-websockets) package.

<a name="using-example-application"></a>
### Using An Example Application

Before diving into each component of event broadcasting, let's take a high
level overview using an e-commerce store as an example.

In our application, let's assume we have a page that allows users to view
the shipping status for their orders. Let's also assume that a
`OrderShipmentStatusUpdated` event is fired when a shipping status update is
processed by the application:

    use App\Events\OrderShipmentStatusUpdated;

    OrderShipmentStatusUpdated::dispatch($order);

<a name="the-shouldbroadcast-interface"></a>
#### The `ShouldBroadcast` Interface

When a user is viewing one of their orders, we don't want them to have to
refresh the page to view status updates. Instead, we want to broadcast the
updates to the application as they are created. So, we need to mark the
`OrderShipmentStatusUpdated` event with the `ShouldBroadcast`
interface. This will instruct Laravel to broadcast the event when it is
fired:

    <?php

    namespace App\Events;

    use App\Order;
    use Illuminate\Broadcasting\Channel;
    use Illuminate\Broadcasting\InteractsWithSockets;
    use Illuminate\Broadcasting\PresenceChannel;
    use Illuminate\Broadcasting\PrivateChannel;
    use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
    use Illuminate\Queue\SerializesModels;

    class OrderShipmentStatusUpdated implements ShouldBroadcast
    {
        /**
         * The order instance.
         *
         * @var \App\Order
         */
        public $order;
    }

The `ShouldBroadcast` interface requires our event to define a `broadcastOn`
method. This method is responsible for returning the channels that the event
should broadcast on. An empty stub of this method is already defined on
generated event classes, so we only need to fill in its details. We only
want the creator of the order to be able to view status updates, so we will
broadcast the event on a private channel that is tied to the order:

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\PrivateChannel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('orders.'.$this->order->id);
    }

<a name="example-application-authorizing-channels"></a>
#### Authorizing Channels

Remember, users must be authorized to listen on private channels. We may
define our channel authorization rules in our application's
`routes/channels.php` file. In this example, we need to verify that any user
attempting to listen on the private `order.1` channel is actually the
creator of the order:

    use App\Models\Order;

    Broadcast::channel('orders.{orderId}', function ($user, $orderId) {
        return $user->id === Order::findOrNew($orderId)->user_id;
    });

The `channel` method accepts two arguments: the name of the channel and a
callback which returns `true` or `false` indicating whether the user is
authorized to listen on the channel.

All authorization callbacks receive the currently authenticated user as
their first argument and any additional wildcard parameters as their
subsequent arguments. In this example, we are using the `{orderId}`
placeholder to indicate that the "ID" portion of the channel name is a
wildcard.

<a name="listening-for-event-broadcasts"></a>
#### Listening For Event Broadcasts

Next, all that remains is to listen for the event in our JavaScript
application. We can do this using Laravel Echo. First, we'll use the
`private` method to subscribe to the private channel. Then, we may use the
`listen` method to listen for the `OrderShipmentStatusUpdated` event. By
default, all of the event's public properties will be included on the
broadcast event:

```js
Echo.private(`orders.${orderId}`)
    .listen('OrderShipmentStatusUpdated', (e) => {
        console.log(e.order);
    });
```

<a name="defining-broadcast-events"></a>
## Defining Broadcast Events

To inform Laravel that a given event should be broadcast, you must implement
the `Illuminate\Contracts\Broadcasting\ShouldBroadcast` interface on the
event class. This interface is already imported into all event classes
generated by the framework so you may easily add it to any of your events.

The `ShouldBroadcast` interface requires you to implement a single method:
`broadcastOn`. The `broadcastOn` method should return a channel or array of
channels that the event should broadcast on. The channels should be
instances of `Channel`, `PrivateChannel`, or `PresenceChannel`. Instances of
`Channel` represent public channels that any user may subscribe to, while
`PrivateChannels` and `PresenceChannels` represent private channels that
require [channel authorization](#authorizing-channels):

    <?php

    namespace App\Events;

    use App\Models\User;
    use Illuminate\Broadcasting\Channel;
    use Illuminate\Broadcasting\InteractsWithSockets;
    use Illuminate\Broadcasting\PresenceChannel;
    use Illuminate\Broadcasting\PrivateChannel;
    use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
    use Illuminate\Queue\SerializesModels;

    class ServerCreated implements ShouldBroadcast
    {
        use SerializesModels;

        /**
         * The user that created the server.
         *
         * @var \App\Models\User
         */
        public $user;

        /**
         * Create a new event instance.
         *
         * @param  \App\Models\User  $user
         * @return void
         */
        public function __construct(User $user)
        {
            $this->user = $user;
        }

        /**
         * Get the channels the event should broadcast on.
         *
         * @return Channel|array
         */
        public function broadcastOn()
        {
            return new PrivateChannel('user.'.$this->user->id);
        }
    }

After implementing the `ShouldBroadcast` interface, you only need to [fire
the event](/docs/{{version}}/events) as you normally would. Once the event
has been fired, a [queued job](/docs/{{version}}/queues) will automatically
broadcast the event using your specified broadcast driver.

<a name="broadcast-name"></a>
### Broadcast Name

By default, Laravel will broadcast the event using the event's class
name. However, you may customize the broadcast name by defining a
`broadcastAs` method on the event:

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'server.created';
    }

If you customize the broadcast name using the `broadcastAs` method, you
should make sure to register your listener with a leading `.`
character. This will instruct Echo to not prepend the application's
namespace to the event:

    .listen('.server.created', function (e) {
        ....
    });

<a name="broadcast-data"></a>
### Broadcast Data

When an event is broadcast, all of its `public` properties are automatically
serialized and broadcast as the event's payload, allowing you to access any
of its public data from your JavaScript application. So, for example, if
your event has a single public `$user` property that contains an Eloquent
model, the event's broadcast payload would be:

    {
        "user": {
            "id": 1,
            "name": "Patrick Stewart"
            ...
        }
    }

However, if you wish to have more fine-grained control over your broadcast
payload, you may add a `broadcastWith` method to your event. This method
should return the array of data that you wish to broadcast as the event
payload:

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return ['id' => $this->user->id];
    }

<a name="broadcast-queue"></a>
### Broadcast Queue

By default, each broadcast event is placed on the default queue for the
default queue connection specified in your `queue.php` configuration
file. You may customize the queue connection and name used by the
broadcaster by defining `connection` and `queue` properties on your event
class:

    /**
     * The name of the queue connection to use when broadcasting the event.
     *
     * @var string
     */
    public $connection = 'redis';

    /**
     * The name of the queue on which to place the broadcasting job.
     *
     * @var string
     */
    public $queue = 'default';

If you want to broadcast your event using the `sync` queue instead of the
default queue driver, you can implement the `ShouldBroadcastNow` interface
instead of `ShouldBroadcast`:

    <?php

    use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

    class OrderShipmentStatusUpdated implements ShouldBroadcastNow
    {
        //
    }

<a name="broadcast-conditions"></a>
### Broadcast Conditions

Sometimes you want to broadcast your event only if a given condition is
true. You may define these conditions by adding a `broadcastWhen` method to
your event class:

    /**
     * Determine if this event should broadcast.
     *
     * @return bool
     */
    public function broadcastWhen()
    {
        return $this->order->value > 100;
    }

<a name="broadcasting-and-database-transactions"></a>
#### Broadcasting & Database Transactions

When broadcast events are dispatched within database transactions, they may
be processed by the queue before the database transaction has
committed. When this happens, any updates you have made to models or
database records during the database transaction may not yet be reflected in
the database. In addition, any models or database records created within the
transaction may not exist in the database. If your event depends on these
models, unexpected errors can occur when the job that broadcasts the event
is processed.

If your queue connection's `after_commit` configuration option is set to
`false`, you may still indicate that a particular broadcast event should be
dispatched after all open database transactions have been committed by
defining an `$afterCommit` property on the event class:

    <?php

    namespace App\Events;

    use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
    use Illuminate\Queue\SerializesModels;

    class ServerCreated implements ShouldBroadcast
    {
        use SerializesModels;

        public $afterCommit = true;
    }

> {tip} To learn more about working around these issues, please review the documentation regarding [queued jobs and database transactions](/docs/{{version}}/queues#jobs-and-database-transactions).

<a name="authorizing-channels"></a>
## Authorizing Channels

Private channels require you to authorize that the currently authenticated
user can actually listen on the channel. This is accomplished by making an
HTTP request to your Laravel application with the channel name and allowing
your application to determine if the user can listen on that channel. When
using [Laravel Echo](#client-side-installation), the HTTP request to
authorize subscriptions to private channels will be made automatically;
however, you do need to define the proper routes to respond to these
requests.

<a name="defining-authorization-routes"></a>
### Defining Authorization Routes

Thankfully, Laravel makes it easy to define the routes to respond to channel
authorization requests. In the `App\Providers\BroadcastServiceProvider`
included with your Laravel application, you will see a call to the
`Broadcast::routes` method. This method will register the
`/broadcasting/auth` route to handle authorization requests:

    Broadcast::routes();

The `Broadcast::routes` method will automatically place its routes within
the `web` middleware group; however, you may pass an array of route
attributes to the method if you would like to customize the assigned
attributes:

    Broadcast::routes($attributes);

<a name="customizing-the-authorization-endpoint"></a>
#### Customizing The Authorization Endpoint

By default, Echo will use the `/broadcasting/auth` endpoint to authorize
channel access. However, you may specify your own authorization endpoint by
passing the `authEndpoint` configuration option to your Echo instance:

    window.Echo = new Echo({
        broadcaster: 'pusher',
        // ...
        authEndpoint: '/custom/endpoint/auth'
    });

<a name="defining-authorization-callbacks"></a>
### Defining Authorization Callbacks

Next, we need to define the logic that will actually determine if the
currently authenticated user can listen to a given channel. This is done in
the `routes/channels.php` file that is included with your application. In
this file, you may use the `Broadcast::channel` method to register channel
authorization callbacks:

    Broadcast::channel('orders.{orderId}', function ($user, $orderId) {
        return $user->id === Order::findOrNew($orderId)->user_id;
    });

The `channel` method accepts two arguments: the name of the channel and a
callback which returns `true` or `false` indicating whether the user is
authorized to listen on the channel.

All authorization callbacks receive the currently authenticated user as
their first argument and any additional wildcard parameters as their
subsequent arguments. In this example, we are using the `{orderId}`
placeholder to indicate that the "ID" portion of the channel name is a
wildcard.

<a name="authorization-callback-model-binding"></a>
#### Authorization Callback Model Binding

Just like HTTP routes, channel routes may also take advantage of implicit
and explicit [route model
binding](/docs/{{version}}/routing#route-model-binding). For example,
instead of receiving a string or numeric order ID, you may request an actual
`Order` model instance:

    use App\Models\Order;

    Broadcast::channel('orders.{order}', function ($user, Order $order) {
        return $user->id === $order->user_id;
    });

> {note} Unlike HTTP route model binding, channel model binding does not support automatic [implicit model binding scoping](/docs/{{version}}/routing#implicit-model-binding-scoping). However, this is rarely a problem because most channels can be scoped based on a single model's unique, primary key.

<a name="authorization-callback-authentication"></a>
#### Authorization Callback Authentication

Private and presence broadcast channels authenticate the current user via
your application's default authentication guard. If the user is not
authenticated, channel authorization is automatically denied and the
authorization callback is never executed. However, you may assign multiple,
custom guards that should authenticate the incoming request if necessary:

    Broadcast::channel('channel', function () {
        // ...
    }, ['guards' => ['web', 'admin']]);

<a name="defining-channel-classes"></a>
### Defining Channel Classes

If your application is consuming many different channels, your
`routes/channels.php` file could become bulky. So, instead of using closures
to authorize channels, you may use channel classes. To generate a channel
class, use the `make:channel` Artisan command. This command will place a new
channel class in the `App/Broadcasting` directory.

    php artisan make:channel OrderChannel

Next, register your channel in your `routes/channels.php` file:

    use App\Broadcasting\OrderChannel;

    Broadcast::channel('orders.{order}', OrderChannel::class);

Finally, you may place the authorization logic for your channel in the
channel class' `join` method. This `join` method will house the same logic
you would have typically placed in your channel authorization closure. You
may also take advantage of channel model binding:

    <?php

    namespace App\Broadcasting;

    use App\Models\Order;
    use App\Models\User;

    class OrderChannel
    {
        /**
         * Create a new channel instance.
         *
         * @return void
         */
        public function __construct()
        {
            //
        }

        /**
         * Authenticate the user's access to the channel.
         *
         * @param  \App\Models\User  $user
         * @param  \App\Models\Order  $order
         * @return array|bool
         */
        public function join(User $user, Order $order)
        {
            return $user->id === $order->user_id;
        }
    }

> {tip} Like many other classes in Laravel, channel classes will automatically be resolved by the [service container](/docs/{{version}}/container). So, you may type-hint any dependencies required by your channel in its constructor.

<a name="broadcasting-events"></a>
## Broadcasting Events

Once you have defined an event and marked it with the `ShouldBroadcast`
interface, you only need to fire the event using the event's dispatch
method. The event dispatcher will notice that the event is marked with the
`ShouldBroadcast` interface and will queue the event for broadcasting:

    use App\Events\OrderShipmentStatusUpdated;

    OrderShipmentStatusUpdated::dispatch($order);

<a name="only-to-others"></a>
### Only To Others

When building an application that utilizes event broadcasting, you may
occasionally need to broadcast an event to all subscribers to a given
channel except for the current user. You may accomplish this using the
`broadcast` helper and the `toOthers` method:

    use App\Events\OrderShipmentStatusUpdated;

    broadcast(new OrderShipmentStatusUpdated($update))->toOthers();

To better understand when you may want to use the `toOthers` method, let's
imagine a task list application where a user may create a new task by
entering a task name. To create a task, your application might make a
request to a `/task` URL which broadcasts the task's creation and returns a
JSON representation of the new task. When your JavaScript application
receives the response from the end-point, it might directly insert the new
task into its task list like so:

    axios.post('/task', task)
        .then((response) => {
            this.tasks.push(response.data);
        });

However, remember that we also broadcast the task's creation. If your
JavaScript application is also listening for this event in order to add
tasks to the task list, you will have duplicate tasks in your list: one from
the end-point and one from the broadcast. You may solve this by using the
`toOthers` method to instruct the broadcaster to not broadcast the event to
the current user.

> {note} Your event must use the `Illuminate\Broadcasting\InteractsWithSockets` trait in order to call the `toOthers` method.

<a name="only-to-others-configuration"></a>
#### Configuration

When you initialize a Laravel Echo instance, a socket ID is assigned to the
connection. If you are using a global
[Axios](https://github.com/mzabriskie/axios) instance to make HTTP requests
from your JavaScript application, the socket ID will automatically be
attached to every outgoing request as a `X-Socket-ID` header. Then, when you
call the `toOthers` method, Laravel will extract the socket ID from the
header and instruct the broadcaster to not broadcast to any connections with
that socket ID.

If you are not using a global Axios instance, you will need to manually
configure your JavaScript application to send the `X-Socket-ID` header with
all outgoing requests. You may retrieve the socket ID using the
`Echo.socketId` method:

    var socketId = Echo.socketId();

<a name="receiving-broadcasts"></a>
## Receiving Broadcasts

<a name="listening-for-events"></a>
### Listening For Events

Once you have [installed and instantiated Laravel
Echo](#client-side-installation), you are ready to start listening for
events that are broadcast from your Laravel application. First, use the
`channel` method to retrieve an instance of a channel, then call the
`listen` method to listen for a specified event:

```js
Echo.channel(`orders.${this.order.id}`)
    .listen('OrderShipmentStatusUpdated', (e) => {
        console.log(e.order.name);
    });
```

If you would like to listen for events on a private channel, use the
`private` method instead. You may continue to chain calls to the `listen`
method to listen for multiple events on a single channel:

```js
Echo.private(`orders.${this.order.id}`)
    .listen(...)
    .listen(...)
    .listen(...);
```

<a name="leaving-a-channel"></a>
### Leaving A Channel

To leave a channel, you may call the `leaveChannel` method on your Echo
instance:

```js
Echo.leaveChannel(`orders.${this.order.id}`);
```

If you would like to leave a channel and also its associated private and
presence channels, you may call the `leave` method:

```js
Echo.leave(`orders.${this.order.id}`);
```
<a name="namespaces"></a>
### Namespaces

You may have noticed in the examples above that we did not specify the full
`App\Events` namespace for the event classes. This is because Echo will
automatically assume the events are located in the `App\Events`
namespace. However, you may configure the root namespace when you
instantiate Echo by passing a `namespace` configuration option:

```js
window.Echo = new Echo({
    broadcaster: 'pusher',
    // ...
    namespace: 'App.Other.Namespace'
});
```

Alternatively, you may prefix event classes with a `.` when subscribing to
them using Echo. This will allow you to always specify the fully-qualified
class name:

```js
Echo.channel('orders')
    .listen('.Namespace\\Event\\Class', (e) => {
        //
    });
```

<a name="presence-channels"></a>
## Presence Channels

Presence channels build on the security of private channels while exposing
the additional feature of awareness of who is subscribed to the
channel. This makes it easy to build powerful, collaborative application
features such as notifying users when another user is viewing the same page
or listing the inhabitants of a chat room.

<a name="authorizing-presence-channels"></a>
### Authorizing Presence Channels

All presence channels are also private channels; therefore, users must be
[authorized to access them](#authorizing-channels). However, when defining
authorization callbacks for presence channels, you will not return `true` if
the user is authorized to join the channel. Instead, you should return an
array of data about the user.

The data returned by the authorization callback will be made available to
the presence channel event listeners in your JavaScript application. If the
user is not authorized to join the presence channel, you should return
`false` or `null`:

    Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
        if ($user->canJoinRoom($roomId)) {
            return ['id' => $user->id, 'name' => $user->name];
        }
    });

<a name="joining-presence-channels"></a>
### Joining Presence Channels

To join a presence channel, you may use Echo's `join` method. The `join`
method will return a `PresenceChannel` implementation which, along with
exposing the `listen` method, allows you to subscribe to the `here`,
`joining`, and `leaving` events.

    Echo.join(`chat.${roomId}`)
        .here((users) => {
            //
        })
        .joining((user) => {
            console.log(user.name);
        })
        .leaving((user) => {
            console.log(user.name);
        });

The `here` callback will be executed immediately once the channel is joined
successfully, and will receive an array containing the user information for
all of the other users currently subscribed to the channel. The `joining`
method will be executed when a new user joins a channel, while the `leaving`
method will be executed when a user leaves the channel.

<a name="broadcasting-to-presence-channels"></a>
### Broadcasting To Presence Channels

Presence channels may receive events just like public or private
channels. Using the example of a chatroom, we may want to broadcast
`NewMessage` events to the room's presence channel. To do so, we'll return
an instance of `PresenceChannel` from the event's `broadcastOn` method:

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('room.'.$this->message->room_id);
    }

As with other events, you may use the `broadcast` helper and the `toOthers`
method to exclude the current user from receiving the broadcast:

    broadcast(new NewMessage($message));

    broadcast(new NewMessage($message))->toOthers();

As typical of other types of events, you may listen for events sent to
presence channels using Echo's `listen` method:

    Echo.join(`chat.${roomId}`)
        .here(...)
        .joining(...)
        .leaving(...)
        .listen('NewMessage', (e) => {
            //
        });

<a name="client-events"></a>
## Client Events

> {tip} When using [Pusher Channels](https://pusher.com/channels), you must enable the "Client Events" option in the "App Settings" section of your [application dashboard](https://dashboard.pusher.com/) in order to send client events.

Sometimes you may wish to broadcast an event to other connected clients
without hitting your Laravel application at all. This can be particularly
useful for things like "typing" notifications, where you want to alert users
of your application that another user is typing a message on a given screen.

To broadcast client events, you may use Echo's `whisper` method:

    Echo.private(`chat.${roomId}`)
        .whisper('typing', {
            name: this.user.name
        });

To listen for client events, you may use the `listenForWhisper` method:

    Echo.private(`chat.${roomId}`)
        .listenForWhisper('typing', (e) => {
            console.log(e.name);
        });

<a name="notifications"></a>
## Notifications

By pairing event broadcasting with
[notifications](/docs/{{version}}/notifications), your JavaScript
application may receive new notifications as they occur without needing to
refresh the page. Before getting started, be sure to read over the
documentation on using [the broadcast notification
channel](/docs/{{version}}/notifications#broadcast-notifications).

Once you have configured a notification to use the broadcast channel, you
may listen for the broadcast events using Echo's `notification`
method. Remember, the channel name should match the class name of the entity
receiving the notifications:

    Echo.private(`App.Models.User.${userId}`)
        .notification((notification) => {
            console.log(notification.type);
        });

In this example, all notifications sent to `App\Models\User` instances via
the `broadcast` channel would be received by the callback. A channel
authorization callback for the `App.Models.User.{id}` channel is included in
the default `BroadcastServiceProvider` that ships with the Laravel
framework.
