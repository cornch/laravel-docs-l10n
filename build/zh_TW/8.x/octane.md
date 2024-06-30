---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/111/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:15:00Z'
---

# Laravel Octane

- [簡介](#introduction)
- [安裝](#installation)
- [伺服器前置需求](#driver-prerequisites)
   - [RoadRunner](#roadrunner)
   - [Swoole](#swoole)
- [處理你的網站](#serving-your-application)
   - [通過 HTTPS 來處理你的網站](#serving-your-application-via-https)
   - [通過 Nginx 來處理你的網站](#serving-your-application-via-nginx)
   - [監控檔案更改](#watching-for-file-changes)
   - [指定 Worker 的數量](#specifying-the-worker-count)
   - [指定最大 Request 數](#specifying-the-max-request-count)
   - [重新載入 Worker](#reloading-the-workers)
   - [停止伺服器](#stopping-the-server)
- [相依性插入與 Octane](#dependency-injection-and-octane)
   - [插入 Container](#container-injection)
   - [插入 Request](#request-injection)
   - [插入 Configuration Repository](#configuration-repository-injection)
- [管理 Memory Leak](#managing-memory-leaks)
- [併行的任務](#concurrent-tasks)
- [Tick 與 Interval](#ticks-and-intervals)
- [Octane 的 Cache](#the-octane-cache)
- [資料表](#tables)

<a name="introduction"></a>

## 簡介

[Laravel Octane](https://github.com/laravel/octane) 通過高效能得網頁伺服器，如 [Open Swoole](https://swoole.co.uk/)、[Swoole](https://github.com/swoole/swoole-src) 與 [RoadRunner](https://roadrunner.dev) 來增強你的網站效能。Octane 會一次性載入你的專案，將專案保存在記憶體中，然後以超音速般的超快速度將 Request 傳給專案。

<a name="installation"></a>

## 安裝

可以使用 Composer 套件管理員來安裝 Octane：

```bash
composer require laravel/octane
```

安裝好 Octane 後，就可以執行 `octane:install` Artisan 指令來安裝 Octane 的設定檔到專案中：

```bash
php artisan octane:install
```

<a name="server-prerequisites"></a>

## 伺服器的前置需求

> {note} Laravel Octane 需要 [PHP 8.0 或之後的版本](https://php.net/releases)。

<a name="roadrunner"></a>

### RoadRunner

[RoadRunner](https://roadrunner.dev) 是由 Go 製作的 RoadRunner 執行檔所驅動。初次啟動基於 RoadRunner 的 Octane Server 時，Octane 會為你下載與安裝 RoadRunner 執行檔。

<a name="roadrunner-via-laravel-sail"></a>

#### 通過 Laravel Sail 的 RoadRunner

若你打算使用 [Laravel Sail](/docs/{{version}}/sail) 來開發專案，請執行下列指令來安裝 Octane 與 RoadRunner：

```bash
./vendor/bin/sail up

./vendor/bin/sail composer require laravel/octane spiral/roadrunner
```

接著，請開啟 Sail Shell，並使用 `rr` 執行檔來取得 RoadRunner 的最新版 Linux 執行檔：

```bash
./vendor/bin/sail shell

# 在 Sail Shell 中...
./vendor/bin/rr get-binary
```

安裝好 RoadRunner 執行檔後，就可退出 Sail 的 Shell 工作階段。接著我們需要調整 Sail 所使用的 `supervisor.conf` 檔案來讓網站保持執行。要開始調整 `supervisor.conf` 檔案，請執行 `sail:publish` Artisan 指令：

```bash
./vendor/bin/sail artisan sail:publish
```

接著，請更新專案中 `docker/supervisord.conf` 檔案內的 `command` 指示詞，讓 Sail 使用 Octane 而不是 PHP 開發伺服器來執行你的網站：

```ini
command=/usr/bin/php -d variables_order=EGPCS /var/www/html/artisan octane:start --server=roadrunner --host=0.0.0.0 --rpc-port=6001 --port=8000
```

最後，請確認 `rr` 二進位檔案是否具有可執行權限，並 ^[Build](建置) 你的 Sail ^[Image](映像)：

```bash
chmod +x ./rr

./vendor/bin/sail build --no-cache
```

<a name="swoole"></a>

### Swoole

若要使用 Swoole 應用程式伺服器來處理你的 Laravel Octane 網站，需要先安裝 Swoole PHP 擴充套件。一般來說，可以使用 PECL 來安裝：

```bash
pecl install swoole
```

<a name="swoole-via-laravel-sail"></a>

#### 通過 Laravel Sail 來使用 Swoole

> {note} 在使用 Sail 來處理 Octane 網站前，請確認是否使用最新版的 Laravel Sail，並在專案的根目錄中執行 `./vendor/bin/sail build --no-cache`。

或者，也可以使用 [Laravel Sail](/docs/{{version}}/sail) —— Laravel 官方所提供的 Docker 開發環境 —— 來開發基於 Swoole 的 Octane 網站。Laravel Sail 預設已包含了 Swoole 擴充套件，但我們需要先調整 Sail
所使用的 `supervisor.conf` 檔案，才能讓你的網站保持執行。若要開始調整 `supervisor.conf` 檔案，請執行 `sail:publish` Artisan 指令：

```bash
./vendor/bin/sail artisan sail:publish
```

接著，請更新專案中 `docker/supervisord.conf` 檔案內的 `command` 指示詞，讓 Sail 使用 Octane 而不是 PHP 開發伺服器來執行你的網站：

```ini
command=/usr/bin/php -d variables_order=EGPCS /var/www/html/artisan octane:start --server=swoole --host=0.0.0.0 --port=80
```

最後，請 ^[Build](建置) 你的 Sail ^[Image](映像)：

```bash
./vendor/bin/sail build --no-cache
```

<a name="swoole-configuration"></a>

#### Swoole 設定

若由需要，Swoole 還支援多個可以加到 `octane` 設定檔中的額外設定選項。由於這些選項通常不會被修改，因此在預設的設定檔中並未包含：

```php
'swoole' => [
    'options' => [
        'log_file' => storage_path('logs/swoole_http.log'),
        'package_max_length' => 10 * 1024 * 1024,
    ],
];
```

<a name="serving-your-application"></a>

## 處理你的網站

可以通過 `octane:start` Artisan 指令來啟動 Octane Server。預設情況下，這個指令會使用專案中 `octane` 設定檔內 `server` 設定選項所指定的伺服器：

```bash
php artisan octane:start
```

預設情況下，Octane 會在 8000 ^[Port](連接埠) 上啟動伺服器，因此我們可以在瀏覽器上通過 `http://localhost:8000` 來存取網站：

<a name="serving-your-application-via-https"></a>

### 通過 HTTPS 來處理你的網站

預設情況下。Octane 會產生 `http://` 開頭的連結。在專案內的 `config/octane.php` 中，使用到了 `OCTANE_HTTPS` 這個環境變數。使用 HTTPS 來處理網站時，請將該環境變數設為 `true`，以讓 Octane 來告訴 Laravel 所有產生的連結都要以 `https://` 開頭：

```php
'https' => env('OCTANE_HTTPS', false),
```

<a name="serving-your-application-via-nginx"></a>

### 通過 Nginx 來處理你的網站

> {tip} 若你還未準備好自行管理伺服器設定，或不擅長設定各種執行大型 Laravel Octane 專案所需要的設定，請參考看看 [Laravel Forge](https://forge.laravel.com)。

在正式環境中，請在傳統的網頁伺服器 —— 如 Nginx 或 Apache —— 後處理你的 Octane 網站。這樣一來，網站伺服器就可負責處理如圖片或 CSS 等的靜態網站，或是管理 SSL 憑證等。

在下方的 Nginx 設定檔中，Nginx 會負責處理網站的靜態資源，並將 Request ^[Proxy](代理) 到 8000 ^[Port](連接埠) 上所執行的 Octane 伺服器：

```conf
map $http_upgrade $connection_upgrade {
    default upgrade;
    ''      close;
}

server {
    listen 80;
    listen [::]:80;
    server_name domain.com;
    server_tokens off;
    root /home/forge/domain.com/public;

    index index.php;

    charset utf-8;

    location /index.php {
        try_files /not_exists @octane;
    }

    location / {
        try_files $uri $uri/ @octane;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    access_log off;
    error_log  /var/log/nginx/domain.com-error.log error;

    error_page 404 /index.php;

    location @octane {
        set $suffix "";

        if ($uri = /index.php) {
            set $suffix ?$query_string;
        }

        proxy_http_version 1.1;
        proxy_set_header Host $http_host;
        proxy_set_header Scheme $scheme;
        proxy_set_header SERVER_PORT $server_port;
        proxy_set_header REMOTE_ADDR $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection $connection_upgrade;

        proxy_pass http://127.0.0.1:8000$suffix;
    }
}
```

<a name="watching-for-file-changes"></a>

### 監控檔案修改

由於你的網站會在 Octane 伺服器啟動當下就被載入到記憶體內，因此，在瀏覽器上重新整理，並不會反映出你對網站所作出的修改。舉例來說，除非重新啟動 Octane 伺服器，不然在 `routes/web.php` 檔內所新增的 Route 定義並不會被反映出來。為了方便開發，可以使用 `--watch` ^[Flag](旗標) 來讓 Octane 在偵測到專案內有任何檔案修改時自動重新啟動伺服器：

```bash
php artisan octane:start --watch
```

在使用此功能前，請先確認本機開發環境上是否有安裝 [Node](https://nodejs.org)。此外，也需要在專案中安裝 [Chokidar](https://github.com/paulmillr/chokidar) 檔案監控套件：

```bash
npm install --save-dev chokidar
```

可以在專案內的 `config/octane.php` 設定檔中，使用 `watch` 設定選項來設定要監控哪些目錄與檔案。

<a name="specifying-the-worker-count"></a>

### 指定 Worker 的數量

預設情況下，Octane 會依照你裝置的 CPU 核心數量來啟動相應的 Worker 數。啟動之後，當連入的 HTTP Request 進入你的網站時，就會由這些 Worker 來負責處理。可以在執行 `octane:start` 指令時，使用 `--workers` 選項來手動指定要啟動多少個 Worker：

```bash
php artisan octane:start --workers=4
```

使用 Swoole 應用程式伺服器時，還可以指定要啟動多少個「[Task Worker](#concurrent-tasks)」：

```bash
php artisan octane:start --workers=4 --task-workers=6
```

<a name="specifying-the-max-request-count"></a>

### 指定最大 Request 數

為了協助避免造成 Memory Leak，可以讓 Octane 在處理給定數量的 Request 後重新柔性重新啟動 (Gracefully Restart) Worker。若要讓 Octane 在處理一定數量後重新啟動 Server，可以使用 `--max-requests` 選項：

```bash
php artisan octane:start --max-requests=250
```

<a name="reloading-the-workers"></a>

### 重新載入 Worker

可以使用 `octane:reload` 指令來柔性重啟 Octane 伺服器的應用程式 Worker。一般來說，該指令應在部屬完成後使用，以將新部署的程式碼載入至記憶體當中，並用於處理接下來的 Request：

```bash
php artisan octane:reload
```

<a name="stopping-the-server"></a>

### 停止伺服器

可使用 `octane:stop` Artisan 指令以停止 Octane 伺服器：

```bash
php artisan octane:stop
```

<a name="checking-the-server-status"></a>

#### 檢查伺服器狀態

可使用 `octane:status` Artisan 指令來檢查目前的 Octane 伺服器狀態：

```bash
php artisan octane:status
```

<a name="dependency-injection-and-octane"></a>

## 相依性插入與 Octane

啟動 Octane 後，由於 Octane 在處理 Request 時只會一次性地將整個網站程式碼載入進記憶體中，因此在製作網站時有一些需要注意的點。舉例來說，在專案的 Service Provider 內，各個 `register` 與 `boot` 方法都只會在 Request Worker 第一次載入的時候被執行一次，並接下來的 Request 中重複使用同一個 Application 實體。

因此，在將 Service Container 或 Request 插入到任何物件的 ^[Constructor](建構函式) 中時，請特別注意，這些物件在後續的 Request 中可能會收到非最新狀態的 Service Container 或 Request 實體。

Octane 會自動在各個 Request 間重設 Laravel 第一方的物件狀態 (State)。不過，Octane 無從得知如何處理您的專案所建立的全域狀態。因此，在製作專案時，必須考量到如何針對 Octane 作出調整。在接下來的文件中，我們會討論使用 Octane 時可能會遇到的常見問題。

<a name="container-injection"></a>

### 插入 Container

一般來說，我們應該避免將 Service Container 或 HTTP Request 實體插入到其他物件的 Constructor 中。舉例來說，下列繫結會將整個 Service Container 插入到被繫結為單例 (Singleton) 的物件中：

```php
use App\Service;

/**
 * Register any application services.
 *
 * @return void
 */
public function register()
{
    $this->app->singleton(Service::class, function ($app) {
        return new Service($app);
    });
}
```

在這個例子中，若該 `Service` 實體是在網站啟動過程中被解析的，則在解析時，會插入 Container 到該 Service 中。在接下來的 Request 中，Service 實體上都將擁有相同的 Container 實體。對於部分專案來說，此狀況 **或許**不是個問題。不過，在啟動時，若由繫結是在解析 Service 實體之後才被加入到 Container 中的，或是在接下來的 Request 中有其他繫結被加入到 Container 中，則 Service 實體上的 Container 可能會缺少這些繫結。

針對此問題的解決方法有兩種，一種方法是不用單例來註冊繫結，而另一種方法則是將一個用於解析 Container 的 ^[Closure](閉包) 插入到 Service 中，以隨時解析為最新的 Container 實體：

```php
use App\Service;
use Illuminate\Container\Container;

$this->app->bind(Service::class, function ($app) {
    return new Service($app);
});

$this->app->singleton(Service::class, function () {
    return new Service(fn () => Container::getInstance());
});
```

全域補助函式 `app` 以及 `Container::getInstance()` 方法都會回傳最新版的 Container。

<a name="request-injection"></a>

### 插入 Request

一般來說，我們應該避免將 Service Container 或 HTTP Request 實體插入到其他物件的 Constructor 中。舉例來說，下列繫結會將整個 Request 實體插入到被繫結為單例 (Singleton) 的物件中：

```php
use App\Service;

/**
 * Register any application services.
 *
 * @return void
 */
public function register()
{
    $this->app->singleton(Service::class, function ($app) {
        return new Service($app['request']);
    });
}
```

在此例子中，若 `Service` 實體是在網站啟動過程中被解析的，則 HTTP Request 實體會被插入到 Service 實體內，並且在接下來的 Request 中，該 Service 實體都將擁有同一個 Request 實體。因此，所有的 Header、Input、Query String，以及其他 Request 資料都會是不正確的。

針對此問題，有幾種解決方法。第一種方法就是不要使用單例來註冊繫結，或者，可以將一個用於解析 Request 的 Closure 傳入給 Service 以隨時解析最新的 Request 實體。另一種方法，也是最推薦的作法，就是在執行階段時，只在 Request 中取出該物件所需的資訊，然後只傳入這些資訊到該物件的方法中：

```php
use App\Service;

$this->app->bind(Service::class, function ($app) {
    return new Service($app['request']);
});

$this->app->singleton(Service::class, function ($app) {
    return new Service(fn () => $app['request']);
});

// 或者...

$service->method($request->input('name'));
```

全域輔助函式 `request` 會回傳網站目前正在處理的 Request，因此在專案中可以安全地使用該函式。

> {note} 在 Controller 方法或 Route Closure 中，可型別提示 `Illuminate\Http\Request`。

<a name="configuration-repository-injection"></a>

### 插入 Configuration Repository

一般來說，我們應該避免將 Configuration Repository 實體插入到其他物件的 Constructor 中。舉例來說，下列繫結會將整個 Configuration Repository 插入到被繫結為單例 (Singleton) 的物件中：

```php
use App\Service;

/**
 * Register any application services.
 *
 * @return void
 */
public function register()
{
    $this->app->singleton(Service::class, function ($app) {
        return new Service($app->make('config'));
    });
}
```

在這個例子中，若在各個 Request 間，設定值有發生變動，則 Service 將無法存取到最新的值，因為 Service 物件仍相依於原始的 Repository 實體。

要解決此問題，有兩種做法。第一種方法就是不要使用單例來繫結，而第二種方法則是插入一個用於解析 Configuration Repository 的 Closure 至該類別中：

```php
use App\Service;
use Illuminate\Container\Container;

$this->app->bind(Service::class, function ($app) {
    return new Service($app->make('config'));
});

$this->app->singleton(Service::class, function () {
    return new Service(fn () => Container::getInstance()->make('config'));
});
```

全域函式 `config` 會回傳最新版本的 Configuration Repository，因此可以安全地在專案中使用該函式。

<a name="managing-memory-leaks"></a>

### 管理 ^[Memory Leak](記憶體流失)

再次提醒，由於 Octane 會在各個 Request 間將網站程式保留在記憶體中，因此，若將資料加入到靜態維護的陣列將導致 Memory Leak。舉例來說，在下列 Controller 中，由於每個 Request 都會向靜態 `$data` 陣列加入資料，因此會導致 Memory Leak：

```php
use App\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Handle an incoming request.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return void
 */
public function index(Request $request)
{
    Service::$data[] = Str::random(10);

    // ...
}
```

在製作網站時，應特別注意以避免造成這些類型的 Memory Leak。建議在本機開發環境上監控網站的記憶體用量，以確保沒有在網站中造成 Memory Leak。

<a name="concurrent-tasks"></a>

## 併行的任務

> {note} 使用此功能時必須使用 [Swoole](#swoole)。

在使用 Swoole 時，只需要使用 Octane 的 `concurrently` 方法，就可以通過輕型的背景任務來併行執行一些動作。可以將 `concurrently` 方法與 PHP 的陣列解構 (Destructure) 搭配使用以取得各個動作的執行結果：

```php
use App\Models\User;
use App\Models\Server;
use Laravel\Octane\Facades\Octane;

[$users, $servers] = Octane::concurrently([
    fn () => User::all(),
    fn () => Server::all(),
]);
```

Octane 使用 Swoole 的「Task Worker」來處理併行的任務，並在與處理連入 Request 不同的處理程序中執行。在執行 `octane:start` 指令時，可以使用 `--task-workers` 指示詞來指定處理併行任務時可用的 Worker 數量：

```bash
php artisan octane:start --workers=4 --task-workers=6
```

<a name="ticks-and-intervals"></a>

## Tick 與 Interval

> {note} 使用此功能時必須使用 [Swoole](#swoole)。

在使用 Swoole 時，可以註冊一個「Tick」動作。每隔指定秒數時，就會執行一次該動作。可以使用 `tick` 方法來註冊「Tick」^[Callback](回呼)。`tick` 方法的第一個引數為字串，代表該 Ticker 的名稱。第二個引數則為每個特定間隔會被呼叫的 Callable。

在這個例子中，我們會註冊一個沒隔 10 秒會被執行的 Closure。一般來說，應在專案中某個 Service Provider 內的 `boot` 方法中呼叫 `tick` 方法：

```php
Octane::tick('simple-ticker', fn () => ray('Ticking...'))
        ->seconds(10);
```

使用 `immediate` 方法，就可以讓 Octane 在 Octane 伺服器一啟動後馬上呼叫該 Tick Callback，並在接下來的每 N 秒執行：

```php
Octane::tick('simple-ticker', fn () => ray('Ticking...'))
        ->seconds(10)
        ->immediate();
```

<a name="the-octane-cache"></a>

## Octane Cache

> {note} 使用此功能時必須使用 [Swoole](#swoole)。

使用 Swoole 時，可以使用 Octane 的 Cache Driver。Octane 的 Cache Driver 提供了最快 2 百萬讀寫 / 秒的讀寫速度。因此，對於在快取層上需要高度讀寫速度的專案，Octane 的 Cache Driver 是很好的選擇。

該 Cache Driver 由 [Swoole Table](https://www.swoole.co.uk/docs/modules/swoole-table) 驅動。儲存在 Cache 中的所有資料可在 Swoole Server 中的所有 Worker 中取用。不過，若重新啟動 Server，則已快取的資料會被清除。

```php
Cache::store('octane')->put('framework', 'Laravel', 30);
```

> {tip} 可存在 Octane Cache 中的最大資料筆數可在專案的 `octane` 設定檔中定義。

<a name="cache-intervals"></a>

### Cache 週期

除了 Laravel 的 Cache 系統所提供的一般方法外，Octane 的 Cache Driver 還提供了基於週期的快取。這些快取會在特定週期後被自動重新整理。需要在專案中某個 Service Provider 內的 `boot` 方法中註冊這些快取。舉例來說，下列快取每隔 5 秒就會被重新整理：

```php
use Illuminate\Support\Str;

Cache::store('octane')->interval('random', function () {
    return Str::random(10);
}, seconds: 5)
```

<a name="tables"></a>

## Table

> {note} 使用此功能時必須使用 [Swoole](#swoole)。

使用 Swoole 時，也可以定義與使用任意的 [Swoole Table](https://www.swoole.co.uk/docs/modules/swoole-table)。Swoole Table 提供了超快的吞吐效能。而存在 Swoole Table 中的資料可被 Swoole Server 中的所有 Worker 存取。不過，一旦重新啟動 Server，存在 Swoole Table 中的資料就會消失。

可以在專案的 `octane` 設定檔中 `tables` 設定陣列內定義 Swoole Table。在設定檔中，已包含了一個允許最多 1000 行資料的範例 Table 定義。可像下面範例這樣在欄位型別後指定字串欄位的最大大小：

```php
'tables' => [
    'example:1000' => [
        'name' => 'string:1000',
        'votes' => 'int',
    ],
],
```

若要存取 Swoole Table，可使用 `Octane::table` 方法：

```php
use Laravel\Octane\Facades\Octane;

Octane::table('example')->set('uuid', [
    'name' => 'Nuno Maduro',
    'votes' => 1000,
]);

return Octane::table('example')->get('uuid');
```

> {note} Swoole 所支援的欄位型別為：`string`、`int`，與 `float`。
