---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/133/en-zhtw'
updatedAt: '2023-02-11T10:28:00Z'
contributors: {  }
progress: 44.44
---

# Redis

- [簡介](#introduction)
- [設定](#configuration)
  - [叢集](#clusters)
  - [Predis](#predis)
  - [phpredis](#phpredis)
  
- [使用 Redis](#interacting-with-redis)
  - [Transaction](#transactions)
  - [指令管道](#pipelining-commands)
  
- [Pub / Sub](#pubsub)

<a name="introduction"></a>

## 簡介

[Redis](https://redis.io) 是一個開放原始碼的高階索引鍵／值存放空間。Redis 常被稱作資料結構伺服器，因為索引鍵中可以保存[字串 (String)](https://redis.io/topics/data-types#strings)、[雜湊 (Hash)](https://redis.io/topics/data-types#hashes)、[清單 (List)](https://redis.io/topics/data-types#lists)、[集合 (Set)](https://redis.io/topics/data-types#sets)、[有序集合 (Sorted Set)](https://redis.io/topics/data-types#sorted-sets)等。

在 Laravel 中使用 Redis 前，我們建議先使用 PECL 安裝 [phpredis](https://github.com/phpredis/phpredis) PHP 擴充程式。比起安裝其他「User-Land (即，非 PHP 官方套件)」提供的 PHP 套件，要安裝 phpredis 比較複雜一點。不過，對於重度使用 Redis 的專案來說，使用 phpredis 的效能會比較好。若使用 [Laravel Sail](/docs/{{version}}/sail)，則該擴充程式已安裝在專案的 Docker Container 裡了。

若無法安裝 phpredis 擴充程式，則可使用 Composer 安裝 `predis/predis` 套件。Predis 是完全以 PHP 撰寫的 Redis 用戶端。使用 Predis 就不需要安裝其他額外的擴充程式：

```shell
composer require predis/predis
```
<a name="configuration"></a>

## 設定

我們可以在 `config/database.php` 設定檔中設定專案的 Redis 設定。在該檔案中，可以看到一個 `redis` 陣列，其中存放的就是專案要使用的 Redis 伺服器：

    'redis' => [
    
        'client' => env('REDIS_CLIENT', 'phpredis'),
    
        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
        ],
    
        'cache' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 1),
        ],
    
    ],
除非使用單一 URL 來代表 Redis 連線，否則該設定檔中所定義的每個 Redis 伺服器都必須有名稱、主機、連接埠：

    'redis' => [
    
        'client' => env('REDIS_CLIENT', 'phpredis'),
    
        'default' => [
            'url' => 'tcp://127.0.0.1:6379?database=0',
        ],
    
        'cache' => [
            'url' => 'tls://user:password@127.0.0.1:6380?database=1',
        ],
    
    ],
<a name="configuring-the-connection-scheme"></a>

#### 設定連線的 Scheme

預設情況下，Redis 用戶端會使用 `tcp` ^[Scheme](%E9%85%8D%E7%BD%AE) 來連線到 Redis 伺服器。不過，我們也可以在 Redis 伺服器設定陣列中指定 `scheme` 設定選項來使用 TLS / SSL 加密：

    'redis' => [
    
        'client' => env('REDIS_CLIENT', 'phpredis'),
    
        'default' => [
            'scheme' => 'tls',
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
        ],
    
    ],
<a name="clusters"></a>

### 叢集

若專案使用 Redis 伺服器叢集 (Cluster)，則應在 Redis 設定中的 `clusters` 索引鍵下定義這些叢集。預設情況下沒有該設定索引鍵，因此我們需要手動在 `config/database.php` 設定檔中建立該索引鍵：

    'redis' => [
    
        'client' => env('REDIS_CLIENT', 'phpredis'),
    
        'clusters' => [
            'default' => [
                [
                    'host' => env('REDIS_HOST', 'localhost'),
                    'password' => env('REDIS_PASSWORD'),
                    'port' => env('REDIS_PORT', 6379),
                    'database' => 0,
                ],
            ],
        ],
    
    ],
預設情況下，叢集會在各個節點間做用戶端分區 (Sharding)，讓我們能集區化 (Pool) 節點，並儘量取得更多可用的 RAM。不過，使用用戶端分區將無法處理 ^[Failover](%E5%AE%B9%E9%8C%AF%E7%A7%BB%E8%BD%89)。因此，這種做法主要只適合用在一些存放時間短的、快取的資料。這些資料應該要能從其他主要的資料存放空間內取得。

若想使用 Redis 原生的叢集功能，而不使用用戶端分區，則我們可以在 `config/database.php` 設定檔中將 `options.cluster` 設定值設為 `redis`：

    'redis' => [
    
        'client' => env('REDIS_CLIENT', 'phpredis'),
    
        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
        ],
    
        'clusters' => [
            // ...
        ],
    
    ],
<a name="predis"></a>

### Predis

若專案通過 Predis 套件來使用 Redis，則請確定 `REDIS_CLIENT` 環境變數是否有設為 `predis`：

    'redis' => [
    
        'client' => env('REDIS_CLIENT', 'predis'),
    
        // ...
    ],
除了預設的 `host`、`port`、`database`、`password` 等伺服器設定選項外，Predis 還支援其他的[連線參數](https://github.com/nrk/predis/wiki/Connection-Parameters)，這些連線參數可以在每個 Redis 伺服器上定義。若要使用這些其他的設定選項，請將這些選項駕到 `config/database.php` 設定檔中的 Redis 伺服器設定內：

    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', 6379),
        'database' => 0,
        'read_write_timeout' => 60,
    ],
<a name="the-redis-facade-alias"></a>

#### Redis Facade 的別名

Laravel 的 `config/app.php` 設定檔中包含了一個 `aliases` 陣列，該陣列中定義了所有 Laravel 會註冊的類別別名。預設情況下，該檔案中並未包含 `Redis` 別名，因為使用 `Redis` 會與 phpredis 擴充程式的 `Redis` 類別名稱衝突。在使用 Predis 用戶端時，若想新增使用 `Redis` 別名，則可將該別名加入到專案 `config/app.php` 設定檔中的 `aliases` 陣列中：

    'aliases' => Facade::defaultAliases()->merge([
        'Redis' => Illuminate\Support\Facades\Redis::class,
    ])->toArray(),
<a name="phpredis"></a>

### phpredis

預設情況下，Laravel 會使用 phpredis 擴充程式來與 Redis 溝通。Laravel 要用來與 Redis 溝通的用戶端是由 `redis.client` 設定選項來判斷的，一般來說這個設定選項的值就是 `REDIS_CLIENT` 環境變數值：

    'redis' => [
    
        'client' => env('REDIS_CLIENT', 'phpredis'),
    
        // Rest of Redis configuration...
    ],
出了預設的 `scheme`、`host`、`port`、`database`、`password` 等伺服器設定選項外，phpredis 還支援下列其他的連線參數：`name`、`persistent`、`persistent_id`、`prefix`、`read_timeout`、`retry_interval`、`timeout`、`context` 等。我們可以在 `config/database.php` 設定檔中將這些選項新增到 Redis 伺服器設定上：

    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', 6379),
        'database' => 0,
        'read_timeout' => 60,
        'context' => [
            // 'auth' => ['username', 'secret'],
            // 'stream' => ['verify_peer' => false],
        ],
    ],
<a name="phpredis-serialization"></a>

#### phpredis 的序列化與壓縮

phpredis 擴充程式可以設定各種各樣的序列化與壓縮演算法。可以在 Redis 設定的 `options` 陣列內設定這些演算法：

    'redis' => [
    
        'client' => env('REDIS_CLIENT', 'phpredis'),
    
        'options' => [
            'serializer' => Redis::SERIALIZER_MSGPACK,
            'compression' => Redis::COMPRESSION_LZ4,
        ],
    
        // Rest of Redis configuration...
    ],
目前所支援的序列化演算法有 `Redis::SERIALIZER_NONE` (預設)、`Redis::SERIALIZER_PHP`、`Redis::SERIALIZER_JSON`、`Redis::SERIALIZER_IGBINARY`、`Redis::SERIALIZER_MSGPACK` 。

支援的壓縮演算法包含：`Redis::COMPRESSION_NONE` (預設)、`Redis::COMPRESSION_LZF`、`Redis::COMPRESSION_ZSTD`、`Redis::COMPRESSION_LZ4`。

<a name="interacting-with-redis"></a>

## 使用 Redis

我們可以呼叫 `Redis` [Facade](/docs/{{version}}/facades) 上的各種方法來使用 Redis。`Redis` Facade 支援動態方法，著表示，我們可以在該 Facade 上呼叫任何的 [Redis 指令](https://redis.io/commands)，而該指令會被直接傳到 Redis 上。在這個範例中，我們會在 `Redis` Facade 上呼叫 `get` 方法，以呼叫 Redis 的 `GET` 指令：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\Redis;
    
    class UserController extends Controller
    {
        /**
         * Show the profile for the given user.
         *
         * @param  int  $id
         * @return \Illuminate\Http\Response
         */
        public function show($id)
        {
            return view('user.profile', [
                'user' => Redis::get('user:profile:'.$id)
            ]);
        }
    }
剛才也提到過，我們可以在 `Redis` Facade 上呼叫任何的 Redis 指令。Laravel 會使用 Magic Method 來將這些指令傳給 Redis 伺服器。若是有要求引數的 Redis 指令，則我們可以將引數傳給 Facade 上對應的方法：

    use Illuminate\Support\Facades\Redis;
    
    Redis::set('name', 'Taylor');
    
    $values = Redis::lrange('names', 5, 10);
或者，我們也可以使用 `Redis` Facade 的 `command` 方法來將指令傳給 Redis 伺服器。`command` 方法的第一個引數是指令名稱，而第二個引數則是一個陣列：

    $values = Redis::command('lrange', ['name', 5, 10]);
<a name="using-multiple-redis-connections"></a>

#### 使用多個 Redis 連線

在專案的 `config/database.php` 設定檔中，我們可以定義多個 Redis 連線／伺服器。我們可以使用 `Redis` Facade 上的 `connection` 方法來取得一個特定的 Redis 連線：

    $redis = Redis::connection('connection-name');
若要取得預設的 Redis 連線，可直接呼叫 `connection` 方法而不帶任何引數：

    $redis = Redis::connection();
<a name="transactions"></a>

### Transaction

`Redis` Facade 的 `transaction` 方法提供了一個 Redis 原生 `MULTI` 與 `EXEC` 指令的方便包裝。`transaction` 方法只有一個引數，該引數為一個閉包。傳入的閉包會收到一個 Redis 連線實體，並可在該實體上下任何指令。在該閉包中所下的所有指令，都會被放在單一、不可部分完成 (Atomic) 的 Transaction 內執行：

    use Illuminate\Support\Facades\Redis;
    
    Redis::transaction(function ($redis) {
        $redis->incr('user_visits', 1);
        $redis->incr('total_visits', 1);
    });
> [!WARNING]  
> 定義 Redis Transaction 時，無法從 Redis 連線中取值。請記得，Transaction 是以單一、不可部分完成的動作來執行的，因此這些動作會在整個閉包內的指令都執行完畢後才被執行。

#### Lua Script

`eval` 方法提供了另一種以單一、不可部分完成動作執行多個 Redis 指令的方法。不過，使用 `eval` 方法還有個好處，就是能在動作的期間處理與偵測 Redis 的索引鍵值。Redis Script 使用 [Lua 程式語言](https://www.lua.org)撰寫。

雖然，一開始，`eval` 方法可能有點可怕。不過我們會先來看看一個簡單的例子。`eval` 方法接受多個引數。首先，我們需要先 (以字串形式) 傳入 Lua Script 給該方法。然後。我們需要將該 Script 要處理的索引鍵數量 (以整數形式) 傳入。再來，我們需要傳入這些索引鍵的名稱。最後，我們可以傳入在該 Script 中需要存取的其他引數。

在這個範例中，我們會遞增一個計數器，並取得該計數器的值，判斷該值是否大於 5。如果大於 5，就再遞增另一個計數器。最後，回傳第一個計數器的值：

    $value = Redis::eval(<<<'LUA'
        local counter = redis.call("incr", KEYS[1])
    
        if counter > 5 then
            redis.call("incr", KEYS[2])
        end
    
        return counter
    LUA, 2, 'first-counter', 'second-counter');
> [!WARNING]  
> 有關更多在 Redis 上撰寫 Script 的資訊，請參考 [Redis 的說明文件](https://redis.io/commands/eval)。

<a name="pipelining-commands"></a>

### 指令管道

有時候，我們會需要執行多個 Redis 指令。除了個別以網路連線將每個指令傳給 Redis，我們還可以使用 `pipeline` 方法。`pipeline` 方法只有一個引數：一個接收 Redis 實體的閉包。我們可以使用這個 Redis 實體來下指令，下的所有指令會被一次性地傳送給 Redis 伺服器，以減少網路使用。指令會依照所下的順序執行：

    use Illuminate\Support\Facades\Redis;
    
    Redis::pipeline(function ($pipe) {
        for ($i = 0; $i < 1000; $i++) {
            $pipe->set("key:$i", $i);
        }
    });
<a name="pubsub"></a>

## Pub / Sub

Laravel 中為 Redis 的 `publish` 與 `subscribe` 指令提供了一個方便的介面。使用這兩個 Redis 指令，我們就能在給定的「頻道 (Channel)」上監聽訊息。接著，我們可以在另一個專案內、甚至使用另一個程式語言來 ^[Publish](%E7%99%BC%E4%BD%88) 訊息。這樣一來我們就能輕鬆地在不同專案或處理程序間進行溝通。

首先，我們先使用 `subscribe` 方法來建立一個頻道的 ^[Listener](%E7%9B%A3%E8%81%BD%E7%A8%8B%E5%BC%8F)。我們將這個指令放在一個 [Artisan 指令](/docs/{{version}}/artisan)內呼叫。因為，呼叫 ^[`subscribe`](%E8%A8%82%E9%96%B1) 方法就代表要開啟一個執行時間較長的處理程序：

    <?php
    
    namespace App\Console\Commands;
    
    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\Redis;
    
    class RedisSubscribe extends Command
    {
        /**
         * The name and signature of the console command.
         *
         * @var string
         */
        protected $signature = 'redis:subscribe';
    
        /**
         * The console command description.
         *
         * @var string
         */
        protected $description = 'Subscribe to a Redis channel';
    
        /**
         * Execute the console command.
         *
         * @return mixed
         */
        public function handle()
        {
            Redis::subscribe(['test-channel'], function ($message) {
                echo $message;
            });
        }
    }
接著，我們就能使用 `publish` 方法來將訊息發佈到頻道上：

    use Illuminate\Support\Facades\Redis;
    
    Route::get('/publish', function () {
        // ...
    
        Redis::publish('test-channel', json_encode([
            'name' => 'Adam Wathan'
        ]));
    });
<a name="wildcard-subscriptions"></a>

#### 使用萬用字元來 Subscribe

使用 `psubscribe` 方法，我們就能以萬用字元來 Subscribe 頻道。若要從所有頻道中取得所有的訊息，就適合使用這個方法。頻道名稱會以第二個引數傳給所提供的閉包：

    Redis::psubscribe(['*'], function ($message, $channel) {
        echo $message;
    });
    
    Redis::psubscribe(['users.*'], function ($message, $channel) {
        echo $message;
    });