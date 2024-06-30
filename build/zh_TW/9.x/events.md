---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/69/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:26:00Z'
---

# 事件 - Event

- [簡介](#introduction)
- [註冊 Event 與 Listener](#registering-events-and-listeners)
   - [產生 Event 與 Listener](#generating-events-and-listeners)
   - [手動註冊 Event](#manually-registering-events)
   - [Event Discovery](#event-discovery)
- [定義 Event](#defining-events)
- [定義 Listener](#defining-listeners)
- [在佇列中處理的 Event Listener](#queued-event-listeners)
   - [手動使用佇列](#manually-interacting-with-the-queue)
   - [在佇列中執行的 Event Listener 與資料庫 Transaction](#queued-event-listeners-and-database-transactions)
   - [處理失敗的任務](#handling-failed-jobs)
- [分派 Event](#dispatching-events)
- [Event Subscriber](#event-subscribers)
   - [撰寫 Event Subscriber](#writing-event-subscribers)
   - [註冊 Event Subscriber](#registering-event-subscribers)

<a name="introduction"></a>

## 簡介

Laravel 的 ^[Event](事件) 提供了一種簡單的 Observer 設計模式實作，能讓你^[註冊](Subscribe)與^[監聽](Listen)程式內發生的多種事件。Event 類別一般儲存在 `app/Events` 目錄下，而 ^[Listener](監聽程式) 則一般儲存在 `app/Listeners` 目錄。若在專案內沒看到這些目錄的話請別擔心，在使用 Artisan 指令產生 Event 跟 Listener 的時候會自動建立。

Event 是以各種層面^[解耦](Decouple)程式的好方法，因為一個 Event 可以由多個不互相依賴的 Listener。舉例來說，我們可能會想在訂單出貨的時候傳送 Slack 通知給使用者。除了耦合訂單處理的程式碼跟 Slack 通知的程式碼外，我們可以產生一個 `App\Events\OrderShipped` 事件，然後使用一個 Listener 來接收並分派 Slack 通知。

<a name="registering-events-and-listeners"></a>

## 註冊 Event 與 Listener

在你的 Laravel 專案中有個 `App\Providers\EventServiceProvider`，這個 Service Provider 是可以註冊所有 Event Listener 的好所在。`listen` 屬性是一個陣列，其中包含了所有的 Event (索引鍵) 即其 Listener (陣列值)。可以按照專案需求隨意增加 Event 到這個陣列。舉例來說，我們來新增一個 `OrderShipped` Event：

    use App\Events\OrderShipped;
    use App\Listeners\SendShipmentNotification;
    
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        OrderShipped::class => [
            SendShipmentNotification::class,
        ],
    ];

> **Note** 可以使用 `event:list` 指令來顯示程式中註冊的所有 Event 與 Listener 列表。

<a name="generating-events-and-listeners"></a>

### 產生 Event 與 Listener

當然，手動為每個 Event 跟 Listener 建立檔案有點麻煩。我們不需要手動建立，只需要在 `EventServiceProvider` 中加上 Listener 與 Event，然後使用 `event:generate` Artisan 指令即可。這個指令會產生所有列在 `EventServiceProvider` 中不存在的 Event 與 Listener：

```shell
php artisan event:generate
```

或者，也可以使用 `make:event` 與 `make:listener` Artisan 指令來產生個別的 Event 與 Listener：

```shell
php artisan make:event PodcastProcessed

php artisan make:listener SendPodcastNotification --event=PodcastProcessed
```

<a name="manually-registering-events"></a>

### 手動註冊 Event

一般來說，Event 應在 `EventServiceProvider` 的 `$listen` 陣列中註冊。不過，也可以在 `EventServiceProvider` 的 `boot` 方法中手動註冊基於類別或閉包的 Listener：

    use App\Events\PodcastProcessed;
    use App\Listeners\SendPodcastNotification;
    use Illuminate\Support\Facades\Event;
    
    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen(
            PodcastProcessed::class,
            [SendPodcastNotification::class, 'handle']
        );
    
        Event::listen(function (PodcastProcessed $event) {
            //
        });
    }

<a name="queuable-anonymous-event-listeners"></a>

#### 可放入佇列的匿名 Event Listener

在註冊基於閉包的 Event Listener 時，可以將該 Listener 閉包以 `Illuminate\Events\queueable` 函式^[包裝](Wrap)起來，以指示 Laravel 使用 [Queue](/docs/{{version}}/queues) 來執行這個 Listener：

    use App\Events\PodcastProcessed;
    use function Illuminate\Events\queueable;
    use Illuminate\Support\Facades\Event;
    
    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen(queueable(function (PodcastProcessed $event) {
            //
        }));
    }

就像佇列任務一樣，可以使用 `onConnection`、`onQueue`、`delay` 等方法來自訂放入佇列之 Listener 的執行：

    Event::listen(queueable(function (PodcastProcessed $event) {
        //
    })->onConnection('redis')->onQueue('podcasts')->delay(now()->addSeconds(10)));

若想處理執行失敗的匿名佇列 Listener，可在定義 `queueable` Listener`時提供一個閉包給`catch`方法。這個閉包會收到 Event 實體以及一個導致 Listener 失敗的`Throwable` 實體：

    use App\Events\PodcastProcessed;
    use function Illuminate\Events\queueable;
    use Illuminate\Support\Facades\Event;
    use Throwable;
    
    Event::listen(queueable(function (PodcastProcessed $event) {
        //
    })->catch(function (PodcastProcessed $event, Throwable $e) {
        // 在佇列中執行的 Listener 執行失敗了...
    }));

<a name="wildcard-event-listeners"></a>

#### 萬用字元 Event Listener

可以使用 `*` 作為^[萬用字元](Wildcard)參數來註冊 Listener，這樣我們就可以在同一個 Listener 上處理多個 Event。萬用字元 Listener 會🉑️事件名稱作為其第一個引數，而整個 Event 資料陣列則為其第二個引數：

    Event::listen('event.*', function ($eventName, array $data) {
        //
    });

<a name="event-discovery"></a>

### Event Discovery

除了在 `EventServiceProvider` 的 `$listen` 陣列中手動指定 Listener 以外，還可以啟用 ^[Event Discovery](Event 發現)。當啟用 Event Discovery 時，Laravel 會搜尋專案的 `Listeners` 目錄來自動找到並註冊你的 Event 與 Listener。此外，列在 `EventServiceProvider` 中顯式定義的 Event 還是會被註冊。

Laravel 會使用 PHP 的 Reflection 服務來搜尋 Listener 類別以尋找 Event Listener。當 Laravel 找到名稱以 `handle` 或 `__invoke` 開頭的 Listener 類別方法時，Laravel 會從該方法^[簽章](Signature)上的^[型別提示](Type-Hint)中取得 Event，並將該方法註冊為該 Event 的 Listener：

    use App\Events\PodcastProcessed;
    
    class SendPodcastNotification
    {
        /**
         * Handle the given event.
         *
         * @param  \App\Events\PodcastProcessed  $event
         * @return void
         */
        public function handle(PodcastProcessed $event)
        {
            //
        }
    }

Event Discovery 預設是關閉的，但可以在 `EventServiceProvider` 上複寫 `shouldDiscoverEvents` 方法來啟用：

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return true;
    }

預設情況下，會掃描專案 `app/Listeners` 目錄下的所有 Listener。若想定義其他要掃描的目錄，可在 `EventServiceProvider` 上複寫 `discoverEventsWithin` 方法：

    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array
     */
    protected function discoverEventsWithin()
    {
        return [
            $this->app->path('Listeners'),
        ];
    }

<a name="event-discovery-in-production"></a>

#### 在正式環境下使用 Event Discovery

在^[正式環境](Production)中，讓 Laravel 在每個 Request 上都掃描所有 Listener 很沒效率。因此，在部署過程，請記得執行 `event:cache` Artisan 指令來為專案的所有 Event 與 Listener 建立一個^[快取資訊清單](Cache Manifest)。Laravel 會使用這個資訊清單來加快 Event 的註冊流程。可使用 `event:clear` 來清除該快取。

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
         * The order instance.
         *
         * @var \App\Models\Order
         */
        public $order;
    
        /**
         * Create a new event instance.
         *
         * @param  \App\Models\Order  $order
         * @return void
         */
        public function __construct(Order $order)
        {
            $this->order = $order;
        }
    }

就像這樣，這個 Event 類別中並不包含邏輯。這個類別只是已付款訂單 `App\Models\Order` 實體的容器而已。若要使用 PHP 的 `serialize` 方法序列化這個 Event 物件時 (如：[佇列 Listener] 會序列化 Event)，這個 Event 使用的 `SerializesModels` Trait 會妥善序列化所有的 Eloquent Model。

<a name="defining-listeners"></a>

## 定義 Listener

接著，來看看要給我們的範例 Event 使用的 Listener。Event Listener 會在 `handle` 方法中接收 Event 實體。`event:generate` 與 `make:listener` Artisan 指令會自動載入適當的 Event 類別，並在 `handle` 方法上型別提示這個 Event。在 `handle` 方法中，我們就可以針對該 Event 回應適當的動作：

    <?php
    
    namespace App\Listeners;
    
    use App\Events\OrderShipped;
    
    class SendShipmentNotification
    {
        /**
         * Create the event listener.
         *
         * @return void
         */
        public function __construct()
        {
            //
        }
    
        /**
         * Handle the event.
         *
         * @param  \App\Events\OrderShipped  $event
         * @return void
         */
        public function handle(OrderShipped $event)
        {
            // Access the order using $event->order...
        }
    }

> **Note** 也可以在 Event Listener 的 ^[Constructor](建構函式) 中型別提示任何的相依性。所有的 Event Listener 都會使用 Laravel [Service Provider](/docs/{{version}}/container) 解析，所以這些相依性也會自動被插入。

<a name="stopping-the-propagation-of-an-event"></a>

#### 停止 Event 的^[傳播](Propagation)

有時候，我們可能會想停止將某個 Event ^[傳播](Propagation)到另一個 Listener 上。若要停止傳播，只要在 Listener 的 `handle` 方法上回傳 `false` 即可。

<a name="queued-event-listeners"></a>

## 放入佇列的 Event Listener

若你的 Listener 要處理一些很慢的任務 (如寄送 E-Mail 或產生 HTTP Request)，則 Listener 放入佇列可獲得許多好處。在使用佇列 Listener 前，請先確定已[設定佇列](/docs/{{version}}/queues)，並在伺服器或本機開發環境上開啟一個 ^[Queue Worker](佇列背景工作程式)。

要將 Listener 指定為放在佇列裡執行，請在該 Listener 類別上加上 `ShouldQueue` 介面。由 `event:generate` 與 `make:listener` Artisan 指令產生的 Listener 都已先將這個介面匯入到目前的 ^[Namespace](命名空間) 下了，因此我們可以直接使用該介面：

    <?php
    
    namespace App\Listeners;
    
    use App\Events\OrderShipped;
    use Illuminate\Contracts\Queue\ShouldQueue;
    
    class SendShipmentNotification implements ShouldQueue
    {
        //
    }

就這樣！之後，當這個 Listener 要處理的 Event 被^[分派](Dispatch)後，Event ^[Dispatcher](分派程式) 就會自動使用 Laravel 的[佇列系統](/docs/{{version}}/queues)來將這個 Listener 放入佇列。若佇列在執行該 Listener 時沒有^[擲回](Throw)任何 Exception，則該佇列任務會在執行完畢後自動刪除。

<a name="customizing-the-queue-connection-queue-name"></a>

#### 自訂佇列連線與佇列名稱

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

若想在執行階段定義 Listener 的佇列連線或佇列名稱，可以在 Listener 上定義 `viaConnection` 或 `viaQueue` 方法：

    /**
     * Get the name of the listener's queue connection.
     *
     * @return string
     */
    public function viaConnection()
    {
        return 'sqs';
    }
    
    /**
     * Get the name of the listener's queue.
     *
     * @return string
     */
    public function viaQueue()
    {
        return 'listeners';
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
         *
         * @param  \App\Events\OrderCreated  $event
         * @return void
         */
        public function handle(OrderCreated $event)
        {
            //
        }
    
        /**
         * Determine whether the listener should be queued.
         *
         * @param  \App\Events\OrderCreated  $event
         * @return bool
         */
        public function shouldQueue(OrderCreated $event)
        {
            return $event->order->subtotal >= 5000;
        }
    }

<a name="manually-interacting-with-the-queue"></a>

### 手動使用佇列

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
         *
         * @param  \App\Events\OrderShipped  $event
         * @return void
         */
        public function handle(OrderShipped $event)
        {
            if (true) {
                $this->release(30);
            }
        }
    }

<a name="queued-event-listeners-and-database-transactions"></a>

### 佇列的 Event Listener 與資料庫 Transaction

當 Event Listener 是在資料庫 Transaction 內^[分派](Dispatch)的時候，這個 Listner 可能會在資料庫 Transaction 被 Commit 前就被佇列進行處理了。發生這種情況時，在資料庫 Transaction 期間對 Model 或資料庫記錄所做出的更新可能都還未反應到資料庫內。另外，所有在 Transaction 期間新增的 Model 或資料庫記錄也可能還未出現在資料庫內。若 Listner 有依賴這些 Model 的話，在處理分派該佇列 Listener 的任務時可能會出現未預期的錯誤。

若佇列連線的 `after_commit` 設定選項是 `false`，則我們還是可以通過在 Listner 類別上定義 `$afterCommit` 屬性來標示出這個佇列 Listener 應在資料庫 Transaction 被 Commit 後才可進行分派：

    <?php
    
    namespace App\Listeners;
    
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Queue\InteractsWithQueue;
    
    class SendShipmentNotification implements ShouldQueue
    {
        use InteractsWithQueue;
    
        public $afterCommit = true;
    }

> **Note** 要瞭解更多有關這類問題的解決方法，請參考有關[佇列任務與資料庫 Transaction](/docs/{{version}}/queues#jobs-and-database-transactions) 有關的說明文件。

<a name="handling-failed-jobs"></a>

### 處理失敗的任務

有時候，放入佇列的 Listener 可能會執行失敗。若該佇列的 Listener 達到最大 Queue Worker 所定義的最大嘗試次數，就會呼叫 Listener 上的 `failed` 方法。`failed` 方法會接收一個 Event 實體，以及導致失敗的 `Throwable`：

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
         *
         * @param  \App\Events\OrderShipped  $event
         * @return void
         */
        public function handle(OrderShipped $event)
        {
            //
        }
    
        /**
         * Handle a job failure.
         *
         * @param  \App\Events\OrderShipped  $event
         * @param  \Throwable  $exception
         * @return void
         */
        public function failed(OrderShipped $event, $exception)
        {
            //
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

    /**
     * Determine the time at which the listener should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
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
    use Illuminate\Http\Request;
    
    class OrderShipmentController extends Controller
    {
        /**
         * Ship the given order.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\Response
         */
        public function store(Request $request)
        {
            $order = Order::findOrFail($request->order_id);
    
            // Order shipment logic...
    
            OrderShipped::dispatch($order);
        }
    }

若想要有條件地分派 Event，可使用 `dispatchIf` 與` `dispatchUnless` 方法：

    OrderShipped::dispatchIf($condition, $order);
    
    OrderShipped::dispatchUnless($condition, $order);

> **Note** 在測試時，若能在不實際觸發 Listener 的情況下判斷是否有分派特定 Event 會很實用。Laravel 的[內建測試輔助函式](/docs/{{version}}/mocking#event-fake)就能讓我們在不實際觸發 Listener 的情況下分派 Event。

<a name="event-subscribers"></a>

## Event Subscriber

<a name="writing-event-subscribers"></a>

### 撰寫 Event Subscriber

Event Subscriber 是一種類別，在 Subscriber 類別內可以^[訂閱](Subscribe)多個 Event，讓我們能在單一類別中定義多個 Event 的^[處理程式](Handler)。Subscriber 應定義 `subscribe` 方法，會傳入一個 Event Dispatcher 實體給該方法。我們可以在給定的 Dispatcher 上呼叫 `listen` 方法來註冊 Event Listener：

    <?php
    
    namespace App\Listeners;
    
    use Illuminate\Auth\Events\Login;
    use Illuminate\Auth\Events\Logout;
    
    class UserEventSubscriber
    {
        /**
         * Handle user login events.
         */
        public function handleUserLogin($event) {}
    
        /**
         * Handle user logout events.
         */
        public function handleUserLogout($event) {}
    
        /**
         * Register the listeners for the subscriber.
         *
         * @param  \Illuminate\Events\Dispatcher  $events
         * @return void
         */
        public function subscribe($events)
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
    
    class UserEventSubscriber
    {
        /**
         * Handle user login events.
         */
        public function handleUserLogin($event) {}
    
        /**
         * Handle user logout events.
         */
        public function handleUserLogout($event) {}
    
        /**
         * Register the listeners for the subscriber.
         *
         * @param  \Illuminate\Events\Dispatcher  $events
         * @return array
         */
        public function subscribe($events)
        {
            return [
                Login::class => 'handleUserLogin',
                Logout::class => 'handleUserLogout',
            ];
        }
    }

<a name="registering-event-subscribers"></a>

### 註冊 Event Subscriber

寫好 Subscriber 後，就可以將 Subscriber 註冊到 Dispatcher 上了。可以使用 `EventServiceProvider` 的 `$subscribe` 屬性來註冊 Subscriber。舉例來說，我們來將 `UserEventSubscriber` 加到這個列表上：

    <?php
    
    namespace App\Providers;
    
    use App\Listeners\UserEventSubscriber;
    use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
    
    class EventServiceProvider extends ServiceProvider
    {
        /**
         * The event listener mappings for the application.
         *
         * @var array
         */
        protected $listen = [
            //
        ];
    
        /**
         * The subscriber classes to register.
         *
         * @var array
         */
        protected $subscribe = [
            UserEventSubscriber::class,
        ];
    }
