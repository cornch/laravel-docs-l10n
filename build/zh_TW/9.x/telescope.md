---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/161/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 60.8
---

# Laravel Telescope

- [簡介](#introduction)
- [安裝](#installation)
  - [只在本機上安裝](#local-only-installation)
  - [設定](#configuration)
  - [修剪資料](#data-pruning)
  - [主控台的權限控制](#dashboard-authorization)
  
- [升級 Telescope](#upgrading-telescope)
- [篩選](#filtering)
  - [Entry](#filtering-entries)
  - [Batch](#filtering-batches)
  
- [Tag](#tagging)
- [可用的 Watcher](#available-watchers)
  - [Batch Watcher](#batch-watcher)
  - [Cache Watcher](#cache-watcher)
  - [指令 Watcher](#command-watcher)
  - [Dump Watcher](#dump-watcher)
  - [Event Watcher](#event-watcher)
  - [Exception Watcher](#exception-watcher)
  - [Gate Watcher](#gate-watcher)
  - [HTTP Client Watcher](#http-client-watcher)
  - [Job Watcher](#job-watcher)
  - [Log Watcher](#log-watcher)
  - [Mail Watcher](#mail-watcher)
  - [Model Watcher](#model-watcher)
  - [Notification Watcher](#notification-watcher)
  - [Query Watcher](#query-watcher)
  - [Redis Watcher](#redis-watcher)
  - [Request Watcher](#request-watcher)
  - [Schedule Watcher](#schedule-watcher)
  - [View Watcher](#view-watcher)
  
- [顯示使用者的顯示圖片](#displaying-user-avatars)

<a name="introduction"></a>

## 簡介

[Laravel Telescope](https://github.com/laravel/telescope) 是 Laravel 本機開發環境的好夥伴。在 Telescope 中，可以檢視連入 Request、Exception、Log 項目、資料庫查詢、放入佇列的 Job、Mail、Cache 操作、排程任務、變數傾印⋯⋯等。

<img src="https://laravel.com/img/docs/telescope-example.png">
<a name="installation"></a>

## 安裝

可以使用 Composer 套件管理員來將 Telescope 安裝到 Laravel 專案中：

```shell
composer require laravel/telescope
```
安裝好 Telescope 後，使用 `telescope:install` Artisan 指令來將 Telescope 的素材安裝到專案中。安裝好 Telescope 後，也請一併執行 `migrate` 指令來建立保存 Telescope 資料所需要的資料表：

```shell
php artisan telescope:install

php artisan migrate
```
<a name="migration-customization"></a>

#### 自訂 Migration

若不打算使用 Telescope 的預設 Migration，請在專案的 `App\Providers\AppServiceProvider` 內 `register` 方法中呼叫 `Telescope::ignoreMigrations` 方法。可以使用下列指令來匯出預設的 Migration：`php artisan vendor:publish --tag=telescope-migrations`

<a name="local-only-installation"></a>

### 只在本機上安裝

若只打算使用 Telescope 來協助在本機上開發，可使用 `--dev` 旗標來安裝 Telescope：

```shell
composer require laravel/telescope --dev

php artisan telescope:install

php artisan migrate
```
執行 `telescope:install` 後，請從專案的 `config/app.php` 設定檔中移除 `TelescopeServiceProvider` Service Provider 的註冊。然後，請在 `App\Providers\AppServiceProvider` 類別中的 `register` 方法內手動註冊 Telescope 的 Service Provider。先檢查目前環境是否為 `local`，再註冊 Provider：

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }
最後，也請在 `composer.json` 檔中加上下列內容來防止 Telescope 套件被 [Auto-Discover](/docs/{{version}}/packages#package-discovery)：

```json
"extra": {
    "laravel": {
        "dont-discover": [
            "laravel/telescope"
        ]
    }
},
```
<a name="configuration"></a>

### 設定

安裝好 Telescope 的素材後，主要設定檔會被放到 `config/telescope.php`。在這個設定檔中，我們可以設定 [Watcher 選項](#available-watchers)。每個設定選項都包含了有關該選項功能的說明，因此建議先仔細看過這個設定檔。

若有需要，可以使用 `enabled` 設定選項來完全禁用 Telescope 的資料蒐集：

    'enabled' => env('TELESCOPE_ENABLED', true),
<a name="data-pruning"></a>

### 資料修剪

若未^[修建](Prune)批次，則 `telescope_entries` 資料表很快就會變得很大。為了避免這個狀況，應[定期](/docs/{{version}}/scheduling)每日執行 `telescope:prune` Artisan 指令：

    $schedule->command('telescope:prune')->daily();
預設情況下，所有超過 24 小時的資料都會被修建掉。可以在呼叫該指令時使用 `hours` 選項來指定 Telescope 的資料要被保留多久。舉例來說，下列指令會刪除建立超過 48 小時的所有資料：

    $schedule->command('telescope:prune --hours=48')->daily();
<a name="dashboard-authorization"></a>

### 主控台的權限控制

可以在 `/telescope` Route 上存取 Telescope 的主控台。預設情況下，只有在 `local` 環境下可以存取主控台。在 `app/Providers/TelescopeServiceProvider.php` 檔案中，定義了一個[授權 Gate](/docs/{{version}}/authorization#gates)。這個授權 Gate 控制了在**非 local** 環境下的 Telescope 存取。可以依照需求調整這個 Gate 來限制 Telescope 的存取：

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewTelescope', function ($user) {
            return in_array($user->email, [
                'taylor@laravel.com',
            ]);
        });
    }
> [!WARNING]  
> 請確定有在正式環境中將 `APP_ENV` 環境變數設為 `production`。否則會讓任何人都能存取 Telescope。

<a name="upgrading-telescope"></a>

## 升級 Telescope

將 Telescope 升級到新的主要 (Major) 版本時，請務必仔細閱讀[升級指南](https://github.com/laravel/telescope/blob/master/UPGRADE.md)。

此外，升級到任何新的 Telescope 版本時，請重新安裝 Telescope 的素材：

```shell
php artisan telescope:publish
```
為了確保素材在最新版本並避免在未來的更新中造成問題，可以將 `vendor:publish --tag=laravel-assets` 指令加到 `composer.json` 檔中的 `post-update-cmd` Script 中：

```json
{
    "scripts": {
        "post-update-cmd": [
            "@php artisan vendor:publish --tag=laravel-assets --ansi --force"
        ]
    }
}
```
<a name="filtering"></a>

## 篩選

<a name="filtering-entries"></a>

### Entry

可以使用 `App\Providers\TelescopeServiceProvider` 類別中定義的 `filter` 閉包來篩選 Telescope 紀錄的資料。預設情況下，這個閉包會紀錄 `local` 環境下的所有資料，或是 其他任何環境下的 Exceoption、失敗的 Job、排程任務、以及包含監控中 Tag 的資料：

    use Laravel\Telescope\IncomingEntry;
    use Laravel\Telescope\Telescope;
    
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->hideSensitiveRequestDetails();
    
        Telescope::filter(function (IncomingEntry $entry) {
            if ($this->app->environment('local')) {
                return true;
            }
    
            return $entry->isReportableException() ||
                $entry->isFailedJob() ||
                $entry->isScheduledTask() ||
                $entry->isSlowQuery() ||
                $entry->hasMonitoredTag();
        });
    }
<a name="filtering-batches"></a>

### Batch

`filter` 閉包會篩選個別資料項目，而 `filterBatch` 方法則可註冊一個用來篩選特定 Request 或主控台指令中所有資料的閉包。若該閉包回傳 `true`，則 Telescope 就會紀錄所有資料：

    use Illuminate\Support\Collection;
    use Laravel\Telescope\Telescope;
    
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->hideSensitiveRequestDetails();
    
        Telescope::filterBatch(function (Collection $entries) {
            if ($this->app->environment('local')) {
                return true;
            }
    
            return $entries->contains(function ($entry) {
                return $entry->isReportableException() ||
                    $entry->isFailedJob() ||
                    $entry->isScheduledTask() ||
                    $entry->isSlowQuery() ||
                    $entry->hasMonitoredTag();
                });
        });
    }
<a name="tagging"></a>

## Tag

在 Telescope 中，可以使用「Tag」 來搜尋項目。通常來說，Tag 會是 Eloquent Model 的類別名稱，或是已登入使用者的 ID。這兩種情況 Telescope 會自動為其加上 Tag。但有時候，我們會想在一些項目上加上自訂的 Tag。這時，可以使用 `Telescope::tag` 方法。`tag` 方法的參數為一個閉包，該閉包回傳一組 Tag 陣列。這個閉包所回傳的 Tag 會被與 Telescope 自動加入的 Tag 合併。一般來說，應在 `App\Providers\TelescopeServiceProvider` 類別的 `register` 方法內呼叫 `tag` 方法：

    use Laravel\Telescope\IncomingEntry;
    use Laravel\Telescope\Telescope;
    
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->hideSensitiveRequestDetails();
    
        Telescope::tag(function (IncomingEntry $entry) {
            return $entry->type === 'request'
                        ? ['status:'.$entry->content['response_status']]
                        : [];
        });
     }
<a name="available-watchers"></a>

## 可用的 Watcher

Telescope 的「Watcher」負責在 Request 或主控台指令被執行時取得專案中的資料。可以在 `config/telescope.php` 設定檔中自訂要啟用的 Watcher 清單：

    'watchers' => [
        Watchers\CacheWatcher::class => true,
        Watchers\CommandWatcher::class => true,
        ...
    ],
有的 Watcher 也支援一些客製化選項：

    'watchers' => [
        Watchers\QueryWatcher::class => [
            'enabled' => env('TELESCOPE_QUERY_WATCHER', true),
            'slow' => 100,
        ],
        ...
    ],
<a name="batch-watcher"></a>

### Batch Watcher

Batch Watcher 會紀錄有關放入佇列的 [Batch](/docs/{{version}}/queues#job-batching) 資料，其中包含 Job 與連線的資訊。

<a name="cache-watcher"></a>

### Cache Watcher

Cache Watcher 會在 Cache 索引鍵被取得 (Hit)、找不到 (Missed)、更新 (Update)、刪除 (Forgotten) 時紀錄資料。

<a name="command-watcher"></a>

### Command Watcher

Command Watcher 會在 Artisan 指令執行時紀錄指令的引數、選項、終止代碼 (Exit Code)、與輸出。若想排除掉某些指令不被此 Watcher 紀錄，可以在 `config/telescope.php` 檔中的 `ignore` 選項內指定這些要排除的指令：

    'watchers' => [
        Watchers\CommandWatcher::class => [
            'enabled' => env('TELESCOPE_COMMAND_WATCHER', true),
            'ignore' => ['key:generate'],
        ],
        ...
    ],
<a name="dump-watcher"></a>

### Dump Watcher

Dump Watcher 會將變數傾印紀錄並顯示在 Telescope 內。在使用 Laravel 時，可以使用全域的 `dump` 函式來傾印變數。若要紀錄傾印，必須在瀏覽器中打開 Dump Watcher 分頁。若未開啟頁面，則 Dump Watcher 會忽略該傾印。

<a name="event-watcher"></a>

### Event Watcher

Event Watcher 會紀錄專案所分派的任何 [Event](/docs/{{version}}/events) 的 ^[Payload](%E6%89%BF%E8%BC%89)、Listener、與 Broadcast 資料。Event Watcher 會忽略 Laravel 框架內部的 Event。

<a name="exception-watcher"></a>

### Exception Watcher

Exception Watcher 會紀錄專案回擲的任何可回報 (Reportable) 之 Exception 的資料與堆疊追蹤 (Stack Trace)。

<a name="gate-watcher"></a>

### Gate Watcher

Gate Watcher 會紀錄專案中所有 [Gate 與 Policy](/docs/{{version}}/authorization) 檢查的資料與結果。若想排除特定的 Ability 被此 Watcher 紀錄，可在 `config/telescope.php` 檔中的 `ignore_abilities` 選項內指定要排除的 Ability：

    'watchers' => [
        Watchers\GateWatcher::class => [
            'enabled' => env('TELESCOPE_GATE_WATCHER', true),
            'ignore_abilities' => ['viewNova'],
        ],
        ...
    ],
<a name="http-client-watcher"></a>

### HTTP Client Watcher

HTTP Client Watcher 會紀錄專案中所執行的聯外 [HTTP Client Request](/docs/{{version}}/http-client) 紀錄。

<a name="job-watcher"></a>

### Job Watcher

Job Watcher 會紀錄專案中分派的所有 [Job](/docs/{{version}}/queues) 的資料與狀態。

<a name="log-watcher"></a>

### Log Watcher

Log Watcher 會紀錄專案所寫入的任何 [Log 資料](/docs/{{version}}/logging)。

<a name="mail-watcher"></a>

### Mail Watcher

在 Mail Watcher 中，可以在瀏覽器內預覽專案所送出的 [E-Mail](/docs/{{version}}/mail)，以及其相關的資料。這些 E-Mail 也可以被下載為 `.eml` 檔。

<a name="model-watcher"></a>

### Model Watcher

Model Watcher 會在每次有 Eloquent [Model Event](/docs/{{version}}/eloquent#events) 被分派時紀錄 Model 的更改。可以在此 Watcher 的 `events` 選項中指定要紀錄哪些 Model Event：

    'watchers' => [
        Watchers\ModelWatcher::class => [
            'enabled' => env('TELESCOPE_MODEL_WATCHER', true),
            'events' => ['eloquent.created*', 'eloquent.updated*'],
        ],
        ...
    ],
若想紀錄某個特定 Request 中有多少的 Model 被重新回填 (Hydrate)，可以啟用 `hydrations` 選項：

    'watchers' => [
        Watchers\ModelWatcher::class => [
            'enabled' => env('TELESCOPE_MODEL_WATCHER', true),
            'events' => ['eloquent.created*', 'eloquent.updated*'],
            'hydrations' => true,
        ],
        ...
    ],
<a name="notification-watcher"></a>

### Notification Watcher

Notification Watcher 會紀錄專案中寄出的所有 [Notification](/docs/{{version}}/notifications)。若 Notification 有觸發 E-Mail，且啟用的 Mail Watcher，則也可以在 Mail Watcher 畫面中預覽該 E-Mail。

<a name="query-watcher"></a>

### Query Watcher

Query Watcher 會紀錄專案中執行的所有查詢之原始 SQL、繫結、與執行時間。Query Watcher 也會在所有執行時間大於 100 毫秒的查詢上加入 `slow` Tag。可以使用此 Watcher 的 `slow` 選項來自訂慢查詢的界定值：

    'watchers' => [
        Watchers\QueryWatcher::class => [
            'enabled' => env('TELESCOPE_QUERY_WATCHER', true),
            'slow' => 50,
        ],
        ...
    ],
<a name="redis-watcher"></a>

### Redis Watcher

Redis Watcher 會紀錄專案中執行的所有 [Redis](/docs/{{version}}/redis) 指令。若使用 Redis 來進行快取，則快取指令也會被 Redis Watcher 紀錄。

<a name="request-watcher"></a>

### Request Watcher

Request Watcher 會紀錄專案所處理的所有 Request 之 Header、Session、以及該 Request 所關聯的 Response 資料。可以使用 `size_limit` (單位為 KB) 選項來限制要紀錄的 Response 資料：

    'watchers' => [
        Watchers\RequestWatcher::class => [
            'enabled' => env('TELESCOPE_REQUEST_WATCHER', true),
            'size_limit' => env('TELESCOPE_RESPONSE_SIZE_LIMIT', 64),
        ],
        ...
    ],
<a name="schedule-watcher"></a>

### Schedule Watcher

Schedule Watcher 會紀錄專案所執行的所有[排程任務](/docs/{{version}}/scheduling)的指令與輸出。

<a name="view-watcher"></a>

### View Watcher

View Watcher 會在轉譯 [View](/docs/{{version}}/views) 時紀錄 View 的名稱、路徑、資料、與所使用的「Composer」。

<a name="displaying-user-avatars"></a>

## 顯示使用者的顯示圖片

Telescope 主控台會顯示資料在紀錄時已登入使用者的顯示圖片。預設情況下，Telescope 會使用 Gravatar 服務來取得顯示圖片。不過，也可以在 `App\Providers\TelescopeServiceProvider` 類別中註冊一個回呼來自訂顯示圖片的網址。該回呼會收到使用者的 ID 與其 E-Mail 位址，並且應會穿該使用者的顯示圖片網址：

    use App\Models\User;
    use Laravel\Telescope\Telescope;
    
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // ...
    
        Telescope::avatar(function ($id, $email) {
            return '/avatars/'.User::find($id)->avatar_path;
        });
    }