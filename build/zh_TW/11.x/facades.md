---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/71/en-zhtw'
updatedAt: '2024-06-30T08:26:00Z'
contributors: {  }
progress: 80.27
---

# Facade

- [簡介](#introduction)
- [When to Utilize Facades](#when-to-use-facades)
  - [Facades vs. Dependency Injection](#facades-vs-dependency-injection)
  - [Facades vs. Helper Functions](#facades-vs-helper-functions)
  
- [Facade 是怎麼運作的](#how-facades-work)
- [即時 Facade](#real-time-facades)
- [Facade 類別參照](#facade-class-reference)

<a name="introduction"></a>

## 簡介

在 Laravel 的說明文件中，我們可以看到範例程式碼都使用「Facade」來操作 Laravel 的功能。Facade 提供了一個「靜態 (Static)」介面來存取 [Service Container](/docs/{{version}}/container) 中提供的類別。Laravel 隨附了許多 Facade，幾乎可以存取所有的 Laravel 功能。

Laravel Facade 是一個用來存取 Service Container 中類別的一個「靜態代理 (Static Proxy)」，讓我們能使用簡潔、語意化的語法，卻由不像傳統靜態方法一樣要犧牲可測試性與靈活性。若你還不了解 Facade 如何運作，完全沒關係 —— 只要繼續使用並持續學習 Laravel 就好。

Laravel 中所有的 Facade 都定義在 `Illuminate\Support\Facades` Namespace 下。因此，我們可以像這樣輕鬆地存取 Facade：

    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\Route;
    
    Route::get('/cache', function () {
        return Cache::get('key');
    });
在 Laravel 說明文件中，有許多的範例都使用 Facade 來示範 Laravel 的許多功能：

<a name="helper-functions"></a>

#### 輔助函式

為了能與 Facade 互補，Laravel 還提供了一系列的全域「輔助函式」，能讓你更輕鬆的使用常見的 Laravel 功能。其中，你可能常使用到的輔助函式包含：`view`、`response`、`url`、`config`⋯⋯等。Laravel 中提供的每個輔助函式都有說明文件來說明其功能。關於輔助函式完整的列表列在專門的[輔助函式說明文件](/docs/{{version}}/helpers)。

舉例來說，除了使用 `Illuminate\Support\Facades\Response` Facade 來產生 JSON 回應以外，我們也可以使用 `response` 韓式。由於輔助函式在全域都可使用，因此我們不需要特別 Import 任何函式就可以使用：

    use Illuminate\Support\Facades\Response;
    
    Route::get('/users', function () {
        return Response::json([
            // ...
        ]);
    });
    
    Route::get('/users', function () {
        return response()->json([
            // ...
        ]);
    });
<a name="when-to-use-facades"></a>

## When to Utilize Facades

Facade 提供了許多的好處。Facade 提供了簡介、好記憶的語法，能讓你不需記著長長的類別名稱、不需手動插入或設定類別，就能使用 Laravel 的功能。此外，由於 Facade 使用了獨特的 PHP 動態方法，因此要測試 Facade 也很簡單。

不過，在使用 Facade 的時候有幾點需要注意。第一個要注意的點是，Facade 是類別的「作用範圍陷阱 (Scope Creep)」。由於 Facade 很容易使用，而且不需要做相依性插入，所以我們很容易讓類別不斷增長、並在單一類別中使用太多的 Facade。在使用相依性插入時，這種問題很容易一眼看出，因為我們看到類別的 Constructor (建構函式) 就知道類別太肥大了。因此，在使用 Facade 時，請特別注意類別的大小，讓類別的功能範圍保持專一。若類別變的太大，請考慮將其拆分為多個小類別。

<a name="facades-vs-dependency-injection"></a>

### Facades vs. Dependency Injection

使用相依性插入的主要好處就是我們能替換掉要插入類別的實作。在測試時這點特別適用，因為這樣我們就能插入 Mock (模擬) 或 Stub (虛設常式)，並在 Stub 上檢查各種方法是否真的有被呼叫。

一般來說，對真正的靜態類別方法來說，我們是不可能去 Mock 或 Stub 的。不過，因為 Facade 使用動態方法來代理這些方法呼叫到 Service Container 解析的物件上，因此我們就可以測試這些 Facade，就像我們在測試插入的類別實體一樣。舉例來說，假設有下列 Route：

    use Illuminate\Support\Facades\Cache;
    
    Route::get('/cache', function () {
        return Cache::get('key');
    });
使用 Laravel 的 Facade 測試方法，我們就能撰寫下列測試，並驗證 `Cache::get` 方法是否有使用預期的引數呼叫：

```php
use Illuminate\Support\Facades\Cache;

test('basic example', function () {
    Cache::shouldReceive('get')
         ->with('key')
         ->andReturn('value');

    $response = $this->get('/cache');

    $response->assertSee('value');
});
```
```php
use Illuminate\Support\Facades\Cache;

/**
 * A basic functional test example.
 */
public function test_basic_example(): void
{
    Cache::shouldReceive('get')
         ->with('key')
         ->andReturn('value');

    $response = $this->get('/cache');

    $response->assertSee('value');
}
```
<a name="facades-vs-helper-functions"></a>

### Facades vs. Helper Functions

除了 Facade 外，Laravel 也提供了多個「輔助」函式，可用來處理像是產生 View、觸發事件、分派任務、送出 HTTP Response⋯⋯等常見的工作，其中許多的輔助函式都與對應的 Facade 提供相同的功能。舉例來說，下列的 Facade 呼叫與輔助函式的呼叫是相同的：

    return Illuminate\Support\Facades\View::make('profile');
    
    return view('profile');
在實務上，使用 Facade 方法與輔助函式並沒有不同，使用輔助函式時，我們還是可以像對 Facade 一樣測試這些功能。舉例來說，假設有下列 Route：

    Route::get('/cache', function () {
        return cache('key');
    });
在 Laravel 中，`cache` 輔助函式會去呼叫 `Cache` Facade 底層類別的 `get` 方法。因此，雖然我們在使用的是輔助函式，但我們可以撰寫下列這樣的測試來驗證該方法是否有用我們給定的引數呼叫：

    use Illuminate\Support\Facades\Cache;
    
    /**
     * A basic functional test example.
     */
    public function test_basic_example(): void
    {
        Cache::shouldReceive('get')
             ->with('key')
             ->andReturn('value');
    
        $response = $this->get('/cache');
    
        $response->assertSee('value');
    }
<a name="how-facades-work"></a>

## Facade 是如何運作的？

在 Laravel 程式中，Facade 能讓我們從 Container 內存取物件。為什麼我們能這麼做？答案就藏在 `Facade` 類別中。Laravel 的 Facade 與你自己建立的自訂 Facade 都繼承一個基礎的 `Illuminate\Support\Facades\Facade` 類別。

`Facade ` 的基礎類別使用 `__callStatic()` 魔法方法來將所有對 Facade 的呼叫轉移到從 Container 中解析出來的物件上。在上面的例子中，我們呼叫了 Laravel 快取系統。看一眼這個程式碼，有人可能會假設我們是在呼叫 `Cache` 類別的靜態 `get` 方法：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\View\View;
    
    class UserController extends Controller
    {
        /**
         * Show the profile for the given user.
         */
        public function showProfile(string $id): View
        {
            $user = Cache::get('user:'.$id);
    
            return view('profile', ['user' => $user]);
        }
    }
可以注意到，在檔案最上端，我們「Import」了 `Cache` Facade。這個 Facade 會作為代理來讓我們存取底層 `Illuminate\Contracts\Cache\Factory` 介面的實作。使用 Facade 呼叫的所有方法都會被傳到 Laravel 快取系統的底層實體上。

若我們打開 `Illuminate\Support\Facades\Cache` 類別看，會發現裡面沒有靜態的 `get` 方法：

    class Cache extends Facade
    {
        /**
         * Get the registered name of the component.
         */
        protected static function getFacadeAccessor(): string
        {
            return 'cache';
        }
    }
沒有 `get` 方法，`Cache` Facade 只有繼承了基礎的 `Facade` 類別並定義了 `getFacadeAccessor()` 方法。這個方法的功能就是用來回傳 Service Container 繫結的名稱。當使用者在 `Cache` Facade 上參照任何靜態方法時，Laravel 會去從 [Service Container](/docs/{{version}}/container) 中解析出 `cache` 繫結，然後在這個物件上執行要求的方法 (在這個例子中就是 `get`)。

<a name="real-time-facades"></a>

## 即時 Facade

使用即時 Facade 時，我們可以把程式內的任何類別都當作 Facade 來使用。為了說明這個功能，我們先來看一些不使用即時 Facade 的例子。舉例來說，我們先假設 `Podcast` Model 有個 `publish` 方法。不過，為了要發布 (Publish) 這個 Podcast，我們還需要插入 `Publisher` 實體：

    <?php
    
    namespace App\Models;
    
    use App\Contracts\Publisher;
    use Illuminate\Database\Eloquent\Model;
    
    class Podcast extends Model
    {
        /**
         * Publish the podcast.
         */
        public function publish(Publisher $publisher): void
        {
            $this->update(['publishing' => now()]);
    
            $publisher->publish($this);
        }
    }
將 Publisher 實作插入到這個方法後，只要 Mock 這個插入的 Publisher，我們就能輕鬆地在分離的狀態下測試這個方法。不過，這樣一來每次我們呼叫 `publish` 方法也都需要傳入一個 Publisher 實體。使用即時 Facade，我們一樣可以能保有可測試性，又不需要顯式傳入 `Publisher` 實體。若要產生即時 Facade，請在 Import 類別的 Namespace 前方加上 `Facades`：

    <?php
    
    namespace App\Models;
    
    use App\Contracts\Publisher; // [tl! remove]
    use Facades\App\Contracts\Publisher; // [tl! add]
    use Illuminate\Database\Eloquent\Model;
    
    class Podcast extends Model
    {
        /**
         * Publish the podcast.
         */
        public function publish(Publisher $publisher): void // [tl! remove]
        public function publish(): void // [tl! add]
        {
            $this->update(['publishing' => now()]);
    
            $publisher->publish($this); // [tl! remove]
            Publisher::publish($this); // [tl! add]
        }
    }
在使用即時 Facade 時，Laravel 會使用 `Facades` 前置詞後方的介面或類別名稱來從 Service Container 上解析出 Publisher 實作。測試時，我們可以使用 Laravel 的內建 Facade 測試工具來 Mock 這個方法呼叫：

```php
<?php

use App\Models\Podcast;
use Facades\App\Contracts\Publisher;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('podcast can be published', function () {
    $podcast = Podcast::factory()->create();

    Publisher::shouldReceive('publish')->once()->with($podcast);

    $podcast->publish();
});
```
```php
<?php

namespace Tests\Feature;

use App\Models\Podcast;
use Facades\App\Contracts\Publisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PodcastTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A test example.
     */
    public function test_podcast_can_be_published(): void
    {
        $podcast = Podcast::factory()->create();

        Publisher::shouldReceive('publish')->once()->with($podcast);

        $podcast->publish();
    }
}
```
<a name="facade-class-reference"></a>

## Facade 類別參照

在下表中，讀者可以找到所有的 Facade 與其底層的類別。對於像在 API 說明文件中找到某個 Facade 來源的時候，下表是很實用的工具。若有 [Service container 的繫結](/docs/{{version}}/container)索引鍵時，下表中也會列出。

<div class="overflow-auto">
| Facade | 類別 | Service Container 的繫結 |
| --- | --- | --- |
| App | [Illuminate\Foundation\Application](https://laravel.com/api/{{version}}/Illuminate/Foundation/Application.html) | `app` |
| Artisan | [Illuminate\Contracts\Console\Kernel](https://laravel.com/api/{{version}}/Illuminate/Contracts/Console/Kernel.html) | `artisan` |
| Auth (實體) | [Illuminate\Contracts\Auth\Guard](https://laravel.com/api/{{version}}/Illuminate/Contracts/Auth/Guard.html) | `auth.driver` |
| Auth | [Illuminate\Auth\AuthManager](https://laravel.com/api/{{version}}/Illuminate/Auth/AuthManager.html) | `auth` |
| Blade | [Illuminate\View\Compilers\BladeCompiler](https://laravel.com/api/{{version}}/Illuminate/View/Compilers/BladeCompiler.html) | `blade.compiler` |
| Broadcast (實體) | [Illuminate\Contracts\Broadcasting\Broadcaster](https://laravel.com/api/{{version}}/Illuminate/Contracts/Broadcasting/Broadcaster.html) |   |
| Broadcast | [Illuminate\Contracts\Broadcasting\Factory](https://laravel.com/api/{{version}}/Illuminate/Contracts/Broadcasting/Factory.html) |   |
| Bus | [Illuminate\Contracts\Bus\Dispatcher](https://laravel.com/api/{{version}}/Illuminate/Contracts/Bus/Dispatcher.html) |   |
| Cache (實體) | [Illuminate\Cache\Repository](https://laravel.com/api/{{version}}/Illuminate/Cache/Repository.html) | `cache.store` |
| Cache | [Illuminate\Cache\CacheManager](https://laravel.com/api/{{version}}/Illuminate/Cache/CacheManager.html) | `cache` |
| Config | [Illuminate\Config\Repository](https://laravel.com/api/{{version}}/Illuminate/Config/Repository.html) | `config` |
| Context | [Illuminate\Log\Context\Repository](https://laravel.com/api/{{version}}/Illuminate/Log/Context/Repository.html) |   |
| Cookie | [Illuminate\Cookie\CookieJar](https://laravel.com/api/{{version}}/Illuminate/Cookie/CookieJar.html) | `cookie` |
| Crypt | [Illuminate\Encryption\Encrypter](https://laravel.com/api/{{version}}/Illuminate/Encryption/Encrypter.html) | `encrypter` |
| Date | [Illuminate\Support\DateFactory](https://laravel.com/api/{{version}}/Illuminate/Support/DateFactory.html) | `date` |
| DB (實體) | [Illuminate\Database\Connection](https://laravel.com/api/{{version}}/Illuminate/Database/Connection.html) | `db.connection` |
| DB | [Illuminate\Database\DatabaseManager](https://laravel.com/api/{{version}}/Illuminate/Database/DatabaseManager.html) | `db` |
| Event | [Illuminate\Events\Dispatcher](https://laravel.com/api/{{version}}/Illuminate/Events/Dispatcher.html) | `events` |
| Exceptions (Instance) | [Illuminate\Contracts\Debug\ExceptionHandler](https://laravel.com/api/{{version}}/Illuminate/Contracts/Debug/ExceptionHandler.html) |   |
| Exceptions | [Illuminate\Foundation\Exceptions\Handler](https://laravel.com/api/{{version}}/Illuminate/Foundation/Exceptions/Handler.html) |   |
| File | [Illuminate\Filesystem\Filesystem](https://laravel.com/api/{{version}}/Illuminate/Filesystem/Filesystem.html) | `files` |
| Gate | [Illuminate\Contracts\Auth\Access\Gate](https://laravel.com/api/{{version}}/Illuminate/Contracts/Auth/Access/Gate.html) |   |
| Hash | [Illuminate\Contracts\Hashing\Hasher](https://laravel.com/api/{{version}}/Illuminate/Contracts/Hashing/Hasher.html) | `hash` |
| Http | [Illuminate\Http\Client\Factory](https://laravel.com/api/{{version}}/Illuminate/Http/Client/Factory.html) |   |
| Lang | [Illuminate\Translation\Translator](https://laravel.com/api/{{version}}/Illuminate/Translation/Translator.html) | `translator` |
| Log | [Illuminate\Log\LogManager](https://laravel.com/api/{{version}}/Illuminate/Log/LogManager.html) | `log` |
| Mail | [Illuminate\Mail\Mailer](https://laravel.com/api/{{version}}/Illuminate/Mail/Mailer.html) | `mailer` |
| Notification | [Illuminate\Notifications\ChannelManager](https://laravel.com/api/{{version}}/Illuminate/Notifications/ChannelManager.html) |   |
| Password (實體) | [Illuminate\Auth\Passwords\PasswordBroker](https://laravel.com/api/{{version}}/Illuminate/Auth/Passwords/PasswordBroker.html) | `auth.password.broker` |
| Password | [Illuminate\Auth\Passwords\PasswordBrokerManager](https://laravel.com/api/{{version}}/Illuminate/Auth/Passwords/PasswordBrokerManager.html) | `auth.password` |
| Pipeline (實體) | [Illuminate\Pipeline\Pipeline](https://laravel.com/api/{{version}}/Illuminate/Pipeline/Pipeline.html) |   |
| Process | [Illuminate\Process\Factory](https://laravel.com/api/{{version}}/Illuminate/Process/Factory.html) |   |
| Queue (基礎類別) | [Illuminate\Queue\Queue](https://laravel.com/api/{{version}}/Illuminate/Queue/Queue.html) |   |
| Queue (實體) | [Illuminate\Contracts\Queue\Queue](https://laravel.com/api/{{version}}/Illuminate/Contracts/Queue/Queue.html) | `queue.connection` |
| Queue | [Illuminate\Queue\QueueManager](https://laravel.com/api/{{version}}/Illuminate/Queue/QueueManager.html) | `queue` |
| RateLimiter | [Illuminate\Cache\RateLimiter](https://laravel.com/api/{{version}}/Illuminate/Cache/RateLimiter.html) |   |
| Redirect | [Illuminate\Routing\Redirector](https://laravel.com/api/{{version}}/Illuminate/Routing/Redirector.html) | `redirect` |
| Redis (實體) | [Illuminate\Redis\Connections\Connection](https://laravel.com/api/{{version}}/Illuminate/Redis/Connections/Connection.html) | `redis.connection` |
| Redis | [Illuminate\Redis\RedisManager](https://laravel.com/api/{{version}}/Illuminate/Redis/RedisManager.html) | `redis` |
| Request | [Illuminate\Http\Request](https://laravel.com/api/{{version}}/Illuminate/Http/Request.html) | `request` |
| Response (實體) | [Illuminate\Http\Response](https://laravel.com/api/{{version}}/Illuminate/Http/Response.html) |   |
| Response | [Illuminate\Contracts\Routing\ResponseFactory](https://laravel.com/api/{{version}}/Illuminate/Contracts/Routing/ResponseFactory.html) |   |
| Route | [Illuminate\Routing\Router](https://laravel.com/api/{{version}}/Illuminate/Routing/Router.html) | `router` |
| Schedule | [Illuminate\Console\Scheduling\Schedule](https://laravel.com/api/{{version}}/Illuminate/Console/Scheduling/Schedule.html) |   |
| Schema | [Illuminate\Database\Schema\Builder](https://laravel.com/api/{{version}}/Illuminate/Database/Schema/Builder.html) |   |
| Session (實體) | [Illuminate\Session\Store](https://laravel.com/api/{{version}}/Illuminate/Session/Store.html) | `session.store` |
| Session | [Illuminate\Session\SessionManager](https://laravel.com/api/{{version}}/Illuminate/Session/SessionManager.html) | `session` |
| Storage (實體) | [Illuminate\Contracts\Filesystem\Filesystem](https://laravel.com/api/{{version}}/Illuminate/Contracts/Filesystem/Filesystem.html) | `filesystem.disk` |
| Storage | [Illuminate\Filesystem\FilesystemManager](https://laravel.com/api/{{version}}/Illuminate/Filesystem/FilesystemManager.html) | `filesystem` |
| URL | [Illuminate\Routing\UrlGenerator](https://laravel.com/api/{{version}}/Illuminate/Routing/UrlGenerator.html) | `url` |
| Validator (實體) | [Illuminate\Validation\Validator](https://laravel.com/api/{{version}}/Illuminate/Validation/Validator.html) |   |
| Validator | [Illuminate\Validation\Factory](https://laravel.com/api/{{version}}/Illuminate/Validation/Factory.html) | `validator` |
| View (實體) | [Illuminate\View\View](https://laravel.com/api/{{version}}/Illuminate/View/View.html) |   |
| View | [Illuminate\View\Factory](https://laravel.com/api/{{version}}/Illuminate/View/Factory.html) | `view` |
| Vite | [Illuminate\Foundation\Vite](https://laravel.com/api/{{version}}/Illuminate/Foundation/Vite.html) |   |

</div>