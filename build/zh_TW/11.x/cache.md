---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/21/en-zhtw'
updatedAt: '2024-06-30T08:17:00Z'
contributors: {  }
progress: 45.78
---

# 快取

- [簡介](#introduction)
- [設定](#configuration)
  - [Driver 前置需求](#driver-prerequisites)
  
- [使用 Cache](#cache-usage)
  - [Obtaining a Cache Instance](#obtaining-a-cache-instance)
  - [Retrieving Items From the Cache](#retrieving-items-from-the-cache)
  - [Storing Items in the Cache](#storing-items-in-the-cache)
  - [Removing Items From the Cache](#removing-items-from-the-cache)
  - [Cache 輔助函式](#the-cache-helper)
  
- [Atomic Lock](#atomic-locks)
  - [管理 Lock](#managing-locks)
  - [在不同處理程序間管理 Lock](#managing-locks-across-processes)
  
- [新增自訂的 Cache Driver](#adding-custom-cache-drivers)
  - [Writing the Driver](#writing-the-driver)
  - [Registering the Driver](#registering-the-driver)
  
- [事件](#events)

<a name="introduction"></a>

## 簡介

有些取得資料或處理任務的過程可能很消耗 CPU、或是需要數秒鐘來完成。這種時候，我們通常會將取得的資料快取住一段時間，這樣一來在接下來的請求上就能快速存取相同的資料。快取的資料通常會初存在一些非常快速的資料儲存上，如 [Memcached](https://memcached.org) 或 [Redis](https://redis.io)。

所幸，Laravel 為多種快取後端提供了一個表達性、統一的 API，可以享受快取提供的快速資料存取，並加速你的網站。

<a name="configuration"></a>

## 設定

Your application's cache configuration file is located at `config/cache.php`. In this file, you may specify which cache store you would like to be used by default throughout your application. Laravel supports popular caching backends like [Memcached](https://memcached.org), [Redis](https://redis.io), [DynamoDB](https://aws.amazon.com/dynamodb), and relational databases out of the box. In addition, a file based cache driver is available, while `array` and "null" cache drivers provide convenient cache backends for your automated tests.

The cache configuration file also contains a variety of other options that you may review. By default, Laravel is configured to use the `database` cache driver, which stores the serialized, cached objects in your application's database.

<a name="driver-prerequisites"></a>

### Driver 需求

<a name="prerequisites-database"></a>

#### 資料庫

When using the `database` cache driver, you will need a database table to contain the cache data. Typically, this is included in Laravel's default `0001_01_01_000001_create_cache_table.php` [database migration](/docs/{{version}}/migrations); however, if your application does not contain this migration, you may use the `make:cache-table` Artisan command to create it:

```shell
php artisan make:cache-table

php artisan migrate
```
<a name="memcached"></a>

#### Memcached

要使用 Memcached Driver 需要安裝 [Memcached PECL 套件](https://pecl.php.net/package/memcached)。可以在 `config/cache.php` 設定檔中列出所有的 Memcached 伺服器。這個檔案已預先包含了 `memcached.servers` 欄位來讓你開始使用：

    'memcached' => [
        // ...
    
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
        // ...
    
        'servers' => [
            [
                'host' => '/var/run/memcached/memcached.sock',
                'port' => 0,
                'weight' => 100
            ],
        ],
    ],
<a name="redis"></a>

#### Redis

Before using a Redis cache with Laravel, you will need to either install the PhpRedis PHP extension via PECL or install the `predis/predis` package (~2.0) via Composer. [Laravel Sail](/docs/{{version}}/sail) already includes this extension. In addition, official Laravel deployment platforms such as [Laravel Forge](https://forge.laravel.com) and [Laravel Vapor](https://vapor.laravel.com) have the PhpRedis extension installed by default.

更多有關設定 Redis 的資訊，請參考 [Laravel 說明文件頁面](/docs/{{version}}/redis#configuration)。

<a name="dynamodb"></a>

#### DynamoDB

Before using the [DynamoDB](https://aws.amazon.com/dynamodb) cache driver, you must create a DynamoDB table to store all of the cached data. Typically, this table should be named `cache`. However, you should name the table based on the value of the `stores.dynamodb.table` configuration value within the `cache` configuration file. The table name may also be set via the `DYNAMODB_CACHE_TABLE` environment variable.

該資料表也應擁有一個字串 Partition Key，其名稱應對應專案的 `cache` 設定檔的 `stores.dynamodb.attributes.key` 設定值。預設情況下，該 Partition Key 應命名為 `key`。

Typically, DynamoDB will not proactively remove expired items from a table. Therefore, you should [enable Time to Live (TTL)](https://docs.aws.amazon.com/amazondynamodb/latest/developerguide/TTL.html) on the table. When configuring the table's TTL settings, you should set the TTL attribute name to `expires_at`.

Next, install the AWS SDK so that your Laravel application can communicate with DynamoDB:

```shell
composer require aws/aws-sdk-php
```
In addition, you should ensure that values are provided for the DynamoDB cache store configuration options. Typically these options, such as `AWS_ACCESS_KEY_ID` and `AWS_SECRET_ACCESS_KEY`, should be defined in your application's `.env` configuration file:

```php
'dynamodb' => [
    'driver' => 'dynamodb',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    'table' => env('DYNAMODB_CACHE_TABLE', 'cache'),
    'endpoint' => env('DYNAMODB_ENDPOINT'),
],
```
<a name="mongodb"></a>

#### MongoDB

If you are using MongoDB, a `mongodb` cache driver is provided by the official `mongodb/laravel-mongodb` package and can be configured using a `mongodb` database connection. MongoDB supports TTL indexes, which can be used to automatically clear expired cache items.

For more information on configuring MongoDB, please refer to the MongoDB [Cache and Locks documentation](https://www.mongodb.com/docs/drivers/php/laravel-mongodb/current/cache/).

<a name="cache-usage"></a>

## 使用快取

<a name="obtaining-a-cache-instance"></a>

### Obtaining a Cache Instance

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

### Retrieving Items From the Cache

`Cache` Facade 的 `get` 方法是用來從快取內取得資料的。若該項目不存在於快取內，則會回傳 `null`。若有需要，可以傳入第二個引數給 `get` 來指定項目不存在時要回傳什麼預設值：

    $value = Cache::get('key');
    
    $value = Cache::get('key', 'default');
也可以傳入一個閉包來作為預設值。若指定項目不存在於快取內，則該閉包的結果會被回傳。傳入閉包可讓你暫緩從資料庫或其他外部服務取得預設值的過程：

    $value = Cache::get('key', function () {
        return DB::table(/* ... */)->get();
    });
<a name="determining-item-existence"></a>

#### Determining Item Existence

`has` 方法可以用來判斷某個項目是否存在於快取內。該方法也會在項目存在，但其值為 `null` 時回傳 `false`：

    if (Cache::has('key')) {
        // ...
    }
<a name="incrementing-decrementing-values"></a>

#### 遞增或遞減值

`increment`（遞增）與 `decrement`（遞減）方法可以用來調整快取中的整數項目值。這兩個方法都接收一個可選的第二個引數來判斷項目值所要遞增或遞減的值：

    // Initialize the value if it does not exist...
    Cache::add('key', 0, now()->addHours(4));
    
    // Increment or decrement the value...
    Cache::increment('key');
    Cache::increment('key', $amount);
    Cache::decrement('key');
    Cache::decrement('key', $amount);
<a name="retrieve-store"></a>

#### Retrieve and Store

有時候，我們可能會想要從快取內取得項目，但也想在項目不存在的時候設定預設值。舉例來說，我們可能想從快取內取得所有的使用者，但若快取不存在，則從資料庫內取得所有使用者，並存入快取。可以使用 `Cache::remember` 方法：

    $value = Cache::remember('users', $seconds, function () {
        return DB::table('users')->get();
    });
若該項目不存在於快取內，則傳入 `remember` 的閉包會被執行，並將其結果放入快取內。

可以使用 `rememberForever` 方法來從快取內取得項目，並在項目不存在時將其永久保存在快取內：

    $value = Cache::rememberForever('users', function () {
        return DB::table('users')->get();
    });
<a name="swr"></a>

#### Stale While Revalidate

When using the `Cache::remember` method, some users may experience slow response times if the cached value has expired. For certain types of data, it can be useful to allow partially stale data to be served while the cached value is recalculated in the background, preventing some users from experiencing slow response times while cached values are calculated. This is often referred to as the "stale-while-revalidate" pattern, and the `Cache::flexible` method provides an implementation of this pattern.

The flexible method accepts an array that specifies how long the cached value is considered “fresh” and when it becomes “stale.” The first value in the array represents the number of seconds the cache is considered fresh, while the second value defines how long it can be served as stale data before recalculation is necessary.

If a request is made within the fresh period (before the first value), the cache is returned immediately without recalculation. If a request is made during the stale period (between the two values), the stale value is served to the user, and a [deferred function](/docs/{{version}}/helpers#deferred-functions) is registered to refresh the cached value after the response is sent to the user. If a request is made after the second value, the cache is considered expired, and the value is recalculated immediately, which may result in a slower response for the user:

    $value = Cache::flexible('users', [5, 10], function () {
        return DB::table('users')->get();
    });
<a name="retrieve-delete"></a>

#### Retrieve and Delete

若有需要從快取內取得並同時刪除項目，則可以使用 `pull` 方法。與 `get` 方法類似，當項目不存在於快取內時，會回傳 `null`：

    $value = Cache::pull('key');
    
    $value = Cache::pull('key', 'default');
<a name="storing-items-in-the-cache"></a>

### Storing Items in the Cache

可以使用 `Cache` Facade 上的 `put` 方法來將項目存入快取：

    Cache::put('key', 'value', $seconds = 10);
若未傳入儲存時間給 `put` 方法，則該項目將被永久儲存：

    Cache::put('key', 'value');
除了將秒數作為整數傳入，也可以傳入一個 `DateTime` 實體來代表指定的快取項目過期時間：

    Cache::put('key', 'value', now()->addMinutes(10));
<a name="store-if-not-present"></a>

#### Store if Not Present

`add` 方法會只在項目不存在於快取儲存內時將項目加進快取內。該方法會在項目有真正被加進快取後回傳 `true`。否則，該方法會回傳 `false`。`add` 方法是一個不可部分完成的操作（Atomic）：

    Cache::add('key', 'value', $seconds);
<a name="storing-items-forever"></a>

#### 永久儲存項目

`forever` 方法可用來將項目永久儲存於快取。由於這些項目永遠不會過期，因此這些項目必須手動使用 `forget` 方法來移除：

    Cache::forever('key', 'value');
> [!NOTE]  
> 若使用 Memcached Driver，使用「forever」儲存的項目可能會在快取達到大小限制時被移除。

<a name="removing-items-from-the-cache"></a>

### Removing Items From the Cache

可以使用 `forget` 方法來自快取內移除項目：

    Cache::forget('key');
也可以提供 0 或負數的過期時間來移除項目：

    Cache::put('key', 'value', 0);
    
    Cache::put('key', 'value', -5);
可以使用 `flush` 方法來移除整個快取：

    Cache::flush();
> [!WARNING]  
> 使用 Flush 移除快取並不理會所設定的快取「^[Prefix](%E5%89%8D%E7%BD%AE%E8%A9%9E)」，會將快取內所有的項目都移除。當快取有與其他應用程式共用時，在清除快取前請三思。

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
> [!NOTE]  
> 在測試呼叫全域的 `cache` 函式時，可以像在[測試 Facade](/docs/{{version}}/mocking#mocking-facades)一樣，使用 `Cache::shouldReceive` 方法。

<a name="atomic-locks"></a>

## Atomic Lock (不可部分完成的鎖定)

> [!WARNING]  
> 若要使用此功能，則應用程式必須要使用 `memcached`, `redis`, `dynamodb`, `database`, `file` 或 `array` 作為應用程式的預設快取 Driver。另外，所有的伺服器也都必須要連線至相同的中央快取伺服器。

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
        // Lock acquired for 10 seconds and automatically released...
    });
If the lock is not available at the moment you request it, you may instruct Laravel to wait for a specified number of seconds. If the lock cannot be acquired within the specified time limit, an `Illuminate\Contracts\Cache\LockTimeoutException` will be thrown:

    use Illuminate\Contracts\Cache\LockTimeoutException;
    
    $lock = Cache::lock('foo', 10);
    
    try {
        $lock->block(5);
    
        // Lock acquired after waiting a maximum of 5 seconds...
    } catch (LockTimeoutException $e) {
        // Unable to acquire lock...
    } finally {
        $lock->release();
    }
上述範例可以通過將閉包傳入 `block` 方法來簡化。當傳入閉包給該方法後，Laravel 會嘗試在指定秒數內取得 Lock，並在閉包執行後自動釋放 Lock：

    Cache::lock('foo', 10)->block(5, function () {
        // Lock acquired after waiting a maximum of 5 seconds...
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

### Writing the Driver

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
> [!NOTE]  
> 若不知道該將自定快取 Driver 的程式碼放在哪裡，可在 `app` 目錄內建立一個 `Extensions` 命名空間。不過，請記得，Laravel 並沒有硬性規定應用程式的架構，你可以隨意依照你的喜好來阻止程式碼。

<a name="registering-the-driver"></a>

### Registering the Driver

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

Once your extension is registered, update the `CACHE_STORE` environment variable or `default` option within your application's `config/cache.php` configuration file to the name of your extension.

<a name="events"></a>

## 事件

To execute code on every cache operation, you may listen for various [events](/docs/{{version}}/events) dispatched by the cache:

<div class="overflow-auto">
| Event Name |
| --- |
| `Illuminate\Cache\Events\CacheHit` |
| `Illuminate\Cache\Events\CacheMissed` |
| `Illuminate\Cache\Events\KeyForgotten` |
| `Illuminate\Cache\Events\KeyWritten` |

</div>
To increase performance, you may disable cache events by setting the `events` configuration option to `false` for a given cache store in your application's `config/cache.php` configuration file:

```php
'database' => [
    'driver' => 'database',
    // ...
    'events' => false,
],
```