---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/139/en-zhtw'
updatedAt: '2023-02-11T10:28:00Z'
contributors: {  }
progress: 46.75
---

# HTTP Response

- [建立 Response](#creating-responses)
  - [Attaching Headers to Responses](#attaching-headers-to-responses)
  - [Attaching Cookies to Responses](#attaching-cookies-to-responses)
  - [Cookies and Encryption](#cookies-and-encryption)
  
- [重新導向](#redirects)
  - [Redirecting to Named Routes](#redirecting-named-routes)
  - [Redirecting to Controller Actions](#redirecting-controller-actions)
  - [Redirecting to External Domains](#redirecting-external-domains)
  - [重新導向並帶上快閃存入的 Session 資料](#redirecting-with-flashed-session-data)
  
- [其他 Response 類型](#other-response-types)
  - [View Response](#view-responses)
  - [JSON Response](#json-responses)
  - [檔案下載](#file-downloads)
  - [File Response](#file-responses)
  
- [Response Macro](#response-macros)

<a name="creating-responses"></a>

## 建立 Response

<a name="strings-arrays"></a>

#### Strings and Arrays

所有的 Route 與 Controller 都應回傳 Response，以傳送回使用者的瀏覽器。Laravel 中提供了多種不同的方法來回傳 Response。最基礎的 Response 就是從 Route 或 Controller 中回傳字串。Laravel 會自動將字串轉換為完整的 HTTP 回應：

    Route::get('/', function () {
        return 'Hello World';
    });
除了從 Route 與 Controller 中回傳字串外，也可以回傳陣列。Laravel 會自動將陣列轉換為 JSON 回應：

    Route::get('/', function () {
        return [1, 2, 3];
    });
> [!NOTE]  
> 你知道你也可以從 Route 或 Controller 中回傳 [Eloquent Collection](/docs/{{version}}/eloquent-collections) 嗎？回傳的 Eloquent Collection 會自動被轉為 JSON。試試看吧！

<a name="response-objects"></a>

#### Response 物件

通常來說，我們不會只想在 Route 動作裡回傳簡單的字串或陣列。除了字串 / 陣列外，我們還可以回傳完整的 `Illuminate\Http\Response` 實體或 [View](/docs/{{version}}/views)。

若回傳完整的 `Response`，就可以自訂回應的 HTTP 狀態碼與標頭。`Response` 實體繼承自 `Symfony\Component\HttpFoundation\Response` 類別，該類別提供各種不同的方法來建立 HTTP Response：

    Route::get('/home', function () {
        return response('Hello World', 200)
                      ->header('Content-Type', 'text/plain');
    });
<a name="eloquent-models-and-collections"></a>

#### Eloquent Models and Collections

我們也可以從 Route 或 Controller 中回傳 [Eloquent ORM](/docs/{{version}}/eloquent) Model 或 Collection。回傳 Eloquent Model 或 Collection 時，Laravel 會自動將其轉換為 JSON 回應。當 Model 上有[隱藏屬性](/docs/{{version}}/eloquent-serialization#hiding-attributes-from-json)時，這些屬性也會被隱藏：

    use App\Models\User;
    
    Route::get('/user/{user}', function (User $user) {
        return $user;
    });
<a name="attaching-headers-to-responses"></a>

### Attaching Headers to Responses

請記得，大多數的 Response 方法都是可串連的 (Chainable)，讓我們能流暢地建構 Response 實體。舉例來說，我們可以在把 Respnse 傳回給使用者前使用 `header` 方法來加上一系列的標頭：

    return response($content)
                ->header('Content-Type', $type)
                ->header('X-Header-One', 'Header Value')
                ->header('X-Header-Two', 'Header Value');
或者，我們也可以使用 `withHeaders` 方法來指定一組包含標頭的陣列，來將該陣列加到 Response 上：

    return response($content)
                ->withHeaders([
                    'Content-Type' => $type,
                    'X-Header-One' => 'Header Value',
                    'X-Header-Two' => 'Header Value',
                ]);
<a name="cache-control-middleware"></a>

#### 快取 Controller Middleware

Laravel 中提供了一個 `cache.headers` Middleware，可以使用該 Middleware 來快速將 `Cache-Control` 標頭設定到一組 Route 上。必須提供與 Cache-Control 指示詞 (Directive) 對應的「蛇形命名法 (snake_case)」指示詞，並使用分號區隔。若指示詞列表中有 `etag`，則會自動以 Response 內容的 MD5 雜湊 (Hash) 來設定 ETag 識別元：

    Route::middleware('cache.headers:public;max_age=2628000;etag')->group(function () {
        Route::get('/privacy', function () {
            // ...
        });
    
        Route::get('/terms', function () {
            // ...
        });
    });
<a name="attaching-cookies-to-responses"></a>

### Attaching Cookies to Responses

可以使用 `cookie` 方法來將 Cookie 附加到外連的 `Illuminate\Http\Response` 實體。我們可以傳入 Cookie 的名稱、Cookie 值、以及單位為分鐘的有效期限給該方法：

    return response('Hello World')->cookie(
        'name', 'value', $minutes
    );
`cookie` 方法還接受一些更多的引數，但這些引數很少用。一般來說，這些引數的功能跟 PHP 原生的 [setcookie](https://secure.php.net/manual/en/function.setcookie.php) 方法一樣：

    return response('Hello World')->cookie(
        'name', 'value', $minutes, $path, $domain, $secure, $httpOnly
    );
若想要與連出的 Response 一起送出 Cookie，但目前還未有 Response 實體的話，可使用 `Cookie` Facade 來將 Cookie 「放到佇列」，以在 Response 送出的時候將其附加上去。`queue` (佇列) 方法接受要用來建立 Cookie 實體的引數。這些佇列中的 Cookie 會在連出 Response 被送到瀏覽器前被附加上去：

    use Illuminate\Support\Facades\Cookie;
    
    Cookie::queue('name', 'value', $minutes);
<a name="generating-cookie-instances"></a>

#### 產生 Cookie 實體

若想產生稍後可附加到 Response 實體上的 `Symfony\Component\HttpFoundation\Cookie` 實體，則可使用全域的 `cookie` 輔助函式。必須將產生的 Cookie 附加到 Response 實體，這些 Cookie 才會被送回用戶端：

    $cookie = cookie('name', 'value', $minutes);
    
    return response('Hello World')->cookie($cookie);
<a name="expiring-cookies-early"></a>

#### 提早讓 Cookie 過期

可以在連外 Response 上使用 `withoutCookie` 方法來讓 Cookie 無效，以將 Cookie 移除：

    return response('Hello World')->withoutCookie('name');
若還未有連外 Response 實體，則可以使用 `Cookie` Facade 的 `expire` 方法來讓 Cookie 過期：

    Cookie::expire('name');
<a name="cookies-and-encryption"></a>

### Cookies and Encryption

By default, thanks to the `Illuminate\Cookie\Middleware\EncryptCookies` middleware, all cookies generated by Laravel are encrypted and signed so that they can't be modified or read by the client. If you would like to disable encryption for a subset of cookies generated by your application, you may use the `encryptCookies` method in your application's `bootstrap/app.php` file:

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: [
            'cookie_name',
        ]);
    })
<a name="redirects"></a>

## 重新導向

Redirect Response (重新導向回應) 是 `Illuminate\Http\RedirectResponse` 類別的實體，Redirect Response 中包含了用來將使用者重新導向到另一個網址所需的一些標頭 (Header)。要產生 `RedirectResponse` 實體有幾個方法。最簡單的方法是使用全域的 `redirect` 輔助函式：

    Route::get('/dashboard', function () {
        return redirect('home/dashboard');
    });
有時候（如：使用者送出了無效的表單時），我們可能會想把使用者重新導向到使用者瀏覽的前一個位置。為此，我們可以使用全域的 `back` 輔助函式。由於這個功能使用了 [Session](/docs/{{version}}/session)，因此請確保呼叫 `back` 函式的 Route 有使用 `web` Middleware 群組：

    Route::post('/user/profile', function () {
        // Validate the request...
    
        return back()->withInput();
    });
<a name="redirecting-named-routes"></a>

### Redirecting to Named Routes

呼叫 `redirect` 輔助函式時若沒有帶上任何參數，則會回傳 `Illuminate\Routing\Redirector` 實體，這樣我們就可以呼叫 `Redirect` 實體上的所有方法。舉例來說，若要為某個命名 Route 產生 `RedirectResponse`，可以使用 `route` 方法：

    return redirect()->route('login');
若 Route 有參數，則可將這些 Route 參數作為第二個引數傳給 `route` 方法：

    // For a route with the following URI: /profile/{id}
    
    return redirect()->route('profile', ['id' => 1]);
<a name="populating-parameters-via-eloquent-models"></a>

#### Populating Parameters via Eloquent Models

若要重新導向的 Route 中有個可從 Eloquent Model 中填充的「ID」參數，則可傳入 Model。會自動取出 ID：

    // For a route with the following URI: /profile/{id}
    
    return redirect()->route('profile', [$user]);
若想自訂放在 Route 參數中的值，可在 Route 的參數定義中指定欄位 (`/profile/{id:slug}`)，或是在 Eloquent Model 中複寫 `getRouteKey` 方法：

    /**
     * Get the value of the model's route key.
     */
    public function getRouteKey(): mixed
    {
        return $this->slug;
    }
<a name="redirecting-controller-actions"></a>

### Redirecting to Controller Actions

也可以產生一個前往 [Controller 動作](/docs/{{version}}/controllers)的重新導向。為此，請將 Controller 與動作名稱傳入 `action` 方法：

    use App\Http\Controllers\UserController;
    
    return redirect()->action([UserController::class, 'index']);
若這個 Controller 的 Route 有要求參數，則可將這些參數作為第二個引數傳給 `action` 方法：

    return redirect()->action(
        [UserController::class, 'profile'], ['id' => 1]
    );
<a name="redirecting-external-domains"></a>

### Redirecting to External Domains

有時候，我們會需要重新導向到程式外部的網域。為此，可以呼叫 `away` 方法。該方法會建立一個 `RedirectResponse`，並且不會做額外的 URL 編碼或驗證：

    return redirect()->away('https://www.google.com');
<a name="redirecting-with-flashed-session-data"></a>

### 重新導向時帶上快閃存入的 Session 資料

通常，我們在重新導向到新網址的時候，也會[將資料快閃存入 Session]。一般來說，這種情況通常是當某個動作順利進行，而我們將成功訊息寫入 Session 時。為了方便起見，我們可以建立一個 `RedirectResponse` 實體，並以一行流暢的方法串連呼叫來將資料快閃存入 Session：

    Route::post('/user/profile', function () {
        // ...
    
        return redirect('dashboard')->with('status', 'Profile updated!');
    });
使用者被重新導向後，我們就可以從 [Session](/docs/{{version}}/session) 中顯示出剛才快閃存入的資料。舉例來說，我們可以使用 [Blade 語法](/docs/{{version}}/blade)：

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif
<a name="redirecting-with-input"></a>

#### 重新導向時帶上輸入

可以使用 `RedirectResponse` 實體提供的 `withInput` 方法來在將使用者重新導向到新位置前先將目前 Request 的輸入資料快閃存入 Session 中。通常來說我們會在表單驗證錯誤時這麼做。將輸入資料快閃存入 Session 後，我們就可以在下一個 Request 中輕鬆地[取得這些資料](/docs/{{version}}/requests#retrieving-old-input)並將其填回表單中：

    return back()->withInput();
<a name="other-response-types"></a>

## 其他 Response 類型

`response` 輔助函式還能產生一些其他類型的 Response 實體。呼叫 `response` 輔助函式時若未帶入任何引數，則會回傳 `Illuminate\Contracts\Routing\ResponseFactory` [Contract](/docs/{{version}}/contracts) 的實作。這個 Contract 提供了數種用來建立 Response 的實用方法：

<a name="view-responses"></a>

### View Response

若有需要控制 Response 的狀態與標頭，但 Response 的內容又需要是 [View] 時，則可使用 `view` 方法：

    return response()
                ->view('hello', $data, 200)
                ->header('Content-Type', $type);
當然，若不需要傳入自訂 HTTP 狀態或自訂標頭的話，應該使用全域的 `view` 輔助函式。

<a name="json-responses"></a>

### JSON Response

`json` 方法會自動將 `Content-Type` 標頭設為 `application/json`，並使用 `json_encode` PHP 函式來將任何給定的陣列轉換為 JSON：

    return response()->json([
        'name' => 'Abigail',
        'state' => 'CA',
    ]);
若想建立 JSONP Response，則可在使用 `json` 方法時搭配使用 `withCallback` 方法：

    return response()
                ->json(['name' => 'Abigail', 'state' => 'CA'])
                ->withCallback($request->input('callback'));
<a name="file-downloads"></a>

### 檔案下載

可使用 `download` 方法來產生一個強制使用者在給定路徑上下載檔案的 Response。`download` 方法接受檔案名稱作為其第二個引數，該引數用來判斷使用者看到的檔案名稱。最後，我們可以傳入一組包含 HTTP 標頭的陣列作為該方法的第三個引數：

    return response()->download($pathToFile);
    
    return response()->download($pathToFile, $name, $headers);
> [!WARNING]  
> Symfony HttpFoundation —— 負責處理檔案下載的類別 —— 要求下載的檔案名稱必須為 ASCII。

<a name="streamed-downloads"></a>

#### 串流下載

有時候，我們會需要在不寫入磁碟的情況下將某個操作的字串結果轉變成可下載的 Response。這時可以使用 `streamDownload` 方法。這個方法接受一個回呼、檔案名稱、以及一個可選的標頭陣列作為其引數：

    use App\Services\GitHub;
    
    return response()->streamDownload(function () {
        echo GitHub::api('repo')
                    ->contents()
                    ->readme('laravel', 'laravel')['contents'];
    }, 'laravel-readme.md');
<a name="file-responses"></a>

### File Response

The `file` method may be used to display a file, such as an image or PDF, directly in the user's browser instead of initiating a download. This method accepts the absolute path to the file as its first argument and an array of headers as its second argument:

    return response()->file($pathToFile);
    
    return response()->file($pathToFile, $headers);
<a name="response-macros"></a>

## Response Macro

若想定義可在各個 Route 或 Controller 內重複使用的自訂 Response 方法，可使用 `Response` Facade 上的 `macro` 方法。通常來說，該方法應在某個 [Service Provider](/docs/{{version}}/providers) 的 `boot` 方法內呼叫，如 `App\Providers\AppServiceProvider` Service Provider：

    <?php
    
    namespace App\Providers;
    
    use Illuminate\Support\Facades\Response;
    use Illuminate\Support\ServiceProvider;
    
    class AppServiceProvider extends ServiceProvider
    {
        /**
         * Bootstrap any application services.
         */
        public function boot(): void
        {
            Response::macro('caps', function (string $value) {
                return Response::make(strtoupper($value));
            });
        }
    }
`macro` 方法接受一個名稱作為其第一個引數，以及閉包作為其第二個引數。當在 `ResponseFactory` 的實作或 `response` 輔助函式上呼叫給定的 Macro 名稱時，會執行該 Macro 的閉包：

    return response()->caps('foo');