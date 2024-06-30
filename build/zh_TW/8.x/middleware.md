---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/101/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:27:00Z'
---

# 中介軟體 - Middleware

- [簡介](#introduction)
- [定義 Middleware](#defining-middleware)
- [註冊 Middleware](#registering-middleware)
   - [全域 Middleware](#global-middleware)
   - [將 Middleware 指派給 Route](#assigning-middleware-to-routes)
   - [Middleware 群組](#middleware-groups)
   - [排序 Middleware](#sorting-middleware)
- [Middleware 參數](#middleware-parameters)
- [可終止的 Middleware](#terminable-middleware)

<a name="introduction"></a>

## 簡介

Middleware 提供了一個機制，可檢驗與過濾進入應用程式的 HTTP Request。舉例來說，Laravel 中包含了一個可以認證使用者是否已登入的 Middleware。若使用者未登入，該 Middleware 會將使用者重新導向回登入畫面。不過，若使用者已登入，這個 Middleware 就會讓 Request 進一步進入程式中處理。

除了登入認證外，我們還能撰寫追加的 Middleware 來進行各種任務。舉例來說，可以有個 Logging Middleware 來將程式的所有連入 Request 都紀錄到日誌裡。Laravel Framework 還包含了許多 Middleware，包含用於登入認證的 Middleware、以及用於 CSRF 保護的 Middleware。這些 Middleware 都放置在 `app/Http/Middleware` 目錄內。

<a name="defining-middleware"></a>

## 定義 Middleware

若要建立新的 Middleware，請使用 `make:middleware` Artisan 指令：

    php artisan make:middleware EnsureTokenIsValid

該指令會在 `app/Http/Middleware` 目錄中放置一個新的 `EnsureTokenIsValid` 類別。在這個 Middleware 中，我們要只在提供的 `token` 符合特定的值時才允許存取該 Route。`token` 不符合時，會將使用者重新導向回到 `home` URI：

    <?php
    
    namespace App\Http\Middleware;
    
    use Closure;
    
    class EnsureTokenIsValid
    {
        /**
         * Handle an incoming request.
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  \Closure  $next
         * @return mixed
         */
        public function handle($request, Closure $next)
        {
            if ($request->input('token') !== 'my-secret-token') {
                return redirect('home');
            }
    
            return $next($request);
        }
    }

就像我們可以看到的一樣，若給定的 `token` 不符合我們的私密權杖 (Secret Token)，則這個 Middleware 會回傳一個 HTTP Redirect 給用戶端。`token` 符合時，這個 Request 就會進一步地傳給我們的程式。若要將 Request 進一步傳進我們的應用程式中 (即，讓 Middleware「通過 - Pass」)，應以 `$request` 呼叫 `$next` 回呼。

最好想像成我們有「一層又一層」的 Middleware。HTTP Request 必須通過每一層的 Middleware，最後才能進入你的應用程式中。每一層 Middleware 都可以檢查 Request 的內容，甚至還能完全拒絕 Request。

> {tip} 所有的 Middleware 都會經過 [Service Container] 解析，因此我們可以在 Middleware 的 ^[Constructor](建構函式) 上^[型別提示](Type-Hint) 任何需要的相依性。

<a name="before-after-middleware"></a> <a name="middleware-and-responses"></a>

#### Middleare 與 Response

當然，Middleware 可以在將 Request 傳入應用程式的前後執行。舉例來說，下列 Middleware 會在 Request 被程式處理 **之後** 進行一些任務：

    <?php
    
    namespace App\Http\Middleware;
    
    use Closure;
    
    class BeforeMiddleware
    {
        public function handle($request, Closure $next)
        {
            // Perform action
    
            return $next($request);
        }
    }

不過，這個 Middleware 會在 Request 被程式處理 **之後** 才進行其任務：

    <?php
    
    namespace App\Http\Middleware;
    
    use Closure;
    
    class AfterMiddleware
    {
        public function handle($request, Closure $next)
        {
            $response = $next($request);
    
            // Perform action
    
            return $response;
        }
    }

<a name="registering-middleware"></a>

## 註冊 Middleware

<a name="global-middleware"></a>

### 全域 Middleware

若想讓 Middleware 在每一個 HTTP Request 上都執行的話，請將該 Middleware 列在 `app/Http/Kernel.php` 類別中的 `$middleware` 屬性內。

<a name="assigning-middleware-to-routes"></a>

### 將 Middleware 指派給 Route

若想將 Middleware 指派給特定的 Route，請先在專案的 `app/Http/Kernel.php` 檔案中為該 Middleware 指派一個索引鍵。預設情況下，這個類別的 `$routeMiddleware` 屬性包含了 Laravel 附帶的 Middleware。我們可以在這個列表中列出我們自己的 Middleware，並為其指派一個自訂的索引鍵：

    // 在 App\Http\Kernel 類別中...
    
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];

Middleware 被定義為 HTTP Kernel 後，就可以使用 `middleware` 方法來將 Middleware 指派給 Route：

    Route::get('/profile', function () {
        //
    })->middleware('auth');

也可以傳入一組 Middleware 陣列給 `middleware` 方法來指派多個 Middleware 給 Route：

    Route::get('/', function () {
        //
    })->middleware(['first', 'second']);

指派 Middleware 時，也可以傳入完整類別名稱 (Fully Qualified Class Name)：

    use App\Http\Middleware\EnsureTokenIsValid;
    
    Route::get('/profile', function () {
        //
    })->middleware(EnsureTokenIsValid::class);

<a name="excluding-middleware"></a>

#### 排除 Middleware

當我們將 Middleware 指派給 Route 群組時，我們有時候會需要讓某個 Middleware 不要被套用到群組中的個別 Route 上。我們可以使用 `withoutMiddleware` 方法來完成：

    use App\Http\Middleware\EnsureTokenIsValid;
    
    Route::middleware([EnsureTokenIsValid::class])->group(function () {
        Route::get('/', function () {
            //
        });
    
        Route::get('/profile', function () {
            //
        })->withoutMiddleware([EnsureTokenIsValid::class]);
    });

也可以將一組 Middleware 從整個 Route [群組](/docs/{{version}}/routing#route-groups)定義中排除：

    use App\Http\Middleware\EnsureTokenIsValid;
    
    Route::withoutMiddleware([EnsureTokenIsValid::class])->group(function () {
        Route::get('/profile', function () {
            //
        });
    });

`withoutMiddleware` 方法只能移除 Route Middleware，不能移除[全域 Middleware](#global-middleware)。

<a name="middleware-groups"></a>

### Middleware 群組

有時候，我們會想將多個 Middleware 分組在單一索引鍵上，來讓我們可以輕鬆地將其指派給 Route。可以在 HTTP Kernel 中使用 `$middlewareGroups` 屬性來完成。

Laravel 中安裝完後就自帶了 `web` 與 `api` 兩個 Middleware 群組，其中共包含了可用在網頁與 API Route 上的常見 Middleware。請記得，這些 Middleware 群組由 `App\Providers\RouteServiceProvider` Service Provider 自動套用到對應的 `web` 與 `api` Route 檔案：

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    
        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

也可以使用相同的語法來將 Middleware 群組作為個別 Middleware 一樣指派給 Route 與 Controller 動作。同樣的，使用 Middleware 群組來一次指派多個 Middleware 給 Route 比較方便：

    Route::get('/', function () {
        //
    })->middleware('web');
    
    Route::middleware(['web'])->group(function () {
        //
    });

> {tip} 在新安裝的 Laravel 中隨附了 `web` 與 `api` Middleware 群組，並由 `App\Providers\RouteServiceProvider` 自動套用到對應的 `routes/web.php` 與 `routes/api.php` 檔上。

<a name="sorting-middleware"></a>

### 排序 Middleware

我們偶爾會需要讓 Middleware 以特定的順序執行，但有時候沒有辦法控制 Middleware 是以什麼順序指派給 Route 的。這時，我們可以使用 `app/Http/Kernel.php` 檔案中的 `$middlewarePriority` 屬性來執行 Middleware 的優先順序。這個屬性預設可能不存在 HTTP Kernel 中。若沒有這個屬性，可以複製下列預設定義來用：

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var string[]
     */
    protected $middlewarePriority = [
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
        \Illuminate\Routing\Middleware\ThrottleRequests::class,
        \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];

<a name="middleware-parameters"></a>

## Middleware 參數

Middleware 也可以接收額外的參數。舉例來說，若你的程式需要在執行給定動作前認證登入的使用者是否有給定的「職位 (Role)」，則我們可以先建立一個 `EnsureUserHasRole` Middleware，讓該 Middleware 接收一個職位名稱來作為其額外的引數。

額外的 Middleware 引數會被放在 `$next` 引數之後傳遞給 Middleware：

    <?php
    
    namespace App\Http\Middleware;
    
    use Closure;
    
    class EnsureUserHasRole
    {
        /**
         * Handle the incoming request.
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  \Closure  $next
         * @param  string  $role
         * @return mixed
         */
        public function handle($request, Closure $next, $role)
        {
            if (! $request->user()->hasRole($role)) {
                // Redirect...
            }
    
            return $next($request);
        }
    
    }

也可以在定義 Route 時使用 `:` 區分出 Middleware 名稱與參數來指定 Middleware 參數。多個參數請使用逗點 (,) 區隔：

    Route::put('/post/{id}', function ($id) {
        //
    })->middleware('role:editor');

<a name="terminable-middleware"></a>

## 可終止的 Middleware

有時候，某個 Middleware 可能需要在 HTTP Response 被傳送到瀏覽器後才進行某些動作。若我們在 Middleware 上定義一個 `terminate` 方法，且網頁伺服器 (Web Server) 使用 FastCGI，則會在 Response 傳送給瀏覽器後會自動呼叫 `terminate` 方法：

    <?php
    
    namespace Illuminate\Session\Middleware;
    
    use Closure;
    
    class TerminatingMiddleware
    {
        /**
         * Handle an incoming request.
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  \Closure  $next
         * @return mixed
         */
        public function handle($request, Closure $next)
        {
            return $next($request);
        }
    
        /**
         * Handle tasks after the response has been sent to the browser.
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  \Illuminate\Http\Response  $response
         * @return void
         */
        public function terminate($request, $response)
        {
            // ...
        }
    }

`terminate` 方法應接收 Request 與 Response。定義好可終止的 Middleware (Terminable Middleware) 後，請將其加到 Route 列表或 `app/Http/Kernel.php` 檔案中的全域 Middleware 內。

呼叫 Middleware 上的 `terminate` 方法時，Laravel 會從 [Service Container] 中解析出這個 Middleware 的新實體。若想讓 `handle` 與 `terminate` 都在同一個 Middleware 實體上呼叫的話，請使用 Container 的 `singleton` 方法來想 Container 註冊這個 Middleware。一般來說，這個註冊應在 `AppServiceProvider` 的 `register` 方法中進行：

    use App\Http\Middleware\TerminatingMiddleware;
    
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(TerminatingMiddleware::class);
    }
