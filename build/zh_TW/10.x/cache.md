---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/21/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:17:00Z'
---

# 快取

- [簡介](#introduction)
- [設定](#configuration)
   - [Driver 前置需求](#driver-prerequisites)
- [使用 Cache](#cache-usage)
   - [取得 Cache 實體](#obtaining-a-cache-instance)
   - [從 Cache 中取得項目](#retrieving-items-from-the-cache)
   - [在 Cache 內儲存項目](#storing-items-in-the-cache)
   - [從 Cache 內移除項目](#removing-items-from-the-cache)
   - [Cache 輔助函式](#the-cache-helper)
- [Atomic Lock](#atomic-locks)
   - [Driver 前置需求](#lock-driver-prerequisites)
   - [管理 Lock](#managing-locks)
   - [在不同處理程序間管理 Lock](#managing-locks-across-processes)
- [新增自訂的 Cache Driver](#adding-custom-cache-drivers)
   - [撰寫 Driver](#writing-the-driver)
   - [註冊 Driver](#registering-the-driver)
- [事件](#events)

<a name="introduction"></a>

## 簡介

有些取得資料或處理任務的過程可能很消耗 CPU、或是需要數秒鐘來完成。這種時候，我們通常會將取得的資料快取住一段時間，這樣一來在接下來的請求上就能快速存取相同的資料。快取的資料通常會初存在一些非常快速的資料儲存上，如 [Memcached](https://memcached.org) 或 [Redis](https://redis.io)。

所幸，Laravel 為多種快取後端提供了一個表達性、統一的 API，可以享受快取提供的快速資料存取，並加速你的網站。

<a name="configuration"></a>

## 設定

快取設定檔位於 `config/cache.php`。在這個檔案中，可以指定專案中預設要使用哪個快取 Driver。Laravel 內建支援像是 [Memcached](https://memcached.org), [Redis](https://redis.io), [DynamoDB](https://aws.amazon.com/dynamodb) 以及關聯式資料庫等多種熱門的快取後端。此外，也可以使用基於檔案的快取 Driver，而 `array` 與「null」Driver 則為自動化測試提供方便的快取後端。

快取設定檔也包含了其他數種選項，並在該設定檔中包含了說明文件。請確保有先閱讀這些選項。預設情況下，Laravel 設定使用 `file` 快取 Driver，在伺服器的檔案系統上儲存經過序列化的快取物件。對於大型的專案，建議使用如 Memcached 或 Redis 等更專門的快取 Driver。甚至也可以為相同的 Driver 設定多個快取設定。

<a name="driver-prerequisites"></a>

### Driver 需求

<a name="prerequisites-database"></a>

#### 資料庫

在使用 `database` 快取 Driver 時，需要先設定包含快取項目的資料表。該資料表的 `Schema` 宣告範例如下：

    Schema::create('cache', function (Blueprint $table) {
        $table->string('key')->unique();
        $table->text('value');
        $table->integer('expiration');
    });

> **Note** 可以使用 `php artisan cache:table` Artisan 指令來產生包含正確 Schema 的 Migration。

<a name="memcached"></a>

#### Memcached

要使用 Memcached Driver 需要安裝 [Memcached PECL 套件](https://pecl.php.net/package/memcached)。可以在 `config/cache.php` 設定檔中列出所有的 Memcached 伺服器。這個檔案已預先包含了 `memcached.servers` 欄位來讓你開始使用：

    'memcached' => [
        'servers' => [
            [
                'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                'port' => env('MEMCACHED_PORT', 11211),
                'weight' => 100,
            ],
        ],
    ],

若有需要，可以將 `host` 選項設為 UNIX Socket 路徑。若設定為 UNIX Socket，則 `port` 選項應設為 `0`：

    'memcached' => [
        [
            'host' => '/var/run/memcached/memcached.sock',
            'port' => 0,
            'weight' => 100
        ],
    ],

<a name="redis"></a>

#### Redis

在 Laravel 內使用 Redis 快取前，必須先通過 PECL 安裝 PhpRedis PHP 擴充套件，或是通過 Composer 安裝 `predis/predis` 套件 (~1.0)。[Laravel Sail](/docs/{{version}}/sail) 已內建了該擴充套件。此外，官方的 Laravel 部署平台，如 [Laravel Forge](https://forge.laravel.com) 與 [Laravel Vapor](https://vapor.laravel.com)，都已預設安裝了 PhpRedis 擴充套件。

更多有關設定 Redis 的資訊，請參考 [Laravel 說明文件頁面](/docs/{{version}}/redis#configuration)。

<a name="dynamodb"></a>

#### DynamoDB

在開始使用 [DynamoDB](https://aws.amazon.com/dynamodb) 快取 Driver 前，必須先建立 DynamoDB 資料表以儲存所有的快取資料。通常來說，這個資料表應命名為 `cache`。不過，應依照專案的 `cache` 設定檔中的 `stores.dynamodb.table` 設定值來設定這個資料表的名稱。

該資料表也應擁有一個字串 Partition Key，其名稱應對應專案的 `cache` 設定檔的 `stores.dynamodb.attributes.key` 設定值。預設情況下，該 Partition Key 應命名為 `key`。

<a name="cache-usage"></a>

## 使用快取

<a name="obtaining-a-cache-instance"></a>

### 取得 Cache 實體

若要取得快取儲存的實體，可以使用 `Cache` Facade。我們在這篇說明文件中都會使用該 Facade。`Cache` Facade 提供了一個方便簡潔的方式來存取 Laravel 快取 Contract 底層的實作：

    <?php
    
    namespace App\Http\Controllers;
    
    use Illuminate\Support\Facades\Cache;
    
    class UserController extends Controller
    {
        /**
         * Show a list of all users of the application.
         */
        public function index(): array
        {
            $value = Cache::get('key');
    
            return [
                // ...
            ];
        }
    }

<a name="accessing-multiple-cache-stores"></a>

#### 存取多個快取儲存

使用 `Cache` Facade，即可通過 `store` 方法來存取多個快取儲存。傳入給 `store` 方法的索引鍵應對應於列在 `cache` 設定檔中 `stores` 設定的索引鍵名稱：

    $value = Cache::store('file')->get('foo');
    
    Cache::store('redis')->put('bar', 'baz', 600); // 10 Minutes

<a name="retrieving-items-from-the-cache"></a>

### 自快取內取得項目

`Cache` Facade 的 `get` 方法是用來從快取內取得資料的。若該項目不存在於快取內，則會回傳 `null`。若有需要，可以傳入第二個引數給 `get` 來指定項目不存在時要回傳什麼預設值：

    $value = Cache::get('key');
    
    $value = Cache::get('key', 'default');

也可以傳入一個閉包來作為預設值。若指定項目不存在於快取內，則該閉包的結果會被回傳。傳入閉包可讓你暫緩從資料庫或其他外部服務取得預設值的過程：

    $value = Cache::get('key', function () {
        return DB::table(/* ... */)->get();
    });

<a name="checking-for-item-existence"></a>

#### 檢查項目是否存在

`has` 方法可以用來判斷某個項目是否存在於快取內。該方法也會在項目存在，但其值為 `null` 時回傳 `false`：

    if (Cache::has('key')) {
        // ...
    }

<a name="incrementing-decrementing-values"></a>

#### 遞增或遞減值

`increment`（遞增）與 `decrement`（遞減）方法可以用來調整快取中的整數項目值。這兩個方法都接收一個可選的第二個引數來判斷項目值所要遞增或遞減的值：

    // 若該值不存在，則進行初始化...
    Cache::add('key', 0, now()->addHours(4));
    
    // 遞增或遞減值...
    Cache::increment('key');
    Cache::increment('key', $amount);
    Cache::decrement('key');
    Cache::decrement('key', $amount);

<a name="retrieve-store"></a>

#### 取得與儲存

有時候，我們可能會想要從快取內取得項目，但也想在項目不存在的時候設定預設值。舉例來說，我們可能想從快取內取得所有的使用者，但若快取不存在，則從資料庫內取得所有使用者，並存入快取。可以使用 `Cache::remember` 方法：

    $value = Cache::remember('users', $seconds, function () {
        return DB::table('users')->get();
    });

若該項目不存在於快取內，則傳入 `remember` 的閉包會被執行，並將其結果放入快取內。

可以使用 `rememberForever` 方法來從快取內取得項目，並在項目不存在時將其永久保存在快取內：

    $value = Cache::rememberForever('users', function () {
        return DB::table('users')->get();
    });

<a name="retrieve-delete"></a>

#### 取得或刪除

若有需要從快取內取得並同時刪除項目，則可以使用 `pull` 方法。與 `get` 方法類似，當項目不存在於快取內時，會回傳 `null`：

    $value = Cache::pull('key');

<a name="storing-items-in-the-cache"></a>

### 將項目存入快取

可以使用 `Cache` Facade 上的 `put` 方法來將項目存入快取：

    Cache::put('key', 'value', $seconds = 10);

若未傳入儲存時間給 `put` 方法，則該項目將被永久儲存：

    Cache::put('key', 'value');

除了將秒數作為整數傳入，也可以傳入一個 `DateTime` 實體來代表指定的快取項目過期時間：

    Cache::put('key', 'value', now()->addMinutes(10));

<a name="store-if-not-present"></a>

#### 當不存在時儲存

`add` 方法會只在項目不存在於快取儲存內時將項目加進快取內。該方法會在項目有真正被加進快取後回傳 `true`。否則，該方法會回傳 `false`。`add` 方法是一個不可部分完成的操作（Atomic）：

    Cache::add('key', 'value', $seconds);

<a name="storing-items-forever"></a>

#### 永久儲存項目

`forever` 方法可用來將項目永久儲存於快取。由於這些項目永遠不會過期，因此這些項目必須手動使用 `forget` 方法來移除：

    Cache::forever('key', 'value');

> **Note** 若使用 Memcached Driver，使用「forever」儲存的項目可能會在快取達到大小限制時被移除。

<a name="removing-items-from-the-cache"></a>

### 從快取內取得項目

可以使用 `forget` 方法來自快取內移除項目：

    Cache::forget('key');

也可以提供 0 或負數的過期時間來移除項目：

    Cache::put('key', 'value', 0);
    
    Cache::put('key', 'value', -5);

可以使用 `flush` 方法來移除整個快取：

    Cache::flush();

> **Warning** 使用 Flush 移除快取並不理會所設定的快取「^[Prefix](前置詞)」，會將快取內所有的項目都移除。當快取有與其他應用程式共用時，在清除快取前請三思。

<a name="the-cache-helper"></a>

### Cache 輔助函式

除了使用 `Cache` Facade，也可以使用全域的 `cache` 函式來自快取內取得與儲存資料。當使用單一的字串引數呼叫 `cache` 方法時，會回傳給定索引鍵的值：

    $value = cache('key');

若傳入一組索引鍵／值配對的陣列以及一個過期時間給該函式，則會將數值初存在快取內一段給定的期間：

    cache(['key' => 'value'], $seconds);
    
    cache(['key' => 'value'], now()->addMinutes(10));

當 `cache` 方法被呼叫，但未傳入任何引數時，會回傳 `Illuminate\Contracts\Cache\Factory` 實作的實體，可以讓你呼叫其他快取方法：

    cache()->remember('users', $seconds, function () {
        return DB::table('users')->get();
    });

> **Note** 在測試呼叫全域的 `cache` 函式時，可以像在[測試 Facade](/docs/{{version}}/mocking#mocking-facades)一樣，使用 `Cache::shouldReceive` 方法。

<a name="atomic-locks"></a>

## Atomic Lock (不可部分完成的鎖定)

> **Warning** 若要使用此功能，則應用程式必須要使用 `memcached`, `redis`, `dynamodb`, `database`, `file` 或 `array` 作為應用程式的預設快取 Driver。另外，所有的伺服器也都必須要連線至相同的中央快取伺服器。

<a name="lock-driver-prerequisites"></a>

### Driver 需求

<a name="atomic-locks-prerequisites-database"></a>

#### 資料庫

在使用 `database` 快取 Driver 時，需要設定包含專案快取 Lock 的資料表。下列為範例的資料表 `Schema` 宣告：

    Schema::create('cache_locks', function (Blueprint $table) {
        $table->string('key')->primary();
        $table->string('owner');
        $table->integer('expiration');
    });

> **Note** 使用 `cache:table` Artisan 指令來建立資料庫 Driver 的快取資料表時，該指令所建立的 Migration 中已包含了 `cache_locks` 資料表的定義。

<a name="managing-locks"></a>

### 管理 Lock

使用 Atomic Lock (不可部分完成鎖定)，在操作與分配 Lock 時即可不需理會競爭條件 (Race Condition)。舉例來說，[Laravel Forge](https://forge.laravel.com) 使用 Atomic Lock 來確保在一台伺服器上一次只有一個遠端任務在執行。可以通過 `Cache::lock` 方法來建立與管理 Lock：

    use Illuminate\Support\Facades\Cache;
    
    $lock = Cache::lock('foo', 10);
    
    if ($lock->get()) {
        // Lock acquired for 10 seconds...
    
        $lock->release();
    }

`get` 方法也接收一個閉包。在該閉包執行後，Laravel 會自動釋放 Lock：

    Cache::lock('foo', 10)->get(function () {
        // 取得 10 秒的 Lock，然後自動釋放...
    });

若在要求時無法取得 Lock，則可以告訴 Laravel 要等待多少秒的事件。若在指定的時間限制後仍無法取得 Lock，則會擲回 `Illuminate\Contracts\Cache\LockTimeoutException`：

    use Illuminate\Contracts\Cache\LockTimeoutException;
    
    $lock = Cache::lock('foo', 10);
    
    try {
        $lock->block(5);
    
        // 等待最多 5 秒取得 Lock...
    } catch (LockTimeoutException $e) {
        // 無法取得 Lock...
    } finally {
        $lock?->release();
    }

上述範例可以通過將閉包傳入 `block` 方法來簡化。當傳入閉包給該方法後，Laravel 會嘗試在指定秒數內取得 Lock，並在閉包執行後自動釋放 Lock：

    Cache::lock('foo', 10)->block(5, function () {
        // 等待最多 5 秒取得 Lock…
    });

<a name="managing-locks-across-processes"></a>

### 在多個處理程序間管理 Lock

有的時候我們可能想要在一個處理程序內要求 Lock，並在另一個處理程序中釋放。舉例來說，我們可能會在某個網頁請求的期間內要求 Lock，並在由該請求觸發的佇列任務完成後才釋放該 Lock。在此情境中，應將該 Lock 的區域性「擁有者權杖」傳給佇列任務，以讓佇列任務可以使用給定的權杖來重新取得 Lock。

在下方的範例中，我們會在成功取得 Lock 後分派佇列任務。另外，我們也會通過 Lock 的 `owner` 方法來將 Lock 的擁有者權杖傳給佇列任務。

    $podcast = Podcast::find($id);
    
    $lock = Cache::lock('processing', 120);
    
    if ($lock->get()) {
        ProcessPodcast::dispatch($podcast, $lock->owner());
    }

在專案的 `ProcessPodcast` 任務中，我們可以通過擁有者權杖來恢復與釋放 Lock：

    Cache::restoreLock('processing', $this->owner)->release();

若想在不理會目前擁有者的情況下釋放 Lock，可以使用 `forceRelease` 方法：

    Cache::lock('processing')->forceRelease();

<a name="adding-custom-cache-drivers"></a>

## 新增自訂快取 Driver

<a name="writing-the-driver"></a>

### 撰寫 Driver

若要建立自訂快取 Driver，首先必須實作 `Illuminate\Contracts\Cache\Store` [Contract](/docs/{{version}}/contracts)。因此，一個 MongoDB 的快取實作看起來會長這樣：

    <?php
    
    namespace App\Extensions;
    
    use Illuminate\Contracts\Cache\Store;
    
    class MongoStore implements Store
    {
        public function get($key) {}
        public function many(array $keys) {}
        public function put($key, $value, $seconds) {}
        public function putMany(array $values, $seconds) {}
        public function increment($key, $value = 1) {}
        public function decrement($key, $value = 1) {}
        public function forever($key, $value) {}
        public function forget($key) {}
        public function flush() {}
        public function getPrefix() {}
    }

我們只需要通過 MongoDB 連線來實作其中的各個方法即可。有關如何實作這些方法，請參考 [Laravel 框架原始碼](https://github.com/laravel/framework) 中的 `Illuminate\Cache\MemcachedStore`。實作完成後，就可以呼叫 `Cache` Facade 的 `extend` 方法來註冊自訂 Driver：

    Cache::extend('mongo', function (Application $app) {
        return Cache::repository(new MongoStore);
    });

> **Note** 若不知道該將自定快取 Driver 的程式碼放在哪裡，可在 `app` 目錄內建立一個 `Extensions` 命名空間。不過，請記得，Laravel 並沒有硬性規定應用程式的架構，你可以隨意依照你的喜好來阻止程式碼。

<a name="registering-the-driver"></a>

### 註冊 Driver

若要向 Laravel 註冊自訂快取 Driver，可以使用 `Cache` Facade 上的 `extend` 方法。由於其他的 Service Provider 可能會嘗試在 `boot` 方法內讀取快取值，因此我們需要將自訂 Driver 註冊在 `booting` 回呼內。只要使用了 `booting` 回呼，就能確保自訂回呼是在其他 Service Provider 的 `boot` 方法被呼叫前、以及 `App\Providers\AppServiceProvider` 類別的 `register` 方法被呼叫前被註冊的。我們會將 `booting` 回呼放在專案的 `App\Providers\AppServiceProvider` 類別中的 `register` 方法內：

    <?php
    
    namespace App\Providers;
    
    use App\Extensions\MongoStore;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\ServiceProvider;
    
    class AppServiceProvider extends ServiceProvider
    {
        /**
         * Register any application services.
         */
        public function register(): void
        {
            $this->app->booting(function () {
                 Cache::extend('mongo', function (Application $app) {
                     return Cache::repository(new MongoStore);
                 });
             });
        }
    
        /**
         * Bootstrap any application services.
         */
        public function boot(): void
        {
            // ...
        }
    }

傳入 `extend` 方法的第一個引數為 Driver 的名稱。這個名稱應對應到 `config/cache.php` 設定檔中的 `driver` 選項。第二個引數則是一個應回傳 `Illuminate\Cache\Repository` 實體的閉包。該閉包會被傳入一個 `$app` 實體，即為 [Service Container](/docs/{{version}}/container) 的實體。

註冊好擴充程式後，就可以將 `config/cache.php` 設定檔中的 `driver` 選項更新為擴充程式的名稱。

<a name="events"></a>

## 事件

若要在每個快取操作時執行程式碼，可以監聽快取所觸發的[事件](/docs/{{version}}/events)。一般來說，這些事件監聽程式應放置於專案的 `App\Providers\EventServiceProvider` 類別：

    use App\Listeners\LogCacheHit;
    use App\Listeners\LogCacheMissed;
    use App\Listeners\LogKeyForgotten;
    use App\Listeners\LogKeyWritten;
    use Illuminate\Cache\Events\CacheHit;
    use Illuminate\Cache\Events\CacheMissed;
    use Illuminate\Cache\Events\KeyForgotten;
    use Illuminate\Cache\Events\KeyWritten;
    
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        CacheHit::class => [
            LogCacheHit::class,
        ],
    
        CacheMissed::class => [
            LogCacheMissed::class,
        ],
    
        KeyForgotten::class => [
            LogKeyForgotten::class,
        ],
    
        KeyWritten::class => [
            LogKeyWritten::class,
        ],
    ];
