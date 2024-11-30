---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/69/en-zhtw'
updatedAt: '2024-06-30T08:26:00Z'
contributors: {  }
progress: 44.54
---

# 事件 - Event

- [簡介](#introduction)
- [Generating Events and Listeners](#generating-events-and-listeners)
- [Registering Events and Listeners](#registering-events-and-listeners)
  - [Event Discovery](#event-discovery)
  - [手動註冊 Event](#manually-registering-events)
  - [Closure Listeners](#closure-listeners)
  
- [定義 Event](#defining-events)
- [定義 Listener](#defining-listeners)
- [在佇列中處理的 Event Listener](#queued-event-listeners)
  - [Manually Interacting With the Queue](#manually-interacting-with-the-queue)
  - [Queued Event Listeners and Database Transactions](#queued-event-listeners-and-database-transactions)
  - [處理失敗的任務](#handling-failed-jobs)
  
- [分派 Event](#dispatching-events)
  - [在資料庫 Transactions 後分派 Event](#dispatching-events-after-database-transactions)
  
- [Event Subscriber](#event-subscribers)
  - [撰寫 Event Subscriber](#writing-event-subscribers)
  - [註冊 Event Subscriber](#registering-event-subscribers)
  
- [測試](#testing)
  - [Faking a Subset of Events](#faking-a-subset-of-events)
  - [限定範圍地模擬 Event](#scoped-event-fakes)
  

<a name="introduction"></a>

## 簡介

Laravel 的 ^[Event](%E4%BA%8B%E4%BB%B6) 提供了一種簡單的 Observer 設計模式實作，能讓你^[註冊](Subscribe)與^[監聽](Listen)程式內發生的多種事件。Event 類別一般儲存在 `app/Events` 目錄下，而 ^[Listener](%E7%9B%A3%E8%81%BD%E7%A8%8B%E5%BC%8F) 則一般儲存在 `app/Listeners` 目錄。若在專案內沒看到這些目錄的話請別擔心，在使用 Artisan 指令產生 Event 跟 Listener 的時候會自動建立。

Event 是以各種層面^[解耦](Decouple)程式的好方法，因為一個 Event 可以由多個不互相依賴的 Listener。舉例來說，我們可能會想在訂單出貨的時候傳送 Slack 通知給使用者。除了耦合訂單處理的程式碼跟 Slack 通知的程式碼外，我們可以產生一個 `App\Events\OrderShipped` 事件，然後使用一個 Listener 來接收並分派 Slack 通知。

<a name="generating-events-and-listeners"></a>

## Generating Events and Listeners

To quickly generate events and listeners, you may use the `make:event` and `make:listener` Artisan commands:

```shell
php artisan make:event PodcastProcessed

php artisan make:listener SendPodcastNotification --event=PodcastProcessed
```
For convenience, you may also invoke the `make:event` and `make:listener` Artisan commands without additional arguments. When you do so, Laravel will automatically prompt you for the class name and, when creating a listener, the event it should listen to:

```shell
php artisan make:event

php artisan make:listener
```
<a name="registering-events-and-listeners"></a>

## Registering Events and Listeners

<a name="event-discovery"></a>

### Event Discovery

By default, Laravel will automatically find and register your event listeners by scanning your application's `Listeners` directory. When Laravel finds any listener class method that begins with `handle` or `__invoke`, Laravel will register those methods as event listeners for the event that is type-hinted in the method's signature:

    use App\Events\PodcastProcessed;
    
    class SendPodcastNotification
    {
        /**
         * Handle the given event.
         */
        public function handle(PodcastProcessed $event): void
        {
            // ...
        }
    }
If you plan to store your listeners in a different directory or within multiple directories, you may instruct Laravel to scan those directories using the `withEvents` method in your application's `bootstrap/app.php` file:

    ->withEvents(discover: [
        __DIR__.'/../app/Domain/Listeners',
    ])
The `event:list` command may be used to list all of the listeners registered within your application:

```shell
php artisan event:list
```
<a name="event-discovery-in-production"></a>

#### Event Discovery in Production

To give your application a speed boost, you should cache a manifest of all of your application's listeners using the `optimize` or `event:cache` Artisan commands. Typically, this command should be run as part of your application's [deployment process](/docs/{{version}}/deployment#optimization). This manifest will be used by the framework to speed up the event registration process. The `event:clear` command may be used to destroy the event cache.

<a name="manually-registering-events"></a>

### 手動註冊 Event

Using the `Event` facade, you may manually register events and their corresponding listeners within the `boot` method of your application's `AppServiceProvider`:

    use App\Domain\Orders\Events\PodcastProcessed;
    use App\Domain\Orders\Listeners\SendPodcastNotification;
    use Illuminate\Support\Facades\Event;
    
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            PodcastProcessed::class,
            SendPodcastNotification::class,
        );
    }
The `event:list` command may be used to list all of the listeners registered within your application:

```shell
php artisan event:list
```
<a name="closure-listeners"></a>

### Closure Listeners

Typically, listeners are defined as classes; however, you may also manually register closure-based event listeners in the `boot` method of your application's `AppServiceProvider`:

    use App\Events\PodcastProcessed;
    use Illuminate\Support\Facades\Event;
    
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(function (PodcastProcessed $event) {
            // ...
        });
    }
<a name="queuable-anonymous-event-listeners"></a>

#### 可放入佇列的匿名 Event Listener

When registering closure based event listeners, you may wrap the listener closure within the `Illuminate\Events\queueable` function to instruct Laravel to execute the listener using the [queue](/docs/{{version}}/queues):

    use App\Events\PodcastProcessed;
    use function Illuminate\Events\queueable;
    use Illuminate\Support\Facades\Event;
    
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(queueable(function (PodcastProcessed $event) {
            // ...
        }));
    }
就像佇列任務一樣，可以使用 `onConnection`、`onQueue`、`delay` 等方法來自訂放入佇列之 Listener 的執行：

    Event::listen(queueable(function (PodcastProcessed $event) {
        // ...
    })->onConnection('redis')->onQueue('podcasts')->delay(now()->addSeconds(10)));
若想處理執行失敗的匿名佇列 Listener，可在定義 `queueable` Listener`時提供一個閉包給`catch`方法。這個閉包會收到 Event 實體以及一個導致 Listener 失敗的`Throwable` 實體：

    use App\Events\PodcastProcessed;
    use function Illuminate\Events\queueable;
    use Illuminate\Support\Facades\Event;
    use Throwable;
    
    Event::listen(queueable(function (PodcastProcessed $event) {
        // ...
    })->catch(function (PodcastProcessed $event, Throwable $e) {
        // The queued listener failed...
    }));
<a name="wildcard-event-listeners"></a>

#### 萬用字元 Event Listener

You may also register listeners using the `*` character as a wildcard parameter, allowing you to catch multiple events on the same listener. Wildcard listeners receive the event name as their first argument and the entire event data array as their second argument:

    Event::listen('event.*', function (string $eventName, array $data) {
        // ...
    });
<a name="defining-events"></a>

## 定義 Event

Event 類別基本上就是一個資料容器，用來保存與該 Event 有關的資訊。舉例來說，假設有個會接收 [Eloquent ORM](/docs/{{version}}/eloquent) 物件的 `App\Events\OrderShipped` Event：

    <?php
    
    namespace App\Events;
    
    use App\Models\Order;
    use Illuminate\Broadcasting\InteractsWithSockets;
    use Illuminate\Foundation\Events\Dispatchable;
    use Illuminate\Queue\SerializesModels;
    
    class OrderShipped
    {
        use Dispatchable, InteractsWithSockets, SerializesModels;
    
        /**
         * Create a new event instance.
         */
        public function __construct(
            public Order $order,
        ) {}
    }
就像這樣，這個 Event 類別中並不包含邏輯。這個類別只是已付款訂單 `App\Models\Order` 實體的容器而已。若要使用 PHP 的 `serialize` 方法序列化這個 Event 物件時 (如：[佇列 Listener] 會序列化 Event)，這個 Event 使用的 `SerializesModels` Trait 會妥善序列化所有的 Eloquent Model。

<a name="defining-listeners"></a>

## 定義 Listener

Next, let's take a look at the listener for our example event. Event listeners receive event instances in their `handle` method. The `make:listener` Artisan command, when invoked with the `--event` option, will automatically import the proper event class and type-hint the event in the `handle` method. Within the `handle` method, you may perform any actions necessary to respond to the event:

    <?php
    
    namespace App\Listeners;
    
    use App\Events\OrderShipped;
    
    class SendShipmentNotification
    {
        /**
         * Create the event listener.
         */
        public function __construct()
        {
            // ...
        }
    
        /**
         * Handle the event.
         */
        public function handle(OrderShipped $event): void
        {
            // Access the order using $event->order...
        }
    }
> [!NOTE]  
> 也可以在 Event Listener 的 ^[Constructor](%E5%BB%BA%E6%A7%8B%E5%87%BD%E5%BC%8F) 中型別提示任何的相依性。所有的 Event Listener 都會使用 Laravel [Service Provider](/docs/{{version}}/container) 解析，所以這些相依性也會自動被插入。

<a name="stopping-the-propagation-of-an-event"></a>

#### 停止 Event 的^[傳播](Propagation)

有時候，我們可能會想停止將某個 Event ^[傳播](Propagation)到另一個 Listener 上。若要停止傳播，只要在 Listener 的 `handle` 方法上回傳 `false` 即可。

<a name="queued-event-listeners"></a>

## 放入佇列的 Event Listener

若你的 Listener 要處理一些很慢的任務 (如寄送 E-Mail 或產生 HTTP Request)，則 Listener 放入佇列可獲得許多好處。在使用佇列 Listener 前，請先確定已[設定佇列](/docs/{{version}}/queues)，並在伺服器或本機開發環境上開啟一個 ^[Queue Worker](%E4%BD%87%E5%88%97%E8%83%8C%E6%99%AF%E5%B7%A5%E4%BD%9C%E7%A8%8B%E5%BC%8F)。

To specify that a listener should be queued, add the `ShouldQueue` interface to the listener class. Listeners generated by the `make:listener` Artisan commands already have this interface imported into the current namespace so you can use it immediately:

    <?php
    
    namespace App\Listeners;
    
    use App\Events\OrderShipped;
    use Illuminate\Contracts\Queue\ShouldQueue;
    
    class SendShipmentNotification implements ShouldQueue
    {
        // ...
    }
就這樣！之後，當這個 Listener 要處理的 Event 被^[分派](Dispatch)後，Event ^[Dispatcher](%E5%88%86%E6%B4%BE%E7%A8%8B%E5%BC%8F) 就會自動使用 Laravel 的[佇列系統](/docs/{{version}}/queues)來將這個 Listener 放入佇列。若佇列在執行該 Listener 時沒有^[擲回](Throw)任何 Exception，則該佇列任務會在執行完畢後自動刪除。

<a name="customizing-the-queue-connection-queue-name"></a>

#### 自定佇列連線、名稱、與延遲

若想自訂 Event Listener 的佇列連線、佇列名稱、或是佇列^[延遲時間](Delay Time)，可在 Listener 類別上定義 `$connection`、`$queue`、`$delay` 等屬性：

    <?php
    
    namespace App\Listeners;
    
    use App\Events\OrderShipped;
    use Illuminate\Contracts\Queue\ShouldQueue;
    
    class SendShipmentNotification implements ShouldQueue
    {
        /**
         * The name of the connection the job should be sent to.
         *
         * @var string|null
         */
        public $connection = 'sqs';
    
        /**
         * The name of the queue the job should be sent to.
         *
         * @var string|null
         */
        public $queue = 'listeners';
    
        /**
         * The time (seconds) before the job should be processed.
         *
         * @var int
         */
        public $delay = 60;
    }
若想在執行階段定義 Listener 的佇列連線、佇列名稱、或是延遲，可以在 Listener 上定義 `viaConnection`、`viaQueue`、或 `withDelay` 方法：

    /**
     * Get the name of the listener's queue connection.
     */
    public function viaConnection(): string
    {
        return 'sqs';
    }
    
    /**
     * Get the name of the listener's queue.
     */
    public function viaQueue(): string
    {
        return 'listeners';
    }
    
    /**
     * Get the number of seconds before the job should be processed.
     */
    public function withDelay(OrderShipped $event): int
    {
        return $event->highPriority ? 0 : 60;
    }
<a name="conditionally-queueing-listeners"></a>

#### 有條件地將 Listener 放入佇列

有時候，我們可能需要依據一些只有在執行階段才能取得的資料來判斷是否要將 Listener 放入佇列。若要在執行階段判斷是否將 Listner 放入佇列，可在 Listner 中新增一個 `shouldQueue` 方法來判斷。若 `shouldQueue` 方法回傳 `false`，則該 Listener 不會被執行：

    <?php
    
    namespace App\Listeners;
    
    use App\Events\OrderCreated;
    use Illuminate\Contracts\Queue\ShouldQueue;
    
    class RewardGiftCard implements ShouldQueue
    {
        /**
         * Reward a gift card to the customer.
         */
        public function handle(OrderCreated $event): void
        {
            // ...
        }
    
        /**
         * Determine whether the listener should be queued.
         */
        public function shouldQueue(OrderCreated $event): bool
        {
            return $event->order->subtotal >= 5000;
        }
    }
<a name="manually-interacting-with-the-queue"></a>

### Manually Interacting With the Queue

若有需要手動存取某個 Listener 底層佇列任務的 `delete` 與 `release` 方法，可使用 `Illuminate\Queue\InteractsWithQueue` Trait。在產生的 Listener 上已預設匯入了這個 Trait。有了 `InteractsWithQueue` 就可以存取這些方法：

    <?php
    
    namespace App\Listeners;
    
    use App\Events\OrderShipped;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Queue\InteractsWithQueue;
    
    class SendShipmentNotification implements ShouldQueue
    {
        use InteractsWithQueue;
    
        /**
         * Handle the event.
         */
        public function handle(OrderShipped $event): void
        {
            if (true) {
                $this->release(30);
            }
        }
    }
<a name="queued-event-listeners-and-database-transactions"></a>

### Queued Event Listeners and Database Transactions

當 Event Listener 是在資料庫 Transaction 內^[分派](Dispatch)的時候，這個 Listner 可能會在資料庫 Transaction 被 Commit 前就被佇列進行處理了。發生這種情況時，在資料庫 Transaction 期間對 Model 或資料庫記錄所做出的更新可能都還未反應到資料庫內。另外，所有在 Transaction 期間新增的 Model 或資料庫記錄也可能還未出現在資料庫內。若 Listner 有依賴這些 Model 的話，在處理分派該佇列 Listener 的任務時可能會出現未預期的錯誤。

即使佇列連線的 `after_commit` 設定選項被設為 `false`，還是可以通過在 Listener 類別上實作 `ShouldHandleEventsAfterCommit` 介面來讓 Laravel 知道要在所有開啟的資料庫 Transaction 被 Commit 後分派被放入佇列的 Listener：

    <?php
    
    namespace App\Listeners;
    
    use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Queue\InteractsWithQueue;
    
    class SendShipmentNotification implements ShouldQueue, ShouldHandleEventsAfterCommit
    {
        use InteractsWithQueue;
    }
> [!NOTE]  
> 要瞭解更多有關這類問題的解決方法，請參考有關[佇列任務與資料庫 Transaction](/docs/{{version}}/queues#jobs-and-database-transactions) 有關的說明文件。

<a name="handling-failed-jobs"></a>

### 處理失敗的任務

有時候，放入佇列的 Listener 可能會執行失敗。若該佇列的 Listener 達到最大 Queue Worker 所定義的最大嘗試次數，就會呼叫 Listener 上的 `failed` 方法。`failed` 方法會接收一個 Event 實體，以及導致失敗的 `Throwable`：

    <?php
    
    namespace App\Listeners;
    
    use App\Events\OrderShipped;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Queue\InteractsWithQueue;
    use Throwable;
    
    class SendShipmentNotification implements ShouldQueue
    {
        use InteractsWithQueue;
    
        /**
         * Handle the event.
         */
        public function handle(OrderShipped $event): void
        {
            // ...
        }
    
        /**
         * Handle a job failure.
         */
        public function failed(OrderShipped $event, Throwable $exception): void
        {
            // ...
        }
    }
<a name="specifying-queued-listener-maximum-attempts"></a>

#### 指定佇列 Listener 的最大嘗試次數

若有某個佇列 Listener 遇到錯誤，我們通常不會想讓這個 Listener 一直重試。因此，Laravel 提供了多種定義 Listener 重試次數的方法。

可以在 Listener 類別中定義 `$tries` 屬性來指定要嘗試多少次後才將其視為執行失敗：

    <?php
    
    namespace App\Listeners;
    
    use App\Events\OrderShipped;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Queue\InteractsWithQueue;
    
    class SendShipmentNotification implements ShouldQueue
    {
        use InteractsWithQueue;
    
        /**
         * The number of times the queued listener may be attempted.
         *
         * @var int
         */
        public $tries = 5;
    }
除了定義 Listener 重試多少次要視為失敗以外，也可以限制 Listener 嘗試執行的時間長度。這樣一來，在指定的時間範圍內，Listener 就可以不斷重試。若要定義最長可重試時間，請在 Listener 類別中定義一個 `retryUntil` 方法。該方法應回傳 `DateTime` 實體：

    use DateTime;
    
    /**
     * Determine the time at which the listener should timeout.
     */
    public function retryUntil(): DateTime
    {
        return now()->addMinutes(5);
    }
<a name="dispatching-events"></a>

## 分派 Event

若要分派 Event，可呼叫該 Event 上的靜態 `dispatch` 方法。這個方法由 `Illuminate\Foundation\Events\Dispatchable` Trait 提供。任何傳入 `dispatch` 方法的引數會被傳給 Event 的 Constructor：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Events\OrderShipped;
    use App\Http\Controllers\Controller;
    use App\Models\Order;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Http\Request;
    
    class OrderShipmentController extends Controller
    {
        /**
         * Ship the given order.
         */
        public function store(Request $request): RedirectResponse
        {
            $order = Order::findOrFail($request->order_id);
    
            // Order shipment logic...
    
            OrderShipped::dispatch($order);
    
            return redirect('/orders');
        }
    }
若想要有條件地分派 Event，可使用 `dispatchIf` 與` `dispatchUnless` 方法：

    OrderShipped::dispatchIf($condition, $order);
    
    OrderShipped::dispatchUnless($condition, $order);
> [!NOTE]  
> 在測試時，若能在不實際觸發 Listener 的情況下判斷是否有分派特定 Event 會很實用。Laravel 的[內建測試輔助函式](#event-fake)就能讓我們在不實際觸發 Listener 的情況下分派 Event。

<a name="dispatching-events-after-database-transactions"></a>

### 在資料庫 Transaction 後分派 Event

有時候，你可能會想讓 Laravel 只在有效資料庫 Transaction 被 Commit 後才分派 Event。這時候，可以在 Event 類別上實作 `ShouldDispatchAfterCommit` 介面。

該介面會使 Laravel 等到資料庫 Transaction 被 Commit 後才分派 Event。若 Transaction 執行失敗，該 Event 則會被取消。若在分派 Event 時沒有正在進行的 Transaction，則該 Event 會立刻被分派：

    <?php
    
    namespace App\Events;
    
    use App\Models\Order;
    use Illuminate\Broadcasting\InteractsWithSockets;
    use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
    use Illuminate\Foundation\Events\Dispatchable;
    use Illuminate\Queue\SerializesModels;
    
    class OrderShipped implements ShouldDispatchAfterCommit
    {
        use Dispatchable, InteractsWithSockets, SerializesModels;
    
        /**
         * Create a new event instance.
         */
        public function __construct(
            public Order $order,
        ) {}
    }
<a name="event-subscribers"></a>

## Event Subscriber

<a name="writing-event-subscribers"></a>

### 撰寫 Event Subscriber

Event Subscriber 是一種類別，在 Subscriber 類別內可以^[訂閱](Subscribe)多個 Event，讓我們能在單一類別中定義多個 Event 的^[處理程式](Handler)。Subscriber 應定義 `subscribe` 方法，會傳入一個 Event Dispatcher 實體給該方法。我們可以在給定的 Dispatcher 上呼叫 `listen` 方法來註冊 Event Listener：

    <?php
    
    namespace App\Listeners;
    
    use Illuminate\Auth\Events\Login;
    use Illuminate\Auth\Events\Logout;
    use Illuminate\Events\Dispatcher;
    
    class UserEventSubscriber
    {
        /**
         * Handle user login events.
         */
        public function handleUserLogin(Login $event): void {}
    
        /**
         * Handle user logout events.
         */
        public function handleUserLogout(Logout $event): void {}
    
        /**
         * Register the listeners for the subscriber.
         */
        public function subscribe(Dispatcher $events): void
        {
            $events->listen(
                Login::class,
                [UserEventSubscriber::class, 'handleUserLogin']
            );
    
            $events->listen(
                Logout::class,
                [UserEventSubscriber::class, 'handleUserLogout']
            );
        }
    }
在 Subscriber 內可以定義 Event Listener 方法，但比起這麼做，在 Subscriber 的 `subscribe` 方法內回傳一組包含 Event 與方法名稱的陣列應該會更方便。在註冊 Event Listener 時，Laravel 會自動判斷該 Subscriber 的類別名稱：

    <?php
    
    namespace App\Listeners;
    
    use Illuminate\Auth\Events\Login;
    use Illuminate\Auth\Events\Logout;
    use Illuminate\Events\Dispatcher;
    
    class UserEventSubscriber
    {
        /**
         * Handle user login events.
         */
        public function handleUserLogin(Login $event): void {}
    
        /**
         * Handle user logout events.
         */
        public function handleUserLogout(Logout $event): void {}
    
        /**
         * Register the listeners for the subscriber.
         *
         * @return array<string, string>
         */
        public function subscribe(Dispatcher $events): array
        {
            return [
                Login::class => 'handleUserLogin',
                Logout::class => 'handleUserLogout',
            ];
        }
    }
<a name="registering-event-subscribers"></a>

### 註冊 Event Subscriber

After writing the subscriber, you are ready to register it with the event dispatcher. You may register subscribers using the `subscribe` method of the `Event` facade. Typically, this should be done within the `boot` method of your application's `AppServiceProvider`:

    <?php
    
    namespace App\Providers;
    
    use App\Listeners\UserEventSubscriber;
    use Illuminate\Support\Facades\Event;
    use Illuminate\Support\ServiceProvider;
    
    class AppServiceProvider extends ServiceProvider
    {
        /**
         * Bootstrap any application services.
         */
        public function boot(): void
        {
            Event::subscribe(UserEventSubscriber::class);
        }
    }
<a name="testing"></a>

## 測試

在測試會分派 Event 的程式時，可以讓 Laravel 不要真的執行該 Event 的 Listener。我們可以直接測試 Event 的 Listener，將 Listener 的測試與分派該 Event 的程式碼測試分開。當然，若要測試 Listener，需要在測試中先初始化一個 Listener 實體，並直接呼叫 `handle` 方法。

使用 `Event` Facade 的 `fake` 方法，就可避免執行真正的 Listener，在測試中執行程式碼，然後使用 `assertDispatched`、`assertNotDispatched`、`assertNothingDispatched` 等方法來判斷程式分派了哪些 Event：

```php
<?php

use App\Events\OrderFailedToShip;
use App\Events\OrderShipped;
use Illuminate\Support\Facades\Event;

test('orders can be shipped', function () {
    Event::fake();

    // Perform order shipping...

    // Assert that an event was dispatched...
    Event::assertDispatched(OrderShipped::class);

    // Assert an event was dispatched twice...
    Event::assertDispatched(OrderShipped::class, 2);

    // Assert an event was not dispatched...
    Event::assertNotDispatched(OrderFailedToShip::class);

    // Assert that no events were dispatched...
    Event::assertNothingDispatched();
});
```
```php
<?php

namespace Tests\Feature;

use App\Events\OrderFailedToShip;
use App\Events\OrderShipped;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test order shipping.
     */
    public function test_orders_can_be_shipped(): void
    {
        Event::fake();

        // Perform order shipping...

        // Assert that an event was dispatched...
        Event::assertDispatched(OrderShipped::class);

        // Assert an event was dispatched twice...
        Event::assertDispatched(OrderShipped::class, 2);

        // Assert an event was not dispatched...
        Event::assertNotDispatched(OrderFailedToShip::class);

        // Assert that no events were dispatched...
        Event::assertNothingDispatched();
    }
}
```
可以傳入一個閉包給 `assertDispatched` 或 `assertNotDispatched` 方法，來判斷某個 Event 是否通過給定的「真值測試 (Truth Test)」。若分派的 Event 中至少有一個 Event 通過給定的真值測試，則該 Assertion 會被視為成功：

    Event::assertDispatched(function (OrderShipped $event) use ($order) {
        return $event->order->id === $order->id;
    });
若只想判斷某個 Event Listener 是否有在監聽給定的 Event，可使用 `assertListening` 方法：

    Event::assertListening(
        OrderShipped::class,
        SendShipmentNotification::class
    );
> [!WARNING]  
> 呼叫 `Event::fake()` 後，就不會執行 Event Listener。因此，若有測試使用的 Model Factory 仰賴於 Event，如在 Model 的 `creating` Event 上建立 UUID 等，請在使用完 Factory **之後** 再呼叫 `Event::fake()`。

<a name="faking-a-subset-of-events"></a>

### Faking a Subset of Events

若只想為一部分 Event 來 Fake Event Listener，則可將這些 Event 傳入`fake` 或 `fakeFor` 方法：

```php
test('orders can be processed', function () {
    Event::fake([
        OrderCreated::class,
    ]);

    $order = Order::factory()->create();

    Event::assertDispatched(OrderCreated::class);

    // Other events are dispatched as normal...
    $order->update([...]);
});
```
```php
/**
 * Test order process.
 */
public function test_orders_can_be_processed(): void
{
    Event::fake([
        OrderCreated::class,
    ]);

    $order = Order::factory()->create();

    Event::assertDispatched(OrderCreated::class);

    // Other events are dispatched as normal...
    $order->update([...]);
}
```
也可以使用 `except` 方法來 Fake 除了一組特定 Event 外的所有 Event：

    Event::fake()->except([
        OrderCreated::class,
    ]);
<a name="scoped-event-fakes"></a>

### 限定範圍地模擬 Event

若只想未一部分的測試 Fake Event Listener，則可使用 `fakeFor` 方法：

```php
<?php

use App\Events\OrderCreated;
use App\Models\Order;
use Illuminate\Support\Facades\Event;

test('orders can be processed', function () {
    $order = Event::fakeFor(function () {
        $order = Order::factory()->create();

        Event::assertDispatched(OrderCreated::class);

        return $order;
    });

    // Events are dispatched as normal and observers will run ...
    $order->update([...]);
});
```
```php
<?php

namespace Tests\Feature;

use App\Events\OrderCreated;
use App\Models\Order;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test order process.
     */
    public function test_orders_can_be_processed(): void
    {
        $order = Event::fakeFor(function () {
            $order = Order::factory()->create();

            Event::assertDispatched(OrderCreated::class);

            return $order;
        });

        // Events are dispatched as normal and observers will run ...
        $order->update([...]);
    }
}
```