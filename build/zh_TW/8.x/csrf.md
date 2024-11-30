---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/39/en-zhtw'
updatedAt: '2023-02-11T10:27:00Z'
contributors: {  }
progress: 64.0
---

# CSRF 保護

- [簡介](#csrf-introduction)
- [預防 CSRF Request](#preventing-csrf-requests)
  - [排除 URI](#csrf-excluding-uris)
  
- [X-CSRF-Token](#csrf-x-csrf-token)
- [X-XSRF-Token](#csrf-x-xsrf-token)

<a name="csrf-introduction"></a>

## 簡介

CSRF (跨網站要求偽造，Cross-site Request Forgery) 是一種在通過登入使用者來進行未授權操作的惡意入侵方式。還好，Laravel 能讓你輕鬆保護網站免於遭受 [CSRF](https://zh.wikipedia.org/zh-tw/%E8%B7%A8%E7%AB%99%E8%AF%B7%E6%B1%82%E4%BC%AA%E9%80%A0) 攻擊。

<a name="csrf-explanation"></a>

#### CSRF 弱點說明

為避免讀者不熟悉 CSRF，我們來討論有關如何入侵該弱點的範例。假設專案中有個 `/user/email` 路由接受 `POST` 請求來修改登入使用者的 E-Mail 位址。顯然，這個路由預期有個 `email` 輸入欄位，其中包含了該使用者要使用的 E-Mail 位址。

若沒有 CSRF 保護，某個惡意網站可以建立一個 HTML 表單指向網站的 `/user/email` 路由，並送出假的使用者 E-Mail 位址：

    <form action="https://your-application.com/user/email" method="POST">
        <input type="email" value="malicious-email@example.com">
    </form>
    
    <script>
        document.forms[0].submit();
    </script>
若這個惡意網站在頁面載入後自動送出該表單，則惡意使用者只需要誘拐某個不經意的使用者瀏覽惡意網站，該使用者的 E-Mail 位址就會被修改。

為了防止此一弱點，我們需要在所有連入的 `POST`, `PUT`, `PATCH` 或 `DELETE` 請求上檢查某個私密 Session 值，該 Session 值必須是惡意網站無法存取的。

<a name="preventing-csrf-requests"></a>

## 防止 CSRF 請求

Laravel 會自動為每個有效的[使用者 Session](/docs/{{version}}/session) 產生一個由網站管理的 CSRF「權杖 (Token)」。該權杖會用來認證正在登入的使用者是否真的是實際發起該請求的使用者。由於該權杖儲存於使用者 Session 內，且會在每次 Session 重新產生的時候更改，因此惡意網站無法存取該權杖。

可以通過請求的 Session 或是 `csrf_token` 輔助函式存取目前 Session 的 CSRF 權杖：

    use Illuminate\Http\Request;
    
    Route::get('/token', function (Request $request) {
        $token = $request->session()->token();
    
        $token = csrf_token();
    
        // ...
    });
定義 "POST", "PUT", "PATCH", 或是 "DELETE" 的 HTML 表單時，應在表單內包含一個隱藏的 CSRF `_token` 欄位以讓 CSRF 保護 Middleware 認證該請求。為了方便起見，可以使用 `@csrf` Blade 指示詞來產生這個隱藏的權杖輸入欄位：

    <form method="POST" action="/profile">
        @csrf
    
        <!-- Equivalent to... -->
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
    </form>
預設包含在 `web` Middleware 群組內的 `App\Http\Middleware\VerifyCsrfToken` [Middleware](/docs/{{version}}/middleware) 會自動認證請求內的這個權杖是否符合儲存在 Session 內的權杖。若這兩個權杖相符，則我們就知道是登入使用者執行該請求的。

<a name="csrf-tokens-and-spas"></a>

### CSRF 權杖與 SPA

若正在建立使用 Laravel 作為 API 後端的 SPA，則可以考慮參考 [Laravel Sanctum 說明文件](/docs/{{version}}/sanctum)瞭解有關使用 API 認證與保護 CSRF 弱點的資訊。

<a name="csrf-excluding-uris"></a>

### 自 CSRF 保護內排除 URI

有時候，我們可能會想從 CSRF 保護內排除一些 URI。舉例來說，若正在使用 [Stripe](https://stripe.com) 來處理付款，並使用 Stripe 的 Webhook 系統，則需要將 Stripe Webhook 處理程式的路由從 CSRF 保護內排除，因為 Stripe 並不會知道要傳送什麼 CSRF 權杖給你的路由。

通常來說，應將這類路由放在 `web` Middleware 群組外。`App\Providers\RouteServiceProvider` 會將所有 `routes/web.php` 內的路由都套用到 `web` Middleware 群組內。不過，也可以通過將這些要排除的 URI 新增到 `VerifyCsrfToken` Middleware 內的 `$except` 屬性來排除這些路由：

    <?php
    
    namespace App\Http\Middleware;
    
    use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
    
    class VerifyCsrfToken extends Middleware
    {
        /**
         * The URIs that should be excluded from CSRF verification.
         *
         * @var array
         */
        protected $except = [
            'stripe/*',
            'http://example.com/foo/bar',
            'http://example.com/foo/*',
        ];
    }
> [!TIP]  
> 為了方便起見，在[執行測試](/docs/{{version}}/testing)時會自動禁用所有路由的 CSRF Middleware。

<a name="csrf-x-csrf-token"></a>

## X-CSRF-TOKEN

除了使用 POST 參數來檢查 CSRF 權杖外，`App\Http\Middleware\VerifyCsrfToken` Middleware 也會檢查 `X-CSRF-TOKEN` 請求標頭。舉例來說，我們可以將該權杖儲存於 HTML `meta` 標籤內：

    <meta name="csrf-token" content="{{ csrf_token() }}">
然後，可以讓如 jQuery 之類的函式庫自動將這個權杖加到所有請求標頭上。這樣就可為一些使用老舊 JavaScript 技術的 AJAX 程式提供簡單方便的 CSRF 保護：

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
<a name="csrf-x-xsrf-token"></a>

## X-XSRF-TOKEN

Laravel 將目前的 CSRF 權杖儲存為加密的 `XSRF-TOKEN` Cookie，會被包含在所有又框架產生的回應內。可以使用這個 Cookie 值來設定 `X-XSRF-TOKEN` 請求標頭。

由於一些 JavaScript 框架如 Angular 與 Axios 會自動在同源請求時將該 Cookie 的值放在 `X-XSRF-TOKEN` 標頭內，該 Cookie 就是為了提供開發者方便而傳送的。

> [!TIP]  
> 預設情況下，`resources/js/bootstrap.js` 檔案已包含了 Axios HTTP 函式庫，該函式庫會自動為你傳送 `X-XSRF-TOKEN` 標頭。
