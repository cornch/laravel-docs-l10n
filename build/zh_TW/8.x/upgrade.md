---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/165/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 45.51
---

# 升級指南

- [從 7.x 升級至 8.0](#upgrade-8.0)

<a name="high-impact-changes"></a>

## 高度影響的更改

<div class="content-list" markdown="1">
- [Model Factory](#model-factories)
- [Queue 的 `retryAfter` 方法](#queue-retry-after-method)
- [Queue 的 `timeoutAt` 屬性](#queue-timeout-at-property)
- [Queue 的 `allOnQueue` 與 `allOnConnection` 屬性](#queue-allOnQueue-allOnConnection)
- [分頁的預設](#pagination-defaults)
- [Seeder 與 Factory 的 Namespace](#seeder-factory-namespaces)

</div>
<a name="medium-impact-changes"></a>

## 中度影響的更改

<div class="content-list" markdown="1">
- [最低需求 PHP 7.3.0](#php-7.3.0-required)
- [失敗 Job 資料表的批次支援](#failed-jobs-table-batch-support)
- [維護模式更新](#maintenance-mode-updates)
- [`php artisan down --message` 選項](#artisan-down-message)
- [`assertExactJson` 方法](#assert-exact-json-method)

</div>
<a name="upgrade-8.0"></a>

## 從 7.x 升級到 8.0

<a name="estimated-upgrade-time-15-minutes"></a>

#### 預計升級所需時間：15 分鐘

> [!NOTE]  
> We attempt to document every possible breaking change. Since some of these breaking changes are in obscure parts of the framework only a portion of these changes may actually affect your application.

<a name="php-7.3.0-required"></a>

### 最低版本要求為 PHP 7.3.0

**受影響的可能性：中等**

最低要求的 PHP 版本現為 7.3.0

<a name="updating-dependencies"></a>

### 相依性套件更新

請在 `composer.json` 檔案中更新下列相依性套件：

<div class="content-list" markdown="1">
- `guzzlehttp/guzzle` to `^7.0.1`
- `facade/ignition` to `^2.3.6`
- `laravel/framework` to `^8.0`
- `laravel/ui` to `^3.0`
- `nunomaduro/collision` to `^5.0`
- `phpunit/phpunit` to `^9.0`

</div>
下列第一方專案也有更新新的版本以支援 Laravel 8。若有使用這些套件，請在升級前先閱讀各套件的升級指南：

<div class="content-list" markdown="1">
- [Horizon v5.0](https://github.com/laravel/horizon/blob/master/UPGRADE.md)
- [Passport v10.0](https://github.com/laravel/passport/blob/master/UPGRADE.md)
- [Socialite v5.0](https://github.com/laravel/socialite/blob/master/UPGRADE.md)
- [Telescope v4.0](https://github.com/laravel/telescope/blob/master/UPGRADE.md)

</div>
此外，Laravel Installer 也更新了對 `composer create-project` 與 Laravel Jetstream 的支援。從 2020 年 10 月起，4.0 版以前的安裝程式將停止運作。請儘快將全域的 Installer 升級至 `^4.0`。

最後，請檢視你的專案使用的其他第三方套件，確認一下是否有使用支援 Laravel 8 的版本。

<a name="collections"></a>

### Collections

<a name="the-isset-method"></a>

#### `isset` 方法

**受影響的可能：低**

為了與一般 PHP 的行為保持一致，`Illuminate\Support\Collection` 的 `offsetExists` 方法已從 `array_key_exists` 改為使用 `isset`。因此，在處理值為 `null` 的 Collection 項目時的行為可能會有所變更：

    $collection = collect([null]);
    
    // Laravel 7.x - true
    isset($collection[0]);
    
    // Laravel 8.x - false
    isset($collection[0]);
<a name="database"></a>

### 資料庫

<a name="seeder-factory-namespaces"></a>

#### Seeder 與 Factory 的 Namespace

**受影響的可能：高**

Seeder 與 Factory 現在被放到 Namespace 下了。為了符合這項更改，請在 Seeder 類別中加上 `Database\Seeders` Namespace。此外，原本的 `database/seeds` 目錄也應重新命名為 `database/seeders`：

    <?php
    
    namespace Database\Seeders;
    
    use App\Models\User;
    use Illuminate\Database\Seeder;
    
    class DatabaseSeeder extends Seeder
    {
        /**
         * Seed the application's database.
         *
         * @return void
         */
        public function run()
        {
            ...
        }
    }
若要使用 `laravel/legacy-factories` 套件，則不需更改 Factory 類別。不過，如果要更新 Factory，則應在這些類別內加上 `Database\Factories` Namespace。

接著，在 `composer.json` 檔案中，請從 `autoload` 段落中移除 `classmap` 區塊，並為這些新放進 Namespace 的類別加上目錄映射：

    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        }
    },
<a name="eloquent"></a>

### Eloquent

<a name="model-factories"></a>

#### Model Factory

**受影響的可能：高**

Laravel 的 [Model Factory] 功能已完全重寫以支援新的類別格式的寫法，因此已不相容於 Laravel 7.x 風格的 Factory。不過，為了使升級過程更簡單，我們建立了新的 `laravel/legacy-factories` 套件，可讓你繼續在 Laravel 8.x 中使用現有的 Factory。可使用 Composer 來安裝這個套件：

    composer require laravel/legacy-factories
<a name="the-castable-interface"></a>

#### `Castable` 介面

**受影響的可能：低**

`Castable` 介面的 `castUsing` 方法已更新為接受一組陣列的引數。若你有實作這個介面，請更新該實作：

    public static function castUsing(array $arguments);
<a name="increment-decrement-events"></a>

#### 遞增與遞減的事件

**受影響的可能：低**

現在，在 Eloquent Model 實體上執行 `increment` 或 `decrement` 方法時，會分派相應與「update」與「save」相應的 Model 事件。

<a name="events"></a>

### 事件

<a name="the-event-service-provider-class"></a>

#### `EventServiceProvider` 類別

**受影響的可能：低**

如果你的 `App\Providers\EventServiceProvider` 類別內有包含 `register` 方法，請確認是否有在此方法的開頭呼叫 `parent::register`。若未呼叫 `parent::register` 會導致專案中的事件不被註冊。

<a name="the-dispatcher-contract"></a>

#### `Dispatcher` Contract

**受影響的可能：低**

`Illuminate\Contracts\Events\Dispatcher` Contract 的 `listen` 方法已將 `$listener` 屬性改為可選屬性。此修改是為了要支援使用 Reflection 來自動偵測所處理的 Event 型別。若有手動實作此介面，請更改你的實作：

    public function listen($events, $listener = null);
<a name="framework"></a>

### Laravel Framework

<a name="maintenance-mode-updates"></a>

#### 維護模式更新

**受影響的可能性：可選**

在 Laravel 8.x 中，Laravel 的[維護模式](/docs/{{version}}/configuration#maintenance-mode)已獲得了改進。現在已支援了預先轉譯的維護模式樣板，並可減少終端使用者在維護模式時遇到錯誤的機會。不過，為了支援此功能，必須在 `public/index.php` 中加上下列更改。這幾行程式碼必須被緊接著放在現有的 `LARAVEL_START` 常數定義之後：

    define('LARAVEL_START', microtime(true));
    
    if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
        require $maintenance;
    }
<a name="artisan-down-message"></a>

#### `php artisan down --message` 選項

**受影響的可能性：中等**

`php artisan down` 指令的 `--message` 選項已被移除。作為替代方案，請考慮使用所需的訊息來[預先轉譯維護模式的 View](/docs/{{version}}/configuration#maintenance-mode)。

<a name="php-artisan-serve-no-reload-option"></a>

#### `php artisan serve --no-reload` 選項

**受影響的可能：低**

`php artisan serve` 指令新增了一個 `--no-reload` 選項。該選項可讓內建的 Server 在偵測到環境變數檔更改時不要自動重新載入。在 CI 環境中執行 Laravel Dusk 測試時特別適合使用此選項。

<a name="manager-app-property"></a>

#### Manager 的 `$app` 屬性

**受影響的可能：低**

原本在 `Illuminate\Support\Manager` 類別上停止支援 (Deprecated) 的 `$app` 屬性現已移除。若你需要使用此屬性，請改用 `$container` 屬性。

<a name="the-elixir-helper"></a>

#### `elixir` 輔助函式

**受影響的可能：低**

原本停止支援 (Deprecated) 的 `elixir` 輔助函式現已被移除。建議使用此方法的專案升級為使用 [Laravel Mix](https://github.com/JeffreyWay/laravel-mix)。

<a name="mail"></a>

### 郵件

<a name="the-sendnow-method"></a>

#### `sendNow` 方法

**受影響的可能：低**

原本停止支援 (Deprecated) 的 `sendNow` 方法現已移除。請改用 `send` 方法。

<a name="pagination"></a>

### 分頁

<a name="pagination-defaults"></a>

#### 分頁的預設

**受影響的可能：高**

Paginator 現在使用 [Tailwind CSS Framework](https://tailwindcss.com) 作為其預設樣式。若要繼續使用 Bootstrap，請在專案的 `AppServiceProvider` 中 `boot` 方法內加上下列方法呼叫：

    use Illuminate\Pagination\Paginator;
    
    Paginator::useBootstrap();
<a name="queue"></a>

### 佇列

<a name="queue-retry-after-method"></a>

#### `retryAfter` 方法

**受影響的可能：高**

為了與 Laravel 中的其他功能保持一致性，放入佇列的 Job、Mailer、Notification、與 Lisenter 上的`retryAfter` 方法與 `retryAfter` 屬性已重新命名為 `backoff`。請在專案內的相應類別中更新此方法／屬性名稱。

<a name="queue-timeout-at-property"></a>

#### `timeoutAt` 屬性

**受影響的可能：高**

放入佇列的 Job、Notification、Listener 的 `timeoutAt` 屬性已被重新命名為 `retryUntil`。請在專案中相應類別上更新此屬性的名稱。

<a name="queue-allOnQueue-allOnConnection"></a>

#### `allOnQueue()` 與 `allOnConnection()` 方法

**受影響的可能：高**

為了與其他分派方法保持一致，在 Job 串聯中使用的 `allOnQueue()` 與 `allOnConnection()` 方法已被移除。請使用 `onQueue()` 與 `onConnection()` 方法代替。這些方法應在呼叫 `dispatch` 方法前呼叫：

    ProcessPodcast::withChain([
        new OptimizePodcast,
        new ReleasePodcast
    ])->onConnection('redis')->onQueue('podcasts')->dispatch();
請注意，此更改只會影響有使用 `withChain` 方法的程式。使用全域的 `dispatch` 輔助函式時，仍然可使用 `allOnQueue()` 與 `allOnConnection()`。

<a name="failed-jobs-table-batch-support"></a>

#### 失敗 Job 資料表的批次支援

**受影響的可能性：可選**

若要使用 Laravel 8.x 的 [Job 批次](/docs/{{version}}/queues#job-batching)功能，則必須更新 `failed_jobs` 資料庫資料表。首先，需要在資料表中加上一個新的 `uuid` 欄位：

    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    
    Schema::table('failed_jobs', function (Blueprint $table) {
        $table->string('uuid')->after('id')->nullable()->unique();
    });
接著，在 `queue` 設定檔中的 `failed.driver` 設定選項應更新為 `database-uuids`。

此外，你可能也會想為現有的失敗 Job 產生 UUID：

    DB::table('failed_jobs')->whereNull('uuid')->cursor()->each(function ($job) {
        DB::table('failed_jobs')
            ->where('id', $job->id)
            ->update(['uuid' => (string) Illuminate\Support\Str::uuid()]);
    });
<a name="routing"></a>

### 路由

<a name="automatic-controller-namespace-prefixing"></a>

#### Controller 的自動 Namespace 前置詞

**受影響的可能性：可選**

在過往版本的 Laravel 中，`RouteServiceProvider` 類別包含了一個 `$namespace` 屬性，該屬性的值為 `App\Http\Controllers`。這個屬性的值是用來在 Controller 的 Route 定義與使用 `action` 輔助函式來產生 Controller Route URL 時自動為 Controller 加上前置詞用的。

在 Laravel 8 中，此屬性預設被設為 `null` 。這樣一來，你就可以在 Controller Route 定義中使用標準的 PHP Callable 語法。這種語法對於大多數 IDE 來說支援度也更高，可直接跳至 Controller 類別：

    use App\Http\Controllers\UserController;
    
    // Using PHP callable syntax...
    Route::get('/users', [UserController::class, 'index']);
    
    // Using string syntax...
    Route::get('/users', 'App\Http\Controllers\UserController@index');
在大多數情況下，此更改不會影響到從舊版升級過來的專案，因為專案中的 `RouteServiceProvider` 內 `$namespace` 應該還是包含了原本的值。不過，如果你是通過建立新專案來升級 Laravel 的，此更改就可能變成中斷性變更。

若想繼續使用原本這種自動為 Controller 加上前置詞的 Route，只需要在 `RouteServiceProvider` 中為 `$namespace` 屬性設定適當的值，並在 `boot` 方法中將 Route 定義更新為使用 `$namespace` 屬性即可：

    class RouteServiceProvider extends ServiceProvider
    {
        /**
         * The path to the "home" route for your application.
         *
         * This is used by Laravel authentication to redirect users after login.
         *
         * @var string
         */
        public const HOME = '/home';
    
        /**
         * If specified, this namespace is automatically applied to your controller routes.
         *
         * In addition, it is set as the URL generator's root namespace.
         *
         * @var string
         */
        protected $namespace = 'App\Http\Controllers';
    
        /**
         * Define your route model bindings, pattern filters, etc.
         *
         * @return void
         */
        public function boot()
        {
            $this->configureRateLimiting();
    
            $this->routes(function () {
                Route::middleware('web')
                    ->namespace($this->namespace)
                    ->group(base_path('routes/web.php'));
    
                Route::prefix('api')
                    ->middleware('api')
                    ->namespace($this->namespace)
                    ->group(base_path('routes/api.php'));
            });
        }
    
        /**
         * Configure the rate limiters for the application.
         *
         * @return void
         */
        protected function configureRateLimiting()
        {
            RateLimiter::for('api', function (Request $request) {
                return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
            });
        }
    }
<a name="scheduling"></a>

### 排程任務

<a name="the-cron-expression-library"></a>

#### `cron-expression` 函式庫

**受影響的可能：低**

Laravel 的相依性套件 `dragonmantank/cron-expression` 已從 `2.x` 版更新為 `3.x` 版。除非你有直接使用到 `cron-expression` 函式庫，否則這個更改應該不會造成任何中斷性變更。若你有直接使用到此函式庫，請閱讀其[更新日誌](https://github.com/dragonmantank/cron-expression/blob/master/CHANGELOG.md)。

<a name="session"></a>

### Session

<a name="the-session-contract"></a>

#### `Session` Contract

**受影響的可能：低**

`Illuminate\Contracts\Session\Session` Contract 現已定義了一個 `pull` 方法。若你有手動實作該 Contract，請更新該實作：

    /**
     * Get the value of a given key and then forget it.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function pull($key, $default = null);
<a name="testing"></a>

### 測試

<a name="decode-response-json-method"></a>

#### `decodeResponseJson` 方法

**受影響的可能：低**

`Illuminate\Testing\TestResponse` 類別中的 `decodeResponseJson` 方法已不再接受任何引數。請考慮改用 `json` 方法。

<a name="assert-exact-json-method"></a>

#### `assertExactJson` 方法

**受影響的可能性：中等**

`assertExactJson` 方法現在要求以數字作為索引鍵來比較的陣列必須使用相同的順序。若想在不要求相同順序的情況下比較 JSON 與以數字作為索引鍵的陣列，請改用 `assertSimilarJson`。

<a name="validation"></a>

### 表單驗證

<a name="database-rule-connections"></a>

### 資料庫 Rule 的連線

**受影響的可能：低**

`unique` 與 `exists` 規則現在會在查詢時使用 Eloquent Model 所指定的連線名稱 (通過 Model 的 `getConnectionName` 方法取得)。

<a name="miscellaneous"></a>

### 其他

我們也鼓勵你檢視 `laravel/laravel` [GitHub 存放庫](https://github.com/laravel/laravel)上的更改。雖然這些更改中大多數都不是必須要進行的，但你可能也會想讓專案中的這些檔案保持同步。其中一些修改有在本升級指南中提到，但有些其他的更改（如設定檔的更改或註解等）就沒有提到。可以通過 [GitHub 的比較工具](https://github.com/laravel/laravel/compare/7.x...8.x)來輕鬆地檢視這些更改，並自行評估哪些更改對你來說比較重要。
