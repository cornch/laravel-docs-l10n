---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/101/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors:
    14684796: { name: cornch, avatarUrl: 'https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png' }
progress: 28.96
---

# 中介軟體 - Middleware

- [簡介](#introduction)
- [定義 Middleware](#defining-middleware)
- [註冊 Middleware](#registering-middleware)
  - [全域 Middleware](#global-middleware)
  - [Assigning Middleware to Routes](#assigning-middleware-to-routes)
  - [Middleware 群組](#middleware-groups)
  - [Middleware Aliases](#middleware-aliases)
  - [排序 Middleware](#sorting-middleware)
  
- [Middleware 參數](#middleware-parameters)
- [可終止的 Middleware](#terminable-middleware)

<a name="introduction"></a>

## 簡介

Middleware 提供了一個機制，可檢驗與過濾進入應用程式的 HTTP Request。舉例來說，Laravel 中包含了一個可以認證使用者是否已登入的 Middleware。若使用者未登入，該 Middleware 會將使用者重新導向回登入畫面。不過，若使用者已登入，這個 Middleware 就會讓 Request 進一步進入程式中處理。

Additional middleware can be written to perform a variety of tasks besides authentication. For example, a logging middleware might log all incoming requests to your application. A variety of middleware are included in Laravel, including middleware for authentication and CSRF protection; however, all user-defined middleware are typically located in your application's `app/Http/Middleware` directory.

<a name="defining-middleware"></a>

## 定義 Middleware

若要建立新的 Middleware，請使用 `make:middleware` Artisan 指令：

```shell
php artisan make:middleware EnsureTokenIsValid
```
This command will place a new `EnsureTokenIsValid` class within your `app/Http/Middleware` directory. In this middleware, we will only allow access to the route if the supplied `token` input matches a specified value. Otherwise, we will redirect the users back to the `/home` URI:

    <?php
    
    namespace App\Http\Middleware;
    
    use Closure;
    use Illuminate\Http\Request;
    use Symfony\Component\HttpFoundation\Response;
    
    class EnsureTokenIsValid
    {
        /**
         * Handle an incoming request.
         *
         * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
         */
        public function handle(Request $request, Closure $next): Response
        {
            if ($request->input('token') !== 'my-secret-token') {
                return redirect('/home');
            }
    
            return $next($request);
        }
    }
就像我們可以看到的一樣，若給定的 `token` 不符合我們的私密權杖 (Secret Token)，則這個 Middleware 會回傳一個 HTTP Redirect 給用戶端。`token` 符合時，這個 Request 就會進一步地傳給我們的程式。若要將 Request 進一步傳進我們的應用程式中 (即，讓 Middleware「通過 - Pass」)，應以 `$request` 呼叫 `$next` 回呼。

最好想像成我們有「一層又一層」的 Middleware。HTTP Request 必須通過每一層的 Middleware，最後才能進入你的應用程式中。每一層 Middleware 都可以檢查 Request 的內容，甚至還能完全拒絕 Request。

> [!NOTE]  
> 所有的 Middleware 都會經過 [Service Container] 解析，因此我們可以在 Middleware 的 Constructor (建構函式) 上型別提示 (Type-Hint) 任何需要的相依性。

<a name="middleware-and-responses"></a>

#### Middleware and Responses

當然，Middleware 可以在將 Request 傳入應用程式的前後執行。舉例來說，下列 Middleware 會在 Request 被程式處理 **之後** 進行一些任務：

    <?php
    
    namespace App\Http\Middleware;
    
    use Closure;
    use Illuminate\Http\Request;
    use Symfony\Component\HttpFoundation\Response;
    
    class BeforeMiddleware
    {
        public function handle(Request $request, Closure $next): Response
        {
            // Perform action
    
            return $next($request);
        }
    }
不過，這個 Middleware 會在 Request 被程式處理 **之後** 才進行其任務：

    <?php
    
    namespace App\Http\Middleware;
    
    use Closure;
    use Illuminate\Http\Request;
    use Symfony\Component\HttpFoundation\Response;
    
    class AfterMiddleware
    {
        public function handle(Request $request, Closure $next): Response
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

If you want a middleware to run during every HTTP request to your application, you may append it to the global middleware stack in your application's `bootstrap/app.php` file:

    use App\Http\Middleware\EnsureTokenIsValid;
    
    ->withMiddleware(function (Middleware $middleware) {
         $middleware->append(EnsureTokenIsValid::class);
    })
The `$middleware` object provided to the `withMiddleware` closure is an instance of `Illuminate\Foundation\Configuration\Middleware` and is responsible for managing the middleware assigned to your application's routes. The `append` method adds the middleware to the end of the list of global middleware. If you would like to add a middleware to the beginning of the list, you should use the `prepend` method.

<a name="manually-managing-laravels-default-global-middleware"></a>

#### Manually Managing Laravel's Default Global Middleware

If you would like to manage Laravel's global middleware stack manually, you may provide Laravel's default stack of global middleware to the `use` method. Then, you may adjust the default middleware stack as necessary:

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([
            \Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks::class,
            // \Illuminate\Http\Middleware\TrustHosts::class,
            \Illuminate\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);
    })
<a name="assigning-middleware-to-routes"></a>

### Assigning Middleware to Routes

若想將 Middleware 指定到特定的 Route 中，可以在定義 Route 時呼叫 `middleware` 方法：

    use App\Http\Middleware\EnsureTokenIsValid;
    
    Route::get('/profile', function () {
        // ...
    })->middleware(EnsureTokenIsValid::class);
也可以傳入一組 Middleware 陣列給 `middleware` 方法來指派多個 Middleware 給 Route：

    Route::get('/', function () {
        // ...
    })->middleware([First::class, Second::class]);
<a name="excluding-middleware"></a>

#### 排除 Middleware

當我們將 Middleware 指派給 Route 群組時，我們有時候會需要讓某個 Middleware 不要被套用到群組中的個別 Route 上。我們可以使用 `withoutMiddleware` 方法來完成：

    use App\Http\Middleware\EnsureTokenIsValid;
    
    Route::middleware([EnsureTokenIsValid::class])->group(function () {
        Route::get('/', function () {
            // ...
        });
    
        Route::get('/profile', function () {
            // ...
        })->withoutMiddleware([EnsureTokenIsValid::class]);
    });
也可以將一組 Middleware 從整個 Route [群組](/docs/{{version}}/routing#route-groups)定義中排除：

    use App\Http\Middleware\EnsureTokenIsValid;
    
    Route::withoutMiddleware([EnsureTokenIsValid::class])->group(function () {
        Route::get('/profile', function () {
            // ...
        });
    });
`withoutMiddleware` 方法只能移除 Route Middleware，不能移除[全域 Middleware](#global-middleware)。

<a name="middleware-groups"></a>

### Middleware 群組

Sometimes you may want to group several middleware under a single key to make them easier to assign to routes. You may accomplish this using the `appendToGroup` method within your application's `bootstrap/app.php` file:

    use App\Http\Middleware\First;
    use App\Http\Middleware\Second;
    
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('group-name', [
            First::class,
            Second::class,
        ]);
    
        $middleware->prependToGroup('group-name', [
            First::class,
            Second::class,
        ]);
    })
Middleware groups may be assigned to routes and controller actions using the same syntax as individual middleware:

    Route::get('/', function () {
        // ...
    })->middleware('group-name');
    
    Route::middleware(['group-name'])->group(function () {
        // ...
    });
<a name="laravels-default-middleware-groups"></a>

#### Laravel's Default Middleware Groups

Laravel includes predefined `web` and `api` middleware groups that contain common middleware you may want to apply to your web and API routes. Remember, Laravel automatically applies these middleware groups to the corresponding `routes/web.php` and `routes/api.php` files:

<div class="overflow-auto">
| The `web` Middleware Group |
| --- |
| `Illuminate\Cookie\Middleware\EncryptCookies` |
| `Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse` |
| `Illuminate\Session\Middleware\StartSession` |
| `Illuminate\View\Middleware\ShareErrorsFromSession` |
| `Illuminate\Foundation\Http\Middleware\ValidateCsrfToken` |
| `Illuminate\Routing\Middleware\SubstituteBindings` |

</div>
<div class="overflow-auto">
| The `api` Middleware Group |
| --- |
| `Illuminate\Routing\Middleware\SubstituteBindings` |

</div>
If you would like to append or prepend middleware to these groups, you may use the `web` and `api` methods within your application's `bootstrap/app.php` file. The `web` and `api` methods are convenient alternatives to the `appendToGroup` method:

    use App\Http\Middleware\EnsureTokenIsValid;
    use App\Http\Middleware\EnsureUserIsSubscribed;
    
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            EnsureUserIsSubscribed::class,
        ]);
    
        $middleware->api(prepend: [
            EnsureTokenIsValid::class,
        ]);
    })
You may even replace one of Laravel's default middleware group entries with a custom middleware of your own:

    use App\Http\Middleware\StartCustomSession;
    use Illuminate\Session\Middleware\StartSession;
    
    $middleware->web(replace: [
        StartSession::class => StartCustomSession::class,
    ]);
Or, you may remove a middleware entirely:

    $middleware->web(remove: [
        StartSession::class,
    ]);
<a name="manually-managing-laravels-default-middleware-groups"></a>

#### Manually Managing Laravel's Default Middleware Groups

If you would like to manually manage all of the middleware within Laravel's default `web` and `api` middleware groups, you may redefine the groups entirely. The example below will define the `web` and `api` middleware groups with their default middleware, allowing you to customize them as necessary:

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
        ]);
    
        $middleware->group('api', [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            // 'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
> [!NOTE]  
> By default, the `web` and `api` middleware groups are automatically applied to your application's corresponding `routes/web.php` and `routes/api.php` files by the `bootstrap/app.php` file.

<a name="middleware-aliases"></a>

### Middleware Aliases

You may assign aliases to middleware in your application's `bootstrap/app.php` file. Middleware aliases allow you to define a short alias for a given middleware class, which can be especially useful for middleware with long class names:

    use App\Http\Middleware\EnsureUserIsSubscribed;
    
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'subscribed' => EnsureUserIsSubscribed::class
        ]);
    })
Once the middleware alias has been defined in your application's `bootstrap/app.php` file, you may use the alias when assigning the middleware to routes:

    Route::get('/profile', function () {
        // ...
    })->middleware('subscribed');
For convenience, some of Laravel's built-in middleware are aliased by default. For example, the `auth` middleware is an alias for the `Illuminate\Auth\Middleware\Authenticate` middleware. Below is a list of the default middleware aliases:

<div class="overflow-auto">
| Alias | 中介軟體 - Middleware |
| --- | --- |
| `auth` | `Illuminate\Auth\Middleware\Authenticate` |
| `auth.basic` | `Illuminate\Auth\Middleware\AuthenticateWithBasicAuth` |
| `auth.session` | `Illuminate\Session\Middleware\AuthenticateSession` |
| `cache.headers` | `Illuminate\Http\Middleware\SetCacheHeaders` |
| `can` | `Illuminate\Auth\Middleware\Authorize` |
| `guest` | `Illuminate\Auth\Middleware\RedirectIfAuthenticated` |
| `password.confirm` | `Illuminate\Auth\Middleware\RequirePassword` |
| `precognitive` | `Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests` |
| `signed` | `Illuminate\Routing\Middleware\ValidateSignature` |
| `subscribed` | `\Spark\Http\Middleware\VerifyBillableIsSubscribed` |
| `throttle` | `Illuminate\Routing\Middleware\ThrottleRequests` or `Illuminate\Routing\Middleware\ThrottleRequestsWithRedis` |
| `verified` | `Illuminate\Auth\Middleware\EnsureEmailIsVerified` |

</div>
<a name="sorting-middleware"></a>

### 排序 Middleware

Rarely, you may need your middleware to execute in a specific order but not have control over their order when they are assigned to the route. In these situations, you may specify your middleware priority using the `priority` method in your application's `bootstrap/app.php` file:

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);
    })
