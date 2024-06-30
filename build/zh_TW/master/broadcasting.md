---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/19/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:17:00Z'
---

# 廣播 - Broadcast

- [簡介](#introduction)
- [伺服器端安裝](#server-side-installation)
   - [設定](#configuration)
   - [Pusher Channels](#pusher-channels)
   - [Ably](#ably)
   - [開放原始碼替代](#open-source-alternatives)
- [用戶端安裝](#client-side-installation)
   - [Pusher Channels](#client-pusher-channels)
   - [Ably](#client-ably)
- [概念概覽](#concept-overview)
   - [使用現有的應用程式](#using-example-application)
- [定義 Broadcast 事件](#defining-broadcast-events)
   - [Broadcast 名稱](#broadcast-name)
   - [Broadcast 資料](#broadcast-data)
   - [Broadcast 佇列](#broadcast-queue)
   - [Broadcast 條件](#broadcast-conditions)
   - [Broadcast 與資料庫 Transaction](#broadcasting-and-database-transactions)
- [授權頻道](#authorizing-channels)
   - [定義授權路由](#defining-authorization-routes)
   - [定義授權回呼](#defining-authorization-callbacks)
   - [定義頻道類別](#defining-channel-classes)
- [Broadcast 事件](#broadcasting-events)
   - [僅限其他](#only-to-others)
   - [自訂連線](#customizing-the-connection)
- [接收 Broadcast](#receiving-broadcasts)
   - [監聽事件](#listening-for-events)
   - [離開頻道](#leaving-a-channel)
   - [Namespace](#namespaces)
- [Presence 頻道](#presence-channels)
   - [授權 Precense 頻道](#authorizing-presence-channels)
   - [加入 Presence 頻道](#joining-presence-channels)
   - [廣播至 Presence 頻道](#broadcasting-to-presence-channels)
- [廣播 Model](#model-broadcasting)
   - [廣播 Model 的慣例](#model-broadcasting-conventions)
   - [監聽 Model Broadcast](#listening-for-model-broadcasts)
- [用戶端事件](#client-events)
- [通知](#notifications)

<a name="introduction"></a>

## 簡介

在許多現代 Web App 中，都使用了 WebSocket 來提供即時更新的 UI。當某個資料在伺服器上被更新，通常會通過 WebSocket 連線來將一個訊息傳送給用戶端做處理。比起不斷從伺服器上拉取資料並反應到 UI 上，WebSocket 提供是更有效率的方案。

舉例來說，假設我們的 App 可以將使用者資料匯出為 CSV 檔並以電子郵件寄出。不過，建立 CSV 檔需要數分鐘的事件，因此我們選擇將建立與寄送 CSV 檔的程式放在[佇列任務](/docs/{{version}}/queues)中。當 CSV 當建立完畢並寄給使用者後，我們可以使用「事件廣播」來將 `App\Events\UserDataExported` 事件分派給應用程式的 JavaScript。收到事件後，使用者就能在不重新整理的情況下看到一個訊息，表示我們已將 CSV 檔寄送出去。

為了協助你製作這種類型的功能，Laravel 讓你能簡單地將伺服器端 Laravel [事件](/docs/{{version}}/events)通過 WebSocket 連線來「廣播」出去。通過廣播 Laravel 事件，就可以在伺服器端 Laravel 程式與用戶端 JavaScript 程式間共享相同的事件名稱與資料。

「廣播」背後的核心概念很簡單：用戶端會在前端連線到一個有名稱的頻道，而後端 Laravel 網站則會將事件廣播給這些頻道。這些事件可以包含任何你想讓前端存取的額外資料。

<a name="supported-drivers"></a>

#### 支援的 Driver

預設情況下，Laravel 包含了兩個伺服器端廣播 Driver 可供選擇：[Pusher Channels](https://pusher.com/channels) 與 [Ably](https://ably.com)。不過，也有如 [laravel-websockets](https://beyondco.de/docs/laravel-websockets/getting-started/introduction) 與 [soketi](https://docs.soketi.app/) 這樣由社群開發的套件提供不需要商業 Broadcast Provider 的額外 Broadcast Driver。

> **Note** 在深入探討事件廣播前，請先確保你已閱讀有關 [事件與監聽程式](/docs/{{version}}/events)的 Laravel 說明文件。

<a name="server-side-installation"></a>

## 伺服器端安裝

若要開始使用 Laravel 的事件廣播，我們需要在 Laravel 專案中做一些設定以及安裝一些套件。

事件廣播是通過伺服器端的廣播 Driver 將 Laravel 事件廣播出去，讓 Laravel Echo (一個 JavaScript 套件) 可以在瀏覽器用戶端內接收這個事件。別擔心 —— 我們會一步一步地介紹安裝過程的每一部分。

<a name="configuration"></a>

### 設定

專案中，所有關於事件廣播的設定都放在 `config/boradcasting.php` 設定檔中。Laravel 內建支援多個 Broadcast Driver：[Pusher Channels](https://pusher.com/channels)、[Redis](/docs/{{version}}/redis)、以及一個用於本機開發與偵錯的 `log` Driver。此外，也包含了一個可以在測試期間完全禁用廣播的 `null` Driver。`config/boradcasting.php` 設定中包含了各個 Driver 的設定範例。

<a name="broadcast-service-provider"></a>

#### Broadcast Service Provider

在廣播任何事件以前，需要先註冊 `App\Providers\BroadcastServiceProvider`。在新安裝的 Laravel 專案中，只需要在 `config/app.php` 設定檔內的 `providers` 陣列中取消註解這個 Provider 即可。這個 `BroadcastServiceProvider` 包含了要註冊廣播授權路由以及回呼所需的程式碼。

<a name="queue-configuration"></a>

#### 設定佇列

也需要註冊並執行一個[佇列背景工作角色](/docs/{{version}}/queues)。所有的事件廣播都是通過佇列任務來完成的，這樣一來在事件被廣播的過程所需的事件才不會對網站的回應時間有太大的影響。

<a name="pusher-channels"></a>

### Pusher Channels

若有打算要使用 [Pusher Channels](https://pusher.com/channels)，那麼應通過 Composer 套件管理員來安裝 Pusher Channels 的 PHP SDK：

```shell
composer require pusher/pusher-php-server
```

接著，應在 `config/broadcasting.php` 設定檔中設定 Pusher Channels 的憑證。該檔案中已經有包含了一個範例的 Pusher Channels 設定，讓你可以快速指定你的 Key, Secret 以及 Application ID。通常來說，這些值應該要通過 `PUSHER_APP_KEY`, `PUSHER_APP_SECRET` 與 `PUSHER_APP_ID` [環境變數](/docs/{{version}}/configuration#environment-configuration) 來設定：

```ini
PUSHER_APP_ID=your-pusher-app-id
PUSHER_APP_KEY=your-pusher-key
PUSHER_APP_SECRET=your-pusher-secret
PUSHER_APP_CLUSTER=mt1
```

`config/broadcasting.php` 檔的 `pusher` 設定能讓你指定 Channels 所支援的額外選項 `options`，如簇集 (Cluster)。

接著，需要在 `.env` 檔中更改你的 Broadcast Driver 為 `pusher`：

```ini
BROADCAST_DRIVER=pusher
```

最後，就可以安裝並設定 [Laravel Echo](#client-side-installable)。Laravel Echo 會在用戶端上接收廣播事件。

<a name="pusher-compatible-open-source-alternatives"></a>

#### 開放原始碼的 Pusher 替代

[laravel-websockets](https://github.com/beyondcode/laravel-websockets) 與 [soketi](https://docs.soketi.app/) 套件提供了用於 Laravel 的 Pusher 相同 WebSocket 伺服器。使用這些套件就能在不依賴商業 WebSocket Provider 的情況下使用完整的 Laravel Broadcasting 功能。有關安裝這些套件的更多資訊，請參考我們的[開放原始碼替代](#open-source-alternatives)說明文件。

<a name="ably"></a>

### Ably

> **Note** 下方的說明文件討論了如何在「Pusher 相容模式 (Pusher Compatibility)」下使用 Ably。不過，Ably 團隊還維護了一個 Broadcaster 程式與可使用 Ably 特別功能的 Echo 用戶端。更多有關使用 Ably 維護的 Driver 的資訊，請[參考 Ably 的 Laravel Broadcaster 說明文件 (英語)](https://github.com/ably/laravel-broadcaster)。

若有打算要使用 [Ably](https://ably.com)，則請使用 Composer 套件管理員來安裝 Ably 的 PHP SDK：

```shell
composer require ably/ably-php
```

接著，應在 `config/broadcasting.php` 設定檔中設定 Pusher Channels 的憑證。該檔案中已經有包含了一個範例的 Ably 設定，讓你可以快速指定你的金鑰。通常來說，這個值應該要通過 `ABLY_KEY` [環境變數](/docs/{{version}}/configuration#environment-configuration) 來設定：

```ini
ABLY_KEY=your-ably-key
```

接著，需要在 `.env` 檔中更改你的 Broadcast Driver 為 `ably`：

```ini
BROADCAST_DRIVER=ably
```

最後，就可以安裝並設定 [Laravel Echo](#client-side-installable)。Laravel Echo 會在用戶端上接收廣播事件。

<a name="open-source-alternatives"></a>

### 開放原始碼替代

<a name="open-source-alternatives-php"></a>

#### PHP

[laravel-websockets](https://github.com/beyondcode/laravel-websockets) 套件是一個純 PHP、適用於 Laravel 的 Pusher 相容 WebSocket 套件。這個套件能讓你使用 Laravel 廣播的全部功能，而無需商業 WebSocket Provider。有關安裝與使用該套件的更多資訊，請參考其[官方說明文件](https://beyondco.de/docs/laravel-websockets)。

<a name="open-source-alternatives-node"></a>

#### Node

[Soketi](https://github.com/soketi/soketi) 套件是一個基於 Node、適用於 Laravel 的 Pusher 相容 WebSocket 伺服器。從底層來看，Soketi 使用了 µWebSockets.js 來獲得最佳可擴充性與速度。Sketi 能讓你在不需仰賴商業 WebSocket Provider 的情況下使用 Laravel 廣播的全部功能。有關安裝與使用 Sketi 的更多資訊，請參考其[官方說明文件](https://docs.soketi.app)。

<a name="client-side-installation"></a>

## 用戶端安裝

<a name="client-pusher-channels"></a>

### Pusher Channels

[Laravel Echo](https://github.com/laravel/echo) 是一個 JavaScript 套件，能讓你免於煩惱如何訂閱頻道與監聽來自伺服器端 Broadcasting Driver 的事件廣播。我們可以通過 NPM 套件管理員來安裝 Echo。在這個例子中，因為我們會使用 Pusher Channels Boradcaster，因此我們也會安裝 `pusher-js`：

```shell
npm install --save-dev laravel-echo pusher-js
```

安裝好 Echo 後，就可以在網站的 JavaScript 中建立一個新的 Echo 實體。要建立新 Echo 實體最好的地方就是在 Laravel 附帶的 `resources/js/bootstrap.js` 檔案最尾端。預設情況下，這個檔案內已經包含了一個範例的 Echo 設定，只需要將其取消註解即可：

```js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});
```

取消註解並依照需求調整好 Echo 設定後，就可以編譯專案素材：

```shell
npm run dev
```

> **Note** 要瞭解更多有關編譯應用程式 JavaScript 素材的資訊，請參考 [Vite](/docs/{{version}}/vite) 中的說明文件。

<a name="using-an-existing-client-instance"></a>

#### 使用現有的用戶端實體

若已經有預先設定好的 Pusher Channels 用戶端實體，並想讓 Echo 使用的話，可以將其傳入 Echo 的 `client` 設定選項：

```js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const options = {
    broadcaster: 'pusher',
    key: 'your-pusher-channels-key'
}

window.Echo = new Echo({
    ...options,
    client: new Pusher(options.key, options)
});
```

<a name="client-ably"></a>

### Ably

> **Note** 下方的說明文件討論了如何在「Pusher 相容模式 (Pusher Compatibility)」下使用 Ably。不過，Ably 團隊還維護了一個 Broadcaster 程式與可使用 Ably 特別功能的 Echo 用戶端。更多有關使用 Ably 維護的 Driver 的資訊，請[參考 Ably 的 Laravel Broadcaster 說明文件 (英語)](https://github.com/ably/laravel-broadcaster)。

[Laravel Echo](https://github.com/laravel/echo) 是一個 JavaScript 套件，能讓你免於煩惱如何訂閱頻道與監聽來自伺服器端 Broadcasting Driver 的事件廣播。我們可以通過 NPM 套件管理員來安裝 Echo。在這個例子中，我們也會安裝 `pusher-js`：

你可能會很困惑，為什麼我們明明是要用 Ably 來廣播事件，卻安裝了 `pusher-js` JavaScript 函式庫。謝天謝地，Ably 有個 Pusher 相容模式，可以讓我們在用戶端程式內監聽事件的時候使用 Pusher 協定：

```shell
npm install --save-dev laravel-echo pusher-js
```

**在繼續之前，應先在 Ably 應用程式設定中啟用 Pusher 通訊協定。可以在 Ably 應用程式設定面板中的「Protocol Adapter Settings」這個部分內啟用此功能。**

安裝好 Echo 後，就可以在網站的 JavaScript 中建立一個新的 Echo 實體。要建立新 Echo 實體最好的地方就是在 Laravel 附帶的 `resources/js/bootstrap.js` 檔案最尾端。預設情況下，這個檔案內已經包含了一個範例的 Echo 設定。不過，`bootstrap.js` 檔案中預設的範例是給 Pusher 用的。可以複製下列設定來將你的設定檔改成使用 Ably：

```js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_ABLY_PUBLIC_KEY,
    wsHost: 'realtime-pusher.ably.io',
    wsPort: 443,
    disableStats: true,
    encrypted: true,
});
```

請注意，Ably Echo 設定中參考了 `VITE_ABLY_PUBLIC_KEY` 環境變數。這個環境變數應為 Ably 的公開金鑰。公開金鑰就是 Ably 金鑰中出現在 `:` 字元之前的部分。

取消註解並依照需求調整好 Echo 設定後，就可以編譯專案素材：

```shell
npm run dev
```

> **Note** 要瞭解更多有關編譯應用程式 JavaScript 素材的資訊，請參考 [Vite](/docs/{{version}}/vite) 中的說明文件。

<a name="concept-overview"></a>

## 概念概覽

Laravel 的事件廣播功能能讓你以基於 Driver 的方法來將伺服器端的 Laravel 事件通過 WebSockets 廣播到用戶端 JavaScript 上。目前，Laravel 隨附了 [Pusher Channels](https://pusher.com/channels) 與 [Ably](https://ably.com) 兩個 Driver。可以在用戶端使用 [Laravel Echo](#client-side-installation) JavaScript 套件來輕鬆取得事件。

事件是通過「頻道 (Channel)」進行廣播的，頻道可以被設為公共或私有。任何網站的瀏覽者都可以在不登入或經過授權的情況下訂閱公開頻道。不過，如果要訂閱私有頻道，就必須要登入並經過授權才可以監聽該頻道。

> **Note** 若想瞭解更多 Pusher 的開放原始碼替代品，請參考[開放原始碼替代](#open-source-alternatives)一節。

<a name="using-example-application"></a>

### 使用範例專案

在深入探討事件廣播的各個元件之前，我們先來用網路商店當作例子，以高階的角度來個概覽。

在我們的專案中，先來假設有個能讓使用者檢視訂單配送狀態的頁面。另外，也假設當網站處理到配送狀態更新的時候會觸發 `OrderShipmentStatusUpdated` 事件：

    use App\Events\OrderShipmentStatusUpdated;
    
    OrderShipmentStatusUpdated::dispatch($order);

<a name="the-shouldbroadcast-interface"></a>

#### `ShouldBroadcast` 介面

我們並不希望使用者在檢視某個訂單的時候還需要重新整理整個頁面才能看到狀態更新；我們希望在訂單更新建立的時候就能廣播給專案。因此，我們需要將 `OrderShipmentStatusUpdated` 事件標上 `ShouldBroadcast` 介面。通過加上該介面，就能告訴 Laravel 要在該事件被觸發時將其廣播出去：

    <?php
    
    namespace App\Events;
    
    use App\Models\Order;
    use Illuminate\Broadcasting\Channel;
    use Illuminate\Broadcasting\InteractsWithSockets;
    use Illuminate\Broadcasting\PresenceChannel;
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

`ShouldBroadcast` 介面需要我們在事件中定義一個 `broadcastOn` 方法。這個方法需要回傳該事件廣播的頻道。產生的事件類別當中已經棒我們加上了一個空白的 Stub，因此我們只需要填寫詳情就好了。我們只希望建立該訂單的使用者檢視狀態更新，因此我們會將事件放在該訂單的私有頻道上廣播：

    use Illuminate\Broadcasting\Channel;
    use Illuminate\Broadcasting\PrivateChannel;
    
    /**
     * Get the channel the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('orders.'.$this->order->id);
    }

若想要讓 Event 被 Broadcast 到多個 Channel，可以回傳一組 `array`：

    use Illuminate\Broadcasting\PrivateChannel;
    
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('orders.'.$this->order->id),
            // ...
        ];
    }

<a name="example-application-authorizing-channels"></a>

#### 授權頻道

請記得，使用者必須要經過授權才能監聽私有頻道。我們可以在 `routes/channels.php` 檔中定義頻道權限規則。在此例子中，我們需要認證嘗試監聽私有頻道 `orders.1` 的使用者是否為該訂單實際的建立人：

    use App\Models\Order;
    use App\Models\User;
    
    Broadcast::channel('orders.{orderId}', function (User $user, int $orderId) {
        return $user->id === Order::findOrNew($orderId)->user_id;
    });

`channel` 方法接收 2 個引數：頻道的名稱，以及會回傳 `true` 與 `false` 的回呼。這個回呼用來判斷使用者是否已授權監聽此頻道。

所有的授權回呼都會收到目前登入使用者作為其第一個引數，而接下來的引數則是其他額外的萬用字元參數。在這個例子中，我們使用了 `{orderId}` 預留位置來標示頻道名稱中的「ID」部分是萬用字元。

<a name="listening-for-event-broadcasts"></a>

#### 監聽事件廣播

接著，剩下的工作就是在 JavaScript 程式碼內監聽事件了。我們可以使用 [Laravel Echo](#client-side-installation)。首先，我們要先用 `private` 方法來監聽私有頻道。接著，可以監聽「`listen`」`OrderShipmentStatusUpdated` 事件。預設情況下，該事件的所有公共屬性都會被包含在廣播事件內：

```js
Echo.private(`orders.${orderId}`)
    .listen('OrderShipmentStatusUpdated', (e) => {
        console.log(e.order);
    });
```

<a name="defining-broadcast-events"></a>

## 定義廣播事件

為了告訴 Laravel 應廣播某個給定的事件，我們必須要在事件類別上實作 `Illuminate\Contracts\Broadcasting\ShouldBroadcast` 介面。在所有產生出來的事件類別上，框架已經幫你引入這個介面了，因此你可以輕鬆地將該介面加至任何事件上。

`ShouldBroadcast` 介面只要求實作單一方法：`broadcastOn`。`broadcastOn` 方法應回傳一個頻道，或是一個包含頻道的陣列。這些頻道是事件要進行廣播的頻道。頻道應為 `Channel`, `PrivateChannel` 或 `PresenceChannel` 的實體。`Channel` 的實體代表任何使用者都能監聽的公共頻道，而 `PrivateChannel` 與 `PresenceChannels` 代表需要進行[頻道授權](#authorizing-channels)的私有頻道：

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
         * Create a new event instance.
         */
        public function __construct(
            public User $user,
        ) {}
    
        /**
         * Get the channels the event should broadcast on.
         *
         * @return array<int, \Illuminate\Broadcasting\Channel>
         */
        public function broadcastOn(): array
        {
            return [
                new PrivateChannel('user.'.$this->user->id),
            ];
        }
    }

實作完 `ShouldBroadcast` 介面後，只需要像平常一樣[觸發事件](/docs/{{version}}/events)即可。事件被觸發後，[佇列任務](/docs/{{version}}/queues)會自動通過指定的 Broadcast Driver 來廣播事件。

<a name="broadcast-name"></a>

### Broadcast 名稱

預設情況下，Laravel 會使用事件的類別名來進行廣播。不過，也可以在事件上定義 `broadcastAs` 方法來自訂 Broadcast 名稱：

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'server.created';
    }

若使用 `broadcastAs` 方法來自訂 Broadcast 名稱，則應確保註冊監聽程式時有加上前置 `.` 字元。加上該前置字元可用來告訴 Echo 不要在事件前方加上專案的命名空間：

    .listen('.server.created', function (e) {
        ....
    });

<a name="broadcast-data"></a>

### Broadcast 資料

廣播事件時，事件所有的 `public` 屬性都會被自動序列化，並作為事件的 Payload 進行廣播，讓你能在 JavaScript 程式碼中存取事件的所有公共資料。因此，舉例來說，假設我們的事件有一個 public `$user` 屬性，其中包含了 Eloquent Model，那麼事件的 Broadcast Payload 會是：

```json
{
    "user": {
        "id": 1,
        "name": "Patrick Stewart"
        ...
    }
}
```

不過，若想對 Broadcast Payload 進一步地控制，可以在事件內加上一個 `broadcastWith` 方法。這個方法應回傳一個陣列，包含要作為事件 Payload 使用的資料：

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return ['id' => $this->user->id];
    }

<a name="broadcast-queue"></a>

### Broadcast 佇列

預設情況下，所有的廣播事件都會使用 `queue.php` 設定檔中的預設佇列連連。可以通過在事件類別內定義 `queue` 屬性來自訂 Broadcaster 要使用的佇列連線名稱：

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

或者，你也可以通過在事件中定義 `broadcastQueue` 方法來自訂佇列名稱：

    /**
     * The name of the queue on which to place the broadcasting job.
     */
    public function broadcastQueue(): string
    {
        return 'default';
    }

若像使用 `sync` 佇列來代替預設的佇列 Driver，可以使用 `ShouldBroadcastNow` 來代替 `ShouldBroadcast` 進行實作：

    <?php
    
    use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
    
    class OrderShipmentStatusUpdated implements ShouldBroadcastNow
    {
        // ...
    }

<a name="broadcast-conditions"></a>

### Broadcast 條件

有時候我們可能只想在滿足給定條件的時候才廣播事件。可以通過在事件類別上新增 `broadcastWhen` 方法來在其中定義這些條件：

    /**
     * Determine if this event should broadcast.
     */
    public function broadcastWhen(): bool
    {
        return $this->order->value > 100;
    }

<a name="broadcasting-and-database-transactions"></a>

#### Broadcast 與資料庫 Transaction

當廣播事件是在資料庫 Transaction 內分派的時候，這個事件可能會在資料庫 Transaction 被 Commit 前被佇列進行處理了。發生這種情況時，在資料庫 Transaction 期間對 Model 或資料庫記錄所做出的更新可能都還未反應到資料庫內。另外，所有在 Transaction 期間新增的 Model 或資料庫記錄也可能還未出現在資料庫內。若事件有依賴這些 Model 的話，在處理廣播事件的任務時可能會出現未預期的錯誤。

若佇列連線的 `after_commit` 設定選項是 `false`，那麼就可以通過在事件類別上定義 `$afterCommit` 屬性來標示出特定的廣播事件應在資料庫 Transaction 被 Commit 後才可進行分派：

    <?php
    
    namespace App\Events;
    
    use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
    use Illuminate\Queue\SerializesModels;
    
    class ServerCreated implements ShouldBroadcast
    {
        use SerializesModels;
    
        public $afterCommit = true;
    }

> **Note** 要瞭解更多有關這類問題的解決方法，請參考有關[佇列任務與資料庫 Transaction](/docs/{{version}}/queues#jobs-and-database-transactions) 有關的說明文件。

<a name="authorizing-channels"></a>

## 授權頻道

使用私有頻道，則需要將目前已登入的使用者授權為可監聽該頻道。要授權使用者，需要向 Laravel 端傳送一個包含頻道名稱的 HTTP 請求來讓網站判斷使用者能否監聽該頻道。使用 [Laravel Echo](#client-side-installation) 時，會自動建立用於授權訂閱私有頻道的 HTTP 請求。不過，我們還是需要定義適當的路由來回應這些請求。

<a name="defining-authorization-routes"></a>

### 定義授權路由

好佳在，在 Laravel 中定義回應頻道授權請求的路由非常容易。在 Laravel 中隨附的 `App\Providers\BroadcastServiceProvider` 內，可以看到一個 `Broadcast::routes` 方法的呼叫。這個方法會註冊 `/broadcasting/auth` 路由來處理授權請求：

    Broadcast::routes();

`Broadcast::routes` 方法會自動將其中的路由放置於 `web` Middleware 群組內。不過，若想自訂指派的屬性，也可以傳入包含路由屬性的陣列：

    Broadcast::routes($attributes);

<a name="customizing-the-authorization-endpoint"></a>

#### 自訂授權 Endpoint

預設情況下，Echo 會使用 `/broadcasting/auth` Endpoint 來授權頻道存取。不過，也可以通過將 `authEndpoint` 設定選項傳給 Echo 實體來指定你自己的授權 Endpoint：

```js
window.Echo = new Echo({
    broadcaster: 'pusher',
    // ...
    authEndpoint: '/custom/endpoint/auth'
});
```

<a name="customizing-the-authorization-request"></a>

#### 自訂授權 Request

我們可以自訂 Laravel Echo 要如何執行授權請求。只需要在初始化 Echo 時提供一個自訂授權程式即可：

```js
window.Echo = new Echo({
    // ...
    authorizer: (channel, options) => {
        return {
            authorize: (socketId, callback) => {
                axios.post('/api/broadcasting/auth', {
                    socket_id: socketId,
                    channel_name: channel.name
                })
                .then(response => {
                    callback(null, response.data);
                })
                .catch(error => {
                    callback(error);
                });
            }
        };
    },
})
```

<a name="defining-authorization-callbacks"></a>

### 定義授權回呼

接著，我們需要定義實際上用來判斷目前登入使用者是否能監聽給定頻道的邏輯。這個定義放在專案內`routes/channels.php` 檔案中。在這個檔案中，可以使用 `Broadcast::channel` 方法來註冊頻道授權回呼：

    use App\Models\User;
    
    Broadcast::channel('orders.{orderId}', function (User $user, int $orderId) {
        return $user->id === Order::findOrNew($orderId)->user_id;
    });

`channel` 方法接收 2 個引數：頻道的名稱，以及會回傳 `true` 與 `false` 的回呼。這個回呼用來判斷使用者是否已授權監聽此頻道。

所有的授權回呼都會收到目前登入使用者作為其第一個引數，而接下來的引數則是其他額外的萬用字元參數。在這個例子中，我們使用了 `{orderId}` 預留位置來標示頻道名稱中的「ID」部分是萬用字元。

<a name="authorization-callback-model-binding"></a>

#### 授權回呼的 Model

就像 HTTP 路由一樣，頻道路由也能使用顯式或隱式[路由 Model ](/docs/{{version}}/routing#route-model-binding)的功能。舉例來說，可以不接收字串或數字的 Order ID，而要求實際的 `Order` Model 實體：

    use App\Models\Order;
    use App\Models\User;
    
    Broadcast::channel('orders.{order}', function (User $user, Order $order) {
        return $user->id === $order->user_id;
    });

> **Warning** 與 HTTP 路由 Model 綁定不同，頻道的 Model 綁定不支援自動[為隱式 Model 綁定加上作用域]。不過，通常來說這不會造成問題，因為大部分的頻道都可以被放置與單一 Model 的獨立主鍵作用域內。

<a name="authorization-callback-authentication"></a>

#### 授權回呼認證

私有與 Presence 廣播頻道會通過專案預設的認證 Guard 來認證目前的使用者。若使用者未登入，則頻道認證會自動拒絕，且授權回呼永遠不會被執行。不過，若有需要，也可以指定多個自訂 Guard 來認證連入請求：

    Broadcast::channel('channel', function () {
        // ...
    }, ['guards' => ['web', 'admin']]);

<a name="defining-channel-classes"></a>

### 定義 Channel 類別

若你的專案會使用到許多不同的頻道，則 `routes/channels.php` 可能會變得很肥大。因此，比起使用閉包來授權頻道，我們可以改用頻道類別。要建立頻道類別，請使用 `make:channel` Artisan 指令。這個指令會在 `app/Broadcasting` 目錄內放置一個新的頻道類別。

```shell
php artisan make:channel OrderChannel
```

接著，在 `routes/channels.php` 檔案內註冊頻道：

    use App\Broadcasting\OrderChannel;
    
    Broadcast::channel('orders.{order}', OrderChannel::class);

最後，可以將頻道的授權邏輯放在頻道類別的 `join` 方法內。這個 `join` 方法用來放置與平常放在頻道授權閉包相同的邏輯。也可以使用頻道 Model 綁定：

    <?php
    
    namespace App\Broadcasting;
    
    use App\Models\Order;
    use App\Models\User;
    
    class OrderChannel
    {
        /**
         * Create a new channel instance.
         */
        public function __construct()
        {
            // ...
        }
    
        /**
         * Authenticate the user's access to the channel.
         */
        public function join(User $user, Order $order): array|bool
        {
            return $user->id === $order->user_id;
        }
    }

> **Note** 與 Laravel 內其他類別一樣，頻道類別也會自動由 [Service Container](/docs/{{version}}/container) 解析。因此，我們可以在頻道的建構函式上對任何所需要的依賴進行型別提示。

<a name="broadcasting-events"></a>

## 廣播事件

定義好事件並將其以 `ShouldBroadcast` 介面進行標示後，我們只需要使用 dispatch 方法來觸發事件即可。事件觸發程式會注意到這個事件有被標註為 `ShouldBroadcast` 介面，並將事件放入佇列以進行廣播：

    use App\Events\OrderShipmentStatusUpdated;
    
    OrderShipmentStatusUpdated::dispatch($order);

<a name="only-to-others"></a>

### 僅限其他

在建立一個有使用到事件廣播的專案時，我們可能會需要將某個事件廣播給除了目前使用者以外的所有頻道訂閱者。可以通過 `broadcast` 輔助函式以及 `toOthers` 方法來完成：

    use App\Events\OrderShipmentStatusUpdated;
    
    broadcast(new OrderShipmentStatusUpdated($update))->toOthers();

為了幫助你更容易理解什麼時候會需要用到 `toOthers` 方法，我們來假設有個任務清單 App。在這個 App 中，使用者可以輸入任務名稱來新增任務。為了建立任務，這個 App 可能會向 `/task` URL 發起一個請求，該請求會將任務的建立廣播出去，並回傳代表新任務的 JSON。當 JavaScript 端從這個 End-point 收到回覆後，就可以直接將新任務插入到任務清單內。像這樣：

```js
axios.post('/task', task)
    .then((response) => {
        this.tasks.push(response.data);
    });
```

不過，提醒一下，我們也會將任務的建立廣播出去。如果 JavaScript 端也會監聽這個事件來將任務新增到任務清單上，那麼列表上就會有重複的任務：一個是從 End-point 回傳回來的，另一個則是從監聽事件來的。我們可以通過使用 `toOthers` 方法來告訴廣播程式不要將該事件廣播給目前的使用者。

> **Warning** 若要呼叫 `toOthers` 方法，該事件必須要 use `Illuminate\Broadcasting\InteractsWithSockets` Trait。

<a name="only-to-others-configuration"></a>

#### 設定

初始化 Laravel Echo 實體時，會指派一個 Socket ID 給這個連線。若是使用全域的 [Axios](https://github.com/mzabriskie/axios) 實體來在 JavaScript 端建立 HTTP 連線，則 Socket ID 會以 `X-Socket-ID` 標頭被自動附加到每個外連請求上。接著，當呼叫 `toOthers` 方法時，Laravel 會從標頭內拆出這個 Socket ID，並告知廣播程式不要廣播給有該 Socket ID 的連線。

若你未使用全域 Axios 實體，則需要手動設定 JavaScript 端來在所有外連請求上傳送 `X-Socket-ID` 標頭。可以通過 `Echo.socketId` 方法來取得 Socket ID：

```js
var socketId = Echo.socketId();
```

<a name="customizing-the-connection"></a>

### 自訂連線

若你的專案與許多不同的廣播連線互動時，如果我們想使用預設廣播程式以外的特定廣播程式來廣播事件，則可以使用 `via` 方法來指定要將事件推送給哪個連線：

    use App\Events\OrderShipmentStatusUpdated;
    
    broadcast(new OrderShipmentStatusUpdated($update))->via('pusher');

或者，也可以通過在事件的建構函式 (Constructor) 內呼叫 `broadcastVia` 方法來指定事件的廣播連線。不過，這麼做的時候，請先確保這個事件類別有使用 `InteractsWithBroadcasting` Trait：

    <?php
    
    namespace App\Events;
    
    use Illuminate\Broadcasting\Channel;
    use Illuminate\Broadcasting\InteractsWithBroadcasting;
    use Illuminate\Broadcasting\InteractsWithSockets;
    use Illuminate\Broadcasting\PresenceChannel;
    use Illuminate\Broadcasting\PrivateChannel;
    use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
    use Illuminate\Queue\SerializesModels;
    
    class OrderShipmentStatusUpdated implements ShouldBroadcast
    {
        use InteractsWithBroadcasting;
    
        /**
         * Create a new event instance.
         */
        public function __construct()
        {
            $this->broadcastVia('pusher');
        }
    }

<a name="receiving-broadcasts"></a>

## 接收廣播

<a name="listening-for-events"></a>

### 監聽事件

[安裝並設定好 Laravel Echo](#client-side-installation) 後，就可以開始監聽來自 Laravel 端廣播的事件了。首先，使用 `channel` 方法來取得頻道的實體，然後呼叫 `listen` 方法來監聽某個特定的事件：

```js
Echo.channel(`orders.${this.order.id}`)
    .listen('OrderShipmentStatusUpdated', (e) => {
        console.log(e.order.name);
    });
```

若想監聽私有頻道，可使用 `private` 方法來代替。可以繼續在 `listen` 方法後方串上其他的呼叫來在單一頻道上監聽多個事件：

```js
Echo.private(`orders.${this.order.id}`)
    .listen(/* ... */)
    .listen(/* ... */)
    .listen(/* ... */);
```

<a name="stop-listening-for-events"></a>

#### 停止監聽事件

若想在不[離開頻道](#leaving-a-channel)的情況下停止監聽給定的事件，可以使用 `stopListening` 方法：

```js
Echo.private(`orders.${this.order.id}`)
    .stopListening('OrderShipmentStatusUpdated')
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

### 命名空間 (Namespace)

你可能已經注意到，我們並沒有為事件類別指定完整的 `App\Events` 命名空間。這是因為，Echo 會自動假設事件都放在 `App\Events` 命名空間下。不過，我們可以在初始化 Echo 時傳入 `namespace` 設定選項來設定要使用的根命名空間：

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
        // ...
    });
```

<a name="presence-channels"></a>

## Presence 頻道

Presence 頻道擁有私有頻道的安全性，且會提供該頻道的訂閱者等額外資訊。這樣一來便能輕鬆地建立強大的協作 App 功能，如提示目前使用者由其他人正在檢視相同頁面，或是列出聊天室中的使用者狀態。

<a name="authorizing-presence-channels"></a>

### 授權 Presence 頻道

所有的 Presence 頻道也同時是私有頻道。因此，使用者必須要[經過授權以存取頻道](#authorizing-channels)。不過，在為 Presence 頻道定義授權回呼時，若要授權使用者加入頻道，則不應回傳 `true`，而應回傳包含有關其他使用者資訊的陣列。

由授權回呼回傳的資料可以在 JavaScript 端中的 Presence 頻道事件監聽程式中使用。若使用者未被授權加入 Presence 頻道，則應回傳 `false` 或 `null`：

    use App\Models\User;
    
    Broadcast::channel('chat.{roomId}', function (User $user, int $roomId) {
        if ($user->canJoinRoom($roomId)) {
            return ['id' => $user->id, 'name' => $user->name];
        }
    });

<a name="joining-presence-channels"></a>

### 加入 Presence 頻道

若要加入 Presence 頻道，可以使用 Echo 的 `join` 方法。`join` 方法會與所暴露的 `listen` 方法一起回傳一個 `PresenceChannel` 的實作，這樣一來你就能訂閱 `here`, `joining` 以及 `leaving` 事件。

```js
Echo.join(`chat.${roomId}`)
    .here((users) => {
        // ...
    })
    .joining((user) => {
        console.log(user.name);
    })
    .leaving((user) => {
        console.log(user.name);
    })
    .error((error) => {
        console.error(error);
    });
```

`here` 回呼會在成功加入頻道後被立即執行，並會收到包含所有其他目前訂閱該頻道的使用者資訊。`joining` 方法會在有新使用者加入頻道時被執行，而 `leaving` 則會在有使用者離開時被執行。`error` 方法會在認證 Endpoint 回傳除了 200 以外的 HTTP 狀態時、或是解析回傳的 JSON 時有問題時被執行。

<a name="broadcasting-to-presence-channels"></a>

### 廣播至 Presence 頻道

Presence 頻道可以像公用或私有頻道一樣接收事件。以聊天室為例，我們可能像廣播 `NewMessage` 事件至聊天室的 Presence 頻道。為此，我們可以在事件的 `broadcastOn` 方法內回傳一個 `PresenceChannel` 的實體：

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('room.'.$this->message->room_id),
        ];
    }

與其他事件一樣，可以使用 `broadcast` 輔助函式與 `toOthers` 方法來排除目前使用者接收該 Broadcast：

    broadcast(new NewMessage($message));
    
    broadcast(new NewMessage($message))->toOthers();

與其他一般的事件一樣，也可以使用 Echo 的 `listen` 方法來監聽傳送到 Presence 頻道的事件：

```js
Echo.join(`chat.${roomId}`)
    .here(/* ... */)
    .joining(/* ... */)
    .leaving(/* ... */)
    .listen('NewMessage', (e) => {
        // ...
    });
```

<a name="model-broadcasting"></a>

## Model 廣播

> **Warning** 在進一步閱讀有關 Model 廣播的說明文件前，我們建議讀者先瞭解有關 Laravel 的 Model 廣播服務以及如何手動建立並監聽廣播時間的一般概念。

在專案的 [Eloquent Model](/docs/{{version}}/eloquent) 被建立、更新、或刪除時，我們常常會廣播事件。當然，我們可以手動[為 Eloquent Model 的狀態更改定義自訂事件](/docs/{{version}}/eloquent#events)並將這些事件標記為 `ShouldBroadcast` 來輕鬆達成：

不過，我們讓事件在專案中負責其他功能，那麼如果只建立一個用來廣播的事件就很麻煩。為了解決這個問題，再 Laravel 中，我們可以讓 Eloquent Model 自動將其狀態更改廣播出去：

要開始設定自動廣播，應在 Eloquent Model 上使用 `Illuminate\Database\Eloquent\BroadcastsEvents` Trait。此外，該 Model 應定義一個 `broadcastOn` 方法，並在其中回傳一組包含頻道名稱的陣列，以供 Model 事件廣播：

```php
<?php

namespace App\Models;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use BroadcastsEvents, HasFactory;

    /**
     * Get the user that the post belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the channels that model events should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model>
     */
    public function broadcastOn(string $event): array
    {
        return [$this, $this->user];
    }
}
```

再 Model 中包含該 Trait 並定義好廣播頻道後，當 Model 實體被建立、更新、刪除、軟刪除、或是取消軟刪除後自動廣播事件。

此外，讀者可能已經發現，`broadcastOn` 方法接收了一個字串的 `$event` 引述。這個引述包含了 Model 上所發生的事件，其值為 `created`, `updated`, `deleted`, `trashed`, 或 `restored`。只要檢查這個變數的值，就可以用來判斷對於特定事件要廣播道哪個頻道（若有的話）：

```php
/**
 * Get the channels that model events should broadcast on.
 *
 * @return array<string, array<int, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model>>
 */
public function broadcastOn(string $event): array
{
    return match ($event) {
        'deleted' => [],
        default => [$this, $this->user],
    };
}
```

<a name="customizing-model-broadcasting-event-creation"></a>

#### 自訂 Model 廣播的事件建立

有時候，我們可能會想自訂 Laravel 要如何建立 Model 廣播時使用的事件。為此，我們可以通過在 Eloquent Model 上定義一個 `newBroadcastableEvent` 來達成。該方法應回傳 `Illuminate\Database\Eloquent\BroadcastableModelEventOccurred` 實體：

```php
use Illuminate\Database\Eloquent\BroadcastableModelEventOccurred;

/**
 * Create a new broadcastable model event for the model.
 */
protected function newBroadcastableEvent(string $event): BroadcastableModelEventOccurred
{
    return (new BroadcastableModelEventOccurred(
        $this, $event
    ))->dontBroadcastToCurrentUser();
}
```

<a name="model-broadcasting-conventions"></a>

### Model 廣播慣例

<a name="model-broadcasting-channel-conventions"></a>

#### 頻道慣例

讀者可能已經發現，在上方的 Model 範例中，`broadcastOn` 方法並沒有回傳 `Channel` 實體，而是直接回傳 Eloquent Model。若 Model 的 `broadcastOn` 方法回傳的是 Model 實體 (或是包含 Model 實體的陣列)，則 Laravel 會使用該 Model 的類別名稱與主索引鍵識別元作為頻道名稱，自動為該 Model 初始化一個私人頻道。

因此，`id` 為 `1` 的 `App\Models\User` Model 會被轉換為一個名稱是 `App.Models.User.1` 的 `Illuminate\Broadcasting\PrivateChannel` 實體。當然，除了從 Model 的 `broadcastOn` 方法內回傳 Eloquent Model 實體外，也可以回傳一個完整的 `Channel` 實體來取得對 Model 的頻道名稱的完整控制權：

```php
use Illuminate\Broadcasting\PrivateChannel;

/**
 * Get the channels that model events should broadcast on.
 *
 * @return array<int, \Illuminate\Broadcasting\Channel>
 */
public function broadcastOn(string $event): array
{
    return [
        new PrivateChannel('user.'.$this->id)
    ];
}
```

若有打算要從 Model 的 `broadcastOn` 方法內明顯回傳頻道實體，則可以將 Eloquent Model 實體傳入該頻道的建構函式。這樣一來，Laravel 就可以通過剛才提到的 Model 頻道慣例來將 Eloquent Model 轉換為頻道名稱字串：

```php
return [new Channel($this->user)];
```

若想判斷某個 Model 的頻道名稱，可以在任何 Model 實體上呼叫 `broadcastChannel` 方法。舉例來說，對於一個 `id` 為 `1` 的 `App\Models\User` Model，該方法會回傳一個字串 `App.Models.User.1`：

```php
$user->broadcastChannel()
```

<a name="model-broadcasting-event-conventions"></a>

#### 事件慣例

由於 Model 廣播事件並不與專案的 `App\Events` 目錄內的「真實」事件有關，這些事件只會依據慣例來指派名稱與 Payload (裝載)。Laravel 的慣例就是使用 Model 的類別名稱 (不含 Namespace) 與觸發廣播的 Model 事件來廣播。

因此，對 `App\Models\Post` Model 進行更新，會將 `PostUpdated` 事件與下列 Payload 廣播到用戶端：

```json
{
    "model": {
        "id": 1,
        "title": "My first post"
        ...
    },
    ...
    "socket": "someSocketId",
}
```

刪除 `App\Models\Post` Model 時廣播的事件名稱會是 `UserDeleted`。

若有需要，也可以通過在 Model 中新增一個 `broadcastAs` 與 `broadcastWith` 方法來自訂廣播的名稱與 Payload。這些方法會收到目前發生的 Model 事件或動作，好讓我們能為不同的 Model 動作自訂事件名稱與 Payload。若在 `broadcastAs` 方法中回傳 `null`，則 Laravel 會使用上方討論過的 Model 廣播事件名稱的慣例來廣播這個事件：

```php
/**
 * The model event's broadcast name.
 */
public function broadcastAs(string $event): string|null
{
    return match ($event) {
        'created' => 'post.created',
        default => null,
    };
}

/**
 * Get the data to broadcast for the model.
 *
 * @return array<string, mixed>
 */
public function broadcastWith(string $event): array
{
    return match ($event) {
        'created' => ['title' => $this->title],
        default => ['model' => $this],
    };
}
```

<a name="listening-for-model-broadcasts"></a>

### 監聽 Model 廣播

在 Model 中新增好 `BroadcastsEvents` Trait 並定義好 Model 的 `broadcastOn` 方法後，就可以開始在用戶端中監聽廣播出來的 Model 事件了。在開始前，建議你先閱讀有關[監聽事件](#listening-for-events)的完整說明文件。

首先，使用 `private` 方法來取得 Channel 實體，然後呼叫 `listen` 方法來監聽特定的事件。一般來說，傳給 `private` 方法的頻道名稱應與 Laravel 的 [Model 廣播慣例](#model-broadcasting-conventions)相對應。

取得 Channel 實體後，就可以使用 `listen` 方法來監聽特定的事件。由於 Model 廣播事件並不與專案中 `App\Events` 目錄下的「真實事件」互相關聯，因此，[事件名稱](#model-broadcasting-event-conventions)前應加上一個 `.` 字元，以標識其不屬於特定的命名空間。每個 Model 廣播事件都有一個 `model` 屬性，其中包含了 Model 中所有可廣播的屬性：

```js
Echo.private(`App.Models.User.${this.user.id}`)
    .listen('.PostUpdated', (e) => {
        console.log(e.model);
    });
```

<a name="client-events"></a>

## 用戶端事件

> **Note** 在使用 [Pusher Channels](https://pusher.com/channels) 時，可以在 [Application Dashboard](https://dashboard.pusher.com/) 內啟用「App Settings」中的「Client Event」，以傳送用戶端事件。

有時候我們可能會想將事件直接廣播給其他連線的用戶端，而不經由 Laravel 端。特別像是如顯示「正在輸入」等通知時，我們只是想告訴使用者網站內的其他使用者正在特定畫面上輸入。

若要廣播用戶端事件，可以使用 Echo 的 `whisper` 方法：

```js
Echo.private(`chat.${roomId}`)
    .whisper('typing', {
        name: this.user.name
    });
```

若要監聽用戶端事件，可以使用 `listenForWhisper` 方法：

```js
Echo.private(`chat.${roomId}`)
    .listenForWhisper('typing', (e) => {
        console.log(e.name);
    });
```

<a name="notifications"></a>

## 通知

只要將事件廣播與 [通知](/docs/{{version}}/notifications) 一起使用，JavaScript 端就可以在不重新整理的情況下接收新通知。在開始前，請先閱讀有關[廣播通知頻道](/docs/{{version}}/notifications#broadcast-notifications)的說明文件。

設定讓通知使用廣播頻道後，就可以使用 Echo 的 `notification` 方法來接收廣播。請記住，頻道的名稱應與接收通知的使用者類別名稱相符：

```js
Echo.private(`App.Models.User.${userId}`)
    .notification((notification) => {
        console.log(notification.type);
    });
```

在此範例中，所有通過 `broadcast` 頻道傳送給 `App\Models\User` 實體的通知都會被該回呼收到。用於 `App.Models.User.{id}` 的頻道授權回呼包含在 Laravel 框架附帶的 `BroadcastServiceProvider` 內。
