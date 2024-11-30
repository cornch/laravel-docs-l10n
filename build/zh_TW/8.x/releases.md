---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/135/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 50.7
---

# 版本資訊

- [版本策略](#versioning-scheme)
  - [例外](#exceptions)
  
- [支援政策](#support-policy)
- [Laravel 8](#laravel-8)

<a name="versioning-scheme"></a>

## 版本策略

Laravel 及其第一方套件都遵守 [語義化版本](https://semver.org/lang/zh-Tw/)。框架的主要更新會每年釋出 (約在二月時)，而次版本與修訂版則可能頻繁到每週更新。次版本與修訂版 **絕對不會** 包含^[中斷性變更](Breaking Change)。

由於 Laravel 的主要更新會包含中斷性變更，因此在專案或套件中參照 Laravel 框架或其組件時，應使用如 `^8.0` 這樣的版本限制式。不過，我們也會不斷努力確保每次進行主要版本更新時，都可於一天之內升級完成。

<a name="exceptions"></a>

### 例外

<a name="named-arguments"></a>

#### 帶名稱的引數

截至目前為止，PHP 的[帶名稱引數](https://www.php.net/manual/en/functions.arguments.php#functions.named-arguments) 尚未包含在 Laravel 的向下相容性方針內。我們可能會在有必要的時候更改函式的引數名稱以改進 Laravel 的程式碼。因此，在使用帶名稱引數呼叫 Laravel 方法時應格外注意，並應瞭解到引數名稱未來可能會有所更改。

<a name="support-policy"></a>

## 支援政策

所有的 Laravel 版本都提供 18 個月的 Bug 修正，以及 2 年的安全性修正。對於其他的函式庫，如 Lumen，則只有最新的主要版本會收到 Bug 修正。此外，也請參考 [Laravel 支援的](/docs/{{version}}/database#introduction)資料庫版本。

| 版本 | PHP (*) | 釋出日期 | Bug 修正期限 | 安全性修正期限 |
| --- | --- | --- | --- | --- |
| 6 (LTS) | 7.2 - 8.0 | 2019 年 9 月 3 日 | 2022 年 1 月 25 日 | 2022 年 9 月 6 日 |
| 7 | 7.2 - 8.0 | 2020 年 3 月 3 日 | 2020 年 10 月 6 日 | 2021 年 3 月 3 日 |
| 8 | 7.3 - 8.1 | 2020 年 9 月 8 日 | 2022 年 7 月 26 日 | 2023 年 1 月 24 日 |
| 9 | 8.0 - 8.1 | 2022 年 2 月 8 日 | 2023 年 8 月 8 日 | 2024 年 2 月 6 日 |
| 10 | 8.1 - 8.3 | 2023 年 2 月 14 日 | 2024 年 8 月 6 日 | 2025 年 2 月 4 日 |

<div class="version-colors">
    <div class="end-of-life">
        <div class="color-box"></div>
        <div>End of life</div>
    </div>
    <div class="security-fixes">
        <div class="color-box"></div>
        <div>Security fixes only</div>
    </div>
</div>
(*) 支援的 PHP 版本

<a name="laravel-8"></a>

## Laravel 8

Laravel 8 持續地對 Laravel 7.x 進行改進，包含導入了 Laravel Jetstream、模型 Factory 類別、資料庫遷移壓縮、批次任務、改進頻率限制、佇列改進、動態 Blade 元件、Tailwind 分頁檢視器、測試時間用的輔助函式、對 `artisan serve` 的改進、時間監聽程式改進、以及各種其他 Bug 修正以及使用性改進。

<a name="laravel-jetstream"></a>

### Laravel Jetstream

*Laravel Jetstream 由 [Taylor Otwell](https://github.com/taylorotwell) 撰寫*。

[Laravel Jetstream](https://jetstream.laravel.com) 是一套用於 Laravel 的網站 Scaffolding，有漂亮的設計。Jetstream 為你的下一個專案提供了一個絕佳的開始點，包含登入、註冊、電子郵件認證、二步驟認證、Session 管理、通過 Laravel Sanctum 提供的 API 支援、以及選配的團隊管理。Laravel Jetstream 取代並改進了過往版本 Laravel 所提供的舊版認證 UI Scaffolding。

Jetstream 是使用 [Tailwind CSS](https://tailwindcss.com) 進行設計的，並提供了[Livewire](https://laravel-livewire.com) 或 [Inertia](https://inertiajs.com) Scaffolding 可進行選擇。

<a name="models-directory"></a>

### Model 目錄

為了回應來自社群的強烈要求，Laravel 專案的預設基本架構目前已包含了 `app/Models` 目錄。我們希望你能享受這個 Eloquent Model 的新家！所有相關的產生程式指令都已更新。而且，如果 `app/Models` 目錄存在，那麼這些產生程式會假設這個資料夾是用來存放 Model 的。若該目錄不存在，則框架會假設 Model 應放置於 `app` 目錄內。

<a name="model-factory-classes"></a>

### Model Factory 類別

*Model Factory 類別由 [Taylor Otwell](https://github.com/taylorotwell) 參與貢獻*。

Eloquent 的 [Model Factory](/docs/{{version}}/database-testing#defining-model-factories) 已經全面重寫為基於 Class 的 Factory 了，並且也經過改進來直接支援資料庫關聯。舉例來說，在 Laravel 中的 `UserFactory` 是這樣寫的：

    <?php
    
    namespace Database\Factories;
    
    use App\Models\User;
    use Illuminate\Database\Eloquent\Factories\Factory;
    use Illuminate\Support\Str;
    
    class UserFactory extends Factory
    {
        /**
         * The name of the factory's corresponding model.
         *
         * @var string
         */
        protected $model = User::class;
    
        /**
         * Define the model's default state.
         *
         * @return array
         */
        public function definition()
        {
            return [
                'name' => $this->faker->name(),
                'email' => $this->faker->unique()->safeEmail(),
                'email_verified_at' => now(),
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'remember_token' => Str::random(10),
            ];
        }
    }
由於產生的 Model 中包含了新的 `HasFactory` Trait，因此我們可以這樣使用 Model Factory：

    use App\Models\User;
    
    User::factory()->count(50)->create();
由於 Model Factory 已經是一般的 PHP 類別了，因此 State 的變換應通過類別方法來撰寫。此外，也可以依照需求在 Eloquent Model Factory 內加上任何其他的輔助函式。

舉例來說，`User` Model 可能會有個 `suspended` 狀態，用於修改 Model 中預設的屬性值。可以通過基礎 Factory 的 `state` 方法來定義狀態變換。可以任意為狀態方法命名。不管怎麼樣，這個方法就只是個單純的 PHP 方法而已：

    /**
     * Indicate that the user is suspended.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function suspended()
    {
        return $this->state([
            'account_status' => 'suspended',
        ]);
    }
定義好狀態變換方法後，我們可以這樣使用：

    use App\Models\User;
    
    User::factory()->count(5)->suspended()->create();
就像前面提到的一樣，Laravel 8 的 Model Factory 包含了對關聯的第一手支援。因此，假設我們的 `User` Model 有個 `posts` 關聯方法，我們只需要執行下列程式碼就能產生一個有 3 篇貼文的使用者：

    $users = User::factory()
                ->hasPosts(3, [
                    'published' => false,
                ])
                ->create();
為了減緩升級的過程，我們提供了 [laravel/legacy-factories](https://github.com/laravel/legacy-factories) 套件來在 Laravel 8.x 中提供舊版 Model Factory 的支援。

Laravel 的全新 Factory 包含了其他更多我們認為你會喜歡的功能。要瞭解更多有關 Model Factory 的資訊，請參考[資料庫測試說明文件](/docs/{{version}}/database-testing#defining-model-factories)。

<a name="migration-squashing"></a>

### 資料庫遷移壓縮

*Migration 壓縮由 [Taylor Otwell](https://github.com/taylorotwell) 參與貢獻*。

在寫網站的時候，我們可能會逐漸累積出越來越多的資料庫遷移檔。這樣可能會導致遷移檔目錄中被數百個遷移檔給佔滿。若你使用 MySQL 或 PostgreSQL，現在可以將遷移檔「壓縮」進單一 SQL 檔內。要開始壓縮，請執行 `schema:dump` 指令：

    php artisan schema:dump
    
    // Dump the current database schema and prune all existing migrations...
    php artisan schema:dump --prune
執行該指令時，Laravel 會將一個「結構描述 (Schema)」檔案寫入 `database/schema` 目錄內。接著，當要遷移資料庫且尚未執行過任何遷移時，Laravel 會先執行該結構描述檔的 SQL。執行玩結構描述檔的指令後，Laravel 才會接著執行不在該結構描述傾印中剩下的遷移。

<a name="job-batching"></a>

### 批次任務

*批次任務由 [Taylor Otwell](https://github.com/taylorotwell) & [Mohamed Said](https://github.com/themsaid) 參與貢獻*。

Laravel 的批次任務功能能讓你輕鬆地執行一系列的任務，並接著在這些任務完成後執行其他操作。

`Bus` Facade 的全新 `batch` 方法可以用來分派一批任務。當然，批次功能與完成回呼一起使用時是最有用。因此，可以使用 `then`, `catch` 與 `finally` 方法來為該批次定義完成回呼。這些回呼都會在被叫用時收到 `Illuminate\Bus\Batch` 實體：

    use App\Jobs\ProcessPodcast;
    use App\Models\Podcast;
    use Illuminate\Bus\Batch;
    use Illuminate\Support\Facades\Bus;
    use Throwable;
    
    $batch = Bus::batch([
        new ProcessPodcast(Podcast::find(1)),
        new ProcessPodcast(Podcast::find(2)),
        new ProcessPodcast(Podcast::find(3)),
        new ProcessPodcast(Podcast::find(4)),
        new ProcessPodcast(Podcast::find(5)),
    ])->then(function (Batch $batch) {
        // All jobs completed successfully...
    })->catch(function (Batch $batch, Throwable $e) {
        // First batch job failure detected...
    })->finally(function (Batch $batch) {
        // The batch has finished executing...
    })->dispatch();
    
    return $batch->id;
要瞭解更多有關批次任務的資訊，請參考[佇列說明文件](/docs/{{version}}/queues#job-batching)。

<a name="improved-rate-limiting"></a>

### 改進的頻率限制

(頻率限制的改進由 [Taylor Otwell](https://github.com/taylorotwell) 參與貢獻*。

Laravel 的請求頻率限制功能現在有了更多的彈性與能力，且仍於過去版本的 `throttle` 中間層 API 保持向下相容性。

可以使用 `RateLimiter` Facade 的 `for` 方法來定義頻率限制程式。`for` 方法接收頻率限制程式的名稱、以及一個閉包。該閉包應回傳頻率限制的設定，該設定將套用到有設定這個頻率限制程式的路由上：

    use Illuminate\Cache\RateLimiting\Limit;
    use Illuminate\Support\Facades\RateLimiter;
    
    RateLimiter::for('global', function (Request $request) {
        return Limit::perMinute(1000);
    });
由於頻率限制程式的回呼會接收連入 HTTP 請求實體，因此我們可以依據連入請求或登入使用者來動態調整適當的頻率限制：

    RateLimiter::for('uploads', function (Request $request) {
        return $request->user()->vipCustomer()
                    ? Limit::none()
                    : Limit::perMinute(100);
    });
有時候，我們可能會像以某些任意數值來設定頻率限制。舉例來說，我們可能會想限制給定的路由：每個 IP 位址每分鐘只能存取 100 次。為此，可以在設定頻率限制時使用 `by` 方法：

    RateLimiter::for('uploads', function (Request $request) {
        return $request->user()->vipCustomer()
                    ? Limit::none()
                    : Limit::perMinute(100)->by($request->ip());
    });
可以使用 `throttle` [Middleware](/docs/{{version}}/middleware) 來將頻率限制程式附加到路由或路由群組上。這個 Throttle Middleware 接受欲指派給路由的頻率限制程式名稱：

    Route::middleware(['throttle:uploads'])->group(function () {
        Route::post('/audio', function () {
            //
        });
    
        Route::post('/video', function () {
            //
        });
    });
要瞭解更多有關頻率限制的資訊，請參考[路由說明文件](/docs/{{version}}/routing#rate-limiting)。

<a name="improved-maintenance-mode"></a>

### 改進過的維護模式

*改進過的維護模式由 [Taylor Otwell](https://github.com/taylorotwell) 參與貢獻，靈感來自 [Spatie](https://spatie.be)*。

在之前版本的 Laravel 中，`php artisan down` 維護模式功能可以通過使用一組允許存取網站的 IP 位址「允許列表」來繞過。該功能現已被移除，並改用了一種更簡單的「密鑰」/ 權杖方案來代替。

在維護模式下，可以使用 `secret` 選項來指定一個用來繞過維護模式的權杖：

    php artisan down --secret="1630542a-246b-4b66-afa1-dd72a4c43515"
將應用程式放入維護模式後，可以瀏覽符合該權杖的應用程式網址，Laravel 會簽發一個繞過維護模式的 Cookie 給瀏覽器：

    https://example.com/1630542a-246b-4b66-afa1-dd72a4c43515
在存取該隱藏路由時，會接著被重新導向至應用程式的 `/` 路由。該 Cookie 被簽發給瀏覽器後，就可以像沒有在維護模式一樣正常地瀏覽應用程式。

<a name="pre-rendering-the-maintenance-mode-view"></a>

#### 預轉譯維護模式 View

若在部署過程中使用了 `php artisan down` 指令，若使用者在 Composer 依賴或其他基礎設施元件更新時存取了應用程式，則可能會遇到錯誤。這是因為 Laravel 框架中重要的部分必須要先啟動才能判斷應用程式是否在維護模式下，並才能接著使用樣板引擎來轉譯維護模式的 View。

基於此原因，現在，Laravel 能讓你預先轉譯維護模式 View，並在整個請求週期的一開始就將其回傳。這個 View 會在任何應用程式的依賴載入前就預先被轉譯。可以使用 `down` 指令的 `render` 選項來預轉譯所選的樣板：

    php artisan down --render="errors::503"
<a name="closure-dispatch-chain-catch"></a>

### 閉包分派與顆串連的 `catch`

*Catch 的改進由 [Mohamed Said](https://github.com/themsaid) 參與貢獻*。

使用新的 `catch` 方法，就能為佇列閉包提供一組要在所有重試次數都失敗的時候執行的閉包：

    use Throwable;
    
    dispatch(function () use ($podcast) {
        $podcast->publish();
    })->catch(function (Throwable $e) {
        // This job has failed...
    });
<a name="dynamic-blade-components"></a>

### 動態 Blade 元件

*動態 Blade 元件由 [Taylor Otwell](https://github.com/taylorotwell) 參與貢獻*。

有時候我們可能會需要轉譯元件，但在執行階段前並不知道要轉譯哪個元件。對於這種情況，現在，我們可以使用 Laravel 的內建「dynamic-component」動態元件來依照執行階段的值或變數進行轉譯：

    <x-dynamic-component :component="$componentName" class="mt-4" />
要瞭解更多有關 Blade 元件的資訊，請參考 [Blade 的說明文件](/docs/{{version}}/blade#components)。

<a name="event-listener-improvements"></a>

### 事件監聽程式的改進

*Event Listener 的改進由 [Taylor Otwell](https://github.com/taylorotwell) 參與貢獻*。

現在，只要將閉包傳給 `Event::listen` 方法，就可以註冊基於閉包的事件監聽程式。Laravel 會偵測閉包，以判斷該事件監聽程式能負責的事件類型：

    use App\Events\PodcastProcessed;
    use Illuminate\Support\Facades\Event;
    
    Event::listen(function (PodcastProcessed $event) {
        //
    });
此外，可以使用 `Illuminate\Events\queueable` 方法來將基於閉包的事件監聽程式標記為要放入佇列 (Queueable)：

    use App\Events\PodcastProcessed;
    use function Illuminate\Events\queueable;
    use Illuminate\Support\Facades\Event;
    
    Event::listen(queueable(function (PodcastProcessed $event) {
        //
    }));
就像佇列任務一樣，可以使用 `onConnection`, `onQueue`, 與 `delay` 方法來自訂放入佇列的監聽程式的執行：

    Event::listen(queueable(function (PodcastProcessed $event) {
        //
    })->onConnection('redis')->onQueue('podcasts')->delay(now()->addSeconds(10)));
若想在匿名的佇列監聽程式執行失敗時進行處理，可以在定義 `queueable` 監聽程式時提供一個閉包給 `catch` 方法：

    use App\Events\PodcastProcessed;
    use function Illuminate\Events\queueable;
    use Illuminate\Support\Facades\Event;
    use Throwable;
    
    Event::listen(queueable(function (PodcastProcessed $event) {
        //
    })->catch(function (PodcastProcessed $event, Throwable $e) {
        // The queued listener failed...
    }));
<a name="time-testing-helpers"></a>

### 時間測試輔助函式

*時間測試輔助韓式由 [Taylor Otwell](https://github.com/taylorotwell) 參與貢獻，靈感來自 Ruby on Rails*。

在測試的時候，我們有時候會想要更改如 `now` 或 `Illuminate\Support\Carbon::now()` 等輔助函式所回傳的時間。現在，Laravel 的基礎功能測試 (Feature Test) 類別已包含了顆用來更改目前時間的輔助函式：

    public function testTimeCanBeManipulated()
    {
        // Travel into the future...
        $this->travel(5)->milliseconds();
        $this->travel(5)->seconds();
        $this->travel(5)->minutes();
        $this->travel(5)->hours();
        $this->travel(5)->days();
        $this->travel(5)->weeks();
        $this->travel(5)->years();
    
        // Travel into the past...
        $this->travel(-5)->hours();
    
        // Travel to an explicit time...
        $this->travelTo(now()->subHours(6));
    
        // Return back to the present time...
        $this->travelBack();
    }
<a name="artisan-serve-improvements"></a>

### Artisan `serve` 的改進

*Artisan `serve` 的改進由 [Taylor Otwell](https://github.com/taylorotwell) 參與貢獻*。

Artisan `serve` 指令已經過改進，該指令會偵測本機的 `.env` 檔案，並在環境變數更改的時候自動重新載入。在此之前，需要手動停止並重新啟動該指令。

<a name="tailwind-pagination-views"></a>

### Tailwind 分頁 View

Laravel 的分頁程式 (Paginator) 已更新為預設使用 [Tailwind CSS](https://tailwindcss.com) 框架。Tailwind CSS 是一個可高度客製化、低階的 CSS 框架，能讓你不需處理並複寫一些煩人的固定樣式，就能製作所有你所需要的客製化區塊。當然，Bootstrap 3 與 Bootstrap 4 的 View 依然可用。

<a name="routing-namespace-updates"></a>

### 路由 Namespace 更新

在之前的 Laravel 版本中，`RouteServiceProvider` 包含了一個 `$namespace` 屬性。當使用 Controller 路由定義或是呼叫 `action` 輔助函式 / `URL::action` 方法時，會自動將該屬性的值加到前面。在 Laravel 8.x 中，這個屬性預設為 `null`。這表示，Laravel 將不會自動幫你將 Namespace 放在前面。因此，在新安裝的 Laravel 8.x 專案中，Controller 路由定義應使用標準的 PHP Callable 語法來定義：

    use App\Http\Controllers\UserController;
    
    Route::get('/users', [UserController::class, 'index']);
與呼叫 `actions` 相關的方法也應使用相同的 Callable 語法：

    action([UserController::class, 'index']);
    
    return Redirect::action([UserController::class, 'index']);
若你偏好使用 Laravel 7.x 風格的 Controller 路由前置，只需要在專案的 `RouteServiceProvider` 中加上 `$namespace` 屬性即可。

> [!NOTE]  
> This change only affects new Laravel 8.x applications. Applications upgrading from Laravel 7.x will still have the `$namespace` property in their `RouteServiceProvider`.
