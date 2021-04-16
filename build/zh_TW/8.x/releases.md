# 版本資訊

- [Versioning Scheme](#versioning-scheme)
    - [Exceptions](#exceptions)
- [Support Policy](#support-policy)
- [Laravel 8](#laravel-8)

<a name="versioning-scheme"></a>
## 版本策略

Laravel 及其第一方套件都遵守
[語義化版本](https://semver.org/lang/zh-Tw/)。框架的主要更新會每年釋出（約在九月時），而次版本與修訂版則可能頻繁到每週更新。此版本與修訂版
**絕對不會** 包含中斷性變更（Breaking Change）。

由於 Laravel 的主要更新會包含中斷性變更，因此在應用程式或套件中參照 Laravel 框架或其組件時，應使用如 `^8.0`
這樣的版本限制式。然而，我們竭力確保主要更新應可於一天之內完成。

<a name="exceptions"></a>
### 例外

<a name="named-arguments"></a>
#### 帶名稱的引數

截至目前為止，PHP 的
[帶名稱引數](https://www.php.net/manual/en/functions.arguments.php#functions.named-arguments)
尚未包含在 Laravel 的向下相容性方針內。我們可能會在有必要的時候更改函式的引數名稱以改進 Laravel 的程式碼。因此，在使用帶名稱引數呼叫
Laravel 方法時應格外注意，並瞭解到引數名稱未來可能會有所更改。

<a name="support-policy"></a>
## 支援政策

LTS 版本，如 Laravel 6，提供 2 年的 Bug 修正以及 3 年的安全性修正。這些版本提供了最長的支援與維護期間。而一般性版本，則提供
18 個月的 Bug 修正以及 2 年的安全性更新。其他額外的函式庫，如 Lumen，則至為最新版本提供 Bug 修正。此外，請參考 [Laravel
支援的](/docs/{{version}}/database#introduction) 資料庫版本。

| 版本 | 釋出日期 | Bug 修正至 | 安全性更新至 |
| --- | --- | --- | --- |
| 6 (LTS) | 2019 年 9 月 3 日 | 2021 年 9 月 7 日 | 2022 年 9 月 6 日 |
| 7 | 2020 年 3 月 3 日 | 2020 年 10 月 6 日 | 2021 年 3 月 3 日 |
| 8 | 2020 年 9 月 8 日 | 2022 年 3 月 1 日 | 2022 年 9 月 6 日 |
| 9 (LTS) | 2021 年 9 月 | 2023 年 9 月 | 2024 年 9 月 |
| 10 | 2022 年 9 月 | 2024 年 3 月 | 2024 年 9 月 |

<a name="laravel-8"></a>
## Laravel 8

Laravel 8 持續地對 Laravel 7.x 進行改進，包含導入了 Laravel Jetstream、模型 Factory
類別、資料庫遷移壓縮、批次任務、改進頻率限制、佇列改進、動態 Blade 元件、Tailwind 分頁檢視器、測試時間用的輔助函式、對 `artisan
serve` 的改進、時間監聽程式改進、以及各種其他 Bug 修正以及使用性改進。

<a name="laravel-jetstream"></a>
### Laravel Jetstream

_Laravel Jetstream 由 [Taylor Otwell](https://github.com/taylorotwell) 撰寫_。

[Laravel Jetstream](https://jetstream.laravel.com) 是一套用於 Laravel 的應用程式
Scaffolding，有漂亮的設計。Jetstream
為你的下一個專案提供了一個絕佳的開始點，包含登入、註冊、電子郵件驗證、二步驟驗證、Session 管理、通過 Laravel Sanctum 提供的
API 支援、以及選配的團隊管理。Laravel Jetstream 取代並改進了過往版本 Laravel 所提供的舊版驗證 UI
Scaffolding。

Jetstream 是使用 [Tailwind CSS](https://tailwindcss.com)
進行設計的，並提供了[Livewire](https://laravel-livewire.com) 或
[Inertia](https://inertiajs.com) Scaffolding 可進行選擇。

<a name="models-directory"></a>
### 模型目錄

為了回應來自社群的強烈要求，Laravel 應用程式的預設基本架構目前已包含了 `app/Models` 目錄。我們希望你能享受這個 Eloquent
模型的新家！所有相關的產生程式指令都已更新，並且這些程式會在 `app/Models`
目錄存在的時候假設模型放在該資料夾內。若該目錄不存在，則框架會假設模型放置與 `app` 目錄內。

<a name="model-factory-classes"></a>
### 模型 Factory 類別

_模型 Factory 類別由 [Taylor Otwell](https://github.com/taylorotwell) 貢獻_。

Eloquent 的[模型
Factory](/docs/{{version}}/database-testing#defining-model-factories)
已經全面重寫為基於類別的 Factory 了，並且也經過改進來直接支援資料庫關聯。舉例來說，在 Laravel 中的 `UserFactory`
是這樣寫的：

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
                'name' => $this->faker->name,
                'email' => $this->faker->unique()->safeEmail,
                'email_verified_at' => now(),
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'remember_token' => Str::random(10),
            ];
        }
    }

由於產生的模型中包含了新的 `HasFactory` Trait，模型 Factory 可以這樣使用：

    use App\Models\User;

    User::factory()->count(50)->create();

由於模型 Factory 目前基本上是 PHP 類別，因此 State 的變換應通過類別方法來撰寫。此外，也可以依照需求在 Eloquent 模型
Factory 內加上任何其他的輔助函式。

舉例來說，`User` 模型可能會有個 `suspended` 狀態，用於修改模型中預設的屬性值。可以通過基礎 Factory 的 `state`
方法來定義狀態變換。可以任意為狀態方法命名。不管怎麼樣，這個方法就只是個單純的 PHP 方法而已：

    /**
     * 用於標示該使用者已被停用。
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

就像前面提到的一樣，Laravel 8 的模型 Factory 包含了對關聯的第一手支援。因此，假設我們的 `User` 模型有個 `posts`
關聯方法，我們只需要執行下列程式碼就能產生一個有 3 篇貼文的使用者：

    $users = User::factory()
                ->hasPosts(3, [
                    'published' => false,
                ])
                ->create();

為了減緩升級的過程，我們提供了
[laravel/legacy-factories](https://github.com/laravel/legacy-factories) 套件來在
Laravel 8.x 中提供舊版模型 Factory 的支援。

Laravel 的全新 Factory 包含了其他更多我們認為你會喜歡的功能。要瞭解更多有關模型 Factory 的資訊，請參考
[資料庫測試說明文件](/docs/{{version}}/database-testing#defining-model-factories)。

<a name="migration-squashing"></a>
### 資料庫遷移壓縮

_資料庫遷移壓縮由 [Taylor Otwell](https://github.com/taylorotwell) 貢獻_。

隨著應用程式的建立，我們可能會逐漸累積出越來越多的資料庫遷移檔。這樣可能會導致遷移檔目錄中被數百個遷移檔給佔滿。若你使用 MySQL 或
PostgreSQL，現在可以將遷移檔「壓縮」進單一一個 SQL 檔內。要開始壓縮，請執行 `schema:dump` 指令：

    php artisan schema:dump

    // Dump the current database schema and prune all existing migrations...
    php artisan schema:dump --prune

執行該指令時，Laravel 會將一個「結構描述」檔案寫入 `database/schema`
目錄內。接著，當要遷移資料庫且尚未執行過任何遷移時，Laravel 會先執行該結構描述檔的 SQL。執行玩結構描述檔的指令後，Laravel
才會接著執行不在該結構描述傾印中剩下的遷移。

<a name="job-batching"></a>
### Job Batching

_批次任務是由 [Taylor Otwell](https://github.com/taylorotwell) & [Mohamed
Said](https://github.com/themsaid) 貢獻的_。

Laravel 的批次任務功能能讓你輕鬆地執行一系列的任務，並接著在這些任務完成後執行其他操作。

`Bus` Facade 的全新 `batch` 方法可以用來分派一批任務。當然，批次功能與完成回呼一起使用時是最有用。因此，可以使用 `then`,
`catch` 與 `finally` 方法來為該批次定義完成回呼。這些回呼都會在被叫用時收到 `Illuminate\Bus\Batch` 實體：

    use App\Jobs\ProcessPodcast;
    use App\Podcast;
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
        // 所有任務都已成功完成...
    })->catch(function (Batch $batch, Throwable $e) {
        // 偵測到第一個批次任務失敗...
    })->finally(function (Batch $batch) {
        // 已完成執行批次...
    })->dispatch();

    return $batch->id;

要瞭解更多有關批次任務的資訊，請參考[佇列說明文件](/docs/{{version}}/queues#job-batching)。

<a name="improved-rate-limiting"></a>
### 改進的頻率限制

_頻率限制的改進由 [Taylor Otwell](https://github.com/taylorotwell) 貢獻_。

Laravel 的請求頻率限制功能現在有了更多的彈性與能力，且仍於過去版本的 `throttle` 中間層 API 保持向下相容性。

Rate limiters are defined using the `RateLimiter` facade's `for` method. The
`for` method accepts a rate limiter name and a closure that returns the
limit configuration that should apply to routes that are assigned this rate
limiter:

    use Illuminate\Cache\RateLimiting\Limit;
    use Illuminate\Support\Facades\RateLimiter;

    RateLimiter::for('global', function (Request $request) {
        return Limit::perMinute(1000);
    });

Since rate limiter callbacks receive the incoming HTTP request instance, you
may build the appropriate rate limit dynamically based on the incoming
request or authenticated user:

    RateLimiter::for('uploads', function (Request $request) {
        return $request->user()->vipCustomer()
                    ? Limit::none()
                    : Limit::perMinute(100);
    });

Sometimes you may wish to segment rate limits by some arbitrary value. For
example, you may wish to allow users to access a given route 100 times per
minute per IP address. To accomplish this, you may use the `by` method when
building your rate limit:

    RateLimiter::for('uploads', function (Request $request) {
        return $request->user()->vipCustomer()
                    ? Limit::none()
                    : Limit::perMinute(100)->by($request->ip());
    });

Rate limiters may be attached to routes or route groups using the `throttle`
[middleware](/docs/{{version}}/middleware). The throttle middleware accepts
the name of the rate limiter you wish to assign to the route:

    Route::middleware(['throttle:uploads'])->group(function () {
        Route::post('/audio', function () {
            //
        });

        Route::post('/video', function () {
            //
        });
    });

To learn more about rate limiting, please consult the [routing
documentation](/docs/{{version}}/routing#rate-limiting).

<a name="improved-maintenance-mode"></a>
### Improved Maintenance Mode

_Maintenance mode improvements were contributed by [Taylor
Otwell](https://github.com/taylorotwell) with inspiration from
[Spatie](https://spatie.be)_.

In previous releases of Laravel, the `php artisan down` maintenance mode
feature may be bypassed using an "allow list" of IP addresses that were
allowed to access the application. This feature has been removed in favor of
a simpler "secret" / token solution.

While in maintenance mode, you may use the `secret` option to specify a
maintenance mode bypass token:

    php artisan down --secret="1630542a-246b-4b66-afa1-dd72a4c43515"

將應用程式放入維護模式後，可以瀏覽符合該權杖的應用程式網址，Laravel 會簽發一個繞過維護模式的 Cookie 給瀏覽器：

    https://example.com/1630542a-246b-4b66-afa1-dd72a4c43515

在存取該隱藏路由時，會接著被重新導向至應用程式的 `/` 路由。該 Cookie 被簽發給瀏覽器後，就可以像沒有在維護模式一樣正常地瀏覽應用程式。

<a name="pre-rendering-the-maintenance-mode-view"></a>
#### 預轉譯維護模式 View

若在部署過程中使用了 `php artisan down` 指令，若使用者在 Composer
依賴或其他基礎設施元件更新時存取了應用程式，則可能會遇到錯誤。這是因為 Laravel
框架中重要的部分必須要先啟動才能判斷應用程式是否在維護模式下，並才能接著使用樣板引擎來轉譯維護模式的 View。

For this reason, Laravel now allows you to pre-render a maintenance mode
view that will be returned at the very beginning of the request cycle. This
view is rendered before any of your application's dependencies have
loaded. You may pre-render a template of your choice using the `down`
command's `render` option:

    php artisan down --render="errors::503"

<a name="closure-dispatch-chain-catch"></a>
### Closure Dispatch / Chain `catch`

_Catch improvements were contributed by [Mohamed
Said](https://github.com/themsaid)_.

Using the new `catch` method, you may now provide a closure that should be
executed if a queued closure fails to complete successfully after exhausting
all of your queue's configured retry attempts:

    use Throwable;

    dispatch(function () use ($podcast) {
        $podcast->publish();
    })->catch(function (Throwable $e) {
        // This job has failed...
    });

<a name="dynamic-blade-components"></a>
### Dynamic Blade Components

_Dynamic Blade components were contributed by [Taylor
Otwell](https://github.com/taylorotwell)_.

Sometimes you may need to render a component but not know which component
should be rendered until runtime. In this situation, you may now use
Laravel's built-in `dynamic-component` component to render the component
based on a runtime value or variable:

    <x-dynamic-component :component="$componentName" class="mt-4" />

To learn more about Blade components, please consult the [Blade
documentation](/docs/{{version}}/blade#components).

<a name="event-listener-improvements"></a>
### Event Listener Improvements

_Event listener improvements were contributed by [Taylor
Otwell](https://github.com/taylorotwell)_.

Closure based event listeners may now be registered by only passing the
closure to the `Event::listen` method. Laravel will inspect the closure to
determine which type of event the listener handles:

    use App\Events\PodcastProcessed;
    use Illuminate\Support\Facades\Event;

    Event::listen(function (PodcastProcessed $event) {
        //
    });

In addition, closure based event listeners may now be marked as queueable
using the `Illuminate\Events\queueable` function:

    use App\Events\PodcastProcessed;
    use function Illuminate\Events\queueable;
    use Illuminate\Support\Facades\Event;

    Event::listen(queueable(function (PodcastProcessed $event) {
        //
    }));

Like queued jobs, you may use the `onConnection`, `onQueue`, and `delay`
methods to customize the execution of the queued listener:

    Event::listen(queueable(function (PodcastProcessed $event) {
        //
    })->onConnection('redis')->onQueue('podcasts')->delay(now()->addSeconds(10)));

If you would like to handle anonymous queued listener failures, you may
provide a closure to the `catch` method while defining the `queueable`
listener:

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
### Time Testing Helpers

_Time testing helpers were contributed by [Taylor
Otwell](https://github.com/taylorotwell) with inspiration from Ruby on
Rails_.

When testing, you may occasionally need to modify the time returned by
helpers such as `now` or `Illuminate\Support\Carbon::now()`. Laravel's base
feature test class now includes helpers that allow you to manipulate the
current time:

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
### Artisan `serve` Improvements

_Artisan `serve` improvements were contributed by [Taylor
Otwell](https://github.com/taylorotwell)_.

The Artisan `serve` command has been improved with automatic reloading when
environment variable changes are detected within your local `.env`
file. Previously, the command had to be manually stopped and restarted.

<a name="tailwind-pagination-views"></a>
### Tailwind Pagination Views

The Laravel paginator has been updated to use the [Tailwind
CSS](https://tailwindcss.com) framework by default. Tailwind CSS is a highly
customizable, low-level CSS framework that gives you all of the building
blocks you need to build bespoke designs without any annoying opinionated
styles you have to fight to override. Of course, Bootstrap 3 and 4 views
remain available as well.

<a name="routing-namespace-updates"></a>
### Routing Namespace Updates

In previous releases of Laravel, the `RouteServiceProvider` contained a
`$namespace` property. This property's value would automatically be prefixed
onto controller route definitions and calls to the `action` helper /
`URL::action` method. In Laravel 8.x, this property is `null` by
default. This means that no automatic namespace prefixing will be done by
Laravel. Therefore, in new Laravel 8.x applications, controller route
definitions should be defined using standard PHP callable syntax:

    use App\Http\Controllers\UserController;

    Route::get('/users', [UserController::class, 'index']);

Calls to the `action` related methods should use the same callable syntax:

    action([UserController::class, 'index']);

    return Redirect::action([UserController::class, 'index']);

If you prefer Laravel 7.x style controller route prefixing, you may simply
add the `$namespace` property into your application's
`RouteServiceProvider`.

> {note} This change only affects new Laravel 8.x applications. Applications upgrading from Laravel 7.x will still have the `$namespace` property in their `RouteServiceProvider`.
