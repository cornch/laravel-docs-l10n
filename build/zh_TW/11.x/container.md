---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/31/en-zhtw'
updatedAt: '2024-06-30T08:17:00Z'
contributors: {  }
progress: 45.05
---

# Service Container

- [簡介](#introduction)
  - [不需設定的解析](#zero-configuration-resolution)
  - [When to Utilize the Container](#when-to-use-the-container)
  
- [繫結 (Binding)](#binding)
  - [「繫結」基礎](#binding-basics)
  - [Binding Interfaces to Implementations](#binding-interfaces-to-implementations)
  - [基於上下文的繫結](#contextual-binding)
  - [Contextual Attributes](#contextual-attributes)
  - [繫結原生值](#binding-primitives)
  - [繫結有型別的 Variadic](#binding-typed-variadics)
  - [標籤](#tagging)
  - [擴充繫結](#extending-bindings)
  
- [解析](#resolving)
  - [Make 方法](#the-make-method)
  - [自動插入](#automatic-injection)
  
- [Method Invocation and Injection](#method-invocation-and-injection)
- [Container 事件](#container-events)
  - [Rebinding](#rebinding)
  
- [PSR-11](#psr-11)

<a name="introduction"></a>

## 簡介

Laravel 的 Service Container 是用來管理類別依賴與進行依賴注入的一個有力工具。依賴注入只是個花俏的詞，基本上依賴注入就代表：類別的依賴是通過其建構函式來「注入」進類別的，或者，某些情況下是使用「Setter」方法。

來看看一個簡單的例子：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Services\AppleMusic;
    use Illuminate\View\View;
    
    class PodcastController extends Controller
    {
        /**
         * Create a new controller instance.
         */
        public function __construct(
            protected AppleMusic $apple,
        ) {}
    
        /**
         * Show information about the given podcast.
         */
        public function show(string $id): View
        {
            return view('podcasts.show', [
                'podcast' => $this->apple->findPodcast($id)
            ]);
        }
    }
In this example, the `PodcastController` needs to retrieve podcasts from a data source such as Apple Music. So, we will **inject** a service that is able to retrieve podcasts. Since the service is injected, we are able to easily "mock", or create a dummy implementation of the `AppleMusic` service when testing our application.

要想建立強大的大型專案、或是參與貢獻 Laravel 核心，就必須要深入瞭解 Laravel 的 Service Container。

<a name="zero-configuration-resolution"></a>

### 不需設定的解析

若某個類別沒有依賴，或是只依賴其他實體類別 (即非介面的依賴)，就不需要告訴 Container 如何解析這個類別。舉例來說，可以在 `routes/web.php` 檔中加上下列程式碼：

    <?php
    
    class Service
    {
        // ...
    }
    
    Route::get('/', function (Service $service) {
        die($service::class);
    });
在這個例子中，打開網站的 `/` 路由就會自動解析 `Service` 類別並將其注入路由的處理程式中。這是個顛覆性的方法。因為這表示不需要肥大的設定檔，就能通過依賴注入來進行開發。

所幸，用 Laravel 撰寫專案時會寫到的許多類別都會自動通過 Container 來接收依賴，包含[Controller](/docs/{{version}}/controllers)、[事件監聽程式](/docs/{{version}}/events)、[Middleware](/docs/{{version}}/middleware) …等其他類別。此外，還可以在[佇列任務](/docs/{{version}}/queues) 的 `handle` 方法內型別提示依賴。一旦你試過自動與無需設定的依賴注入後，就很難不用依賴注入來開發了。

<a name="when-to-use-the-container"></a>

### When to Utilize the Container

多虧有不需設定的解析，通常只需要在路由、Controller、事件監聽程式等地方型別提示即可，不需手動與 Container 互動。舉例來說，可以在路由定義上型別提示 `Illuminate\Http\Request` 物件，就可輕鬆存取目前的請求。就算我們從來沒寫過與 Container 互動的程式碼，Container 依然能自動幫我們將這些依賴注入進去：

    use Illuminate\Http\Request;
    
    Route::get('/', function (Request $request) {
        // ...
    });
在許多情況下，多虧有自動依賴注入以及 [Facades](/docs/{{version}}/facades)，我們 **完全** 不需要從 Container 上手動繫結或解析任何東西，就可以使用 Laravel 來進行專案開發。**那麼，什麼時候才會需要手動操作 Container 呢？** 讓我們來看看兩個情況。

首先，若寫了一個實作介面的類別，而希望能在路由或類別建構函式上型別提示這個介面，就必須要[告訴 Container 要如何解析該介面](#binding-interfaces-to-implementations)。再來，若是在[撰寫 Laravel 套件](/docs/{{version}}/packages)，並希望將該套件分享給其他 Laravel 開發者，則可能會需要將套件的服務繫結到 Container 上。

<a name="binding"></a>

## 繫結 (Binding)

<a name="binding-basics"></a>

### 「繫結」基礎

<a name="simple-bindings"></a>

#### 簡單繫結

幾乎所有的服務 Container 繫結都會註冊在 [Service Provider](/docs/{{version}}/providers) 上。因此，這裡大多數的範例都會在這個脈絡下展示使用 Container。

在 Service Provider 中，總是能使用 `$this->app` 屬性來存取 Container。我們可以使用 `bind` 方法來註冊一個繫結，並將我們想註冊的類別或介面與用來回傳該類別實體的閉包一起傳入。

    use App\Services\Transistor;
    use App\Services\PodcastParser;
    use Illuminate\Contracts\Foundation\Application;
    
    $this->app->bind(Transistor::class, function (Application $app) {
        return new Transistor($app->make(PodcastParser::class));
    });
請注意，我們會收到 Container 自己作為該解析程式的一個引數。我們可以接著使用該 Container 來解析我們正在建構的物件的其他子依賴。

就像之前提過的，我們通常會在 Service Provider 內操作 Container。不過，若想在 Service Provider 外操作 Container，則可以使用 `App` [Facade](/docs/{{version}}/facades)：

    use App\Services\Transistor;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Support\Facades\App;
    
    App::bind(Transistor::class, function (Application $app) {
        // ...
    });
可以使用 `bindIf` 方法來只在某個繫結還未被註冊到給定型別時向 Container 註冊繫結：

```php
$this->app->bindIf(Transistor::class, function (Application $app) {
    return new Transistor($app->make(PodcastParser::class));
});
```
> [!NOTE]  
> 若類別沒有依賴任何介面，就不需要將其繫結到 Container 上。不需要告訴 Container 如何建構這些物件，因為這些物件可以通過 Reflection 自動被解析。

<a name="binding-a-singleton"></a>

#### 建構單例 (Singleton)

`singleton` 方法可將一些只需要解析一次的類別或介面繫結至 Container 內，解析完單例繫結後，接下來呼叫 Container 都會回傳相同的物件實體：

    use App\Services\Transistor;
    use App\Services\PodcastParser;
    use Illuminate\Contracts\Foundation\Application;
    
    $this->app->singleton(Transistor::class, function (Application $app) {
        return new Transistor($app->make(PodcastParser::class));
    });
可以使用 `singletonIf` 方法來只在某個繫結還未被註冊到給定型別時向 Container 註冊單例：

```php
$this->app->singletonIf(Transistor::class, function (Application $app) {
    return new Transistor($app->make(PodcastParser::class));
});
```
<a name="binding-scoped"></a>

#### 繫結限定作用範圍的單例

`scoped` 方法用來將一些只在給定 Laravel 請求 / 任務生命週期中被解析一次的類別或介面繫結到 Container 中。雖然這個方法與 `singleton` 方法類似，不過使用 `scoped` 方法繫結的實體會在每次 Laravel 開始新「生命週期」時被清除，如：[Laravel Octane](/docs/{{version}}/octane) 背景工作角色處理新請求時，或是 Laravel [佇列背景工作角色](/docs/{{version}}/queues) 處理新任務時。

    use App\Services\Transistor;
    use App\Services\PodcastParser;
    use Illuminate\Contracts\Foundation\Application;
    
    $this->app->scoped(Transistor::class, function (Application $app) {
        return new Transistor($app->make(PodcastParser::class));
    });
You may use the `scopedIf` method to register a scoped container binding only if a binding has not already been registered for the given type:

    $this->app->scopedIf(Transistor::class, function (Application $app) {
        return new Transistor($app->make(PodcastParser::class));
    });
<a name="binding-instances"></a>

#### 繫結實體

也可以使用 `instance` 方法來將現有的物件實體繫結到 Container 上。接下來對 Container 的呼叫都會回傳給定的實體：

    use App\Services\Transistor;
    use App\Services\PodcastParser;
    
    $service = new Transistor(new PodcastParser);
    
    $this->app->instance(Transistor::class, $service);
<a name="binding-interfaces-to-implementations"></a>

### Binding Interfaces to Implementations

Service Container 其中一個非常強大的功能就是能將介面繫結到給定的實作上。舉例來說，假設我們有一個 `EventPusher` 介面，以及一個 `RedisEventPusher` 實作。寫好這個介面的 `RedisEventPusher` 實作程式後，我們就像這樣將其註冊到 Service Container 上：

    use App\Contracts\EventPusher;
    use App\Services\RedisEventPusher;
    
    $this->app->bind(EventPusher::class, RedisEventPusher::class);
這個陳述式會告訴 Container 應在有類別需要 `EventPusher` 的實作時將 `RedisEventPusher` 注入進去。接著，我們可以在某個會被 Container 解析的類別之建構函式上型別提示 `EventPusher` 介面。請記得，Laravel 專案中的 Controller、事件監聽程式、Middleware、以及其他多種類型的類別都是使用 Container 來解析的：

    use App\Contracts\EventPusher;
    
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected EventPusher $pusher,
    ) {}
<a name="contextual-binding"></a>

### 基於上下文的繫結

有時候，可能會有兩個類別使用相同的介面，但又想在各個類別上注入不同的實作。舉例來說，可能有兩個 Controller 依賴不同實作的 `Illuminate\Contracts\Filesystem\Filesystem`  [Contract](/docs/{{version}}/contracts)。Laravel 提供了一個簡單但流暢的介面來定義這種行為：

    use App\Http\Controllers\PhotoController;
    use App\Http\Controllers\UploadController;
    use App\Http\Controllers\VideoController;
    use Illuminate\Contracts\Filesystem\Filesystem;
    use Illuminate\Support\Facades\Storage;
    
    $this->app->when(PhotoController::class)
              ->needs(Filesystem::class)
              ->give(function () {
                  return Storage::disk('local');
              });
    
    $this->app->when([VideoController::class, UploadController::class])
              ->needs(Filesystem::class)
              ->give(function () {
                  return Storage::disk('s3');
              });
<a name="contextual-attributes"></a>

### Contextual Attributes

Since contextual binding is often used to inject implementations of drivers or configuration values, Laravel offers a variety of contextual binding attributes that allow to inject these types of values without manually defining the contextual bindings in your service providers.

For example, the `Storage` attribute may be used to inject a specific [storage disk](/docs/{{version}}/filesystem):

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Container\Attributes\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;

class PhotoController extends Controller
{
    public function __construct(
        #[Storage('local')] protected Filesystem $filesystem
    )
    {
        // ...
    }
}
```
In addition to the `Storage` attribute, Laravel offers `Auth`, `Cache`, `Config`, `DB`, `Log`, `RouteParameter`, and [`Tag`](#tagging) attributes:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Container\Attributes\Cache;
use Illuminate\Container\Attributes\Config;
use Illuminate\Container\Attributes\DB;
use Illuminate\Container\Attributes\Log;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Container\Attributes\Tag;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Connection;
use Psr\Log\LoggerInterface;

class PhotoController extends Controller
{
    public function __construct(
        #[Auth('web')] protected Guard $auth,
        #[Cache('redis')] protected Repository $cache,
        #[Config('app.timezone')] protected string $timezone,
        #[DB('mysql')] protected Connection $connection,
        #[Log('daily')] protected LoggerInterface $log,
        #[RouteParameter('photo')] protected Photo $photo,
        #[Tag('reports')] protected iterable $reports,
    )
    {
        // ...
    }
}
```
Furthermore, Laravel provides a `CurrentUser` attribute for injecting the currently authenticated user into a given route or class:

```php
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;

Route::get('/user', function (#[CurrentUser] User $user) {
    return $user;
})->middleware('auth');
```
<a name="defining-custom-attributes"></a>

#### Defining Custom Attributes

You can create your own contextual attributes by implementing the `Illuminate\Contracts\Container\ContextualAttribute` contract. The container will call your attribute's `resolve` method, which should resolve the value that should be injected into the class utilizing the attribute. In the example below, we will re-implement Laravel's built-in `Config` attribute:

    <?php
    
    namespace App\Attributes;
    
    use Illuminate\Contracts\Container\ContextualAttribute;
    
    #[Attribute(Attribute::TARGET_PARAMETER)]
    class Config implements ContextualAttribute
    {
        /**
         * Create a new attribute instance.
         */
        public function __construct(public string $key, public mixed $default = null)
        {
        }
    
        /**
         * Resolve the configuration value.
         *
         * @param  self  $attribute
         * @param  \Illuminate\Contracts\Container\Container  $container
         * @return mixed
         */
        public static function resolve(self $attribute, Container $container)
        {
            return $container->make('config')->get($attribute->key, $attribute->default);
        }
    }
<a name="binding-primitives"></a>

### 繫結原生型別

有時候可能會有類別要接收注入的類別，但同時也需要注入原生型別的值，如整數。可以輕鬆地使用基於上下文的繫結來注入任何類別所需的值：

    use App\Http\Controllers\UserController;
    
    $this->app->when(UserController::class)
              ->needs('$variableName')
              ->give($value);
有時候，某個類別可能會依賴一個包含[有標記](#tagging)實體的陣列。使用 `giveTagged` 方法，就可以輕鬆將所有有該標籤的 Container 繫結注入進去：

    $this->app->when(ReportAggregator::class)
        ->needs('$reports')
        ->giveTagged('reports');
若有需要注入來自專案設定檔的值，則可使用 `giveConfig` 方法：

    $this->app->when(ReportAggregator::class)
        ->needs('$timezone')
        ->giveConfig('app.timezone');
<a name="binding-typed-variadics"></a>

### 繫結有型別提示的 Variadic 參數

有時候，某個類別可能會需要使用 Variadic 建構函式引數來接收一個包含型別提示物件的陣列：

    <?php
    
    use App\Models\Filter;
    use App\Services\Logger;
    
    class Firewall
    {
        /**
         * The filter instances.
         *
         * @var array
         */
        protected $filters;
    
        /**
         * Create a new class instance.
         */
        public function __construct(
            protected Logger $logger,
            Filter ...$filters,
        ) {
            $this->filters = $filters;
        }
    }
若使用基於上下文的繫結，則可以提供一個閉包給 `give` 方法來解析這個依賴。該閉包應回傳解析好的 `Filter` 實體的陣列：

    $this->app->when(Firewall::class)
              ->needs(Filter::class)
              ->give(function (Application $app) {
                    return [
                        $app->make(NullFilter::class),
                        $app->make(ProfanityFilter::class),
                        $app->make(TooLongFilter::class),
                    ];
              });
為了方便起見，當 `Firewall` 需要 `Filter` 實體的時候，也可以只提供一個包含要給 Container 解析的類別名稱的陣列：

    $this->app->when(Firewall::class)
              ->needs(Filter::class)
              ->give([
                  NullFilter::class,
                  ProfanityFilter::class,
                  TooLongFilter::class,
              ]);
<a name="variadic-tag-dependencies"></a>

#### Variadic 參數的標籤依賴

有時候，某個類別可能會有型別提示為給定類別的 Variadic 參數 (`Report ...$reports`)。只要使用 `needs` 與 `giveTagged` 方法，就可以輕鬆將所有該[標籤](#tagging)的 Container 繫結注入進給定依賴：

    $this->app->when(ReportAggregator::class)
        ->needs(Report::class)
        ->giveTagged('reports');
<a name="tagging"></a>

### 標籤

有時候，可能會需要解析特定「類別」的繫結。舉例來說，若是在製作一個報表解析程式，會接收含有不同 `Report` 介面實作的陣列。註冊好 `Report` 實作後，可以使用 `tag` 方法來標記這些實作：

    $this->app->bind(CpuReport::class, function () {
        // ...
    });
    
    $this->app->bind(MemoryReport::class, function () {
        // ...
    });
    
    $this->app->tag([CpuReport::class, MemoryReport::class], 'reports');
標記好服務後就可以輕鬆地通過 Container 的 `tagged` 方法來解析所有的這些服務：

    $this->app->bind(ReportAnalyzer::class, function (Application $app) {
        return new ReportAnalyzer($app->tagged('reports'));
    });
<a name="extending-bindings"></a>

### 擴充繫結

`extend` 方法能修改解析過的服務。舉例來說，當某個服務被解析後，可以執行額外的程式碼來修改或設定這個服務。`extend` 方法接受兩個引數，第一個印數為要擴充的 Service 類別，以及一個回傳經過修改服務的閉包。該閉包會接收經過解析的服務，以及 Container 實體：

    $this->app->extend(Service::class, function (Service $service, Application $app) {
        return new DecoratedService($service);
    });
<a name="resolving"></a>

## 解析

<a name="the-make-method"></a>

### `make` 方法

可以使用 `make` 方法來從 Container 中解析一個類別實體。`make` 方法接收欲解析的類別或介面名稱：

    use App\Services\Transistor;
    
    $transistor = $this->app->make(Transistor::class);
若該類別的某些依賴無法被 Container 解析，則可能需要將這些依賴以關聯式陣列傳入 `makeWith` 方法內。舉例來說，可以手動傳入 `Transistor` 服務所需要的 `$id` 建構函式引數：

    use App\Services\Transistor;
    
    $transistor = $this->app->makeWith(Transistor::class, ['id' => 1]);
`bound` 方法可用來判斷某個類別或介面是否已被明顯繫結至 Container：

    if ($this->app->bound(Transistor::class)) {
        // ...
    }
若不在 Service Provider 內，而是在專案中某處無法存取 `$app` 變數的地方，可以使用 `App` [Facade](/docs/{{version}}/facades) 或 `app` [輔助函式](/docs/{{version}}/helpers#method-app) 來從 Container 內解析類別實體：

    use App\Services\Transistor;
    use Illuminate\Support\Facades\App;
    
    $transistor = App::make(Transistor::class);
    
    $transistor = app(Transistor::class);
若想將 Laravel Container 實體注入值 Container 正在解析的類別，則可以在該類別的建構函式中型別提示 `Illuminate\Container\Container` 類別：

    use Illuminate\Container\Container;
    
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected Container $container,
    ) {}
<a name="automatic-injection"></a>

### 自動注入

此外，還有一點很重要的是，也可以在會由 Container 解析的類別之建構函式內對依賴進行型別提示。這類類別包含 [Controller](/docs/{{version}}/controllers)、[事件處理程式](/docs/{{version}}/events)、[Middleware](/docs/%7B%7Bversion%7D/middleware) …等。此外，也可以在[佇列任務](/docs/{{version}}/queues)的 `handle` 方法內對依賴進行型別提示。實務上來說，這也是大多數由 Container 解析物件的方法。

For example, you may type-hint a service defined by your application in a controller's constructor. The service will automatically be resolved and injected into the class:

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Services\AppleMusic;
    
    class PodcastController extends Controller
    {
        /**
         * Create a new controller instance.
         */
        public function __construct(
            protected AppleMusic $apple,
        ) {}
    
        /**
         * Show information about the given podcast.
         */
        public function show(string $id): Podcast
        {
            return $this->apple->findPodcast($id);
        }
    }
<a name="method-invocation-and-injection"></a>

## Method Invocation and Injection

有時候我們可能會想在某個物件實體上叫用一個方法，並讓 Container 自動將該方法的相依內容注入進去。舉例來說，假設有下列類別：

    <?php
    
    namespace App;
    
    use App\Services\AppleMusic;
    
    class PodcastStats
    {
        /**
         * Generate a new podcast stats report.
         */
        public function generate(AppleMusic $apple): array
        {
            return [
                // ...
            ];
        }
    }
可以使用 Container 來像這樣叫用 `generate` 方法：

    use App\PodcastStats;
    use Illuminate\Support\Facades\App;
    
    $stats = App::call([new PodcastStats, 'generate']);
`call` 方法接受任意 PHP Callable。Container 的 `call` 方法也可以用來在叫用時自動注入其相依性：

    use App\Services\AppleMusic;
    use Illuminate\Support\Facades\App;
    
    $result = App::call(function (AppleMusic $apple) {
        // ...
    });
<a name="container-events"></a>

## Container 事件

Service Container 會在每次解析物件後觸發一個事件。可以通過 `resolving` 方法來監聽這個事件：

    use App\Services\Transistor;
    use Illuminate\Contracts\Foundation\Application;
    
    $this->app->resolving(Transistor::class, function (Transistor $transistor, Application $app) {
        // Called when container resolves objects of type "Transistor"...
    });
    
    $this->app->resolving(function (mixed $object, Application $app) {
        // Called when container resolves object of any type...
    });
如你所見，被解析的物件會被傳入該回呼內，讓你能在物件被交給要求者之前對物件設定額外的屬性。

<a name="rebinding"></a>

### Rebinding

The `rebinding` method allows you to listen for when a service is re-bound to the container, meaning it is registered again or overridden after its initial binding. This can be useful when you need to update dependencies or modify behavior each time a specific binding is updated:

    use App\Contracts\PodcastPublisher;
    use App\Services\SpotifyPublisher;
    use App\Services\TransistorPublisher;
    use Illuminate\Contracts\Foundation\Application;
    
    $this->app->bind(PodcastPublisher::class, SpotifyPublisher::class);
    
    $this->app->rebinding(
        PodcastPublisher::class,
        function (Application $app, PodcastPublisher $newInstance) {
            //
        },
    );
    
    // New binding will trigger rebinding closure...
    $this->app->bind(PodcastPublisher::class, TransistorPublisher::class);
<a name="psr-11"></a>

## PSR-11

Laravel 的 Service Container 實作了 [PSR-11](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md) 介面。因此，可以型別提示 PSR-11 Container 介面來取得 Laravel Container 的實體：

    use App\Services\Transistor;
    use Psr\Container\ContainerInterface;
    
    Route::get('/', function (ContainerInterface $container) {
        $service = $container->get(Transistor::class);
    
        // ...
    });
若給定的識別元無法被解析，則會擲回例外。若該識別元從未被繫結，則該例外為 `Psr\Container\NotFoundExceptionInterface` 的實體。若該識別元有被繫結過，但無法解析，則會擲回 `Psr\Container\ContainerExceptionInterface` 的實體。
