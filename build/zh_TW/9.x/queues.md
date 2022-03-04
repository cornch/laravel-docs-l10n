# 佇列 - Queue

- [簡介](#introduction)

   - [連線 Vs. 佇列](#connections-vs-queues)

   - [Driver 注意事項與前置需求](#driver-prerequisites)

- [建立 Job](#creating-jobs)

   - [產生 Job 類別](#generating-job-classes)

   - [類別架構](#class-structure)

   - [不重複 Job](#unique-jobs)

- [Job Middleware](#job-middleware)

   - [頻率限制](#rate-limiting)

   - [避免 Job 重疊](#preventing-job-overlaps)

   - [頻率限制的 Exception](#throttling-exceptions)

- [分派 Job](#dispatching-jobs)

   - [延遲分派](#delayed-dispatching)

   - [同步分派](#synchronous-dispatching)

   - [Job 與資料庫 Transaction](#jobs-and-database-transactions)

   - [串聯 Job](#job-chaining)

   - [自訂佇列與連線](#customizing-the-queue-and-connection)

   - [指定 Job 最大嘗試次數與逾時](#max-job-attempts-and-timeout)

   - [錯誤處理](#error-handling)

- [批次 Job](#job-batching)

   - [定義批次 Job](#defining-batchable-jobs)

   - [分派批次](#dispatching-batches)

   - [將 Job 加入批次中](#adding-jobs-to-batches)

   - [檢查批次](#inspecting-batches)

   - [取消批次](#cancelling-batches)

   - [批次執行失敗](#batch-failures)

   - [修剪批次](#pruning-batches)

- [將閉包放入佇列](#queueing-closures)

- [執行 Queue Worker](#running-the-queue-worker)

   - [`queue:work` 指令](#the-queue-work-command)

   - [佇列的優先度](#queue-priorities)

   - [Queue Worker 與部署](#queue-workers-and-deployment)

   - [Job 的實效性與逾時](#job-expirations-and-timeouts)

- [Supervisor 組態設定](#supervisor-configuration)

- [處理失敗的 Job](#dealing-with-failed-jobs)

   - [在 Job 失敗後進行清理](#cleaning-up-after-failed-jobs)

   - [重試失敗的 Job](#retrying-failed-jobs)

   - [忽略不存在的 Model](#ignoring-missing-models)

   - [修剪失敗的 Job](#pruning-failed-jobs)

   - [在 DynamoDB 中保存失敗的 Job](#storing-failed-jobs-in-dynamodb)

   - [禁用失敗 Job 的保存](#disabling-failed-job-storage)

   - [失敗 Job 事件](#failed-job-events)

- [在佇列中清除 Job](#clearing-jobs-from-queues)

- [監控佇列](#monitoring-your-queues)

- [Job 事件](#job-events)

<a name="introduction"></a>

## 簡介

在製作 Web App 時，有些任務若在 Web Request 中進行會花費太多時間，如解析 CSV 檔並上傳。所幸，在 Laravel 中，要建立在背景執行的佇列任務非常輕鬆。只要將需要花費時間的任務移到佇列中執行，就能加速網站對 Request 的回應速度，並提供更好的使用者經驗給客戶。

Laravel 的佇列為各種不同的佇列後端都提供了統一的 API。這些後端包括 [Amazon SQS](https://aws.amazon.com/sqs/)、[Redis](https://redis.io)、甚至是關聯式資料庫。

Laravel 的佇列設定選項保存在專案的 `config/queue.php` 設定檔中。在這個檔案內，可以看到供各個 Laravel 內建佇列 Driver 使用的連線設定，包含資料庫、[Amazon SQS](https://aws.amazon.com/sqs/)、[Redis](https://redis.io)、[Beanstalkd](https://beanstalkd.github.io/) 等 Driver，還包括一個會即時執行任務的同步佇列 (用於本機開發)。還包含一個 `null` 佇列，用於忽略佇列任務。

> {tip} Laravel 現在還提供 Horizon。Horizon 是為 Redis 佇列提供的一個一個漂亮面板。更多資訊請參考完整的 [Horizon 說明文件](/docs/{{version}}/horizon)。


<a name="connections-vs-queues"></a>

### 連線 Vs. 佇列

在開始使用 Laravel 佇列前，我們需要先瞭解「^[連線](Connection)」與「^[佇列](Queue)」的差別。在 `config/queue.php` 設定檔中有個 `connections` 設定陣列。該選項用於定義連到後端佇列服務的連線，後端佇列服務就是像 Amazon SQS、Beanstalk、Redis 等。不過，一個佇列連線可以有多個「佇列」，我們可以將這些不同的佇列想成是幾個不同堆疊的佇列任務。

可以注意到範例 `queue` 設定檔中的各個範例連線設定中都包含了一個 `queue` 屬性。這個 `queue` 屬性指定的，就是當我們將任務傳給這個連線時預設會被分派的佇列。換句話說，若我們在分派任務時沒有顯式定義要分派到哪個佇列上，這個任務就會被分派到連線設定中 `queue` 屬性所定義的佇列上：

    use App\Jobs\ProcessPodcast;
    
    // 這個任務會被送到預設連線的預設佇列上...
    ProcessPodcast::dispatch();
    
    // 這個任務會被送到預設連線的「emails」佇列上...
    ProcessPodcast::dispatch()->onQueue('emails');

有的程式沒有要將任務推送到不同佇列的需求，這些程式只需要有單一佇列就好了。不過，因為 Laravel 的 Queue Worker 可調整各個 Queue 的優先處理等級，因此如果想要調整不同任務的優先處理順序，把任務推送到不同佇列就很有用。就來來說，我們如果把任務推送到 `high` 佇列，我們就可以執行一個 Worker 來讓這個佇列以更高優先級處理：

```shell
php artisan queue:work --queue=high,default
```

<a name="driver-prerequisites"></a>

### Driver 注意事項與前置需求

<a name="database"></a>

#### Database

若要使用 `database` 佇列 Driver，我們需要先有一個用來存放任務的資料庫資料表。若要產生一個用於建立這個資料表的 Migration，請執行 `queue:table` Artisan 指令。建立好 Migration 後，就可以使用 `migrate` 指令來 Migrate 資料庫：

```shell
php artisan queue:table

php artisan migrate
```

最後，別忘了更新專案 `.env` 檔中的 `QUEUE_CONNECTION` 變數來讓專案使用 `database` Driver：

    QUEUE_CONNECTION=database

<a name="redis"></a>

#### Redis

若要使用 `redis` 佇列 Driver，請在 `config/database.php` 設定檔中設定 Redis 資料庫連線。

**Redis Cluster**

若 Redis 佇列要使用 Redis Cluster，則設定的佇列名稱必須包含一個 [Key Hash Tag](https://redis.io/topics/cluster-spec#keys-hash-tags)。必須加上 Key Hash Tag，這樣才能確保給定佇列中所有的 Redis 索引鍵都有被放在相同的 Hash Slot 中：

    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => '{default}',
        'retry_after' => 90,
    ],

**Blocking**

在使用 Redis 佇列時，可使用 `block_for` 設定選項來指定 Redis Driver 在迭代 Worker 迴圈並重新讀取 Redis 資料庫來等新 Job 進來時要等待多久。

可依據佇列的負載來調整這個值，以避免不斷讀取 Redis 資料庫來尋找新任務，會比較有效率。舉例來說，我們可以將其設為 `5`，表示 Redis Driver 在等待新任務出現時應先等待 5 秒再查詢 Redis 資料庫：

    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => 5,
    ],

> {note} 將 `block_for` 設為 `0` 會導致 Queue Worker 在新 Job 出現前一直 Block。也會導致在處理下一個 Job 前都無法處理如 `SIGTERM` 等^[訊號](Signal)。


<a name="other-driver-prerequisites"></a>

#### 其他 Driver 的前置要求

下列 Queue Driver 還需要一些相依性套件。可以使用 Composer 套件管理員來安裝這些相依性套件：

- Amazon SQS: `aws/aws-sdk-php ~3.0`

- Beanstalkd: `pda/pheanstalk ~4.0`

- Redis: `predis/predis ~1.0` 或 phpredis PHP 擴充套件

<a name="creating-jobs"></a>

## 建立 Job

<a name="generating-job-classes"></a>

### 產生 Job 類別

預設情況下，專案中所有可放入佇列的任務都存放在 `app/Jobs` 目錄內。若 `app/Jobs` 目錄不存在，則執行 `make:jobs` Artisan 指令時會建立該目錄：

```shell
php artisan make:job ProcessPodcast
```

產生的類別會實作 `Illuminate\Contracts\Queue\ShouldQueue` 介面，這樣 Laravel 就知道該 Job 要被推入佇列並以非同步方式執行。

> {tip} 可以[安裝 Stub](/docs/{{version}}/artisan#stub-customization) 來自訂 Job 的 Stub。


<a name="class-structure"></a>

### 類別架構

Job 類別非常簡單，通常只包含了一個 `handle` 方法，會在佇列處理 Job 時叫用。要開始使用 Job，我們先來看一個範例 Job 類別。在這個範例中，我們先假裝時我們在管理一個 Podcast 上架服務，我們需要在上架前處理上傳的 Podcast 檔案：

    <?php
    
    namespace App\Jobs;
    
    use App\Models\Podcast;
    use App\Services\AudioProcessor;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    
    class ProcessPodcast implements ShouldQueue
    {
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
        /**
         * The podcast instance.
         *
         * @var \App\Models\Podcast
         */
        protected $podcast;
    
        /**
         * Create a new job instance.
         *
         * @param  App\Models\Podcast  $podcast
         * @return void
         */
        public function __construct(Podcast $podcast)
        {
            $this->podcast = $podcast;
        }
    
        /**
         * Execute the job.
         *
         * @param  App\Services\AudioProcessor  $processor
         * @return void
         */
        public function handle(AudioProcessor $processor)
        {
            // Process uploaded podcast...
        }
    }

在這個範例中，可以看到我們直接將 [Eloquent Model] 傳入佇列任務的 ^[Constructor](建構函式) 中。由於該任務有使用 `SerializesModels` Trait，所以 Eloquent Model 與已載入的關聯 Model 都會被序列化處理，並在處理任務時反序列化。

若佇列任務的 Constructor 中接受 Eloquent Model，則只有 Model 的^[識別元](Identifier)會被序列化進佇列中。實際要處理任務時，佇列系統會自動從資料庫中重新取得完整的 Model 實體以及已載入的關聯。通過這種序列化 Model 的做法，我們就能縮小傳入佇列 Driver 的任務^[承載](Payload)。

<a name="handle-method-dependency-injection"></a>

#### `handle` 方法的相依性插入

佇列在處理任務時會叫用該任務的 `handle` 方法。請注意，我們可以在任務的 `handle` 方法上^[型別提示](Type-Hint)任何相依性項目。Laravel [Service Container](/docs/{{version}}/container) 會自動插入這些相依性。

若想完整控制 Container 要如何插入這些相依性到 `handle` 方法，可使用 Container 的 `bindMethod` 方法。`bindMethod` 方法接收一個回呼，該回呼則接收該任務與 Container。我們可以在這個回呼中自行叫用 `handle` 方法。一般來說，我們應該從 `App\Providers\AppServiceProvider` [Service Provider](/docs/{{version}}/providers) 的 `boot` 方法中叫用這個方法：

    use App\Jobs\ProcessPodcast;
    use App\Services\AudioProcessor;
    
    $this->app->bindMethod([ProcessPodcast::class, 'handle'], function ($job, $app) {
        return $job->handle($app->make(AudioProcessor::class));
    });

> {note} 二進位資料，如圖片等，應在傳入佇列任務前先使用 `base64_encode` 函式進行編碼。若未進行編碼，則這些資料在放入佇列時可能無法正確被序列化為 JSON。


<a name="handling-relationships"></a>

#### 佇列中的關聯

由於 Model 上已載入的關聯也會被序列化，因此序列化的任務字串有時候會變得很大。若要防止關聯被序列化，可在設定屬性值時在 Model 上呼叫 `withoutRelations` 方法。該方法會回傳該 Model 不含已載入關聯的實體：

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\Podcast  $podcast
     * @return void
     */
    public function __construct(Podcast $podcast)
    {
        $this->podcast = $podcast->withoutRelations();
    }

此外，當任務被反序列化，然後 Model 關聯被從資料庫中重新取出時，這些關聯的資料會以完整的關聯取出。這表示，若在 Model 被任務佇列序列化前有對關聯套用任何查詢條件，在反序列化時，這些條件都不會被套用。因此，若只想處理給定關聯中的一部分，應在佇列任務中重新套用這些查詢條件。

<a name="unique-jobs"></a>

### 不重複 Job

> {note} 若要使用不重複任務，則需要使用支援 [Atomic Lock] 的快取 Driver。目前，`memcached`、`redis`、`dynamodb`、`database`、`file`、`array` 等快取 Driver 有支援 Atomic Lock。此外，不重複任務的^[條件限制](Constraint)不會被套用到批次任務中的人物上。


有時候，我們可能會想確保某個任務在佇列中一次只能有一個實體。我們可以在 Job 類別上實作 `ShouldBeUnique` 介面來確保一次只執行一個實體。要實作這個介面，我們需要在 Class 上定義幾個額外的方法：

    <?php
    
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Contracts\Queue\ShouldBeUnique;
    
    class UpdateSearchIndex implements ShouldQueue, ShouldBeUnique
    {
        ...
    }

在上述範例中，`UpdateSearchIndex` Job 是^[不重複](Unique)的。所以，若佇列中已經有該 Job 的另一個實體且尚未執行完畢，就不會再次分派該 Job。

在某些情況下，我們可能會想指定要用來判斷 Job 是否重複的「索引鍵」，或是我們可能會想指定一個逾時時間，讓這個 Job 在執行超過該逾時後就不再判斷是否重複。為此，可在 Job 類別上定義 `uniqueId` 與 `uniqueFor` 屬性或方法：

    <?php
    
    use App\Product;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Contracts\Queue\ShouldBeUnique;
    
    class UpdateSearchIndex implements ShouldQueue, ShouldBeUnique
    {
        /**
         * The product instance.
         *
         * @var \App\Product
         */
        public $product;
    
        /**
         * The number of seconds after which the job's unique lock will be released.
         *
         * @var int
         */
        public $uniqueFor = 3600;
    
        /**
         * The unique ID of the job.
         *
         * @return string
         */
        public function uniqueId()
        {
            return $this->product->id;
        }
    }

在上述範例中，`UpdateSearchIndex` Job 使用 Product ID 來判斷是否重複。因此，若新分派的 Job 有相同的 Product ID，則直到現存 Job 執行完畢前，這個 Job 都會被忽略。此外，若現有的 Job 在一個小時內都未被處理，這個不重複鎖定會被解除，之後若有另一個具相同重複索引鍵的 Job 將可被分派進佇列中。

<a name="keeping-jobs-unique-until-processing-begins"></a>

#### 在開始處理 Job 後仍維持讓 Job 不重複

預設情況下，不重複的 Job 會在執行完成或所有嘗試都失敗後「解除鎖定」。不過，有的情況下，我們可能會想在執行完成前就先解除鎖定 Job。為此，不要在該 Job 上實作 `ShouldBeUnique`，而是實作 `ShouldBeUniqueUntillProcessing` Contract：

    <?php
    
    use App\Product;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
    
    class UpdateSearchIndex implements ShouldQueue, ShouldBeUniqueUntilProcessing
    {
        // ...
    }

<a name="unique-job-locks"></a>

#### 不重複 Job 的鎖定

當分派 `ShouldBeUnique` 時，Laravel 會在幕後使用 `uniqueId` 索引鍵來取得一個 [^[Lock](鎖定)](/docs/{{version}}/cache#atomic-locks)。若未能取得 Lock，就不會分派該 Job。當 Job 完成處理或所有嘗試都失敗後，就會解除該 Lock。預設情況下，Laravel 會使用預設的快取 Driver 來取得該 Lock。不過，若想使用其他 Driver 來取得 Lock，可定義一個 `uniqueVia` 方法，並在該方法中回傳要使用的快取 Driver：

    use Illuminate\Support\Facades\Cache;
    
    class UpdateSearchIndex implements ShouldQueue, ShouldBeUnique
    {
        ...
    
        /**
         * Get the cache driver for the unique job lock.
         *
         * @return \Illuminate\Contracts\Cache\Repository
         */
        public function uniqueVia()
        {
            return Cache::driver('redis');
        }
    }

> {tip} 若想限制某個 Job 可^[同時](Concurrent)執行的數量，請使用 [`WithoutOverlapping`](/docs/{{version}}/queues#preventing-job-overlaps) Job Middleware 而不是使用 Unique Job。


<a name="job-middleware"></a>

## Job Middleware

使用 Job Middleware 就能讓我們將佇列 Job 包裝在一組自定邏輯內執行，讓我們能減少在各個 Job 內撰寫重複的程式碼。舉例來說，假設有下列這個 `handle` 方法，該方法會使用 Laravel 的 Redis 頻率限制功能，限制每 5 秒只能處理 1 個 Job：

    use Illuminate\Support\Facades\Redis;
    
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Redis::throttle('key')->block(0)->allow(1)->every(5)->then(function () {
            info('已取得 Lock...');
    
            // 處理 Job...
        }, function () {
            // 無法取得 Lock...
    
            return $this->release(5);
        });
    }

雖然我們確實可以這樣寫，但這樣一來 `handle` 方法的實作就變得很亂，因為我們的程式碼跟 Redis 頻率限制的邏輯混在一起了。此外，這樣的頻率限制邏輯一定也會與其他我們想要作頻率限制的 Job 重複。

我們可以定義一個 Job Middleware 來處理頻率限制，而不用在 handle 方法內處理。Laravel 中沒有預設放置 Job Middleware 的地方，因此我們可以隨意在專案內放置這些 Job Middleware。舉例來說，我們可以把 Middleware 放在 `app/Jobs/Middleware` 目錄下：

    <?php
    
    namespace App\Jobs\Middleware;
    
    use Illuminate\Support\Facades\Redis;
    
    class RateLimited
    {
        /**
         * Process the queued job.
         *
         * @param  mixed  $job
         * @param  callable  $next
         * @return mixed
         */
        public function handle($job, $next)
        {
            Redis::throttle('key')
                    ->block(0)->allow(1)->every(5)
                    ->then(function () use ($job, $next) {
                        // Lock obtained...
    
                        $next($job);
                    }, function () use ($job) {
                        // Could not obtain lock...
    
                        $job->release(5);
                    });
        }
    }

就像這樣，跟 [Route Middleware](/docs/{{version}}/middleware) 很像，Job Middleware 會收到正在處理的 Job，以及要繼續執行 Job 時要叫用的回呼。

建立好 Job Middleware 後，我們就可以在 Job 的 `middleware` 方法內將這個 Middleware 附加上去了。`make:job` 產生的空 Job 不包含 `middleware` 方法，所以我們需要手動在 Job 類別中新增這個方法：

    use App\Jobs\Middleware\RateLimited;
    
    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [new RateLimited];
    }

> {tip} Job Middleware 也可以被指派給可放入佇列的 Event Listener、Mailable、Notification 等。


<a name="rate-limiting"></a>

### 頻率限制

雖然我們已經示範了要如何自行撰寫頻率限制的 Job Middleware。不過，其實 Laravel 有內建用來為 Job 做頻率限制的 Middleware。就跟 [Route 的 Rate Limiter](/docs/{{version}}/routing#defining-rate-limiters) 一樣，可以使用 `RateLimiter` Facade 的 `for` 方法來定義 Job 的頻率限制。

舉例來說，我們可能會想讓使用者能備份資料，而一般的使用者限制為每小時可備份一次，VIP 使用者則不限次數。若要做這種頻率限制，可以在 `AppServiceProvider` 中的 `boot` 方法內定義一個 `RateLimiter`：

    use Illuminate\Cache\RateLimiting\Limit;
    use Illuminate\Support\Facades\RateLimiter;
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        RateLimiter::for('backups', function ($job) {
            return $job->user->vipCustomer()
                        ? Limit::none()
                        : Limit::perHour(1)->by($job->user->id);
        });
    }

在上述範例中，我們定義了一個每小時的頻率限制。除了以小時來定義頻率限制外，也可以使用 `perMinute` 方法來以分鐘定義頻率限制。此外，我們也可以傳入任意值給頻率限制的 `by` 方法。傳給 `by` 的值通常是用來區分不同使用者的：

    return Limit::perMinute(50)->by($job->user->id);

定義好頻率限制後，我們就可以使用 `Illuminate\Queue\Middleware\RateLimited` Middleware 來將這個 Rate Limiter 附加到備份 Job 上。每當這個 Job 超過頻率限制後，這個 Middleware 就會依照頻率限制的間隔，使用適當的延遲時間來將該 Job 放回到佇列中。

    use Illuminate\Queue\Middleware\RateLimited;
    
    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [new RateLimited('backups')];
    }

將受頻率限制的 Job 放回佇列後，一樣會增加 Job 的 `attemps` 總數。若有需要可以在 Job 類別上適當地設定 `tries` 與 `maxExceptions` 屬性。或者，也可以使用 [`retryUntil` 方法](#time-based-attempts) 來定義不再重新嘗試 Job 的時間。

若不想讓 Job 在遇到頻率限制後重新嘗試，可使用 `dontRelease` 方法：

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [(new RateLimited('backups'))->dontRelease()];
    }

> {tip} 若使用 Redis，可使用 `Illuminate\Queue\Middleware\RateLimitedWithRedis` Middleware。這個 Middleware 有為 Redis 做最佳化，比起一般基礎的頻率限制 Middleware 來說會更有效率。


<a name="preventing-job-overlaps"></a>

### 避免 Job 重疊

Laravel 隨附了一個 `Illuminate\Queue\Middleware\WithoutOverlapping` Middleware，可讓我們依照任意索引鍵來避免 Job 重疊。使用這個 Middleware 就能避免同一個資源同時被多個佇列 Job 修改。

舉例來說，假設我們有個佇列任務會負責更新使用者的信用分數，而我們想避免兩個更新相同 User ID 的信用分數 Job 重疊。為此，可在 Job 的 `middleware` 方法中回傳 `WithoutOverlapping` Middleware：

    use Illuminate\Queue\Middleware\WithoutOverlapping;
    
    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [new WithoutOverlapping($this->user->id)];
    }

每當有重疊的 Job，這些 Job 都會被重新放到佇列中。我們可以指定一個秒數，讓這些被重新放回佇列的 Job 在重新嘗試前必須等待多久：

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [(new WithoutOverlapping($this->order->id))->releaseAfter(60)];
    }

若想在 Job 重疊時馬上刪除這些重疊的 Job 來讓這些 Job 不被重試，請使用 `dontRelease` 方法：

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [(new WithoutOverlapping($this->order->id))->dontRelease()];
    }

`WithoutOverlapping` Middleware 使用 Laravel 的 Atomic Lock 功能提供。有時候，Job 可能會未預期地失敗或逾時，並可能未正確釋放 Lock。因此，我們可以使用 `expireAfter` 方法來顯式定義一個 Lock 的有效時間。舉例來說，下列範例會讓 Laravel 在 Job 開始處理的 3 分鐘後釋放 `WithoutOverlapping` Lock：

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [(new WithoutOverlapping($this->order->id))->expireAfter(180)];
    }

> {note} 若要使用 `WithoutOverlapping` Middleware，則需要使用支援 [Atomic Lock] 的快取 Driver。目前，`memcached`、`redis`、`dynamodb`、`database`、`file`、`array` 等快取 Driver 有支援 Atomic Lock。


<a name="throttling-exceptions"></a>

### 頻率限制的 Exception

Laravel 中隨附了一個 `Illuminate\Queue\Middleware\ThrottlesExceptions` Middleware，能讓我們針對 Exception 做頻率限制。每當有 Job 擲回特定數量的 Exception 時，接下來要再次嘗試執行該 Job 前，必須要等待特定的時間過後才能繼續。對於一些使用了不穩定第三方服務的 Job 來說，特別適合使用這個功能。

舉例來說，假設我們有個使用了第三方 API 的佇列 Job，而這個 Job 會擲回 Exception。若要對 Exception 做頻率限制，可以在 Job 的 `middleware` 方法內回傳 `ThrottlesExceptions` Middleware。一般來說，這個 Middleware 應放在實作[基於時間的 attempts](#time-based-attempts)之 Job 內：

    use Illuminate\Queue\Middleware\ThrottlesExceptions;
    
    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [new ThrottlesExceptions(10, 5)];
    }
    
    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        return now()->addMinutes(5);
    }

Middleware 的第一個 Constructor 引數為 Exception 的數量，當 Job 擲回這個數量的 Exception 後就會被限制執行。第二個引數則是當被限制執行後，在繼續執行之前所要等待的分鐘數。在上述的範例中，若 Job 在 5 分鐘內擲回了 10 個 Exception，則 Laravel 會等待 5 分鐘，然後再繼續嘗試執行該 Job。

當 Job 擲回 Exception，但還未達到所設定的 Exception 閥值，則一般情況下會馬上重試 Job。不過，也可以在講 Middleware 附加到 Job 上時呼叫 `backoff` 方法來指定一個以分鐘為單位的數字，來指定 Job 所要延遲的時間：

    use Illuminate\Queue\Middleware\ThrottlesExceptions;
    
    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [(new ThrottlesExceptions(10, 5))->backoff(5)];
    }

這個 Middleware 在內部使用了 Laravel 的快取系統來實作頻率限制，並使用了該 Job 的類別名稱來作為快取的「索引鍵」。可以在講 Middleware 附加到 Job 上時呼叫 `by` 方法來複寫這個索引鍵。當有多個 Job 都使用了同一個第三方服務時，就很適合使用這個方法來讓這些 Job 都共用相同的頻率限制：

    use Illuminate\Queue\Middleware\ThrottlesExceptions;
    
    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [(new ThrottlesExceptions(10, 10))->by('key')];
    }

> {tip} 若使用 Redis，可使用 `Illuminate\Queue\Middleware\ThrottlesExceptionsWithRedis` Middleware。該 Middleware 有為 Redis 最佳化，因此會比一般的 Exception 頻率限制 Middleware 還要有效率。


<a name="dispatching-jobs"></a>

## 分派 Job

寫好 Job 類別後，就可以使用 Job 上的 `dispatch` 方法來分派該 Job。傳給 `dispatch` 方法的引數會被傳給 Job 的 Constructor：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use App\Jobs\ProcessPodcast;
    use App\Models\Podcast;
    use Illuminate\Http\Request;
    
    class PodcastController extends Controller
    {
        /**
         * Store a new podcast.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\Response
         */
        public function store(Request $request)
        {
            $podcast = Podcast::create(...);
    
            // ...
    
            ProcessPodcast::dispatch($podcast);
        }
    }

若想要有條件地分派 Job，可使用 `dispatchIf` 與` `dispatchUnless` 方法：

    ProcessPodcast::dispatchIf($accountActive, $podcast);
    
    ProcessPodcast::dispatchUnless($accountSuspended, $podcast);

<a name="delayed-dispatching"></a>

### 延遲分派

若不想讓 Job 馬上被 Queue Worker 處理，可在分派 Job 時使用 `delay` 方法。舉例來說，我們來指定讓一個 Job 在分派的 10 分鐘後才被開始處理：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use App\Jobs\ProcessPodcast;
    use App\Models\Podcast;
    use Illuminate\Http\Request;
    
    class PodcastController extends Controller
    {
        /**
         * Store a new podcast.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\Response
         */
        public function store(Request $request)
        {
            $podcast = Podcast::create(...);
    
            // ...
    
            ProcessPodcast::dispatch($podcast)
                        ->delay(now()->addMinutes(10));
        }
    }

> {note} Amazon SQS 佇列服務的延遲時間最多只能為 15 分鐘。


<a name="dispatching-after-the-response-is-sent-to-browser"></a>

#### 在 Response 被傳送給瀏覽器後才進行分派

`dispatchAfterResponse` 則是另一個分派 Job 的方法，該方法延遲分派 Job，直到 HTTP Response 被傳回使用者瀏覽器後才開始處理Job。這樣一來，在處理佇列 Job 的同時，使用者就能繼續使用我們的網站。一般來說，這種做法應只用於一些只需花費 1 秒鐘的 Job，如寄送 E-Mail 鄧。由於這些 Job 會在目前的 HTTP Request 中處理，因此使用這種方式分派 Job 就不需要執行 Queue Worker：

    use App\Jobs\SendNotification;
    
    SendNotification::dispatchAfterResponse();

也可以用 `dispatch` 分派一個閉包，然後在 `dispatch` 輔助函式後串上一個 `afterResponse` 方法來在 HTTP Response 被傳送給瀏覽器後執行這個閉包：

    use App\Mail\WelcomeMessage;
    use Illuminate\Support\Facades\Mail;
    
    dispatch(function () {
        Mail::to('taylor@example.com')->send(new WelcomeMessage);
    })->afterResponse();

<a name="synchronous-dispatching"></a>

### 同步分派

若想馬上分派 Job (即，^[同步](Synchronous))，則可使用 `dispatchSync` 方法。在使用這個方法時，所分派的 Job 不會被放入佇列，而會在目前的處理程序中馬上執行：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use App\Jobs\ProcessPodcast;
    use App\Models\Podcast;
    use Illuminate\Http\Request;
    
    class PodcastController extends Controller
    {
        /**
         * Store a new podcast.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\Response
         */
        public function store(Request $request)
        {
            $podcast = Podcast::create(...);
    
            // Create podcast...
    
            ProcessPodcast::dispatchSync($podcast);
        }
    }

<a name="jobs-and-database-transactions"></a>

### Job 與資料庫 Transaction

While it is perfectly fine to dispatch jobs within database transactions, you should take special care to ensure that your job will actually be able to execute successfully. When dispatching a job within a transaction, it is possible that the job will be processed by a worker before the parent transaction has committed. When this happens, any updates you have made to models or database records during the database transaction(s) may not yet be reflected in the database. In addition, any models or database records created within the transaction(s) may not exist in the database.

幸好，Laravel 提供了數種方法可解決這個狀況。第一種方法，我們可以在 Queue 連線的設定陣列中設定 `after_commit` 連線選項：

    'redis' => [
        'driver' => 'redis',
        // ...
        'after_commit' => true,
    ],

When the `after_commit` option is `true`, you may dispatch jobs within database transactions; however, Laravel will wait until the open parent database transactions have been committed before actually dispatching the job. Of course, if no database transactions are currently open, the job will be dispatched immediately.

If a transaction is rolled back due to an exception that occurs during the transaction, the jobs that were dispatched during that transaction will be discarded.

> {tip} 將 `after_commit` 設定選項設為 `true` 後，所有放入佇列的 Listener、Maillable、Notification、廣播事件……等都會等待到所有資料庫 Transaciton 都 Commit 後才被分派。


<a name="specifying-commit-dispatch-behavior-inline"></a>

#### 內嵌指定 Commit 的分派行為

若未將 `after_commit` 佇列連線選項設為 `true`，則我們還是可以指定讓某個特定的 Job 在所有已開啟的資料庫 Transaction 都被 Commit 後才被分派。若要這麼做，可在分派動作後串上 `afterCommit` 方法：

    use App\Jobs\ProcessPodcast;
    
    ProcessPodcast::dispatch($podcast)->afterCommit();

同樣地，若 `after_commit` 選項為 `true`，則我們也可以馬上分派某個特定的 Job，而不等待資料庫 Transaction 的 Commit：

    ProcessPodcast::dispatch($podcast)->beforeCommit();

<a name="job-chaining"></a>

### Job 的串聯

通過 Job 串聯，我們就可以指定一組佇列 Job 的清單，在主要 Job 執行成功後才依序執行這組 Job。若按照順序執行的其中一個 Job 執行失敗，則剩下的 Job 都將不被執行。若要執行佇列的 Job 串聯，可使用 `Bus` Facade 中的 `chain` 方法。Laravel 的 ^[Command Bus](指令匯流排) 是一個低階的原件，佇列 Job 的分派功能就是使用這個原件製作的：

    use App\Jobs\OptimizePodcast;
    use App\Jobs\ProcessPodcast;
    use App\Jobs\ReleasePodcast;
    use Illuminate\Support\Facades\Bus;
    
    Bus::chain([
        new ProcessPodcast,
        new OptimizePodcast,
        new ReleasePodcast,
    ])->dispatch();

除了串聯 Job 類別實體，我們也可以串聯閉包：

    Bus::chain([
        new ProcessPodcast,
        new OptimizePodcast,
        function () {
            Podcast::update(...);
        },
    ])->dispatch();

> {note} 在 Job 中使用 `$this->delete()` 方法來刪除 Job 是沒有辦法讓串聯的 Job 不被執行的。只有當串聯中的 Job 失敗時才會停止執行。


<a name="chain-connection-queue"></a>

#### 串聯的連線與佇列

若想指定串聯 Job 的連線與佇列，則可使用 `onConnection` 與 `onQueue` 方法。除非佇列 Job 有特別指定不同的連線或佇列，否則，這些方法可用來指定要使用的連線名稱與佇列名稱：

    Bus::chain([
        new ProcessPodcast,
        new OptimizePodcast,
        new ReleasePodcast,
    ])->onConnection('redis')->onQueue('podcasts')->dispatch();

<a name="chain-failures"></a>

#### 串聯失敗

將 Job 串聯起來後，可使用 `catch` 方法來指定當串聯中有 Job 失敗時要被叫用的閉包。給定的回呼會收到一個導致 Job 失敗的 `Throwable` 實體：

    use Illuminate\Support\Facades\Bus;
    use Throwable;
    
    Bus::chain([
        new ProcessPodcast,
        new OptimizePodcast,
        new ReleasePodcast,
    ])->catch(function (Throwable $e) {
        // 在串聯中有一個 Job 執行失敗...
    })->dispatch();

<a name="customizing-the-queue-and-connection"></a>

### 自定佇列與連線

<a name="dispatching-to-a-particular-queue"></a>

#### 分派至特定的佇列

我們可以將 Job 分門別類放入不同的佇列中，進而分類管理這些 Job，甚至能針對不同佇列設定優先度、指定要有多少個 Worker。不過請記得，放入不同佇列不會將 Job 推送到佇列設定檔中所定義的不同佇列「連線」上，而只會將 Job 推入單一連線中指定的佇列。若要指定佇列，請在分派 Job 時使用 `onQueue` 方法：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use App\Jobs\ProcessPodcast;
    use App\Models\Podcast;
    use Illuminate\Http\Request;
    
    class PodcastController extends Controller
    {
        /**
         * Store a new podcast.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\Response
         */
        public function store(Request $request)
        {
            $podcast = Podcast::create(...);
    
            // 建立 Podcast...
    
            ProcessPodcast::dispatch($podcast)->onQueue('processing');
        }
    }

或者，也可以在 Job 的 Constructor 中呼叫 `onQueue` 方法來指定 Job 的佇列：

    <?php
    
    namespace App\Jobs;
    
     use Illuminate\Bus\Queueable;
     use Illuminate\Contracts\Queue\ShouldQueue;
     use Illuminate\Foundation\Bus\Dispatchable;
     use Illuminate\Queue\InteractsWithQueue;
     use Illuminate\Queue\SerializesModels;
    
    class ProcessPodcast implements ShouldQueue
    {
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
        /**
         * Create a new job instance.
         *
         * @return void
         */
        public function __construct()
        {
            $this->onQueue('processing');
        }
    }

<a name="dispatching-to-a-particular-connection"></a>

#### 分派至特定連線

若專案有使用到多個佇列連線，則可以使用 `onConnection` 方法來指定要將 Job 推送到哪個連線：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use App\Jobs\ProcessPodcast;
    use App\Models\Podcast;
    use Illuminate\Http\Request;
    
    class PodcastController extends Controller
    {
        /**
         * Store a new podcast.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\Response
         */
        public function store(Request $request)
        {
            $podcast = Podcast::create(...);
    
            // 建立 Podcast...
    
            ProcessPodcast::dispatch($podcast)->onConnection('sqs');
        }
    }

也可以將 `onConnection` 與 `onQueue` 方法串聯在一起來指定 Job 的連線與佇列：

    ProcessPodcast::dispatch($podcast)
                  ->onConnection('sqs')
                  ->onQueue('processing');

或者，也可以在 Job 的 Constructor 中呼叫 `onConnection` 來指定 Job 的連線：

    <?php
    
    namespace App\Jobs;
    
     use Illuminate\Bus\Queueable;
     use Illuminate\Contracts\Queue\ShouldQueue;
     use Illuminate\Foundation\Bus\Dispatchable;
     use Illuminate\Queue\InteractsWithQueue;
     use Illuminate\Queue\SerializesModels;
    
    class ProcessPodcast implements ShouldQueue
    {
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
        /**
         * Create a new job instance.
         *
         * @return void
         */
        public function __construct()
        {
            $this->onConnection('sqs');
        }
    }

<a name="max-job-attempts-and-timeout"></a>

### 指定最大嘗試次數與逾時

<a name="max-attempts"></a>

#### 最大嘗試次數

若有某個佇列 Job 遇到錯誤，我們通常不會想讓這個 Job 一直重試。因此，Laravel 提供了多種定義 Job 重試次數的方法。

其中一種指定 Job 最大嘗試次數的方法是在 Artisan 指令列中使用 `--tries` 開關。使用這種方式指定的嘗試次數會套用到所有該 Worker 處理的 Job，除非 Job 上有特別指定嘗試次數：

```shell
php artisan queue:work --tries=3
```

若 Job 嘗試了最大嘗試次數，則這個 Job 會被視為是「^[執行失敗](Failed)」。更多有關處理執行失敗 Job 的資訊，請參考 [執行失敗 Job 的說明文件](#dealing-with-failed-jobs)。

也可以用另一種更仔細的方法，就是在 Job 類別內定義這個 Job 的最大嘗試次數。若有在 Job 中指定最大嘗試次數，定義在 Job 類別內的次數會比指令列中 `--tries` 的值擁有更高的優先度：

    <?php
    
    namespace App\Jobs;
    
    class ProcessPodcast implements ShouldQueue
    {
        /**
         * The number of times the job may be attempted.
         *
         * @var int
         */
        public $tries = 5;
    }

<a name="time-based-attempts"></a>

#### 基於時間的嘗試限制

除了定義 Job 重試多少次要視為失敗以外，也可以限制 Job 嘗試執行的時間長度。這樣一來，在指定的時間範圍內，Job 就可以不斷重試。若要定義最長可重試時間，請在 Job 類別中定義一個 `retryUntil` 方法。該方法應回傳 `DateTime` 實體：

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        return now()->addMinutes(10);
    }

> {tip} 也可以在[放入佇列的 Event Listener](/docs/{{version}}/events#queued-event-listeners) 中定義一個 `tries` 屬性或 `retryUntil` 方法。


<a name="max-exceptions"></a>

#### 最大 Exception 數

有時候，我們可能會想讓 Job 可重試多次，但當出現指定數量的未處理 Exception 後，就視為執行失敗 (與直接使用 `release` 方法釋放 Job 不同)。若要指定未處理 Exception 數量，可在 Job 類別中定義一個 `maxExceptions` 屬性：

    <?php
    
    namespace App\Jobs;
    
    use Illuminate\Support\Facades\Redis;
    
    class ProcessPodcast implements ShouldQueue
    {
        /**
         * The number of times the job may be attempted.
         *
         * @var int
         */
        public $tries = 25;
    
        /**
         * The maximum number of unhandled exceptions to allow before failing.
         *
         * @var int
         */
        public $maxExceptions = 3;
    
        /**
         * Execute the job.
         *
         * @return void
         */
        public function handle()
        {
            Redis::throttle('key')->allow(10)->every(60)->then(function () {
                // 已取得 Lock，正在處理 Podcast...
            }, function () {
                // 無法取得 Lock...
                return $this->release(10);
            });
        }
    }

在這個例子中，這個 Job 會在程式無法在 10 秒內取得 Redis Lock 時被釋放，而這個 Job 在此期間最多可嘗試 25 次。不過，若 Job 中有擲回未處理的 Exception，則會被視為是失敗的 Job。

<a name="timeout"></a>

#### 逾時

> {note} 必須安裝 `pcntl` PHP 擴充程式才可指定 Job 的逾時。


通常來說，我們知道某個佇列任務大約需要花多少時間執行。因此，在 Laravel 中，我們可以指定一個「逾時」值。若 Job 執行超過逾時值所指定的秒數後，負責處理該 Job 的 Worker 就會以錯誤終止執行。一般來說，Worker 會自動由 [Server 上設定的 Process Manager](#supervisor-configuration) 重新開啟。

可在 Artisan 指令列上使用 `--timeout` 開關來指定 Job 能執行的最大秒數：

```shell
php artisan queue:work --timeout=30
```

若 Job 不斷執行逾時超過其最大重試次數，則該 Job 會被標記為執行失敗。

也可以在 Job 類別中定義該 Job 能執行的最大秒數。若有在 Job 上指定逾時，則在 Job 類別上定義的逾時比在指令列上指定的數字擁有更高的優先度：

    <?php
    
    namespace App\Jobs;
    
    class ProcessPodcast implements ShouldQueue
    {
        /**
         * The number of seconds the job can run before timing out.
         *
         * @var int
         */
        public $timeout = 120;
    }

有時候，如 Socket 或連外 HTTP 連線等的 IO Blocking Process 可能不適用所指定的逾時設定。因此，若有使用到這些功能，也請在這些功能的 API 上指定逾時。舉例來說，若使用 Guzzle，則可像這樣指定連線與 Request 的逾時值：

<a name="failing-on-timeout"></a>

#### 逾時後視為失敗

若想讓 Job 在逾時後被標記為[執行失敗](#dealing-with-failed-jobs)，可在 Job 類別上定義 `$failOnTimeout` 屬性：

```php
/**
 * Indicate if the job should be marked as failed on timeout.
 *
 * @var bool
 */
public $failOnTimeout = true;
```

<a name="error-handling"></a>

### 錯誤處理

若在處理 Job 時有擲回 Exception，則這個 Job 會被自動釋放回佇列中，好讓這個 Job 能被重新嘗試。被釋放會佇列的 Job 會繼續被重試，直到重試次數達到專案上所設定的最大次數。最大重試次數可使用 `queue:work` Artisan 指令上的 `--tries` 開關來定義。或者，也可以在 Job 類別上定義最大重試次數。更多有關如何執行 Queue Worker 的資訊[可在本文後方找到](#running-the-queue-worker)。

<a name="manually-releasing-a-job"></a>

#### 手動釋放 Job

有的時候，我們可能會想手動將 Job 釋放會佇列中，好讓這個 Job 能在稍後重試。若要手動釋放 Job，可以呼叫 `release` 方法：

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // ...
    
        $this->release();
    }

預設情況下，`release` 方法會將 Job 釋放會佇列中並立即處理。不過，若傳入一個整數給 `release` 方法，就可以指定讓佇列等待給定秒數後才開始處理該 Job：

    $this->release(10);

<a name="manually-failing-a-job"></a>

#### 手動讓 Job 失敗

有時候，我們可能需要手動將 Job 標記為「失敗」。若要手動將 Job 標記為失敗，可呼叫 `fail` 方法：

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // ...
    
        $this->fail();
    }

若要在 Catch 到 Exception 時將 Job 標記為失敗，可將這個 Exception 傳給 `fail` 方法：

    $this->fail($exception);

> {tip} For more information on failed jobs, check out the [documentation on dealing with job failures](#dealing-with-failed-jobs).


<a name="job-batching"></a>

## Job Batching

Laravel's job batching feature allows you to easily execute a batch of jobs and then perform some action when the batch of jobs has completed executing. Before getting started, you should create a database migration to build a table to contain meta information about your job batches, such as their completion percentage. This migration may be generated using the `queue:batches-table` Artisan command:

```shell
php artisan queue:batches-table

php artisan migrate
```

<a name="defining-batchable-jobs"></a>

### Defining Batchable Jobs

To define a batchable job, you should [create a queueable job](#creating-jobs) as normal; however, you should add the `Illuminate\Bus\Batchable` trait to the job class. This trait provides access to a `batch` method which may be used to retrieve the current batch that the job is executing within:

    <?php
    
    namespace App\Jobs;
    
    use Illuminate\Bus\Batchable;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    
    class ImportCsv implements ShouldQueue
    {
        use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
        /**
         * Execute the job.
         *
         * @return void
         */
        public function handle()
        {
            if ($this->batch()->cancelled()) {
                // Determine if the batch has been cancelled...
    
                return;
            }
    
            // Import a portion of the CSV file...
        }
    }

<a name="dispatching-batches"></a>

### Dispatching Batches

To dispatch a batch of jobs, you should use the `batch` method of the `Bus` facade. Of course, batching is primarily useful when combined with completion callbacks. So, you may use the `then`, `catch`, and `finally` methods to define completion callbacks for the batch. Each of these callbacks will receive an `Illuminate\Bus\Batch` instance when they are invoked. In this example, we will imagine we are queueing a batch of jobs that each process a given number of rows from a CSV file:

    use App\Jobs\ImportCsv;
    use Illuminate\Bus\Batch;
    use Illuminate\Support\Facades\Bus;
    use Throwable;
    
    $batch = Bus::batch([
        new ImportCsv(1, 100),
        new ImportCsv(101, 200),
        new ImportCsv(201, 300),
        new ImportCsv(301, 400),
        new ImportCsv(401, 500),
    ])->then(function (Batch $batch) {
        // All jobs completed successfully...
    })->catch(function (Batch $batch, Throwable $e) {
        // First batch job failure detected...
    })->finally(function (Batch $batch) {
        // The batch has finished executing...
    })->dispatch();
    
    return $batch->id;

The batch's ID, which may be accessed via the `$batch->id` property, may be used to [query the Laravel command bus](#inspecting-batches) for information about the batch after it has been dispatched.

> {note} Since batch callbacks are serialized and executed at a later time by the Laravel queue, you should not use the `$this` variable within the callbacks.


<a name="naming-batches"></a>

#### Naming Batches

Some tools such as Laravel Horizon and Laravel Telescope may provide more user-friendly debug information for batches if batches are named. To assign an arbitrary name to a batch, you may call the `name` method while defining the batch:

    $batch = Bus::batch([
        // ...
    ])->then(function (Batch $batch) {
        // All jobs completed successfully...
    })->name('Import CSV')->dispatch();

<a name="batch-connection-queue"></a>

#### Batch Connection & Queue

If you would like to specify the connection and queue that should be used for the batched jobs, you may use the `onConnection` and `onQueue` methods. All batched jobs must execute within the same connection and queue:

    $batch = Bus::batch([
        // ...
    ])->then(function (Batch $batch) {
        // All jobs completed successfully...
    })->onConnection('redis')->onQueue('imports')->dispatch();

<a name="chains-within-batches"></a>

#### Chains Within Batches

You may define a set of [chained jobs](#job-chaining) within a batch by placing the chained jobs within an array. For example, we may execute two job chains in parallel and execute a callback when both job chains have finished processing:

    use App\Jobs\ReleasePodcast;
    use App\Jobs\SendPodcastReleaseNotification;
    use Illuminate\Bus\Batch;
    use Illuminate\Support\Facades\Bus;
    
    Bus::batch([
        [
            new ReleasePodcast(1),
            new SendPodcastReleaseNotification(1),
        ],
        [
            new ReleasePodcast(2),
            new SendPodcastReleaseNotification(2),
        ],
    ])->then(function (Batch $batch) {
        // ...
    })->dispatch();

<a name="adding-jobs-to-batches"></a>

### Adding Jobs To Batches

Sometimes it may be useful to add additional jobs to a batch from within a batched job. This pattern can be useful when you need to batch thousands of jobs which may take too long to dispatch during a web request. So, instead, you may wish to dispatch an initial batch of "loader" jobs that hydrate the batch with even more jobs:

    $batch = Bus::batch([
        new LoadImportBatch,
        new LoadImportBatch,
        new LoadImportBatch,
    ])->then(function (Batch $batch) {
        // All jobs completed successfully...
    })->name('Import Contacts')->dispatch();

In this example, we will use the `LoadImportBatch` job to hydrate the batch with additional jobs. To accomplish this, we may use the `add` method on the batch instance that may be accessed via the job's `batch` method:

    use App\Jobs\ImportContacts;
    use Illuminate\Support\Collection;
    
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->batch()->cancelled()) {
            return;
        }
    
        $this->batch()->add(Collection::times(1000, function () {
            return new ImportContacts;
        }));
    }

> {note} You may only add jobs to a batch from within a job that belongs to the same batch.


<a name="inspecting-batches"></a>

### Inspecting Batches

The `Illuminate\Bus\Batch` instance that is provided to batch completion callbacks has a variety of properties and methods to assist you in interacting with and inspecting a given batch of jobs:

    // The UUID of the batch...
    $batch->id;
    
    // The name of the batch (if applicable)...
    $batch->name;
    
    // The number of jobs assigned to the batch...
    $batch->totalJobs;
    
    // The number of jobs that have not been processed by the queue...
    $batch->pendingJobs;
    
    // The number of jobs that have failed...
    $batch->failedJobs;
    
    // The number of jobs that have been processed thus far...
    $batch->processedJobs();
    
    // The completion percentage of the batch (0-100)...
    $batch->progress();
    
    // Indicates if the batch has finished executing...
    $batch->finished();
    
    // Cancel the execution of the batch...
    $batch->cancel();
    
    // Indicates if the batch has been cancelled...
    $batch->cancelled();

<a name="returning-batches-from-routes"></a>

#### Returning Batches From Routes

All `Illuminate\Bus\Batch` instances are JSON serializable, meaning you can return them directly from one of your application's routes to retrieve a JSON payload containing information about the batch, including its completion progress. This makes it convenient to display information about the batch's completion progress in your application's UI.

To retrieve a batch by its ID, you may use the `Bus` facade's `findBatch` method:

    use Illuminate\Support\Facades\Bus;
    use Illuminate\Support\Facades\Route;
    
    Route::get('/batch/{batchId}', function (string $batchId) {
        return Bus::findBatch($batchId);
    });

<a name="cancelling-batches"></a>

### Cancelling Batches

Sometimes you may need to cancel a given batch's execution. This can be accomplished by calling the `cancel` method on the `Illuminate\Bus\Batch` instance:

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->user->exceedsImportLimit()) {
            return $this->batch()->cancel();
        }
    
        if ($this->batch()->cancelled()) {
            return;
        }
    }

As you may have noticed in previous examples, batched jobs should typically check to see if the batch has been cancelled at the beginning of their `handle` method:

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->batch()->cancelled()) {
            return;
        }
    
        // Continue processing...
    }

<a name="batch-failures"></a>

### Batch Failures

When a batched job fails, the `catch` callback (if assigned) will be invoked. This callback is only invoked for the first job that fails within the batch.

<a name="allowing-failures"></a>

#### Allowing Failures

When a job within a batch fails, Laravel will automatically mark the batch as "cancelled". If you wish, you may disable this behavior so that a job failure does not automatically mark the batch as cancelled. This may be accomplished by calling the `allowFailures` method while dispatching the batch:

    $batch = Bus::batch([
        // ...
    ])->then(function (Batch $batch) {
        // All jobs completed successfully...
    })->allowFailures()->dispatch();

<a name="retrying-failed-batch-jobs"></a>

#### Retrying Failed Batch Jobs

For convenience, Laravel provides a `queue:retry-batch` Artisan command that allows you to easily retry all of the failed jobs for a given batch. The `queue:retry-batch` command accepts the UUID of the batch whose failed jobs should be retried:

```shell
php artisan queue:retry-batch 32dbc76c-4f82-4749-b610-a639fe0099b5
```

<a name="pruning-batches"></a>

### Pruning Batches

Without pruning, the `job_batches` table can accumulate records very quickly. To mitigate this, you should [schedule](/docs/{{version}}/scheduling) the `queue:prune-batches` Artisan command to run daily:

    $schedule->command('queue:prune-batches')->daily();

By default, all finished batches that are more than 24 hours old will be pruned. You may use the `hours` option when calling the command to determine how long to retain batch data. For example, the following command will delete all batches that finished over 48 hours ago:

    $schedule->command('queue:prune-batches --hours=48')->daily();

Sometimes, your `jobs_batches` table may accumulate batch records for batches that never completed successfully, such as batches where a job failed and that job was never retried successfully. You may instruct the `queue:prune-batches` command to prune these unfinished batch records using the `unfinished` option:

    $schedule->command('queue:prune-batches --hours=48 --unfinished=72')->daily();

<a name="queueing-closures"></a>

## Queueing Closures

Instead of dispatching a job class to the queue, you may also dispatch a closure. This is great for quick, simple tasks that need to be executed outside of the current request cycle. When dispatching closures to the queue, the closure's code content is cryptographically signed so that it can not be modified in transit:

    $podcast = App\Podcast::find(1);
    
    dispatch(function () use ($podcast) {
        $podcast->publish();
    });

Using the `catch` method, you may provide a closure that should be executed if the queued closure fails to complete successfully after exhausting all of your queue's [configured retry attempts](#max-job-attempts-and-timeout):

    use Throwable;
    
    dispatch(function () use ($podcast) {
        $podcast->publish();
    })->catch(function (Throwable $e) {
        // This job has failed...
    });

<a name="running-the-queue-worker"></a>

## Running The Queue Worker

<a name="the-queue-work-command"></a>

### The `queue:work` Command

Laravel includes an Artisan command that will start a queue worker and process new jobs as they are pushed onto the queue. You may run the worker using the `queue:work` Artisan command. Note that once the `queue:work` command has started, it will continue to run until it is manually stopped or you close your terminal:

```shell
php artisan queue:work
```

> {tip} To keep the `queue:work` process running permanently in the background, you should use a process monitor such as [Supervisor](#supervisor-configuration) to ensure that the queue worker does not stop running.


Remember, queue workers, are long-lived processes and store the booted application state in memory. As a result, they will not notice changes in your code base after they have been started. So, during your deployment process, be sure to [restart your queue workers](#queue-workers-and-deployment). In addition, remember that any static state created or modified by your application will not be automatically reset between jobs.

Alternatively, you may run the `queue:listen` command. When using the `queue:listen` command, you don't have to manually restart the worker when you want to reload your updated code or reset the application state; however, this command is significantly less efficient than the `queue:work` command:

```shell
php artisan queue:listen
```

<a name="running-multiple-queue-workers"></a>

#### Running Multiple Queue Workers

To assign multiple workers to a queue and process jobs concurrently, you should simply start multiple `queue:work` processes. This can either be done locally via multiple tabs in your terminal or in production using your process manager's configuration settings. [When using Supervisor](#supervisor-configuration), you may use the `numprocs` configuration value.

<a name="specifying-the-connection-queue"></a>

#### Specifying The Connection & Queue

You may also specify which queue connection the worker should utilize. The connection name passed to the `work` command should correspond to one of the connections defined in your `config/queue.php` configuration file:

```shell
php artisan queue:work redis
```

By default, the `queue:work` command only processes jobs for the default queue on a given connection. However, you may customize your queue worker even further by only processing particular queues for a given connection. For example, if all of your emails are processed in an `emails` queue on your `redis` queue connection, you may issue the following command to start a worker that only processes that queue:

```shell
php artisan queue:work redis --queue=emails
```

<a name="processing-a-specified-number-of-jobs"></a>

#### Processing A Specified Number Of Jobs

The `--once` option may be used to instruct the worker to only process a single job from the queue:

```shell
php artisan queue:work --once
```

The `--max-jobs` option may be used to instruct the worker to process the given number of jobs and then exit. This option may be useful when combined with [Supervisor](#supervisor-configuration) so that your workers are automatically restarted after processing a given number of jobs, releasing any memory they may have accumulated:

```shell
php artisan queue:work --max-jobs=1000
```

<a name="processing-all-queued-jobs-then-exiting"></a>

#### Processing All Queued Jobs & Then Exiting

The `--stop-when-empty` option may be used to instruct the worker to process all jobs and then exit gracefully. This option can be useful when processing Laravel queues within a Docker container if you wish to shutdown the container after the queue is empty:

```shell
php artisan queue:work --stop-when-empty
```

<a name="processing-jobs-for-a-given-number-of-seconds"></a>

#### Processing Jobs For A Given Number Of Seconds

The `--max-time` option may be used to instruct the worker to process jobs for the given number of seconds and then exit. This option may be useful when combined with [Supervisor](#supervisor-configuration) so that your workers are automatically restarted after processing jobs for a given amount of time, releasing any memory they may have accumulated:

```shell
# Process jobs for one hour and then exit...
php artisan queue:work --max-time=3600
```

<a name="worker-sleep-duration"></a>

#### Worker Sleep Duration

When jobs are available on the queue, the worker will keep processing jobs with no delay in between them. However, the `sleep` option determines how many seconds the worker will "sleep" if there are no new jobs available. While sleeping, the worker will not process any new jobs - the jobs will be processed after the worker wakes up again.

```shell
php artisan queue:work --sleep=3
```

<a name="resource-considerations"></a>

#### Resource Considerations

Daemon queue workers do not "reboot" the framework before processing each job. Therefore, you should release any heavy resources after each job completes. For example, if you are doing image manipulation with the GD library, you should free the memory with `imagedestroy` when you are done processing the image.

<a name="queue-priorities"></a>

### Queue Priorities

Sometimes you may wish to prioritize how your queues are processed. For example, in your `config/queue.php` configuration file, you may set the default `queue` for your `redis` connection to `low`. However, occasionally you may wish to push a job to a `high` priority queue like so:

    dispatch((new Job)->onQueue('high'));

To start a worker that verifies that all of the `high` queue jobs are processed before continuing to any jobs on the `low` queue, pass a comma-delimited list of queue names to the `work` command:

```shell
php artisan queue:work --queue=high,low
```

<a name="queue-workers-and-deployment"></a>

### Queue Workers & Deployment

Since queue workers are long-lived processes, they will not notice changes to your code without being restarted. So, the simplest way to deploy an application using queue workers is to restart the workers during your deployment process. You may gracefully restart all of the workers by issuing the `queue:restart` command:

```shell
php artisan queue:restart
```

This command will instruct all queue workers to gracefully exit after they finish processing their current job so that no existing jobs are lost. Since the queue workers will exit when the `queue:restart` command is executed, you should be running a process manager such as [Supervisor](#supervisor-configuration) to automatically restart the queue workers.

> {tip} The queue uses the [cache](/docs/{{version}}/cache) to store restart signals, so you should verify that a cache driver is properly configured for your application before using this feature.


<a name="job-expirations-and-timeouts"></a>

### Job Expirations & Timeouts

<a name="job-expiration"></a>

#### Job Expiration

In your `config/queue.php` configuration file, each queue connection defines a `retry_after` option. This option specifies how many seconds the queue connection should wait before retrying a job that is being processed. For example, if the value of `retry_after` is set to `90`, the job will be released back onto the queue if it has been processing for 90 seconds without being released or deleted. Typically, you should set the `retry_after` value to the maximum number of seconds your jobs should reasonably take to complete processing.

> {note} The only queue connection which does not contain a `retry_after` value is Amazon SQS. SQS will retry the job based on the [Default Visibility Timeout](https://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/AboutVT.html) which is managed within the AWS console.


<a name="worker-timeouts"></a>

#### Worker Timeouts

The `queue:work` Artisan command exposes a `--timeout` option. If a job is processing for longer than the number of seconds specified by the timeout value, the worker processing the job will exit with an error. Typically, the worker will be restarted automatically by a [process manager configured on your server](#supervisor-configuration):

```shell
php artisan queue:work --timeout=60
```

The `retry_after` configuration option and the `--timeout` CLI option are different, but work together to ensure that jobs are not lost and that jobs are only successfully processed once.

> {note} The `--timeout` value should always be at least several seconds shorter than your `retry_after` configuration value. This will ensure that a worker processing a frozen job is always terminated before the job is retried. If your `--timeout` option is longer than your `retry_after` configuration value, your jobs may be processed twice.


<a name="supervisor-configuration"></a>

## Supervisor Configuration

In production, you need a way to keep your `queue:work` processes running. A `queue:work` process may stop running for a variety of reasons, such as an exceeded worker timeout or the execution of the `queue:restart` command.

For this reason, you need to configure a process monitor that can detect when your `queue:work` processes exit and automatically restart them. In addition, process monitors can allow you to specify how many `queue:work` processes you would like to run concurrently. Supervisor is a process monitor commonly used in Linux environments and we will discuss how to configure it in the following documentation.

<a name="installing-supervisor"></a>

#### Installing Supervisor

Supervisor is a process monitor for the Linux operating system, and will automatically restart your `queue:work` processes if they fail. To install Supervisor on Ubuntu, you may use the following command:

```shell
sudo apt-get install supervisor
```

> {tip} If configuring and managing Supervisor yourself sounds overwhelming, consider using [Laravel Forge](https://forge.laravel.com), which will automatically install and configure Supervisor for your production Laravel projects.


<a name="configuring-supervisor"></a>

#### Configuring Supervisor

Supervisor configuration files are typically stored in the `/etc/supervisor/conf.d` directory. Within this directory, you may create any number of configuration files that instruct supervisor how your processes should be monitored. For example, let's create a `laravel-worker.conf` file that starts and monitors `queue:work` processes:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/forge/app.com/artisan queue:work sqs --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=forge
numprocs=8
redirect_stderr=true
stdout_logfile=/home/forge/app.com/worker.log
stopwaitsecs=3600
```

In this example, the `numprocs` directive will instruct Supervisor to run eight `queue:work` processes and monitor all of them, automatically restarting them if they fail. You should change the `command` directive of the configuration to reflect your desired queue connection and worker options.

> {note} You should ensure that the value of `stopwaitsecs` is greater than the number of seconds consumed by your longest running job. Otherwise, Supervisor may kill the job before it is finished processing.


<a name="starting-supervisor"></a>

#### Starting Supervisor

Once the configuration file has been created, you may update the Supervisor configuration and start the processes using the following commands:

```shell
sudo supervisorctl reread

sudo supervisorctl update

sudo supervisorctl start laravel-worker:*
```

For more information on Supervisor, consult the [Supervisor documentation](http://supervisord.org/index.html).

<a name="dealing-with-failed-jobs"></a>

## Dealing With Failed Jobs

Sometimes your queued jobs will fail. Don't worry, things don't always go as planned! Laravel includes a convenient way to [specify the maximum number of times a job should be attempted](#max-job-attempts-and-timeout). After an asynchronous job has exceeded this number of attempts, it will be inserted into the `failed_jobs` database table. [Synchronously dispatched jobs](/docs/{{version}}/queues#synchronous-dispatching) that fail are not stored in this table and their exceptions are immediately handled by the application.

A migration to create the `failed_jobs` table is typically already present in new Laravel applications. However, if your application does not contain a migration for this table, you may use the `queue:failed-table` command to create the migration:

```shell
php artisan queue:failed-table

php artisan migrate
```

When running a [queue worker](#running-the-queue-worker) process, you may specify the maximum number of times a job should be attempted using the `--tries` switch on the `queue:work` command. If you do not specify a value for the `--tries` option, jobs will only be attempted once or as many times as specified by the job class' `$tries` property:

```shell
php artisan queue:work redis --tries=3
```

Using the `--backoff` option, you may specify how many seconds Laravel should wait before retrying a job that has encountered an exception. By default, a job is immediately released back onto the queue so that it may be attempted again:

```shell
php artisan queue:work redis --tries=3 --backoff=3
```

If you would like to configure how many seconds Laravel should wait before retrying a job that has encountered an exception on a per-job basis, you may do so by defining a `backoff` property on your job class:

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 3;

If you require more complex logic for determining the job's backoff time, you may define a `backoff` method on your job class:

    /**
    * Calculate the number of seconds to wait before retrying the job.
    *
    * @return int
    */
    public function backoff()
    {
        return 3;
    }

You may easily configure "exponential" backoffs by returning an array of backoff values from the `backoff` method. In this example, the retry delay will be 1 second for the first retry, 5 seconds for the second retry, and 10 seconds for the third retry:

    /**
    * Calculate the number of seconds to wait before retrying the job.
    *
    * @return array
    */
    public function backoff()
    {
        return [1, 5, 10];
    }

<a name="cleaning-up-after-failed-jobs"></a>

### Cleaning Up After Failed Jobs

When a particular job fails, you may want to send an alert to your users or revert any actions that were partially completed by the job. To accomplish this, you may define a `failed` method on your job class. The `Throwable` instance that caused the job to fail will be passed to the `failed` method:

    <?php
    
    namespace App\Jobs;
    
    use App\Models\Podcast;
    use App\Services\AudioProcessor;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    use Throwable;
    
    class ProcessPodcast implements ShouldQueue
    {
        use InteractsWithQueue, Queueable, SerializesModels;
    
        /**
         * The podcast instance.
         *
         * @var \App\Podcast
         */
        protected $podcast;
    
        /**
         * Create a new job instance.
         *
         * @param  \App\Models\Podcast  $podcast
         * @return void
         */
        public function __construct(Podcast $podcast)
        {
            $this->podcast = $podcast;
        }
    
        /**
         * Execute the job.
         *
         * @param  \App\Services\AudioProcessor  $processor
         * @return void
         */
        public function handle(AudioProcessor $processor)
        {
            // Process uploaded podcast...
        }
    
        /**
         * Handle a job failure.
         *
         * @param  \Throwable  $exception
         * @return void
         */
        public function failed(Throwable $exception)
        {
            // Send user notification of failure, etc...
        }
    }

> {note} A new instance of the job is instantiated before invoking the `failed` method; therefore, any class property modifications that may have occurred within the `handle` method will be lost.


<a name="retrying-failed-jobs"></a>

### Retrying Failed Jobs

To view all of the failed jobs that have been inserted into your `failed_jobs` database table, you may use the `queue:failed` Artisan command:

```shell
php artisan queue:failed
```

The `queue:failed` command will list the job ID, connection, queue, failure time, and other information about the job. The job ID may be used to retry the failed job. For instance, to retry a failed job that has an ID of `ce7bb17c-cdd8-41f0-a8ec-7b4fef4e5ece`, issue the following command:

```shell
php artisan queue:retry ce7bb17c-cdd8-41f0-a8ec-7b4fef4e5ece
```

If necessary, you may pass multiple IDs to the command:

```shell
php artisan queue:retry ce7bb17c-cdd8-41f0-a8ec-7b4fef4e5ece 91401d2c-0784-4f43-824c-34f94a33c24d
```

You may also retry all of the failed jobs for a particular queue:

```shell
php artisan queue:retry --queue=name
```

To retry all of your failed jobs, execute the `queue:retry` command and pass `all` as the ID:

```shell
php artisan queue:retry all
```

If you would like to delete a failed job, you may use the `queue:forget` command:

```shell
php artisan queue:forget 91401d2c-0784-4f43-824c-34f94a33c24d
```

> {tip} When using [Horizon](/docs/{{version}}/horizon), you should use the `horizon:forget` command to delete a failed job instead of the `queue:forget` command.


To delete all of your failed jobs from the `failed_jobs` table, you may use the `queue:flush` command:

```shell
php artisan queue:flush
```

<a name="ignoring-missing-models"></a>

### Ignoring Missing Models

When injecting an Eloquent model into a job, the model is automatically serialized before being placed on the queue and re-retrieved from the database when the job is processed. However, if the model has been deleted while the job was waiting to be processed by a worker, your job may fail with a `ModelNotFoundException`.

For convenience, you may choose to automatically delete jobs with missing models by setting your job's `deleteWhenMissingModels` property to `true`. When this property is set to `true`, Laravel will quietly discard the job without raising an exception:

    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

<a name="pruning-failed-jobs"></a>

### Pruning Failed Jobs

You may delete all of the records in your application's `failed_jobs` table by invoking the `queue:prune-failed` Artisan command:

```shell
php artisan queue:prune-failed
```

If you provide the `--hours` option to the command, only the failed job records that were inserted within the last N number of hours will be retained. For example, the following command will delete all of the failed job records that were inserted more than 48 hours ago:

```shell
php artisan queue:prune-failed --hours=48
```

<a name="storing-failed-jobs-in-dynamodb"></a>

### Storing Failed Jobs In DynamoDB

Laravel also provides support for storing your failed job records in [DynamoDB](https://aws.amazon.com/dynamodb) instead of a relational database table. However, you must create a DynamoDB table to store all of the failed job records. Typically, this table should be named `failed_jobs`, but you should name the table based on the value of the `queue.failed.table` configuration value within your application's `queue` configuration file.

The `failed_jobs` table should have a string primary partition key named `application` and a string primary sort key named `uuid`. The `application` portion of the key will contain your application's name as defined by the `name` configuration value within your application's `app` configuration file. Since the application name is part of the DynamoDB table's key, you can use the same table to store failed jobs for multiple Laravel applications.

In addition, ensure that you install the AWS SDK so that your Laravel application can communicate with Amazon DynamoDB:

```shell
composer require aws/aws-sdk-php
```

Next, set the `queue.failed.driver` configuration option's value to `dynamodb`. In addition, you should define `key`, `secret`, and `region` configuration options within the failed job configuration array. These options will be used to authenticate with AWS. When using the `dynamodb` driver, the `queue.failed.database` configuration option is unnecessary:

```php
'failed' => [
    'driver' => env('QUEUE_FAILED_DRIVER', 'dynamodb'),
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    'table' => 'failed_jobs',
],
```

<a name="disabling-failed-job-storage"></a>

### Disabling Failed Job Storage

You may instruct Laravel to discard failed jobs without storing them by setting the `queue.failed.driver` configuration option's value to `null`. Typically, this may be accomplished via the `QUEUE_FAILED_DRIVER` environment variable:

```ini
QUEUE_FAILED_DRIVER=null
```

<a name="failed-job-events"></a>

### Failed Job Events

If you would like to register an event listener that will be invoked when a job fails, you may use the `Queue` facade's `failing` method. For example, we may attach a closure to this event from the `boot` method of the `AppServiceProvider` that is included with Laravel:

    <?php
    
    namespace App\Providers;
    
    use Illuminate\Support\Facades\Queue;
    use Illuminate\Support\ServiceProvider;
    use Illuminate\Queue\Events\JobFailed;
    
    class AppServiceProvider extends ServiceProvider
    {
        /**
         * Register any application services.
         *
         * @return void
         */
        public function register()
        {
            //
        }
    
        /**
         * Bootstrap any application services.
         *
         * @return void
         */
        public function boot()
        {
            Queue::failing(function (JobFailed $event) {
                // $event->connectionName
                // $event->job
                // $event->exception
            });
        }
    }

<a name="clearing-jobs-from-queues"></a>

## Clearing Jobs From Queues

> {tip} When using [Horizon](/docs/{{version}}/horizon), you should use the `horizon:clear` command to clear jobs from the queue instead of the `queue:clear` command.


If you would like to delete all jobs from the default queue of the default connection, you may do so using the `queue:clear` Artisan command:

```shell
php artisan queue:clear
```

You may also provide the `connection` argument and `queue` option to delete jobs from a specific connection and queue:

```shell
php artisan queue:clear redis --queue=emails
```

> {note} Clearing jobs from queues is only available for the SQS, Redis, and database queue drivers. In addition, the SQS message deletion process takes up to 60 seconds, so jobs sent to the SQS queue up to 60 seconds after you clear the queue might also be deleted.


<a name="monitoring-your-queues"></a>

## Monitoring Your Queues

If your queue receives a sudden influx of jobs, it could become overwhelmed, leading to a long wait time for jobs to complete. If you wish, Laravel can alert you when your queue job count exceeds a specified threshold.

To get started, you should schedule the `queue:monitor` command to [run every minute](/docs/{{version}}/scheduling). The command accepts the names of the queues you wish to monitor as well as your desired job count threshold:

```shell
php artisan queue:monitor redis:default,redis:deployments --max=100
```

Scheduling this command alone is not enough to trigger a notification alerting you of the queue's overwhelmed status. When the command encounters a queue that has a job count exceeding your threshold, an `Illuminate\Queue\Events\QueueBusy` event will be dispatched. You may listen for this event within your application's `EventServiceProvider` in order to send a notification to you or your development team:

```php
use App\Notifications\QueueHasLongWaitTime;
use Illuminate\Queue\Events\QueueBusy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

/**
 * Register any other events for your application.
 *
 * @return void
 */
public function boot()
{
    Event::listen(function (QueueBusy $event) {
        Notification::route('mail', 'dev@example.com')
                ->notify(new QueueHasLongWaitTime(
                    $event->connection,
                    $event->queue,
                    $event->size
                ));
    });
}
```

<a name="job-events"></a>

## Job Events

Using the `before` and `after` methods on the `Queue` [facade](/docs/{{version}}/facades), you may specify callbacks to be executed before or after a queued job is processed. These callbacks are a great opportunity to perform additional logging or increment statistics for a dashboard. Typically, you should call these methods from the `boot` method of a [service provider](/docs/{{version}}/providers). For example, we may use the `AppServiceProvider` that is included with Laravel:

    <?php
    
    namespace App\Providers;
    
    use Illuminate\Support\Facades\Queue;
    use Illuminate\Support\ServiceProvider;
    use Illuminate\Queue\Events\JobProcessed;
    use Illuminate\Queue\Events\JobProcessing;
    
    class AppServiceProvider extends ServiceProvider
    {
        /**
         * Register any application services.
         *
         * @return void
         */
        public function register()
        {
            //
        }
    
        /**
         * Bootstrap any application services.
         *
         * @return void
         */
        public function boot()
        {
            Queue::before(function (JobProcessing $event) {
                // $event->connectionName
                // $event->job
                // $event->job->payload()
            });
    
            Queue::after(function (JobProcessed $event) {
                // $event->connectionName
                // $event->job
                // $event->job->payload()
            });
        }
    }

Using the `looping` method on the `Queue` [facade](/docs/{{version}}/facades), you may specify callbacks that execute before the worker attempts to fetch a job from a queue. For example, you might register a closure to rollback any transactions that were left open by a previously failed job:

    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Queue;
    
    Queue::looping(function () {
        while (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
    });