<a name="middleware-parameters"></a>

## Middleware 參數

Middleware 也可以接收額外的參數。舉例來說，若你的程式需要在執行給定動作前認證登入的使用者是否有給定的「職位 (Role)」，則我們可以先建立一個 `EnsureUserHasRole` Middleware，讓該 Middleware 接收一個職位名稱來作為其額外的引數。

額外的 Middleware 引數會被放在 `$next` 引數之後傳遞給 Middleware：

    <?php
    
    namespace App\Http\Middleware;
    
    use Closure;
    use Illuminate\Http\Request;
    use Symfony\Component\HttpFoundation\Response;
    
    class EnsureUserHasRole
    {
        /**
         * Handle an incoming request.
         *
         * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
         */
        public function handle(Request $request, Closure $next, string $role): Response
        {
            if (! $request->user()->hasRole($role)) {
                // Redirect...
            }
    
            return $next($request);
        }
    
    }
Middleware parameters may be specified when defining the route by separating the middleware name and parameters with a `:`:

    use App\Http\Middleware\EnsureUserHasRole;
    
    Route::put('/post/{id}', function (string $id) {
        // ...
    })->middleware(EnsureUserHasRole::class.':editor');
Multiple parameters may be delimited by commas:

    Route::put('/post/{id}', function (string $id) {
        // ...
    })->middleware(EnsureUserHasRole::class.':editor,publisher');
