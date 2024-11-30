---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/121/en-zhtw'
updatedAt: '2024-06-30T08:15:00Z'
contributors: {  }
progress: 53.13
---

# Service Provider

- [簡介](#introduction)
- [撰寫 ServiceProvider](#writing-service-providers)
  - [Register 方法](#the-register-method)
  - [Boot 方法](#the-boot-method)
  
- [註冊 Provider](#registering-providers)
- [延遲的 Provider](#deferred-providers)

<a name="introduction"></a>

## 簡介

Service Provider 是 Laravel 中負責啟動應用程式的中心點。不過是你自己開發的應用程式，還是 Laravel 的核心服務，都是使用 Service Provider 啟動的。

不過，「啟動」是什麼意思呢？一般來說，我們指的是 **註冊** 一些東西，包含註冊 Service Container 的繫結、事件監聽常式、Middleware、甚至是 Route。Service Provider 是用來設定應用程式的中心點。

若打開 Laravel 中的 `config/app.php`，可以看到一個 `providers` 陣列。這個陣列中的就是你的程式會載入的所有 Service Provider。預設情況下，這個陣列中列出了一組 Laravel 的核心 Service Provider。這些 Provider 會啟動 Laravel 的核心元件，如：Mailer、佇列 (Queue)⋯⋯等。其中的許多 Provider 是屬於「延遲的」Provider，延遲的 Provider 是指：不是所有 Request 中都會載入這些 Provider，只有在我們有需要這些 Provider 提供的服務時才會載入。

在本篇概覽中，讀者將可以學習到如何撰寫你自己的 Service Provider，並將這些 Provider 註冊到 Laravel 專案上。

> [!NOTE]  
> 若想瞭解 Laravel 如何處理 Request 以及其內部如何運作，請參考我們有關 Laravel [Request 的生命週期](/docs/{{version}}/lifecycle)說明文件。

<a name="writing-service-providers"></a>

## 撰寫 Service Provider

所有的 Service Provider 都繼承自 `Illuminate\Support\ServiceProvider`。大多數的 Service Provider 都包含了 `registe` 與 `boot` 方法。`register` 方法 **只負責將事物繫結到 [Service Container](/docs/{{version}}/container)** 上。請絕對不要在 `register` 方法中註冊任何事件監聽常式、Route、或是任何其他的功能。

Artisan CLI 提供了一個 `make:provider` 指令來新增新 Provider：

```shell
php artisan make:provider RiakServiceProvider
```
<a name="the-register-method"></a>

### Register 方法

剛才也提到過，在 `register` 方法中應只能將東西註冊到 [Service Provider](/docs/{{version}}/container) 內。絕對不要嘗試在 `register` 方法內註冊事件監聽常式、Route、或其他任何功能。否則，我們可能會不小心使用到還沒載入的 Service Provider 提供的服務。

來看看一個基礎的 Service Provider。在 Service Provider 中的任何方法都可以存取一個 `$app` 屬性，該屬性可用來存取 Service Container：

    <?php
    
    namespace App\Providers;
    
    use App\Services\Riak\Connection;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Support\ServiceProvider;
    
    class RiakServiceProvider extends ServiceProvider
    {
        /**
         * Register any application services.
         */
        public function register(): void
        {
            $this->app->singleton(Connection::class, function (Application $app) {
                return new Connection(config('riak'));
            });
        }
    }
這個 Service Provider 只註冊了一個 `register` 方法，我們使用這個方法來向 Service Container 定義 `App\Services\Riak\Connection` 的實作。若你不熟悉 Laravel 的 Service Container，請參考 [Service Container 的說明文件](/docs/{{version}}/container)。

<a name="the-bindings-and-singletons-properties"></a>

#### The `bindings` and `singletons` Properties

若你的 Service Provider 會註冊很多的繫結，則可以使用 `bindings` 或 `singletons` 屬性，而不用手動註冊個別的 Container 繫結。Laravel 載入這個 Service Provider 後，會自動檢查這些屬性並註冊這些繫結：

    <?php
    
    namespace App\Providers;
    
    use App\Contracts\DowntimeNotifier;
    use App\Contracts\ServerProvider;
    use App\Services\DigitalOceanServerProvider;
    use App\Services\PingdomDowntimeNotifier;
    use App\Services\ServerToolsProvider;
    use Illuminate\Support\ServiceProvider;
    
    class AppServiceProvider extends ServiceProvider
    {
        /**
         * All of the container bindings that should be registered.
         *
         * @var array
         */
        public $bindings = [
            ServerProvider::class => DigitalOceanServerProvider::class,
        ];
    
        /**
         * All of the container singletons that should be registered.
         *
         * @var array
         */
        public $singletons = [
            DowntimeNotifier::class => PingdomDowntimeNotifier::class,
            ServerProvider::class => ServerToolsProvider::class,
        ];
    }
<a name="the-boot-method"></a>

### Boot 方法

那麼，若我們想在 Service Provider 內註冊 [View Composer] 該怎麼辦呢？我們可以在 `boot` 方法中註冊。**這個方法會在所有 Service Provider 都註冊好後才被呼叫**，這表示，我們就可以存取所有 Laravel 中已註冊好的服務：

    <?php
    
    namespace App\Providers;
    
    use Illuminate\Support\Facades\View;
    use Illuminate\Support\ServiceProvider;
    
    class ComposerServiceProvider extends ServiceProvider
    {
        /**
         * Bootstrap any application services.
         */
        public function boot(): void
        {
            View::composer('view', function () {
                // ...
            });
        }
    }
<a name="boot-method-dependency-injection"></a>

#### Boot 方法的相依性插入

在 Service Provider 中，若 `boot` 方法有相依性 (Dependency)，我們可以在該方法上做型別提示 (Type-Hint)。[Service Container](/docs/{{version}}/container) 會自動為你插入所有所需的相依性：

    use Illuminate\Contracts\Routing\ResponseFactory;
    
    /**
     * Bootstrap any application services.
     */
    public function boot(ResponseFactory $response): void
    {
        $response->macro('serialized', function (mixed $value) {
            // ...
        });
    }
<a name="registering-providers"></a>

## 註冊 Provider

`config/app.php` 中註冊了所有的 Provider。這個檔案中包含了一個 `providers` 陣列，我們可以在其中列出所有 Service Provider 的類別名稱。預設情況下，這個陣列中已經註冊了一組 Laravel 的核心 Service Provider。預設的這些 Provider 負責啟動 Laravel 的核心元件，如：Mailer、佇列、快取⋯⋯等。

若要註冊 Provider，請將其加到該陣列中：

    'providers' => ServiceProvider::defaultProviders()->merge([
        // Other Service Providers
    
        App\Providers\ComposerServiceProvider::class,
    ])->toArray(),
<a name="deferred-providers"></a>

## 延遲的 Provider

若 Provider **只有** 向 [Service Container](/docs/{{version}}/container) 註冊繫結，則可以選擇將其註冊過程延遲到真正有需要這些繫結時才註冊。由於我們就不需要每個 Request 都從檔案系統中載入這些 Provider，因此延遲載入這類 Provider 可以提升你程式的效能。

Laravel 會編譯並保存延遲的 Service Provider 名稱、以及其所提供的 Service 列表。接著，當有需要解析其中一個 Service 時，Laravel 就會載入這個 Service Provider：

若要延遲載入 Provider，請實作 `\Illuminate\Contracts\Support\DeferrableProvider` 介面，並定義 `provides` 方法。`provides` 方法應回傳該 Provider 中註冊的 Service Container 繫結：

    <?php
    
    namespace App\Providers;
    
    use App\Services\Riak\Connection;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\Support\DeferrableProvider;
    use Illuminate\Support\ServiceProvider;
    
    class RiakServiceProvider extends ServiceProvider implements DeferrableProvider
    {
        /**
         * Register any application services.
         */
        public function register(): void
        {
            $this->app->singleton(Connection::class, function (Application $app) {
                return new Connection($app['config']['riak']);
            });
        }
    
        /**
         * Get the services provided by the provider.
         *
         * @return array<int, string>
         */
        public function provides(): array
        {
            return [Connection::class];
        }
    }