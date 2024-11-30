---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/153/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 51.81
---

# HTTP Session

- [簡介](#introduction)
  - [設定](#configuration)
  - [Driver 前置需求](#driver-prerequisites)
  
- [使用 Session](#interacting-with-the-session)
  - [取得資料](#retrieving-data)
  - [保存資料](#storing-data)
  - [快閃資料](#flash-data)
  - [刪除資料](#deleting-data)
  - [重新產生 Session ID](#regenerating-the-session-id)
  
- [Session 鎖定](#session-blocking)
- [新增自訂的 Session Driver](#adding-custom-session-drivers)
  - [實作 Driver](#implementing-the-driver)
  - [註冊 Driver](#registering-the-driver)
  

<a name="introduction"></a>

## 簡介

由於使用 HTTP 的應用程式是無狀態的 (Stateless)，因此 Session 提供了能在多個 Request 間儲存有關使用者資訊的方法。這個使用者資訊通常儲存於持續性存放空間 (Persistent Store) 或後端中，能讓我們在之後的 Request 中存取。

Laravel 隨附了多種 Session 後端，能讓我們使用直觀且同一的 API 來存取 Session。支援的後端包含常見的 [Memcached](https://memcached.org)、[Redis](https://redis.io)、與資料庫。

<a name="configuration"></a>

### 設定

專案的 Session 設定檔存在 `config/session.php` 中。建議先閱讀該檔案了解一下有哪些可用的選項。預設情況下，Laravel 設定使用 `file` Session Driver，對於大多數的專案來說，都可以使用這個 Driver。若你的網站會在多個 Web Server (網頁伺服器) 間做 Load Balance (負載平衡)，那我們就會需要選擇一種集中式的存放方案，如 Redis 或資料庫。

Session 的 `driver` 設定定義了每個 Request 的 Session 資料要存在哪裡。Laravel 隨附了多個不錯的 Driver：

<div class="content-list" markdown="1">
- `file` - Session 儲存在 `storage/framework/sessions`。
- `cookie` - Session 儲存在安全的加密 Cookie 中。
- `database` - Session 儲存在關聯式資料庫中。
- `memcached` / `redis` - Session 儲存在其中一個快速、基於快取的存放空間中。
- `dynamodb` - Session 儲存在 AWS DynamoDB。
- `array` - Session 儲存在 PHP 陣列中，且不會被^[持續保存](Persist)。

</div>
> [!TIP]  
> Array Driver 主要是用在[測試](/docs/{{version}}/testing)上的，會讓保存在 Session 裡的資料不被持續保存。

<a name="driver-prerequisites"></a>

### Driver 前置需求

<a name="database"></a>

#### Database

使用 `database` Session Driver 時，需要先建立用來保存 Session 紀錄的資料表。下列是一個 Session 紀錄資料表的 `Schema` 定義範例：

    Schema::create('sessions', function ($table) {
        $table->string('id')->primary();
        $table->foreignId('user_id')->nullable()->index();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->text('payload');
        $table->integer('last_activity')->index();
    });
可以使用 `session:table` Artisan 指令來產生這個 Migration。若要瞭解更多資料庫 Migration 的資訊，請參考完整的 [Migration 說明文件](/docs/{{version}}/migrations)：

    php artisan session:table
    
    php artisan migrate
<a name="redis"></a>

#### Redis

在 Laravel 上使用 Redis Session 前，必須先使用 PECL 安裝 PhpRedis PHP 擴充程式，或是使用 Composer 安裝 `predis/predis` 套件 (~1.0)。更多有關設定 Redis 的資訊，請參考 Laravel 的 [Redis 說明文件](/docs/{{version}}/redis#configuration)。

> [!TIP]  
> 在 `session` 設定檔中，可使用 `connection` 選項來指定 Session 要使用哪個 Redis 連線。

<a name="interacting-with-the-session"></a>

## 使用 Session

<a name="retrieving-data"></a>

### 取得資料

在 Laravel 中有兩種使用 Session 的方式：全域 `session` 輔助函式，或是 `Request` 實體。首先，我們先來看看如何使用 `Request` 實體來存取 Session。可以在 Route 閉包或 Controller 方法上型別提示 (Type-Hint) Request。請記住，Controller 方法的相依性會自動由 Laravel 的 [Service Container](/docs/{{version}}/container) 插入：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    
    class UserController extends Controller
    {
        /**
         * Show the profile for the given user.
         *
         * @param  Request  $request
         * @param  int  $id
         * @return Response
         */
        public function show(Request $request, $id)
        {
            $value = $request->session()->get('key');
    
            //
        }
    }
從 Session 中取得資料時，也可以傳入一個預設值作為第二個引數給 `get` 方法。當 Session 中沒有指定的索引鍵時，就會回傳該索引值。若將閉包傳入作為預設值給 `get`，且要求的索引鍵不存在時，就會執行該閉包並回傳執行的結果：

    $value = $request->session()->get('key', 'default');
    
    $value = $request->session()->get('key', function () {
        return 'default';
    });
<a name="the-global-session-helper"></a>

#### 全域 Session 輔助函式

也可以使用全域的 `session` PHP 函式來從 Session 中取得或儲存資料。呼叫 `session` 輔助函式時若只提供一個字串參數，則會回傳該 Session 索引鍵的值。呼叫 `session` 輔助函式時若提供一組索引鍵 / 值配對的陣列，則會將該陣列的值儲存在 Session 中：

    Route::get('/home', function () {
        // Retrieve a piece of data from the session...
        $value = session('key');
    
        // Specifying a default value...
        $value = session('key', 'default');
    
        // Store a piece of data in the session...
        session(['key' => 'value']);
    });
> [!TIP]  
> 使用 HTTP Request 實體跟全域 `session` 輔助函式在實務上沒有太大的不同。不管是哪種方式都是[可測試的](/docs/{{version}}/testing)，測試時可以使用測試例中的 `assertSessionHas` 方法來測試。

<a name="retrieving-all-session-data"></a>

#### 取得所有 Session 資料

若想從 Session 中取得所有資料，可以使用 `all` 方法：

    $data = $request->session()->all();
<a name="determining-if-an-item-exists-in-the-session"></a>

#### 判斷 Session 中某個項目是否存在

若要判斷 Session 中是否有某個項目，可使用 `has` 方法。`has` 方法會在該項目存在且不為 `null` 時回傳 `true`：

    if ($request->session()->has('users')) {
        //
    }
若想判斷某個項目是否存在 Session，且不論其值是否為 `null`，可使用 `exists` 方法：

    if ($request->session()->exists('users')) {
        //
    }
若要判斷 Session 中是否沒有某個項目，可使用 `missing` 方法。`missing` 方法會在該項目為 `null` 或不存在時回傳 `true`：

    if ($request->session()->missing('users')) {
        //
    }
<a name="storing-data"></a>

### 保存資料

若要將資料保存到 Session，我們通常會使用 Request 實體的 `put` 方法或全域的 `session` 輔助函式：

    // Via a request instance...
    $request->session()->put('key', 'value');
    
    // Via the global "session" helper...
    session(['key' => 'value']);
<a name="pushing-to-array-session-values"></a>

#### 在 Session 值中推入陣列資料

可以使用 `push` 方法來將值推入 (Push) 到陣列的 Session 值中。舉例來說，若 `user.teams` 索引鍵中包含了一組團隊名稱陣列，我們可以像這樣將一個新的值推入陣列中：

    $request->session()->push('user.teams', 'developers');
<a name="retrieving-deleting-an-item"></a>

#### 取得與刪除項目

使用 `pull` 方法即可以單一陳述式從 Session 內取得並刪除某個項目：

    $value = $request->session()->pull('key', 'default');
<a name="incrementing-and-decrementing-session-values"></a>

#### 遞增或遞減 Session 值

若 Session 資料中包含要遞增或遞減的整數，可以使用 `increment` (遞增) 與 `decrement` (遞減) 方法：

    $request->session()->increment('count');
    
    $request->session()->increment('count', $incrementBy = 2);
    
    $request->session()->decrement('count');
    
    $request->session()->decrement('count', $decrementBy = 2);
<a name="flash-data"></a>

### 快閃資料

有時候，我們可能會想保存一些資料在 Session 中以供下一個 Request 使用。為此，我們可以使用 `flash` 方法。使用這個方法儲存在 Session 中的資料會在緊接著這個 Request 的下一個 HTTP Request 中可用。在下一個 HTTP Request 執行完成後，快閃資料就會被刪掉。快閃資料特別適合用於生命週期短的 (Short-Lived) 狀態訊息：

    $request->session()->flash('status', 'Task was successful!');
若想將快閃資料維持在好幾個 Request 中，可使用 `reflash` 方法。該方法會將所有的快閃資料都再維持一個 Request。若有需要保存特定的快閃資料，可使用 `keep` 方法：

    $request->session()->reflash();
    
    $request->session()->keep(['username', 'email']);
若只想在目前 Request 中維持快閃資料，可使用 `now` 方法：

    $request->session()->now('status', 'Task was successful!');
<a name="deleting-data"></a>

### 刪除資料

使用 `forget` 方法可從 Session 中刪除一筆資料。若想移除 Session 中的所有資料，可使用 `flush` 方法：

    // Forget a single key...
    $request->session()->forget('name');
    
    // Forget multiple keys...
    $request->session()->forget(['name', 'status']);
    
    $request->session()->flush();
<a name="regenerating-the-session-id"></a>

### 重新產生 Session ID

一般來說，重新產生 Session ID 是為了防止惡意使用者利用 [Session Fixation](https://owasp.org/www-community/attacks/Session_fixation) 弱點攻擊你的程式。

如果你使用其中一種 Laravel 的[專案入門套件](/docs/{{version}}/starter-kits)，或是 [Laravel Fortify](/docs/{{version}}/fortify)，則 Laravel 會在登入時自動重新產生 Session ID。不過，若有需要手動重新產生 Session ID，可使用 `regenerate` 方法：

    $request->session()->regenerate();
若有需要以單一陳述式重新產生 Session ID 並從 Session 中移除所有資料的話，可使用 `invalidate` 方法：

    $request->session()->invalidate();
<a name="session-blocking"></a>

## Session 封鎖

> [!NOTE]  
> 若要使用 Session 鎖定，必須要使用支援 [Atomic Lock](/docs/{{version}}/cache#atomic-locks) (不可部分完成鎖定) 的快取 Driver。目前，支援 Atomic Lock 的快取 Driver 有 `memcached`、`dynamodb`、`redis`、`database` 等 Driver。此外，也沒辦法使用 `cookie` Session Driver。

預設情況下，Laravel 能讓多個 Request 使用相同的 Session 來同步執行。不過，舉例來說，若我們使用某個 JavaScript HTTP 函式庫來建立兩個連到我們專案的 HTTP Request，且這兩個 Request 會同時執行。對於大多數的專案來說，這不會有什麼問題。不過，對一部分的專案，如果這兩個 Request 送往兩個不同的 Endpoint (端點)，且這兩個 Endpoint 都有寫入資料到 Session 的話，就有可能會發生 Session 資料遺失的問題。

為了解決這個問題，Laravel 提供了能讓我們針對給定 Session 限制同步 Request 數量的功能。要開始使用 Session 封鎖，我們只需要在 Route 定義後方串上 `block` 方法即可。在這個例子中，所有連入到 `/profile` Endpoint 的 Request 都會取得一個 Session Lock (鎖定)。當被 Lock 時，所有連到 `/profile` 或 `/order` Endpoint 的 Request 若有相同的 Session ID，都必須等到第一個 Request 執行完成後，才能繼續執行：

    Route::post('/profile', function () {
        //
    })->block($lockSeconds = 10, $waitSeconds = 10)
    
    Route::post('/order', function () {
        //
    })->block($lockSeconds = 10, $waitSeconds = 10)
`block` 方法接受兩個可選的引數。`block` 方法的第一個引數 Session 要被 Lock 的最大秒數。當然，若 Request 比這個時間還要早完成執行的話，也會提早釋放 Lock：

`block` 方法的第二個引數是 Request 在取得 Session Lock 前應等待的秒數。若在給定的秒數後 Request 仍然無法取得 Session Lock 的話，會擲回 `Illuminate\Contracts\Cache\LockTimeoutException`。

若沒有提供這些引數，則 Lock 最長可取得 10 秒，而 Request 在取得 Lock 時最多可等待 10 秒：

    Route::post('/profile', function () {
        //
    })->block()
<a name="adding-custom-session-drivers"></a>

## 新增自訂 Session Driver

<a name="implementing-the-driver"></a>

#### 實作 Driver

若現有的 Session Driver 都無法滿足你的專案需求，在 Laravel 中也可以撰寫你自己的 Session 處理常式 (Handler)。自訂 Session Driver 應實作 PHP 內建的 `SessionHandlerInterface`。這個介面只包含了幾個簡單的方法。MongoDB 實作的 Stub (虛設常式) 看起來會像這樣：

    <?php
    
    namespace App\Extensions;
    
    class MongoSessionHandler implements \SessionHandlerInterface
    {
        public function open($savePath, $sessionName) {}
        public function close() {}
        public function read($sessionId) {}
        public function write($sessionId, $data) {}
        public function destroy($sessionId) {}
        public function gc($lifetime) {}
    }
> [!TIP]  
> Laravel 中沒有內建用來放置擴充程式的目錄。你可以自由放置這些擴充程式。在這個例子中，我們建立了一個 `Extensions` 目錄來放置 `MongoSessionHandler`。

由於只看這些方法很難看出他們的功能，所以我們來快速看一下各個方法都用來做什麼：

<div class="content-list" markdown="1">
- `open` 方法通常是給一些基於檔案的 Session 存放系統使用的。因為 Laravel 已經有附帶 `file` Session Driver 了，所以通常這個方法裡應該不需要寫什麼內容。留空即可。
- `close` 方法跟 `open` 方法一樣，通常可以忽略。對大多數的 Driver 來說並不需要。
- `read` 方法應回傳與給定 `$sessionId` 關聯的字串版本 Session 資料。在從 Driver 中取出資料時不需要進行任何的^[序列化](Serialization) 或其他編碼，因為 Laravel 會幫你序列化。
- The `write` method should write the given `$data` string associated with the `$sessionId` to some persistent storage system, such as MongoDB or another storage system of your choice.  Again, you should not perform any serialization - Laravel will have already handled that for you.
- `destroy` 方法從持續性儲存系統中移除任何與 `$sessionId` 關聯的資料。
- `gc` 方法移除所有時間舊於 `$lifetime` 的 `Session` 資料。`$lifetime` 是 UNIX 時戳。對於^[自帶有效期限](Self-Expiring)的系統，如 Memcached 或 Redis，可以將這個方法留空。

</div>
<a name="registering-the-driver"></a>

#### 註冊 Driver

實作好 Driver 後，就可以將該 Driver 註冊到 Laravel。若要將額外的 Driver 新增到 Laravel 的 Session 後端中，我們可以使用 `Session` [Facade](/docs/{{version}}/facades) 的 `extend` 方法。可以在某個 [Service Provider](/docs/{{version}}/providers) 中呼叫這個 `extend` 方法。可以使用現有的 `App\Providers\AppServiceProvider`，或是建立一個全新的 Provider：

    <?php
    
    namespace App\Providers;
    
    use App\Extensions\MongoSessionHandler;
    use Illuminate\Support\Facades\Session;
    use Illuminate\Support\ServiceProvider;
    
    class SessionServiceProvider extends ServiceProvider
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
            Session::extend('mongo', function ($app) {
                // Return an implementation of SessionHandlerInterface...
                return new MongoSessionHandler;
            });
        }
    }
註冊好 Session Driver 後，就可以在 `config/session.php` 設定檔中使用 `mongo` Driver。