<a name="terminable-middleware"></a>

## 可終止的 Middleware

有時候，某個 Middleware 可能需要在 HTTP Response 被傳送到瀏覽器後才進行某些動作。若我們在 Middleware 上定義一個 `terminate` 方法，且網頁伺服器 (Web Server) 使用 FastCGI，則會在 Response 傳送給瀏覽器後會自動呼叫 `terminate` 方法：

    <?php
    
    namespace Illuminate\Session\Middleware;
    
    use Closure;
    use Illuminate\Http\Request;
    use Symfony\Component\HttpFoundation\Response;
    
    class TerminatingMiddleware
    {
        /**
         * Handle an incoming request.
         *
         * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
         */
        public function handle(Request $request, Closure $next): Response
        {
            return $next($request);
        }
    
        /**
         * Handle tasks after the response has been sent to the browser.
         */
        public function terminate(Request $request, Response $response): void
        {
            // ...
        }
    }
The `terminate` method should receive both the request and the response. Once you have defined a terminable middleware, you should add it to the list of routes or global middleware in your application's `bootstrap/app.php` file.

呼叫 Middleware 上的 `terminate` 方法時，Laravel 會從 [Service Container] 中解析出這個 Middleware 的新實體。若想讓 `handle` 與 `terminate` 都在同一個 Middleware 實體上呼叫的話，請使用 Container 的 `singleton` 方法來想 Container 註冊這個 Middleware。一般來說，這個註冊應在 `AppServiceProvider` 的 `register` 方法中進行：

    use App\Http\Middleware\TerminatingMiddleware;
    
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TerminatingMiddleware::class);
    }