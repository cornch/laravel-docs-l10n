# 廣播 - Broadcast

- [簡介](#introduction)
- [伺服器端安裝](#server-side-installation)
    - [組態設定](#configuration)
    - [Pusher Channels](#pusher-channels)
    - [Ably](#ably)
    - [開放原始碼替代](#open-source-alternatives)
- [用戶端安裝](#client-side-installation)
    - [Pusher Channels](#client-pusher-channels)
    - [Ably](#client-ably)
- [概念概覽](#concept-overview)
    - [使用範例應用程式](#using-example-application)
- [定義廣播事件](#defining-broadcast-events)
    - [Broadcast 名稱](#broadcast-name)
    - [Broadcast 資料](#broadcast-data)
    - [Broadcast 佇列](#broadcast-queue)
    - [Broadcast 條件](#broadcast-conditions)
    - [廣播與資料庫 Transaction](#broadcasting-and-database-transactions)
- [授權頻道](#authorizing-channels)
    - [定義授權路由](#defining-authorization-routes)
    - [定義授權回呼](#defining-authorization-callbacks)
    - [定義頻道類別](#defining-channel-classes)
- [廣播事件](#broadcasting-events)
    - [僅限其他](#only-to-others)
- [接收廣播](#receiving-broadcasts)
    - [監聽事件](#listening-for-events)
    - [離開頻道](#leaving-a-channel)
    - [Namespace](#namespaces)
- [Presence 頻道](#presence-channels)
    - [授權 Presence 頻道](#authorizing-presence-channels)
    - [加入 Presence 頻道](#joining-presence-channels)
    - [廣播至 Presence 頻道](#broadcasting-to-presence-channels)
- [用戶端事件](#client-events)
- [通知](#notifications)

<a name="introduction"></a>
## 簡介

在許多現代的網頁應用程式中，都使用了 WebSocket 來提供即時更新的使用者界面。當某個資料在伺服器上被更新，通常會通過 WebSocket
連線來將一個訊息傳送給用戶端做處理。比起持續從伺服器上拉去資料來反應到 UI 上，WebSocket 提供了一個更有效率的替代方案。

舉例來說，假設我們的應用程式可以將使用者資料匯出為 CSV 檔並將其以電子郵件寄出。不過，建立 CSV 檔需要數分鐘的事件，因此我們選擇將建立與寄送
CSV 檔的程式放在[佇列任務](/docs/{{version}}/queues)中。當 CSV 當建立完畢並寄給使用者後，我們可以使用事件廣播來將
`App\Events\UserDataExported` 事件分派給應用程式的
JavaScript。收到事件後，就可以在不需要使用者重新整理頁面的情況下顯示一個訊息來提示使用者我們已將 CSV 檔寄送出去。

為了協助你製作這種類型的功能，Laravel 讓你能簡單地將伺服器端 Laravel [事件](/docs/{{version}}/events) 通過
WebSocket 連線來「廣播」出去。通過廣播 Laravel 事件，就可以在伺服器端 Laravel 應用程式與用戶端 JavaScript
應用程式間共享相同的事件名稱與資料。

<a name="supported-drivers"></a>
#### 支援的 Driver

預設情況下，Laravel 包含了兩個伺服器端廣播 Driver 可供選擇：[Pusher
Channels](https://pusher.com/channels) 與 [Ably](https://ably.io)。不過，如
[laravel-websockets](https://beyondco.de/docs/laravel-websockets/getting-started/introduction)
等由社群開發的套件也提供了不需要商業 Broadcast Provider 的額外 Broadcast Driver。

> {tip} 在深入探討事件廣播前，請先確保你已閱讀有關 [事件與監聽程式](/docs/{{version}}/events)的 Laravel 說明文件。

<a name="server-side-installation"></a>
## 伺服器端安裝

若要開始使用 Laravel 的事件廣播，我們需要在 Laravel 的應用程式中做一些設定以及安裝一些套件。

事件廣播是通過伺服器端的廣播 Driver 將 Laravel 事件廣播出去，讓 Laravel Echo (一個 JavaScript 套件)
可以在瀏覽器用戶端內接收這個事件。別擔心 —— 我們會一步一步地介紹安裝過程的每一部分。

<a name="configuration"></a>
### 組態設定

應用程式中所有有關事件廣播的組態設定都位於 `config/boradcasting.php` 組態設定檔中。Laravel 內建支援多個
Broadcast Driver：[Pusher
Channels](https://pusher.com/channels)、[Redis](/docs/{{version}}/redis)、以及一個用於本機開發與出錯的
`log` Driver。此外，也包含了一個可以在測試期間完全禁用廣播的 `null` Driver。`config/boradcasting.php`
組態設定中包含了各個 Driver 的組態設定範例。

<a name="broadcast-service-provider"></a>
#### Broadcast Service Provider

在廣播任何事件以前，需要先註冊 `App\Providers\BroadcastServiceProvider`。在新安裝的 Laravel
應用程式中，只需要在 `config/app.php` 組態設定檔內的 `providers` 陣列中取消註解這個 Provider 即可。這個
`BroadcastServiceProvider` 包含了要註冊廣播授權路由以及回呼所需的程式碼。

<a name="queue-configuration"></a>
#### 設定佇列

也需要註冊並執行一個[佇列背景工作角色](/docs/{{version}}/queues)。所有的事件廣播都是通過佇列任務來完成的，這樣一來在事件被廣播的過程所需的事件才不會對應用程式的回應時間有太大的影響。

<a name="pusher-channels"></a>
### Pusher Channels

若有打算要使用 [Pusher Channels](https://pusher.com/channels)，那麼應通過 Composer
套件管理員來安裝 Pusher Channels 的 PHP SDK：

    composer require pusher/pusher-php-server "~4.0"

接著，應在 `config/broadcasting.php` 組態設定檔中設定 Pusher Channels 的憑證。該檔案中已經有包含了一個範例的
Pusher Channels 設定，讓你可以快速指定你的 Key, Secret 以及 Application ID。通常來說，這些值應該要通過
`PUSHER_APP_KEY`, `PUSHER_APP_SECRET` 與 `PUSHER_APP_ID`
[環境變數](/docs/{{version}}/configuration#environment-configuration) 來設定：

    PUSHER_APP_ID=your-pusher-app-id
    PUSHER_APP_KEY=your-pusher-key
    PUSHER_APP_SECRET=your-pusher-secret
    PUSHER_APP_CLUSTER=mt1

`config/broadcasting.php` 檔的 `pusher` 設定能讓你指定 Channels 所支援的額外選項
`options`，如簇集 (Cluster)。

接著，需要在 `.env` 檔中更改你的 Broadcast Driver 為 `pusher`：

    BROADCAST_DRIVER=pusher

最後，就可以安裝並設定 [Laravel Echo](#client-side-installable)。Laravel Echo
會在用戶端上接收廣播事件。

<a name="pusher-compatible-laravel-websockets"></a>
#### 與 Pusher 相容的 Laravel Websockets

[laravel-websockets](https://github.com/beyondcode/laravel-websockets)
套件是一個純 PHP、適用於 Laravel 的 Pusher 相容 WebSocket 套件。這個套件能讓你使用 Laravel
廣播的全部功能，而無需商業 WebSocket Provider。有關安裝與使用該套件的更多資訊，請參考其[官方說明文件
(英語)](https://beyondco.de/docs/laravel-websockets)。

<a name="ably"></a>
### Ably

若有打算要使用 [Ably](https://ably.io)，那麼應通過 Composer 套件管理員來安裝 Ably 的 PHP SDK：

    composer require ably/ably-php

接著，應在 `config/broadcasting.php` 組態設定檔中設定 Pusher Channels 的憑證。該檔案中已經有包含了一個範例的
Ably 設定，讓你可以快速指定你的金鑰。通常來說，這個值應該要通過 `ABLY_KEY`
[環境變數](/docs/{{version}}/configuration#environment-configuration) 來設定：

    ABLY_KEY=your-ably-key

接著，需要在 `.env` 檔中更改你的 Broadcast Driver 為 `ably`：

    BROADCAST_DRIVER=ably

最後，就可以安裝並設定 [Laravel Echo](#client-side-installable)。Laravel Echo
會在用戶端上接收廣播事件。

<a name="open-source-alternatives"></a>
### 開放原始碼替代

[laravel-websockets](https://github.com/beyondcode/laravel-websockets)
套件是一個純 PHP、適用於 Laravel 的 Pusher 相容 WebSocket 套件。這個套件能讓你使用 Laravel
廣播的全部功能，而無需商業 WebSocket Provider。有關安裝與使用該套件的更多資訊，請參考其[官方說明文件
(英語)](https://beyondco.de/docs/laravel-websockets)。

<a name="client-side-installation"></a>
## 用戶端安裝

<a name="client-pusher-channels"></a>
### Pusher Channels

Laravel Echo 是一個 JavaScript 套件，可以讓你不再煩惱如何訂閱頻道與監聽來自伺服器端 Broadcasting Driver
的事件廣播。可以通過 NPM 套件管理員來安裝 Echo。在這個例子中，因為我們會使用 Pusher Channels
Boradcaster，因此我們也會安裝 `pusher-js`：

```bash
npm install --save-dev laravel-echo pusher-js
```

安裝好 Echo 後，就可以在應用程式的 JavaScript 中建立一個新的 Echo 實體。要建立新 Echo 實體最好的地方就是在 Laravel
附帶的 `resources/js/bootstrap.js` 檔案最尾端。預設情況下，這個檔案內已經包含了一個範例的 Echo
設定，你只需要將其取消註解即可：

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

取消註解並依照需求調整好 Echo 設定後，就可以編譯應用程式素材：

    npm run dev

> {tip} 要瞭解更多有關編譯應用程式 JavaScript 素材的資訊，請參考 [Laravel Mix](/docs/{{version}}/mix) 中的說明文件。

<a name="using-an-existing-client-instance"></a>
#### 使用現有的用戶端實體

若已經有預先設定好的 Pusher Channels 用戶端實體，並想讓 Echo 使用的話，可以將其傳入 Echo 的 `client` 設定選項：

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

Laravel Echo 是一個 JavaScript 套件，可以讓你不再煩惱如何訂閱頻道與監聽來自伺服器端 Broadcasting Driver
的事件廣播。可以通過 NPM 套件管理員來安裝 Echo。在這個例子中，我們也會安裝 `pusher-js` 套件：

你可能會很困惑，為什麼我們明明是要用 Ably 來廣播事件，卻安裝了 `pusher-js` JavaScript 函式庫。謝天謝地，Ably 有個
Pusher 相容模式，可以讓我們在用戶端應用程式內監聽事件的時候使用 Pusher 協定：

```bash
npm install --save-dev laravel-echo pusher-js
```

**在繼續之前，應先在 Ably 應用程式設定中啟用 Pusher 通訊協定。可以在 Ably 應用程式設定面板中的「Protocol Adapter Settings」這個部分內啟用此功能。**

安裝好 Echo 後，就可以在應用程式的 JavaScript 中建立一個新的 Echo 實體。要建立新 Echo 實體最好的地方就是在 Laravel
附帶的 `resources/js/bootstrap.js` 檔案最尾端。預設情況下，這個檔案內已經包含了一個範例的 Echo
設定。不過，`bootstrap.js` 檔案中預設的範例是給 Pusher 用的。可以複製下列設定來將你的設定檔改成使用 Ably：

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

請注意，Ably Echo 組態設定中參考了 `MIX_ABLY_PUBLIC_KEY` 環境變數。這個環境變數應為 Ably 的公開金鑰。公開金鑰就是
Ably 金鑰中出現在 `:` 字元之前的部分。

取消註解並依照需求調整好 Echo 設定後，就可以編譯應用程式素材：

    npm run dev

> {tip} 要瞭解更多有關編譯應用程式 JavaScript 素材的資訊，請參考 [Laravel Mix](/docs/{{version}}/mix) 中的說明文件。

<a name="concept-overview"></a>
## 概念概覽

Laravel 的事件廣播功能能讓你以基於 Driver 的方法來將伺服器端的 Laravel 事件通過 WebSockets 廣播到用戶端
JavaScript 應用程式上。目前，Laravel 隨附了 [Pusher
Channels](https://pusher.com/channels) 與 [Ably](https://ably.io) 兩個
Driver。可以在用戶端使用 [Laravel Echo](#client-side-installation) JavaScript
套件來輕鬆取得事件。

事件是通過「頻道
(Channel)」進行廣播的，頻道可以被設為公共或私有。應用程式的任何訪客都可以在不登入或經過授權的情況下訂閱公開頻道。不過，如果要訂閱私有頻道，就必須要登入並經過授權以監聽該頻道。

> {tip} 如果你想使用開放原始碼且由 PHP 驅動的 Pusher 代替品的話，可以看看 [laravel-websockets](https://github.com/beyondcode/laravel-websockets) 套件。

<a name="using-example-application"></a>
### 使用範例應用程式

在深入探討事件廣播的各個元件之前，我們先來用網路商店當作例子，以高階的角度來個概覽。

在我們的應用程式中，先來假設有個能讓使用者檢視訂單配送狀態的頁面。另外，也假設當應用程式處理到配送狀態更新的時候會觸發
`OrderShipmentStatusUpdated` 事件：

    use App\Events\OrderShipmentStatusUpdated;

    OrderShipmentStatusUpdated::dispatch($order);

<a name="the-shouldbroadcast-interface"></a>
#### `ShouldBroadcast` 介面

我們並不希望使用者在檢視某個訂單的時候還需要重新整理整個頁面才能看到狀態更新；我們希望讓訂單更新在建立的時候就被廣播到應用程式上。因此，我們需要將
`OrderShipmentStatusUpdated` 事件標上 `ShouldBroadcast` 介面。通過加上該介面，就能告訴 Laravel
要在該介面被觸發時將其廣播出去：

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

`ShouldBroadcast` 介面需要我們在事件中定義一個 `broadcastOn`
方法。這個方法需要回傳該事件廣播的頻道。產生的事件類別當中已經棒我們加上了一個空白的
Stub，因此我們只需要填寫詳情就好了。我們只希望建立該訂單的使用者檢視狀態更新，因此我們會將事件放在該訂單的私有頻道上廣播：

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
#### 授權頻道

請記得，使用者必須要經過授權才能監聽私有頻道。我們可以在 `routes/channels.php`
檔中定義頻道權限規則。在此例子中，我們需要驗證嘗試監聽私有頻道 `order.1` 的使用者是否為該訂單實際的建立人：

    use App\Models\Order;

    Broadcast::channel('orders.{orderId}', function ($user, $orderId) {
        return $user->id === Order::findOrNew($orderId)->user_id;
    });

`channel` 方法接收 2 個引數：頻道的名稱，以及會回傳 `true` 與 `false` 的回呼。這個回呼用來判斷使用者是否已授權監聽此頻道。

所有的授權回呼都會收到目前登入使用者作為其第一個引數，而接下來的引數則是其他額外的萬用字元參數。在這個例子中，我們使用了 `{orderId}`
預留位置來標示頻道名稱中的「ID」部分是萬用字元。

<a name="listening-for-event-broadcasts"></a>
#### 監聽事件廣播

接著，剩下的工作就是在 JavaScript 應用程式內監聽事件了。我們可以使用 Laravel Echo。首先，我們要先用 `private`
方法來監聽私有頻道。接著，可以監聽「`listen`」`OrderShipmentStatusUpdated`
事件。預設情況下，該事件的所有公共屬性都會被包含在廣播事件內：

```js
Echo.private(`orders.${orderId}`)
    .listen('OrderShipmentStatusUpdated', (e) => {
        console.log(e.order);
    });
```

<a name="defining-broadcast-events"></a>
## 定義廣播事件

為了告訴 Laravel 應廣播某個給定的事件，我們必須要在事件類別上實作
`Illuminate\Contracts\Broadcasting\ShouldBroadcast`
介面。在所有產生出來的事件類別上，框架已經幫你引入這個介面了，因此你可以輕鬆地將該介面加至任何事件上。

`ShouldBroadcast` 介面只要求實作單一方法：`broadcastOn`。`broadcastOn`
方法應回傳一個頻道，或是一個包含頻道的陣列。這些頻道是事件要進行廣播的頻道。頻道應為 `Channel`, `PrivateChannel` 或
`PresenceChannel` 的實體。`Channel` 的實體代表任何使用者都能監聽的公共頻道，而 `PrivateChannel` 與
`PresenceChannels` 代表需要進行[頻道授權](#authorizing-channels)的私有頻道：

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

實作完 `ShouldBroadcast`
介面後，只需要像平常一樣[觸發事件](/docs/{{version}}/events)即可。事件被觸發後，[佇列任務](/docs/{{version}}/queues)會自動通過指定的
Broadcast Driver 來廣播事件。

<a name="broadcast-name"></a>
### Broadcast 名稱

預設情況下，Laravel 會使用事件的類別名來進行廣播。不過，也可以在事件上定義 `broadcastAs` 方法來自定 Broadcast 名稱：

    /**
     * 事件的 Broadcast 名稱。
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'server.created';
    }

若使用 `broadcastAs` 方法來自定 Broadcast 名稱，則應確保註冊監聽程式時有加上前置 `.` 字元。加上該前置字元可用來告訴
Echo 不要在事件前方加上應用程式的命名空間：

    .listen('.server.created', function (e) {
        ....
    });

<a name="broadcast-data"></a>
### Broadcast 資料

廣播事件時，事件所有的 `public` 屬性都會被自動序列化，並作為事件的 Payload 進行廣播，讓你能在 JavaScript
應用程式中存取事件的所有公共資料。因此，舉例來說，假設我們的事件有一個 public `$user` 屬性，其中包含了 Eloquent
Model，那麼事件的 Broadcast Payload 會是：

    {
        "user": {
            "id": 1,
            "name": "Patrick Stewart"
            ...
        }
    }

不過，若想對 Broadcast Payload 進一步地控制，可以在事件內加上一個 `broadcastWith`
方法。這個方法應回傳一個陣列，包含要作為事件 Payload 使用的資料：

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
### Broadcast 佇列

預設情況下，所有的廣播事件都會使用 `queue.php` 組態設定檔中的預設佇列連連。可以通過在事件類別內定義 `queue` 屬性來自定
Broadcaster 要使用的佇列連線名稱：

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

若像使用 `sync` 佇列來代替預設的佇列 Driver，可以使用 `ShouldBroadcastNow` 來代替
`ShouldBroadcast` 進行實作：

    <?php

    use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

    class OrderShipmentStatusUpdated implements ShouldBroadcastNow
    {
        //
    }

<a name="broadcast-conditions"></a>
### Broadcast 條件

有時候我們可能只想在滿足給定條件的時候才廣播事件。可以通過在事件類別上新增 `broadcastWhen` 方法來在其中定義這些條件：

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
#### Broadcast 與資料庫 Transaction

當廣播事件是在資料庫 Transaction 內分派的時候，這個事件可能會在資料庫 Transaction 被 Commit
前被佇列進行處理了。發生這種情況時，在資料庫 Transaction 期間對 Model 或資料庫記錄所做出的更新可能都還未反應到資料庫內。另外，所有在
Transaction 期間新增的 Model 或資料庫記錄也可能還未出現在資料庫內。若事件有依賴這些 Model
的話，在處理廣播事件的任務時可能會出現未預期的錯誤。

若佇列連線的 `after_commit` 設定選項是 `false`，那麼就可以通過在事件類別上定義 `$afterCommit`
屬性來標示出特定的廣播事件應在資料庫 Transaction 被 Commit 後才可進行分派：

    <?php

    namespace App\Events;

    use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
    use Illuminate\Queue\SerializesModels;

    class ServerCreated implements ShouldBroadcast
    {
        use SerializesModels;

        public $afterCommit = true;
    }

> {tip} 要瞭解更多有關這類問題的解決方法，請參考有關[佇列任務與資料庫 Transaction](/docs/{{version}}/queues#jobs-and-database-transactions) 有關的說明文件。

<a name="authorizing-channels"></a>
## 授權頻道

使用私有頻道，則需要將目前已登入的使用者授權為可監聽該頻道。要授權使用者，需要向 Laravel 應用程式傳送一個包含頻道名稱的 HTTP
請求來讓應用程式判斷使用者能否監聽該頻道。使用 [Laravel Echo](#client-side-installation)
時，會自動建立用於授權訂閱私有頻道的 HTTP 請求。不過，我們還是需要定義適當的路由來回應這些請求。

<a name="defining-authorization-routes"></a>
### 定義授權路由

好佳在，在 Laravel 中定義回應頻道授權請求的路由非常容易。在 Laravel 中隨附的
`App\Providers\BroadcastServiceProvider` 內，可以看到一個 `Broadcast::routes`
方法的呼叫。這個方法會註冊 `/broadcasting/auth` 路由來處理授權請求：

    Broadcast::routes();

`Broadcast::routes` 方法會自動將其中的路由放置於 `web` Middleware
群組內。不過，若想自定指派的屬性，也可以傳入包含路由屬性的陣列：

    Broadcast::routes($attributes);

<a name="customizing-the-authorization-endpoint"></a>
#### 自定授權 Endpoint

預設情況下，Echo 會使用 `/broadcasting/auth` Endpoint 來授權頻道存取。不過，也可以通過將
`authEndpoint` 設定選項傳給 Echo 實體來指定你自己的授權 Endpoint：

    window.Echo = new Echo({
        broadcaster: 'pusher',
        // ...
        authEndpoint: '/custom/endpoint/auth'
    });

<a name="defining-authorization-callbacks"></a>
### 定義授權回呼

接著，我們需要定義實際上用來判斷目前登入使用者是否能監聽給定頻道的邏輯。這個定義放在應用程式內`routes/channels.php`
檔案中。在這個檔案中，可以使用 `Broadcast::channel` 方法來註冊頻道授權回呼：

    Broadcast::channel('orders.{orderId}', function ($user, $orderId) {
        return $user->id === Order::findOrNew($orderId)->user_id;
    });

`channel` 方法接收 2 個引數：頻道的名稱，以及會回傳 `true` 與 `false` 的回呼。這個回呼用來判斷使用者是否已授權監聽此頻道。

所有的授權回呼都會收到目前登入使用者作為其第一個引數，而接下來的引數則是其他額外的萬用字元參數。在這個例子中，我們使用了 `{orderId}`
預留位置來標示頻道名稱中的「ID」部分是萬用字元。

<a name="authorization-callback-model-binding"></a>
#### 授權回呼的 Model 綁定

就像 HTTP 路由一樣，頻道路由也能使用顯式或隱式[路由 Model
綁定](/docs/{{version}}/routing#route-model-binding)的功能。舉例來說，可以不接收字串或數字的 Order
ID，而要求實際的 `Order` Model 實體：

    use App\Models\Order;

    Broadcast::channel('orders.{order}', function ($user, Order $order) {
        return $user->id === $order->user_id;
    });

> {note} 與 HTTP 路由 Model 綁定不同，頻道的 Model 綁定不支援自動[為隱式 Model 綁定加上作用域]。不過，通常來說這不會造成問題，因為大部分的頻道都可以被放置與單一 Model 的獨立主鍵作用域內。

<a name="authorization-callback-authentication"></a>
#### 授權回呼驗證

私有與 Presence 廣播頻道通過應用程式的預設驗證 Guard
來驗證目前的使用者。若使用者未登入，則頻道驗證會被自動拒絕，而授權回呼則永遠不會被執行。不過，若有需要，也可以指定多個自定 Guard 來驗證連入請求：

    Broadcast::channel('channel', function () {
        // ...
    }, ['guards' => ['web', 'admin']]);

<a name="defining-channel-classes"></a>
### 定義 Channel 類別

若你的應用程式會使用到許多不同的頻道，則 `routes/channels.php`
可能會變得很肥大。因此，比起使用閉包來授權頻道，我們可以改用頻道類別。要建立頻道類別，請使用 `make:channel` Artisan
指令。這個指令會在 `app/Broadcasting` 目錄內放置一個新的頻道類別。

    php artisan make:channel OrderChannel

接著，在 `routes/channels.php` 檔案內註冊頻道：

    use App\Broadcasting\OrderChannel;

    Broadcast::channel('orders.{order}', OrderChannel::class);

最後，可以將頻道的授權邏輯放在頻道類別的 `join` 方法內。這個 `join` 方法用來放置與平常放在頻道授權閉包相同的邏輯。也可以使用頻道
Model 綁定：

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

> {tip} 與 Laravel 內其他類別一樣，頻道類別也會自動由 [Service Container](/docs/{{version}}/container) 解析。因此，我們可以在頻道的建構函式上對任何所需要的依賴進行型別提示。

<a name="broadcasting-events"></a>
## 廣播事件

定義好事件並將其以 `ShouldBroadcast` 介面進行標示後，我們只需要使用 dispatch
方法來觸發事件即可。事件觸發程式會注意到這個事件有被標註為 `ShouldBroadcast` 介面，並將事件放入佇列以進行廣播：

    use App\Events\OrderShipmentStatusUpdated;

    OrderShipmentStatusUpdated::dispatch($order));

<a name="only-to-others"></a>
### 僅限其他

在建立一個有使用到事件廣播的應用程式時，我們可能會需要將某個事件廣播給除了目前使用者以外的所有頻道訂閱者。可以通過 `broadcast` 輔助函式以及
`toOthers` 方法來完成：

    use App\Events\OrderShipmentStatusUpdated;

    broadcast(new OrderShipmentStatusUpdated($update))->toOthers();

為了幫助你更容易理解什麼時候會需要用到 `toOthers`
方法，我們來假設有個任務清單應用程式。在這個應用程式中，使用者可以輸入任務名稱來新增任務。為了建立任務，這個應用程式可能會向 `/task` URL
發起一個請求，該請求會將任務的建立廣播出去，並回傳代表新任務的 JSON。當 JavaScript 應用程式從這個 End-point
收到回覆後，就可以直接將新任務插入到任務清單內。像這樣：

    axios.post('/task', task)
        .then((response) => {
            this.tasks.push(response.data);
        });

不過，提醒一下，我們也會將任務的建立廣播出去。如果 JavaScript
應用程式也會監聽這個事件來將任務新增到任務清單上，則列表上就會有重複的任務：一個是從 End-point
回傳回來的，另一個則是從事件來的。我們可以通過使用 `toOthers` 方法來告訴廣播程式不要將該事件廣播給目前的使用者。

> {note} 若要呼叫 `toOthers` 方法，該事件必須要 use `Illuminate\Broadcasting\InteractsWithSockets` Trait。

<a name="only-to-others-configuration"></a>
#### 組態設定

初始化 Laravel Echo 實體時，會指派一個 Socket ID 給這個連線。若是使用全域的
[Axios](https://github.com/mzabriskie/axios) 實體來在 JavaScript 應用程式內建立 HTTP
連線，則 Socket ID 會以 `X-Socket-ID` 標頭被自動附加到每個外連請求上。接著，當呼叫 `toOthers`
方法時，Laravel 會從標頭內拆出這個 Socket ID，並告知廣播程式不要廣播給有該 Socket ID 的連線。

若你未使用全域 Axios 實體，則需要手動設定 JavaScript 應用程式來在所有外連請求上傳送 `X-Socket-ID` 標頭。可以通過
`Echo.socketId` 方法來取得 Socket ID：

    var socketId = Echo.socketId();

<a name="receiving-broadcasts"></a>
## 接收廣播

<a name="listening-for-events"></a>
### 監聽事件

[安裝並設定好 Laravel Echo](#client-side-installation) 後，就可以開始監聽來自 Laravel
應用程式廣播的事件了。首先，使用 `channel` 方法來取得頻道的實體，然後呼叫 `listen` 方法來監聽某個特定的事件：

```js
Echo.channel(`orders.${this.order.id}`)
    .listen('OrderShipmentStatusUpdated', (e) => {
        console.log(e.order.name);
    });
```

若想監聽私有頻道，可使用 `private` 方法來代替。可以繼續在 `listen` 方法後方串上其他的呼叫來在單一頻道上監聽多個事件：

```js
Echo.private(`orders.${this.order.id}`)
    .listen(...)
    .listen(...)
    .listen(...);
```

<a name="leaving-a-channel"></a>
### 離開頻道

若要離開頻道，可以在 Echo 實體上呼叫 `leaveChannel` 方法：

```js
Echo.leaveChannel(`orders.${this.order.id}`);
```

若要離開頻道以及其關聯的私有與 Presence 頻道，可以呼叫 `leave` 方法：

```js
Echo.leave(`orders.${this.order.id}`);
```
<a name="namespaces"></a>
### 命名空間

你可能已經注意到，我們並沒有為事件類別指定完整的 `App\Events` 命名空間。這是因為，Echo 會自動假設事件都放在 `App\Events`
命名空間下。不過，我們可以在初始化 Echo 時傳入 `namespace` 組態設定選項來設定要使用的根命名空間：

```js
window.Echo = new Echo({
    broadcaster: 'pusher',
    // ...
    namespace: 'App.Other.Namespace'
});
```

除了在初始化時設定以外，也可以在使用 Echo 訂閱事件時在事件類別的名稱前加上一個前置 `.`。這樣一來，就可以隨時使用完整的類別名稱：

```js
Echo.channel('orders')
    .listen('.Namespace\\Event\\Class', (e) => {
        //
    });
```

<a name="presence-channels"></a>
## Presence 頻道

Presence
頻道擁有私有頻道的安全性，且會提供該頻道的訂閱者等額外資訊。這樣一來便能輕鬆地建立強大的協作應用程式功能，如提示使用者其他使用者正在檢視相同頁面，或是列出聊天室中的使用者狀態。

<a name="authorizing-presence-channels"></a>
### 授權 Presence 頻道

所有的 Presence 頻道也同時是私有頻道。因此，使用者必須要[經過授權以存取頻道](#authorizing-channels)。不過，在為
Presence 頻道定義授權回呼時，若要授權使用者加入頻道，則不應回傳 `true`，而應回傳包含有關其他使用者資訊的陣列。

由授權回呼回傳的資料可以在 JavaScript 應用程式中的 Presence 頻道事件監聽程式中使用。若使用者未被授權加入 Presence
頻道，則應回傳 `false` 或 `null`：

    Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
        if ($user->canJoinRoom($roomId)) {
            return ['id' => $user->id, 'name' => $user->name];
        }
    });

<a name="joining-presence-channels"></a>
### 加入 Presence 頻道

若要加入 Presence 頻道，可以使用 Echo 的 `join` 方法。`join` 方法會與所暴露的 `listen` 方法一起回傳一個
`PresenceChannel` 的實作，這樣一來你就能訂閱 `here`, `joining` 以及 `leaving` 事件。

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
### 廣播至 Presence 頻道

Presence 頻道可以像公用或私有頻道一樣接收事件。以聊天室為例，我們可能像廣播 `NewMessage` 事件至聊天室的 Presence
頻道。為此，我們可以在事件的 `broadcastOn` 方法內回傳一個 `PresenceChannel` 的實體：

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('room.'.$this->message->room_id);
    }

與其他事件一樣，可以使用 `broadcast` 輔助函式與 `toOthers` 方法來排除目前使用者接收該 Broadcast：

    broadcast(new NewMessage($message));

    broadcast(new NewMessage($message))->toOthers();

與其他一般的事件一樣，也可以使用 Echo 的 `listen` 方法來監聽傳送到 Presence 頻道的事件：

    Echo.join(`chat.${roomId}`)
        .here(...)
        .joining(...)
        .leaving(...)
        .listen('NewMessage', (e) => {
            //
        });

<a name="client-events"></a>
## 用戶端事件

> {tip} 在使用 [Pusher Channels](https://pusher.com/channels) 時，可以在 [Application Dashboard](https://dashboard.pusher.com/) 內啟用「App Settings」中的「Client Event」，以傳送用戶端事件。

有時候我們可能會想將事件直接廣播給其他連線的用戶端，而不經由 Laravel
應用程式。特別像是如顯示「正在輸入」等通知時，我們只是想告訴使用者應用程式內的其他使用者正在特定畫面上輸入。

若要廣播用戶端事件，可以使用 Echo 的 `whisper` 方法：

    Echo.private(`chat.${roomId}`)
        .whisper('typing', {
            name: this.user.name
        });

若要監聽用戶端事件，可以使用 `listenForWhisper` 方法：

    Echo.private(`chat.${roomId}`)
        .listenForWhisper('typing', (e) => {
            console.log(e.name);
        });

<a name="notifications"></a>
## 通知

只要將事件廣播與 [通知](/docs/{{version}}/notifications) 一起使用，JavaScript
應用程式就可以在不重新整理的情況下接收新通知。在開始前，請先閱讀有關[廣播通知頻道](/docs/{{version}}/notifications#broadcast-notifications)的說明文件。

設定讓通知使用廣播頻道後，就可以使用 Echo 的 `notification` 方法來接收廣播。請記住，頻道的名稱應與接收通知的使用者類別名稱相符：

    Echo.private(`App.Models.User.${userId}`)
        .notification((notification) => {
            console.log(notification.type);
        });

在此範例中，所有通過 `broadcast` 頻道傳送給 `App\Models\User` 實體的通知都會被該回呼收到。用於
`App.Models.User.{id}` 的頻道授權回呼包含在 Laravel 框架附帶的 `BroadcastServiceProvider`
內。
