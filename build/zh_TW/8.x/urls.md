---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/167/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:27:00Z'
---

# 產生 URL

- [簡介](#introduction)
- [基礎](#the-basics)
   - [產生 URL](#generating-urls)
   - [存取目前 URL](#accessing-the-current-url)
- [命名 Route 的 URL](#urls-for-named-routes)
   - [簽名的 URL](#signed-urls)
- [Controller 動作的 URL](#urls-for-controller-actions)
- [預設值](#default-values)

<a name="introduction"></a>

## 簡介

Laravel 提供了多種輔助函式，來協助你為你的專案產生 URL。對於在樣板或 API 的 Response 中建立連結、或是產生要重新導向到網站中另一個部分的 Redirect Response 時特別實用。

<a name="the-basics"></a>

## 基礎

<a name="generating-urls"></a>

### 產生 URL

可使用 `url` 輔助函式來為你的網站產生任意 URL。產生的 URL 會自動使用網站目前收到 Request 的配置(HTTP 或 HTTPS) 與主機名稱：

    $post = App\Models\Post::find(1);
    
    echo url("/posts/{$post->id}");
    
    // http://example.com/posts/1

<a name="accessing-the-current-url"></a>

### 存取目前的 URL

若未提供路徑給 `url` 輔助函式，則會回傳 `Illuminate\Routing\UrlGenerator` 實體，使用該實體能讓我們存取有關目前 URL 的資訊：

    // 取得無 Query String 的目前 URL...
    echo url()->current();
    
    // 取得含 Query String 的目前 URL...
    echo url()->full();
    
    // 取得前一個 Request 的完整 URL...
    echo url()->previous();

這些方法也可以通過 `URL` [Facade](/docs/{{version}}/facades) 來存取：

    use Illuminate\Support\Facades\URL;
    
    echo URL::current();

<a name="urls-for-named-routes"></a>

## 命名 Route 的 URL

也可以使用 `route` 輔助函式來產生[命名 Route](/docs/{{version}}/routing#named-routes)的 URL。使用命名 Route 能讓我們不需要耦合到 Route 上實際定義的 URL，就能產生 URL。因此，即使 Route 的 URL 更改了，我們也不需要修改 `route` 函式的呼叫。舉例來說，假設我們的專案中有像這樣定義的 Route：

    Route::get('/post/{post}', function (Post $post) {
        //
    })->name('post.show');

若要產生這個 Route 的 URL，可以像這樣使用 `route` 輔助函式：

    echo route('post.show', ['post' => 1]);
    
    // http://example.com/post/1

當然，也可以使用 `route` 輔助函式來為有多個參數的 Route 產生 URL：

    Route::get('/post/{post}/comment/{comment}', function (Post $post, Comment $comment) {
        //
    })->name('comment.show');
    
    echo route('comment.show', ['post' => 1, 'comment' => 3]);
    
    // http://example.com/post/1/comment/3

若有陣列元素對應不上 Route 中定義的參數時，這些元素會被加到 URL 的查詢字串上：

    echo route('post.show', ['post' => 1, 'search' => 'rocket']);
    
    // http://example.com/post/1?search=rocket

<a name="eloquent-models"></a>

#### Eloquent Model

我們常常會使用 [Eloquent Model](/docs/{{version}}/eloquent) 的 Route 索引鍵 (通常是主索引鍵 - Primary Key) 來產生 URL。因此，我們也可以將 Eloquent Model 作為參數值傳入。`route` 輔助函式會自動取出 Model 的 Route 索引鍵：

    echo route('post.show', ['post' => $post]);

<a name="signed-urls"></a>

### 簽名 URL

Laravel 能讓我們輕鬆地為命名 Route 建立「簽名的 (Signed)」URL。這種 URL 的查詢字串中有個「簽名」雜湊，能讓 Laravel 驗證這個 URL 建立後是否有被修改。簽名的 URL 特別適用於一些可公開存取但又需要保護網址不被任意修改的 Route。

舉例來說，我們可以使用簽名 URL 來實作公開「解除訂閱」的連結，這個連結會寄給使用者。若要為命名路由建立簽名 URL，可使用 `URL` Facade 的 `signedRoute` 方法：

    use Illuminate\Support\Facades\URL;
    
    return URL::signedRoute('unsubscribe', ['user' => 1]);

若想產生在指定時間後會過期的臨時簽名 Route URL，可以使用 `temporarySignedRoute` 方法。Laravel 在驗證臨時簽名 Route URL 時，也會確保被編碼進簽名 URL 中的過期時間時戳尚未到期：

    use Illuminate\Support\Facades\URL;
    
    return URL::temporarySignedRoute(
        'unsubscribe', now()->addMinutes(30), ['user' => 1]
    );

<a name="validating-signed-route-requests"></a>

#### 驗證簽名 Route 的 Request

若要驗證連入的 Request 有正確的簽名，可在連入 `Request` 中呼叫 `hasValidSignature` 方法：

    use Illuminate\Http\Request;
    
    Route::get('/unsubscribe/{user}', function (Request $request) {
        if (! $request->hasValidSignature()) {
            abort(401);
        }
    
        // ...
    })->name('unsubscribe');

或者，我們也可以將 `Illuminate\Routing\Middleware\ValidateSignature` [Middleware](/docs/{{version}}/middleware) 指派到 Route 上。若 HTTP Kernel 的 `routeMiddleware` 陣列中沒有這個 Middleware 的話，請為該 Middleware 在該陣列中加個索引鍵：

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
    ];

在 Kernel 中註冊好 Middleware 後，就可以將其附加到 Route 上。若連入的 Request 沒有正確的簽名，該 Middleware 會自動回傳一個 `403` HTTP Response：

    Route::post('/unsubscribe/{user}', function (Request $request) {
        // ...
    })->name('unsubscribe')->middleware('signed');

<a name="responding-to-invalid-signed-routes"></a>

#### 回應無效簽名的 Route

若有人瀏覽了過期的簽名 URL，則會看到 `403` HTTP 狀態碼通用的錯誤頁面。不過，我們也可以在我們的例外處理常式 (Exception Handler) 上為 `InvalidSignatureException` 例外定義一個自訂的「renderable (可轉譯的)」閉包來自訂此行為。這個閉包應回傳 HTTP Response：

    use Illuminate\Routing\Exceptions\InvalidSignatureException;
    
    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (InvalidSignatureException $e) {
            return response()->view('error.link-expired', [], 403);
        });
    }

<a name="urls-for-controller-actions"></a>

## Controller 動作的 URL

`action` 方法可為給定的 Controller 動作產生 URL：

    use App\Http\Controllers\HomeController;
    
    $url = action([HomeController::class, 'index']);

若該 Controller 方法接受 Route 參數，則可將 Route 參數的關聯式陣列作為第二個引數傳給給函式：

    $url = action([UserController::class, 'profile'], ['id' => 1]);

<a name="default-values"></a>

## 預設值

在某個專案中，我們可能會想為特定的 URL 參數設定 Request 層級的預設值。舉例來說，假設我們的 Route 中很多都定義了 `{locale}` 參數：

    Route::get('/{locale}/posts', function () {
        //
    })->name('post.index');

若每次呼叫 `route` 輔助函式都要傳入 `locale` 的話會很麻煩。因此。我們可以使用 `URL::defaults` 方法來為這個參數定義目前 Request 中要套用的預設值。建議在某個 [Route Middleware](/docs/{{version}}/middleware#assigning-middleware-to-routes) 中呼叫這個方法，這樣我們才能存取目前的 Request：

    <?php
    
    namespace App\Http\Middleware;
    
    use Closure;
    use Illuminate\Support\Facades\URL;
    
    class SetDefaultLocaleForUrls
    {
        /**
         * Handle the incoming request.
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  \Closure  $next
         * @return \Illuminate\Http\Response
         */
        public function handle($request, Closure $next)
        {
            URL::defaults(['locale' => $request->user()->locale]);
    
            return $next($request);
        }
    }

為 `locale` 參數設定好預設值後，使用 `route` 輔助函式產生 URL 時就不需要再傳入這個值了：

<a name="url-defaults-middleware-priority"></a>

#### URL 預設與 Middleware 的優先順序

設定 URL 的預設值可能會影響 Laravel 處理 Model 繫結。因此，請[調整 Middleware 的優先順序]，讓設定 URL 預設的 Middleware 在 Laravel 的 `SubstituteBindings` 之前執行。可以通過在 HTTP Kernel 的 `$middlewarePriority`(/docs/{{version}}/middleware#sorting-middleware) 中將你的 Middleware 放在 `SubstituteBindings` 之前來達成。

`Illuminate\Foundation\Http\Kernel` 類別中定義了 `$middlewarePriority` 屬性。我們可以手動從該類別中複製這個定義並在專案的 HTTP Kernel 中複寫該屬性來修改其值：

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        // ...
         \App\Http\Middleware\SetDefaultLocaleForUrls::class,
         \Illuminate\Routing\Middleware\SubstituteBindings::class,
         // ...
    ];
