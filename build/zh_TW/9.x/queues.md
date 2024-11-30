---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/125/en-zhtw'
updatedAt: '2024-06-30T08:15:00Z'
contributors: {  }
progress: 47.13
---

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
  
- [Supervisor 設定](#supervisor-configuration)
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

> [!NOTE]  
> Laravel 現在還提供 Horizon。Horizon 是為 Redis 佇列提供的一個一個漂亮面板。更多資訊請參考完整的 [Horizon 說明文件](/docs/{{version}}/horizon)。

<a name="connections-vs-queues"></a>

### 連線 Vs. 佇列

在開始使用 Laravel 佇列前，我們需要先瞭解「^[連線](Connection)」與「^[佇列](Queue)」的差別。在 `config/queue.php` 設定檔中有個 `connections` 設定陣列。該選項用於定義連到後端佇列服務的連線，後端佇列服務就是像 Amazon SQS、Beanstalk、Redis 等。不過，一個佇列連線可以有多個「佇列」，我們可以將這些不同的佇列想成是幾個不同堆疊的佇列任務。

可以注意到範例 `queue` 設定檔中的各個範例連線設定中都包含了一個 `queue` 屬性。這個 `queue` 屬性指定的，就是當我們將任務傳給這個連線時預設會被分派的佇列。換句話說，若我們在分派任務時沒有顯式定義要分派到哪個佇列上，這個任務就會被分派到連線設定中 `queue` 屬性所定義的佇列上：

    use App\Jobs\ProcessPodcast;
    
    // This job is sent to the default connection's default queue...
    ProcessPodcast::dispatch();
    
    // This job is sent to the default connection's "emails" queue...
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

若 Redis 佇列要使用 Redis Cluster，則設定的佇列名稱必須包含一個 [Key Hash Tag](https://redis.io/docs/reference/cluster-spec/#hash-tags)。必須加上 Key Hash Tag，這樣才能確保給定佇列中所有的 Redis 索引鍵都有被放在相同的 Hash Slot 中：

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
> [!WARNING]  
> 將 `block_for` 設為 `0` 會導致 Queue Worker 在新 Job 出現前一直 Block。也會導致在處理下一個 Job 前都無法處理如 `SIGTERM` 等訊號 (Signal)。

<a name="other-driver-prerequisites"></a>

#### 其他 Driver 的前置要求

下列 Queue Driver 還需要一些相依性套件。可以使用 Composer 套件管理員來安裝這些相依性套件：

<div class="content-list" markdown="1">
- Amazon SQS: `aws/aws-sdk-php ~3.0`
- Beanstalkd: `pda/pheanstalk ~4.0`
- Redis: `predis/predis ~1.0` 或 phpredis PHP 擴充套件

</div>
<a name="creating-jobs"></a>

## 建立 Job

<a name="generating-job-classes"></a>

### 產生 Job 類別

預設情況下，專案中所有可放入佇列的任務都存放在 `app/Jobs` 目錄內。若 `app/Jobs` 目錄不存在，則執行 `make:jobs` Artisan 指令時會建立該目錄：

```shell
php artisan make:job ProcessPodcast
```
產生的類別會實作 `Illuminate\Contracts\Queue\ShouldQueue` 介面，這樣 Laravel 就知道該 Job 要被推入佇列並以非同步方式執行。

> [!NOTE]  
> 可以[安裝 Stub](/docs/{{version}}/artisan#stub-customization) 來自訂 Job 的 Stub。

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
        public $podcast;
    
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
在這個範例中，可以看到我們直接將 [Eloquent Model] 傳入佇列任務的 ^[Constructor](%E5%BB%BA%E6%A7%8B%E5%87%BD%E5%BC%8F) 中。由於該任務有使用 `SerializesModels` Trait，所以 Eloquent Model 與已載入的關聯 Model 都會被序列化處理，並在處理任務時反序列化。

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
> [!WARNING]  
> 二進位資料，如圖片等，應在傳入佇列任務前先使用 `base64_encode` 函式進行編碼。若未進行編碼，則這些資料在放入佇列時可能無法正確被序列化為 JSON。

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

> [!WARNING]  
> 若要使用不重複任務，則需要使用支援 [Atomic Lock] 的快取 Driver。目前，`memcached`、`redis`、`dynamodb`、`database`、`file`、`array` 等快取 Driver 有支援 Atomic Lock。此外，不重複任務的^[條件限制](Constraint)不會被套用到批次任務中的人物上。

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
    
    use App\Models\Product;
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

> [!WARNING]  
> 若專案會在多個 Web Server 或 Container 上分派任務，則請確保所有的這些 Server 都使用相同的中央 Cache Server，好讓 Laravel 可精準判斷該 Job 是否不重複。

<a name="keeping-jobs-unique-until-processing-begins"></a>

#### 在開始處理 Job 後仍維持讓 Job 不重複

預設情況下，不重複的 Job 會在執行完成或所有嘗試都失敗後「解除鎖定」。不過，有的情況下，我們可能會想在執行完成前就先解除鎖定 Job。為此，不要在該 Job 上實作 `ShouldBeUnique`，而是實作 `ShouldBeUniqueUntillProcessing` Contract：

    <?php
    
    use App\Models\Product;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
    
    class UpdateSearchIndex implements ShouldQueue, ShouldBeUniqueUntilProcessing
    {
        // ...
    }
<a name="unique-job-locks"></a>

#### 不重複 Job 的鎖定

當分派 `ShouldBeUnique` 時，Laravel 會在幕後使用 `uniqueId` 索引鍵來取得一個 [^[Lock](%E9%8E%96%E5%AE%9A)](/docs/{{version}}/cache#atomic-locks)。若未能取得 Lock，就不會分派該 Job。當 Job 完成處理或所有嘗試都失敗後，就會解除該 Lock。預設情況下，Laravel 會使用預設的快取 Driver 來取得該 Lock。不過，若想使用其他 Driver 來取得 Lock，可定義一個 `uniqueVia` 方法，並在該方法中回傳要使用的快取 Driver：

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
> [!NOTE]  
> 若想限制某個 Job 可^[同時](Concurrent)執行的數量，請使用 [`WithoutOverlapping`](/docs/{{version}}/queues#preventing-job-overlaps) Job Middleware 而不是使用 Unique Job。

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
            info('Lock obtained...');
    
            // Handle job...
        }, function () {
            // Could not obtain lock...
    
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
> [!NOTE]  
> Job Middleware 也可以被指派給可放入佇列的 Event Listener、Mailable、Notification 等。

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
> [!NOTE]  
> 若使用 Redis，可使用 `Illuminate\Queue\Middleware\RateLimitedWithRedis` Middleware。這個 Middleware 有為 Redis 做最佳化，比起一般基礎的頻率限制 Middleware 來說會更有效率。

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
每當有重疊的 Job，這些 Job 都會被重新放到佇列中。可以指定這些被重新放回佇列的 Job 在重新嘗試前必須等待多久的秒數：

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
> [!WARNING]  
> 若要使用 `WithoutOverlapping` Middleware，則需要使用支援 [Atomic Lock] 的快取 Driver。目前，`memcached`、`redis`、`dynamodb`、`database`、`file`、`array` 等快取 Driver 有支援 Atomic Lock。

<a name="sharing-lock-keys"></a>

#### 在多個 Job 類別中共用 Lock 索引鍵

預設情況下，`WithoutOverlapping` Middleware 只會防止相同類別的重疊 Job。因此，即使某兩個不同的 Job 可能使用相同的 Lock 索引鍵，仍然無法防止 Job 重疊。不過，可以使用 `shared` 方法來指定讓 Laravel 在多個 Job 類別間套用同樣的索引鍵：

```php
use Illuminate\Queue\Middleware\WithoutOverlapping;

class ProviderIsDown
{
    // ...


    public function middleware()
    {
        return [
            (new WithoutOverlapping("status:{$this->provider}"))->shared(),
        ];
    }
}

class ProviderIsUp
{
    // ...


    public function middleware()
    {
        return [
            (new WithoutOverlapping("status:{$this->provider}"))->shared(),
        ];
    }
}
```
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
> [!NOTE]  
> 若使用 Redis，可使用 `Illuminate\Queue\Middleware\ThrottlesExceptionsWithRedis` Middleware。該 Middleware 有為 Redis 最佳化，因此會比一般的 Exception 頻率限制 Middleware 還要有效率。

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
            $podcast = Podcast::create(/* ... */);
    
            // ...
    
            ProcessPodcast::dispatch($podcast);
        }
    }
若想要有條件地分派 Job，可使用 `dispatchIf` 與` `dispatchUnless` 方法：

    ProcessPodcast::dispatchIf($accountActive, $podcast);
    
    ProcessPodcast::dispatchUnless($accountSuspended, $podcast);
在新的 Laravel 專案中，預設的 Queue Driver 是 `sync` Driver。該 Driver 會在目前 Request 中的前景 (Foreground) 同步執行 Job。若想要讓 Job 被真正放進佇列中在背景執行，你需要在專案的 `config/queue.php` 設定檔中指定一個不同的 Queue Driver。

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
            $podcast = Podcast::create(/* ... */);
    
            // ...
    
            ProcessPodcast::dispatch($podcast)
                        ->delay(now()->addMinutes(10));
        }
    }
> [!WARNING]  
> Amazon SQS 佇列服務的延遲時間最多只能為 15 分鐘。

<a name="dispatching-after-the-response-is-sent-to-browser"></a>

#### 在 Response 被傳送給瀏覽器後才進行分派

如果你的網頁伺服器使用 FastCGI，則 `dispatchAfterResponse` 是另一個分派 Job 的方法。該方法會延遲分派 Job，直到 HTTP Response 被傳回使用者瀏覽器後才開始處理Job。這樣一來，在處理佇列 Job 的同時，使用者就能繼續使用我們的網站。一般來說，這種做法應只用於一些只需花費 1 秒鐘的 Job，如寄送 E-Mail 等。由於這些 Job 會在目前的 HTTP Request 中處理，因此使用這種方式分派 Job 就不需要執行 Queue Worker：

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
            $podcast = Podcast::create(/* ... */);
    
            // Create podcast...
    
            ProcessPodcast::dispatchSync($podcast);
        }
    }
<a name="jobs-and-database-transactions"></a>

### Job 與資料庫 Transaction

雖然，在資料庫 Transaction 中分派 Job 是完全 OK 的，但應特別注意 Job 能否被正確執行。當我們在 Transaction 中分派 Job 後，這個 Job 很有可能會在上層 Transaction 被 Commit 前就被 Queue Worker 給執行了。這時候，我們在 Transaction 中對 Model 或資料庫記錄所做出的更改都還未反應到資料庫上。而且，在 Transaction 中做建立的 Model 或資料庫記錄也可能還未出現在資料庫中。

幸好，Laravel 提供了數種方法可解決這個狀況。第一種方法，我們可以在 Queue 連線的設定陣列中設定 `after_commit` 連線選項：

    'redis' => [
        'driver' => 'redis',
        // ...
        'after_commit' => true,
    ],
當 `after_commit` 設為 `true` 後，我們就可以在資料庫 Transaction 中分派 Job 了。Laravel 會等到未完成的上層資料庫 Transaction 都被 Commit 後才將 Job 分派出去。不過，當然，若目前沒有正在處理的資料庫 Transaction，這個 Job 會馬上被分派。

若因為 Transaction 中發上 Exception 而造成 Transaction 被 ^[Roll Back](%E5%9B%9E%E6%BA%AF)，則在這個 Transaction 間所分派的 Job 也會被取消。

> [!NOTE]  
> 將 `after_commit` 設定選項設為 `true` 後，所有放入佇列的 Listener、Maillable、Notification、廣播事件……等都會等待到所有資料庫 Transaciton 都 Commit 後才被分派。

<a name="specifying-commit-dispatch-behavior-inline"></a>

#### 內嵌指定 Commit 的分派行為

若未將 `after_commit` 佇列連線選項設為 `true`，則我們還是可以指定讓某個特定的 Job 在所有已開啟的資料庫 Transaction 都被 Commit 後才被分派。若要這麼做，可在分派動作後串上 `afterCommit` 方法：

    use App\Jobs\ProcessPodcast;
    
    ProcessPodcast::dispatch($podcast)->afterCommit();
同樣地，若 `after_commit` 選項為 `true`，則我們也可以馬上分派某個特定的 Job，而不等待資料庫 Transaction 的 Commit：

    ProcessPodcast::dispatch($podcast)->beforeCommit();
<a name="job-chaining"></a>

### Job 的串聯

通過 Job 串聯，我們就可以指定一組佇列 Job 的清單，在主要 Job 執行成功後才依序執行這組 Job。若按照順序執行的其中一個 Job 執行失敗，則剩下的 Job 都將不被執行。若要執行佇列的 Job 串聯，可使用 `Bus` Facade 中的 `chain` 方法。Laravel 的 ^[Command Bus](%E6%8C%87%E4%BB%A4%E5%8C%AF%E6%B5%81%E6%8E%92) 是一個低階的原件，佇列 Job 的分派功能就是使用這個原件製作的：

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
            Podcast::update(/* ... */);
        },
    ])->dispatch();
> [!WARNING]  
> 在 Job 中使用 `$this->delete()` 方法來刪除 Job 是沒有辦法讓串聯的 Job 不被執行的。只有當串聯中的 Job 失敗時才會停止執行。

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
        // A job within the chain has failed...
    })->dispatch();
> [!WARNING]  
> 由於串聯的回呼會被序列化並在稍後由 Laravel 的佇列執行，因此請不要在串聯的回呼中使用 `$this` 變數。

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
            $podcast = Podcast::create(/* ... */);
    
            // Create podcast...
    
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
            $podcast = Podcast::create(/* ... */);
    
            // Create podcast...
    
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

其中一種指定 Job 最大嘗試次數的方法是在 Artisan 指令列中使用 `--tries` 開關。使用這種方式指定的嘗試次數會套用到所有該 Worker 處理的 Job，除非 Job 上有指定嘗試次數：

```shell
php artisan queue:work --tries=3
```
若 Job 嘗試了最大嘗試次數，則這個 Job 會被視為是「^[執行失敗](Failed)」。更多有關處理執行失敗 Job 的資訊，請參考 [執行失敗 Job 的說明文件](#dealing-with-failed-jobs)。若提供 `--tries=0` 給 `queue:work` 指令，則失敗的 Job 會被無限次數重試。

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
> [!NOTE]  
> 也可以在[放入佇列的 Event Listener](/docs/{{version}}/events#queued-event-listeners) 中定義一個 `tries` 屬性或 `retryUntil` 方法。

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
                // Lock obtained, process the podcast...
            }, function () {
                // Unable to obtain lock...
                return $this->release(10);
            });
        }
    }
在這個例子中，這個 Job 會在程式無法在 10 秒內取得 Redis Lock 時被釋放，而這個 Job 在此期間最多可嘗試 25 次。不過，若 Job 中有擲回未處理的 Exception，則會被視為是失敗的 Job。

<a name="timeout"></a>

#### 逾時

> [!WARNING]  
> 必須安裝 `pcntl` PHP 擴充程式才可指定 Job 的逾時。

通常來說，我們知道某個佇列任務大約需要花多少時間執行。因此，在 Laravel 中，我們可以指定一個「逾時」值。預設情況下，逾時值為 60 秒。若 Job 執行超過逾時值所指定的秒數後，負責處理該 Job 的 Worker 就會以錯誤終止執行。一般來說，Worker 會自動由 [Server 上設定的 Process Manager](#supervisor-configuration) 重新開啟。

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
若想以所 Catch 到的 Exception 來將 Job 標記為失敗，可將該 Exception 傳入 `fail` 方法。或者，為了讓開發起來更方便，也可以傳入一個錯誤訊息字串，而該字串會自動被轉為 Exception：

    $this->fail($exception);
    
    $this->fail('Something went wrong.');
> [!NOTE]  
> 有關失敗 Job 的更多資訊，請參考[有關處理失敗 Job 的說明文件](#dealing-with-failed-jobs)。

<a name="job-batching"></a>

## 批次 Job

使用 Laravel 的批次 Job 功能，就可以輕鬆地批次執行多個 Job，並在批次 Job 執行完成後進行一些動作。在開始使用批次 Job 之前，我們需要先建立一個資料庫 Migration，以建立用來保存有關批次 Job ^[詮釋資訊](Meta Information)的資料表，如批次 Job 的完成度等。可以使用 `queue:batches-table` Artisan 指令來建立這個 Migration：

```shell
php artisan queue:batches-table

php artisan migrate
```
<a name="defining-batchable-jobs"></a>

### 定義可批次處理的 Job

若要定義可批次處理的 Job，請先像平常一樣[建立可放入佇列的 Job](#creating-jobs)。不過，我們還需要在這個 Job 類別中加上 `Illuminate\Bus\Batchable` Trait。這個 Trait 提供了一個 `batch` 方法，可使用該方法來取得該 Job 所在的批次：

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

### 分派批次

若要分派一批次的 Job，可使用 `Bus` Facade 的 `batch` 方法。當然，批次功能與完成回呼一起使用時是最有用。因此，可以使用 `then`, `catch` 與 `finally` 方法來為該批次定義完成回呼。這些回呼都會在被叫用時收到 `Illuminate\Bus\Batch` 實體。在這個範例中，我們先假設我們正在處理一批次的任務，用來在 CSV 檔中處理給定數量的行：

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
可使用 `$batch->id` 屬性來取得該批次的 ID。在該批次被分派後，可使用這個 ID 來[向 Laravel 的 Command Bus 查詢](#inspecting-batches)有關該批次的資訊。

> [!WARNING]  
> 由於批次的回呼會被序列化並在稍後由 Laravel 的佇列執行，因此請不要在回呼中使用 `$this` 變數。

<a name="naming-batches"></a>

#### 為批次命名

若為批次命名，則一些像是 Laravel Horizon 與 Laravel Telescope 之類的工具就可為該批次提供對使用者更友善的偵錯資訊。若要為批次指定任意名稱，可在定義批次時呼叫 `name` 方法：

    $batch = Bus::batch([
        // ...
    ])->then(function (Batch $batch) {
        // All jobs completed successfully...
    })->name('Import CSV')->dispatch();
<a name="batch-connection-queue"></a>

#### 批次的連線與佇列

若想指定批次 Job 的連線與佇列，可使用 `onConnection` 與 `onQueue` 方法。所有的批次 Job 都必須要相同的連線與佇列中執行：

    $batch = Bus::batch([
        // ...
    ])->then(function (Batch $batch) {
        // All jobs completed successfully...
    })->onConnection('redis')->onQueue('imports')->dispatch();
<a name="chains-within-batches"></a>

#### 在批次中串聯

只要將串聯的 Job 放在陣列中，就可以在批次中定義一組[串聯的 Job](#job-chaining)。舉例來說，我們可以平行執行兩個 Job 串聯，並在這兩個 Job 串聯都處理完畢後執行回呼：

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

### 將 Job 加入批次

有時候，若能在批次 Job 中新增其他額外的 Job 會很實用。特別是當我們要在一個 Web Request 中批次處理數千筆 Job 時，會讓 Job 的分派過程變得很耗時。因此，比起直接分派數千筆 Job，我們可以先分派一個初始化的批次，用來作為 Job 的「載入程式」，然後讓這個載入程式再向批次內填入更多的 Job：

    $batch = Bus::batch([
        new LoadImportBatch,
        new LoadImportBatch,
        new LoadImportBatch,
    ])->then(function (Batch $batch) {
        // All jobs completed successfully...
    })->name('Import Contacts')->dispatch();
在這個例子中，我們可以使用 `LoadImportBatch` Job 來填入其他額外的 Job。若要填入其他 Job，我們可以使用批次實體上的 `add` 方法。批次實體可使用 Job 的 `batch` 方法來取得：

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
> [!WARNING]  
> 我們只能向目前 Job 正在執行的批次新增 Job。

<a name="inspecting-batches"></a>

### 檢查批次

提供給批次處理完成回呼的 `Illuminate\Bus\Batch` 實體有許多的屬性與方法，可以讓我們處理與取得給定 Job 批次的資訊：

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

#### 從 Route 上回傳批次

所有的 `Illuminate\Bus\Batch` 實體都可被序列化為 JSON，因此我們可以直接在專案的 Route 中回傳批次實體來取得有關該批次資訊的 JSON Payload，其中也包含該批次的完成度。如此一來，我們就能方便地在專案的 UI 上顯示該批次完成度的資訊。

若要使用 ID 來取得批次，可使用 `Bus` Facade 的 `findBatch` 方法：

    use Illuminate\Support\Facades\Bus;
    use Illuminate\Support\Facades\Route;
    
    Route::get('/batch/{batchId}', function (string $batchId) {
        return Bus::findBatch($batchId);
    });
<a name="cancelling-batches"></a>

### 取消批次

有時候，我們會需要取消給定批次的執行。若要取消執行批次，可在 `Illuminate\Bus\Batch` 實體上呼叫 `cancel` 方法：

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
讀者可能已經從上面的範例中注意到，批次的 Job 一般都應該在繼續執行前先判斷自己所在的批次是否已被取消。不過，為了讓開發過程更方便，也可以在 Job 上指定 `SkipIfBatchCancelled` [Middleware](#job-middleware)，這樣就不需要手動檢查批次是否已被取消。就像該 Middleware 的名稱一樣，這個 Middleware 會告訴 Laravel，當 Job 對應的批次被取消時，就不要在繼續處理 Job：

    use Illuminate\Queue\Middleware\SkipIfBatchCancelled;
    
    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [new SkipIfBatchCancelled];
    }
<a name="batch-failures"></a>

### 批次失敗

若批次中的 Job 執行失敗，則會叫用 `catch` 回呼 (若有指定的話)。只有在批次中第一個失敗的 Job 才會叫用該回呼。

<a name="allowing-failures"></a>

#### 允許失敗

若在批次中的 Job 執行失敗，Laravel 會自動將該批次標記為「^[已取消](Cancelled)」。若有需要的話，我們可以禁用這個行為，好讓 Job 失敗是不要自動將批次標記為取消。若要禁用此行為，可在分派批次時呼叫 `allowFailures` 方法：

    $batch = Bus::batch([
        // ...
    ])->then(function (Batch $batch) {
        // All jobs completed successfully...
    })->allowFailures()->dispatch();
<a name="retrying-failed-batch-jobs"></a>

#### 重試失敗的批次 Job

Laravel 提供了一個方便的 `queue:retry-batch` Artisan 指令，能讓我們輕鬆重試給定批次中所有失敗的 Job。`queue:retry-batch` 指令的參數為要重試 Job 之批次的 UUID：

```shell
php artisan queue:retry-batch 32dbc76c-4f82-4749-b610-a639fe0099b5
```
<a name="pruning-batches"></a>

### 修建批次

若未^[修建](Prune)批次，則 `job_batches` 資料表很快就會變得很大。為了避免這個狀況，應[定期](/docs/{{version}}/scheduling)每日執行 `queue:prune-batches` Artisan 指令：

    $schedule->command('queue:prune-batches')->daily();
預設情況下，完成超過 24 小時的批次會被修建掉。可以在呼叫該指令時使用 `hours` 選項來指定批次資料要保留多久。舉例來說，下列指令會刪除完成超過 48 小時前的所有批次：

    $schedule->command('queue:prune-batches --hours=48')->daily();
有時候，`jobs_batches` 資料表可能會有一些從未成功完成的批次記錄，如批次中有 Job 失敗且從每次嘗試都失敗的批次。可以在 `queue:prune-batxhes` 指令的使用 `unfinished` 選項來修剪這些未完成的批次：

    $schedule->command('queue:prune-batches --hours=48 --unfinished=72')->daily();
類似的，`jobs_batches` 資料表可能會累積一些已取消批次的批次記錄。可以在 `queue:prune-batxhes` 指令上使用 `cancelled` 選項來修剪這些已取消的批次：

    $schedule->command('queue:prune-batches --hours=48 --cancelled=72')->daily();
<a name="queueing-closures"></a>

## 將閉包放入佇列

除了將 Job 類別分派進佇列外，我們也可以分派閉包。分派閉包對於一些要在目前 Request 週期外執行的快速、簡單任務來說很好用。把閉包放入佇列時，該閉包的程式碼內容會以密碼學的方式進行簽署，以避免其程式碼在傳輸過程中遭到篡改：

    $podcast = App\Podcast::find(1);
    
    dispatch(function () use ($podcast) {
        $podcast->publish();
    });
使用 `catch` 方法，就能為佇列閉包提供一組要在所有[重試次數](#max-job-attempts-and-timeout)都失敗的時候執行的閉包：

    use Throwable;
    
    dispatch(function () use ($podcast) {
        $podcast->publish();
    })->catch(function (Throwable $e) {
        // This job has failed...
    });
> [!WARNING]  
> 由於 `catch` 的回呼會被序列化並在稍後由 Laravel 的佇列執行，因此請不要在 `catch` 的回呼中使用 `$this` 變數。

<a name="running-the-queue-worker"></a>

## 執行 Queue Worker

<a name="the-queue-work-command"></a>

### 使用 `queue:work` 指令

Laravel 中隨附了一個 Artisan 指令，可用來開啟 ^[Queue Worker](%E4%BD%87%E5%88%97%E8%83%8C%E6%99%AF%E5%B7%A5%E4%BD%9C%E8%A7%92%E8%89%B2)，以在 Job 被推入佇列後處理這些 Job。可以使用 `queue:work` Artisan 指令來執行 Queue Worker。請注意，當執行 `queue:work` 指令後，該指令會持續執行，直到我們手動停止該指令或關閉終端機為止：

```shell
php artisan queue:work
```
> [!NOTE]  
> 若要讓 `queue:work` 處理程序在背景持續執行，請使用如 [Supervisor](#supervisor-configuration) 等的 ^[Process Monitor](%E8%99%95%E7%90%86%E7%A8%8B%E5%BA%8F%E7%9B%A3%E7%9C%8B%E7%A8%8B%E5%BC%8F)，以確保 Queue Worker 持續執行。

若想將 Job ID 包含在 `queue:work` 指令的輸出，則可在呼叫該指令時加上 `-v` 旗標：

```shell
php artisan queue:work -v
```
請記得，Queue Worker 是會持續執行的處理程序，且會將已開啟的程式狀態保存在記憶體中。因此，Queue Worker 開始執行後若有更改程式碼，這些 Worker 將不會知道有這些修改。所以，在部署過程中，請確保有[重新啟動 Queue Worker](#queue-workers-and-deployment)。此外，也請注意，在各個 Job 間，也不會自動重設程式所建立或修改的任何^[靜態狀態](Static State)。

或者，我們也可以執行 `queue:listen` 指令。使用 `queue:listen` 指令時，若有更新程式碼或重設程式的狀態，就不需手動重新啟動 Queue Worker。不過，這個指令比起 `queue:work` 指令來說比較沒有效率：

```shell
php artisan queue:listen
```
<a name="running-multiple-queue-workers"></a>

#### 執行多個 Queue Worker

若要指派多個 Worker 給某個 Queue 並同時處理多個 Job，只需要啟動多個 `queue:work` 處理程序即可。若要啟動多個 `queue:work`，在本機上，我們可以開啟多個終端機分頁來執行；若是在正是環境上，則可以使用 Process Manager 的設定來啟動多個 `queue:work`。[使用 Supervisor 時](#supervisor-configuration)，可使用 `numprocs` 設定值。

<a name="specifying-the-connection-queue"></a>

#### 指定連線與佇列

也可以指定 Worker 要使用的佇列連線。傳給 `work` 指令的連線名稱應對影到 `config/queue.php` 設定檔中所定義的其中一個連線：

```shell
php artisan queue:work redis
```
預設情況下，`queue:work` 指令擲回處理給定連線上預設佇列的 Job。不過，我們也可以自定 Queue Worker，以處理給定連線上的特定佇列。舉例來說，若我們把所有的電子郵件都放在 `redis` 連線的 `emails` 佇列中執行，則我們可以執行下列指令來啟動一個處理該佇列的 Worker：

```shell
php artisan queue:work redis --queue=emails
```
<a name="processing-a-specified-number-of-jobs"></a>

#### 處理指定數量的 Job

可使用 `--once` 選項來讓 Worker 一次只處理佇列中的一個 Job：

```shell
php artisan queue:work --once
```
可使用 `--max-jobs` 選項來讓 Worker 只處理特定數量的 Job，然後就終止執行。該選項適合與 [Supervisor](#supervisor-configuration) 搭配使用，這樣我們就能讓 Worker 在處理特定數量的 Job 後自動重新執行，以釋出該 Worker 所積累的記憶體：

```shell
php artisan queue:work --max-jobs=1000
```
<a name="processing-all-queued-jobs-then-exiting"></a>

#### 處理所有放入佇列的 Job 然後終止執行

可使用 `--stop-when-empty` 選項來讓 Worker 處理所有的 Job 然後終止執行。在 Docker Container 中處理 Laravel 佇列時，若在佇列為空時停止關閉 Container，就適合使用該選項：

```shell
php artisan queue:work --stop-when-empty
```
<a name="processing-jobs-for-a-given-number-of-seconds"></a>

#### 在給定秒數內處理 Job

`--max-time` 選項可用來讓 Worker 處理給定秒數的 Job，然後終止執行。該選項是何與 [Supervisor](#supervisor-configuration) 搭配使用，以在處理 Job 給定時間後自動重新啟動 Worker，並釋放期間可能積累的記憶體：

```shell
# Process jobs for one hour and then exit...
php artisan queue:work --max-time=3600
```
<a name="worker-sleep-duration"></a>

#### Worker 的休眠期間

若佇列中有 Job，則 Worker 會不間斷地處理這些 Job。不過，使用 `sleep` 選項可用來讓 Worker 判斷當沒有 Job 時要「休眠」多少秒。當然，在休眠期間，Worker 就不會處理任何新的 Job：

```shell
php artisan queue:work --sleep=3
```
<a name="resource-considerations"></a>

#### 資源上的考量

Daemon 型的 Queue Worker 並不會在每個 Job 處理後「重新啟動」Laravel。因此，在每個 Job 處理完畢後，請務必釋放任何吃資源的功能。舉例來說，若我們使用了 GD 函式庫來進行圖片處理，則應在處理完圖片後使用 `imagedestroy` 來釋放記憶體。

<a name="queue-priorities"></a>

### 佇列的優先度

有時候，我們可能會向調整各個佇列的處理優先度。舉例來說，在 `config/queue.php` 設定檔中，我們可以把 `redis` 連線上的預設 `queue` 設為 `low` (低)。不過，有時候，我們可能會想像這樣把 Job 推入 `high` (高) 優先度的佇列：

    dispatch((new Job)->onQueue('high'));
若要啟動 Worker 以驗證是否所有 `high` 佇列上的 Job 都比 `low` 佇列上的 Job 還要早被處理，只需要傳入一組以逗號分隔的佇列名稱列表給 `work` 指令即可：

```shell
php artisan queue:work --queue=high,low
```
<a name="queue-workers-and-deployment"></a>

### Queue Worker 與部署

由於 Queue Worker 時持續執行的處理程序，因此除非重啟啟動 Queue Worker，否則 Queue Worker 不會知道程式碼有被修改過。要部署有使用 Queue Worker 的專案，最簡單的做法就是在部署過程中重新啟動 Queue Worker。我們可以執行 `queue:restart` 指令來重新啟動所有的 Worker：

```shell
php artisan queue:restart
```
該指令會通知所有的 Queue Worker，讓所有的 Worker 在處理完目前 Job 且在現有 Job 不遺失的情況下終止執行 Worker。由於 Queue Worker 會在 `queue:restart` 指令執行後終止執行，因此請務必使用如 [Supervisor](#supervisor-configuration) 這樣的 Process Manager 來自動重新啟動 Queue Worker。

> [!NOTE]  
> 佇列會使用[快取](/docs/{{version}}/cache)來儲存重新啟動訊號，因此在使用此功能前請先確認專案上是否有設定好正確的快取 Driver。

<a name="job-expirations-and-timeouts"></a>

### Job 的有效期限與逾時

<a name="job-expiration"></a>

#### Job 的有效期限

在 `config/queue.php` 設定檔中，每個佇列連線都有定義一個 `retry_after` 選項。這個選項用來指定在重新嘗試目前處理的 Job 前需要等待多少秒。舉例來說，若 `retry_after` 設為 `90`，則若某個 Job 已被處理 90 秒，且期間沒有被釋放或刪除，則該 Job 會被釋放回佇列中。一般來說，應將 `retry_after` 的值設為 Job 在合理情況下要完成執行所需的最大秒數。

> [!WARNING]  
> 唯一一個不含 `retry_after` 值的佇列連線是 Amazon SQS。SQS 會使用[預設的 Visibility Timeout](https://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/AboutVT.html) 來重試 Job。Visibility Timeout 的值由 AWS Console 中控制。

<a name="worker-timeouts"></a>

#### Worker 的逾時

`queue:work` Aritsan 指令有一個 `--timeout` 選項。預設情況下，`--timeout` 值為 60 秒。若 Job 執行超過逾時值所指定的秒數後，負責處理該 Job 的 Worker 就會以錯誤終止執行。一般來說，Worker 會自動由 [Server 上設定的 Process Manager](#supervisor-configuration) 重新開啟：

```shell
php artisan queue:work --timeout=60
```
雖然 `retry_after` 設定選項與 `--timeout` CLI 選項並不相同，不過這兩個選項會互相配合使用，以確保 Job 不遺失，且 Job 只會成功執行一次。

> [!WARNING]  
> `--timeout` 的值必須至少比 `retry_after` 設定選項短個幾秒，以確保 Worker 在處理到當掉的 Job 時會在重試 Job 前先終止該 Job。若 `--timeout` 選項比 `retry_after` 設定值還要長的話，則 Job 就有可能會被處理兩次。

<a name="supervisor-configuration"></a>

## Supervisor 設定

在正式環境中，我們會需要一種能讓 `queue:work` 處理程序持續執行的方法。`queue:work` 可能會因為各種原因而停止執行，如 Worker 執行達到逾時值，或是在執行了 `queue:restart` 指令後等。

因此，我們需要設定一個能偵測到 `queue:work` 處理程序終止執行，並能自動重新啟動這些 Worker 的 Process Monitor。此外，使用 Process Monitor 還能讓我們指定要同時執行多少個 `queue:work` 處理程序。Supervisor 時一個常見用於 Linux 環境的 Process Monitor，在本文中接下來的部分我們會來看看要如何設定 Supervisor。

<a name="installing-supervisor"></a>

#### 安裝 Supervisor

Supervisor 是一個用於 Linux 作業系統的 Process Monitor，使用 Supervisor 就可以在 `queue:work` 處理程序執行失敗時自動重新啟動。若要在 Ubuntu 上安裝 Supervisor，可使用下列指令：

```shell
sudo apt-get install supervisor
```
> [!NOTE]  
> 如果你覺得要設定並管理 Supervisor 太難、太複雜的話，可以考慮使用 [Laravel Forge](https://forge.laravel.com)。Laravel Forge 會幫你在 Laravel 專案的正式環境上自動安裝並設定 Supervisor。

<a name="configuring-supervisor"></a>

#### 設定 Supervisor

Supervisor 設定檔一般都存放在 `/etc/supervisor/conf.d` 目錄下。在該目錄中，我們可以建立任意數量的設定檔，以告訴 Supervisor 要如何監看這些處理程序。舉例來說，我們先建立一個用於啟動並監看 `queue:work` 處理程序的 `laravel-worker.conf` 檔案：

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
在這個範例中，`numprocs` 指示詞用於告訴 Supervisor 要執行 8 個 `queue:work` 處理程序，並監看這 8 個處理程序，然後當這些處理程序執行失敗時自動重新啟動。我們可以更改該設定檔中的 `command` 指示詞，以調整為所需的佇列連線與 Worker 選項。

> [!WARNING]  
> 請務必確保 `stopwaitsecs` 值比花費時間最多的 Job 所需執行的秒數還要大。若該值設定不對，可能會讓 Supervisor 在 Job 處理完成前就終止該 Job。

<a name="starting-supervisor"></a>

#### 開啟 Supervisor

建立好設定檔後，我們就可以使用下列指令更新 Supervisor 設定並開啟我們設定好的處理程序：

```shell
sudo supervisorctl reread

sudo supervisorctl update

sudo supervisorctl start laravel-worker:*
```
更多有關 Supervisor 的資訊，請參考 [Supervisor 的說明文件](http://supervisord.org/index.html)。

<a name="dealing-with-failed-jobs"></a>

## 處理失敗的 Job

有時候，放入佇列的 Job 可能會執行失敗。請別擔心，計劃永遠趕不上變化！Laravel 中內建了一個方便的方法，可用來[指定 Job 要重試的最大次數](#max-job-attempts-and-timeout)。若^[非同步](Asynchronous)執行的 Job 超過最大嘗試執行次數，則該 Job 會被插入到 `failed_jobs` 資料庫資料表中。[同步分派的 Job](/docs/{{version}}/queues#synchronous-dispatching) 若執行失敗，則不會被保存在該資料表中，而我們的專案則會馬上收到 Job 產生的 Exception。

在新安裝的 Laravel 專案中，通常已包含了一個建立 `failed_jobs` 資料表的 Migration。不過，若專案未包含該資料表的 Migration，則可使用 `queue:failed-table` 指令來建立該 Migration：

```shell
php artisan queue:failed-table

php artisan migrate
```
在執行 [Queue Worker] 處理程序時，我們可以使用 `queue:work` 指令上的 `--tries` 開關來指定某個 Job 所要嘗試執行的最大次數。若為指定 `--tries` 選項的值，則 Job 就只會嘗試執行一次，或是依照 Job 類別中 `$tries` 屬性所設定的值作為最大嘗試次數：

```shell
php artisan queue:work redis --tries=3
```
使用 `--backoff` 選項，就可指定當 Job 遇到 Exception 時，Laravel 要等待多少秒才重新嘗試該 Job。預設情況下，Job 會馬上被釋放回佇列中，以便重新嘗試該 Job：

```shell
php artisan queue:work redis --tries=3 --backoff=3
```
若想以 Job 為單位來設定當 Job 遇到 Exception 時 Laravel 要等待多少秒才重新嘗試該 Job，則可在 Job 類別上定義 `backoff` 屬性：

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 3;
若需要使用更複雜的邏輯來判斷 Job 的 Backoff 時間，可在 Job 類別上定義 `backoff` 方法：

    /**
    * Calculate the number of seconds to wait before retrying the job.
    *
    * @return int
    */
    public function backoff()
    {
        return 3;
    }
只要在 `backoff` 方法中回傳一組包含 Backoff 值的陣列，就能輕鬆地設定「指數級」的 Backoff。在這個例子中，第一次重試的延遲為 1 秒，第二次重試為 5 秒，第三次重試為 10 秒：

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

### 當 Job 執行失敗後進行清理

若某個特定的 Job 失敗後，我們可能會想傳送通知給使用者，或是恢復這個 Job 中所部分完成的一些動作。為此，我們可以在 Job 類別中定義一個 `failed` 方法。導致該 Job 失敗的 `Throwable` 實體會傳入給 `failed` 方法：

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
        public $podcast;
    
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
> [!WARNING]  
> 叫用 `failed` 方法前會先初始化該 Job 的一個新。因此，在 `handle` 方法中對類別屬性做出的更改都將遺失。

<a name="retrying-failed-jobs"></a>

### 重試失敗的 Job

若要檢視所有已插入 `failed_jobs` 資料表中失敗的 Job，可使用 `queue:failed` Artisan 指令：

```shell
php artisan queue:failed
```
`queue:failed` 指令會列出 Job ID、連線、佇列、失敗時間……等，以及其他有關該 Job 的資訊。可使用 Job ID 來重試失敗的 Job。舉例來說，若要重試 ID 為 `ce7bb17c-cdd8-41f0-a8ec-7b4fef4e5ece` 的失敗 Job，請執行下列指令：

```shell
php artisan queue:retry ce7bb17c-cdd8-41f0-a8ec-7b4fef4e5ece
```
若有需要，可傳入多個 ID 給該指令：

```shell
php artisan queue:retry ce7bb17c-cdd8-41f0-a8ec-7b4fef4e5ece 91401d2c-0784-4f43-824c-34f94a33c24d
```
也可以嘗試特定佇列中所有失敗的 Job：

```shell
php artisan queue:retry --queue=name
```
若要重試所有失敗的 Job，請執行 `queue:retry` 指令，並傳入 `all` 作為 ID：

```shell
php artisan queue:retry all
```
若想刪除失敗的 Job，可使用 `queue:forget` 指令：

```shell
php artisan queue:forget 91401d2c-0784-4f43-824c-34f94a33c24d
```
> [!NOTE]  
> 使用 [Horizon](/docs/{{version}}/horizon)，請不要使用 `queue:forget` 指令，請使用 `horizon:forget` 指令來刪除失敗的 Job。

若要從 `failed_jobs` 資料表中刪除所有失敗的 Job，可使用 `queue:flush` 指令：

```shell
php artisan queue:flush
```
<a name="ignoring-missing-models"></a>

### 忽略不存在的 Model

將 Eloquent Model 插入進 Job 時，該 Model 會自動被序列化再放入佇列中，並在要處理該 Job 時再重新從資料庫中取出。不過，若在該 Job 等待被 Worker 處理的期間刪除了該 Model，則 Job 可能會遇到 `ModelNotFoundException` 而失敗。

為了方便起見，可將 Job 的 `deleteWhenMissingModels` 屬性設為 `true`，就可以自動刪除有遺失 Model 的 Job。若該選項設為 `true`，則 Laravel 會自動默默地在不產生 Exception 的情況下取消該 Job：

    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;
<a name="pruning-failed-jobs"></a>

### 修剪失敗的 Job

可以呼叫 `queue:prune-failed` Artisan 指令來修剪專案中所有 `failed_jobs` 資料表中的記錄：

```shell
php artisan queue:prune-failed
```
預設情況下，所有超過 24 小時的失敗 Job 記錄都會被修剪。若有提供 `--hours` 選項給該指令，則只會保留過去 N 小時中所插入的失敗 Job 記錄。舉例來說，下列指令會刪除所有插入超過 48 小時的失敗 Job 記錄：

```shell
php artisan queue:prune-failed --hours=48
```
<a name="storing-failed-jobs-in-dynamodb"></a>

### 排序 DynamoDB 中的失敗 Job

出了將失敗 Job 記錄保存在關聯式資料庫資料表意外，在 Laravel 中，也支援將失敗 Job 記錄保存在 [DynamoDB](https://aws.amazon.com/dynamodb) 中。不過，若要保存在 DynamoDB 資料表中，我們需要先建立一個 DynamoDB 資料表來保存失敗的 Job 記錄。一般來說，這個資料表的名稱應為 `failed_jobs`，不過，請依照專案的 `queue` 設定檔中 `queue.failed.table` 設定值來命名資料表。

`failed_jobs` 資料表應有一個名為 `application` 的字串^[主分區索引鍵](Primary Partition Key)，以及一個名為 `uuid` 的字串^[主排序索引鍵](Primary Sort Key)。索引鍵的 `application` 這個部分會包含專案的名稱，即 `app` 設定檔中的 `name` 設定值。由於專案名稱會是 DynamoDB 資料表中索引鍵的一部分，因此，我們可以使用相同的資料表來保存多個 Laravel 專案中的失敗 Job。

此外，也請確保有安裝 AWS SDK，好讓 Laravel 專案能與 Amazon DynamoDB 溝通：

```shell
composer require aws/aws-sdk-php
```
接著，請設定 `queue.failed.driver` 設定選項值為 `dynamodb`。此外，也請在失敗 Job 設定陣列中定義 `key`、`secret`、`region` 等設定選項。這些選項會用來向 AWS 進行身份驗證。使用 `dynamodb` Driver 時，就不需要 `queue.failed.database` 設定選項：

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

### 不保存失敗的 Job

只要將 `queue.failed.driver` 設定值設為 `null`，就可以讓 Laravel 不保存失敗的 Job 以忽略這些 Job。一般來說，可使用  `QUEUE_FAILED_DRIVER` 環境變數來調整這個值：

```ini
QUEUE_FAILED_DRIVER=null
```
<a name="failed-job-events"></a>

### 失敗 Job 的事件

若想註冊一個會在每次 Job 失敗時叫用的 Event Listener，可使用 `Queue` Facade 的 `failing` 方法。舉例來說，我們可以在 Laravel 中內建的 `AppServiceProvider` 中 `boot` 方法內將一個閉包附加至該事件上：

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

## 在佇列中清理 Job

> [!NOTE]  
> 使用 [Horizon](/docs/{{version}}/horizon)，請不要使用 `queue:clear` 指令，請使用 `horizon:clear` 指令來清理佇列中的 Job。

若想從預設連線的預設佇列中刪除所有 Job，可使用 `queue:clear` Artisan 指令：

```shell
php artisan queue:clear
```
可以提供 `connection` 引數與 `queue` 選項來刪除特定連線與佇列中的 Job：

```shell
php artisan queue:clear redis --queue=emails
```
> [!WARNING]  
> 目前，只有 SQS、Redis、資料庫等佇列 Driver 能支援清除佇列中的 Job。此外，刪除 SQS Message 可能會需要至多 60 秒的時間，因此在清理佇列的 60 秒後所傳送給 SQS 佇列的 Job 也可能會被刪除。

<a name="monitoring-your-queues"></a>

## 監控佇列

若佇列突然收到大量的 Job，則佇列可能會有來不及處理，造成 Job 需要更長的等待時間才能完成。若有需要的話，Laravel 可以在佇列 Job 遇到特定閥值時傳送通知。

若要開始監控佇列，請排程設定[每 10 分鐘執行](/docs/{{version}}/scheduling) `queue:monitor` 指令。這個指令接受要監控的佇列名稱，以及所要設定的 Job 數量閥值：

```shell
php artisan queue:monitor redis:default,redis:deployments --max=100
```
若只排程執行這個指令，當佇列的負載過高時還不會觸發通知。當這個指令遇到有佇列超過指定閥值量的 Job 數時，會分派一個 `Illuminate\Queue\Events\QueueBusy` 事件。我們可以在專案的 `EventServiceProvider` 內監聽這個事件，以傳送通知給開發團隊：

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

## Job 事件

在 `Queue` [Facade](/docs/{{version}}/facades) 上使用 `before` 與 `after` 方法，就可以指定要在佇列 Job 處理前後所要執行的回呼。在這些回呼中，我們就有機會能進行記錄額外的日誌、增加主控台上統計數字等動作。一般來說，應在某個 [Service Provider](/docs/{{version}}/providers) 中 `boot` 方法內呼叫這些方法。舉例來說，我們可以使用 Laravel 中內建的 `AppServiceProvider`：

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
使用 `Queue` [Facade](/docs/{{version}}/facades) 的 `looping` 方法，我們就能指定要在 Worker 嘗試從佇列中取得 Job 前執行的回呼。舉例來說，我們可以註冊一個閉包來回溯前一個失敗 Job 中未關閉的 Transaction：

    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Queue;
    
    Queue::looping(function () {
        while (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
    });