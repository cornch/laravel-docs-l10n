---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/11/en-zhtw'
updatedAt: '2024-06-30T08:17:00Z'
contributors: {  }
progress: 53.89
---

# 認證

- [簡介](#introduction)
  - [入門套件](#starter-kits)
  - [資料庫選擇](#introduction-database-considerations)
  - [生態鏈概覽](#ecosystem-overview)
  
- [「驗證」快速入門](#authentication-quickstart)
  - [安裝入門套件](#install-a-starter-kit)
  - [取得已登入的使用者](#retrieving-the-authenticated-user)
  - [受保護的 Route](#protecting-routes)
  - [登入頻率限制](#login-throttling)
  
- [手動登入使用者](#authenticating-users)
  - [記住使用者](#remembering-users)
  - [其他認證方法](#other-authentication-methods)
  
- [HTTP Basic 認證](#http-basic-authentication)
  - [Stateless HTTP Basic 認證](#stateless-http-basic-authentication)
  
- [登出](#logging-out)
  - [登出其他裝置上的工作階段](#invalidating-sessions-on-other-devices)
  
- [確認密碼](#password-confirmation)
  - [設定](#password-confirmation-configuration)
  - [路由](#password-confirmation-routing)
  - [保護 Route](#password-confirmation-protecting-routes)
  
- [新增自訂 Guard](#adding-custom-guards)
  - [閉包的 Request Guard](#closure-request-guards)
  
- [新增自訂的 User Provider](#adding-custom-user-providers)
  - [UserProvider Contract](#the-user-provider-contract)
  - [Authenticatable Contract](#the-authenticatable-contract)
  
- [社群登入](/docs/{{version}}/socialite)
- [事件](#events)

<a name="introduction"></a>

## 簡介

許多網頁 App 都提供了讓使用者向 App 認證以及「登入」的功能。在網頁 App 上實作這些功能可能會很複雜，而且可能會有些風險。為此，Laravel 竭力為你提供了用於快速、安全、且簡單地實作認證功能的工具。

在 Laravel 的核心中，認證功能是通過「Guard」與「Provider」來提供的。Guard 用來定義使用者在每個請求上是如何被認證的。舉例來說，Laravel 附帶了一個 `session` Guard，會通過 Session 儲存空間與 Cookie 來維護認證狀態。

Provider 則定義了要如何從長期儲存空間內取得使用者。Laravel 內建支援使用 [Eloquent](/docs/{{version}}/eloquent) 或資料庫 Query Builder 來取得使用者。不過，你也可以自行依據需求額外定義其他 Provider。

你的專案的認證設定檔位於 `config/auth.php` 內。該檔案包含了多個有文件說明的選項，可以調整 Laravel 認證服務的行為。

> [!NOTE]  
> Guard 與 Provider 與「角色」以及「權限」不同，不應溷肴。要瞭解如何依照權限來授權使用者的方法，請參考 [授權](/docs/{{version}}/authorization) 說明文件。

<a name="starter-kits"></a>

### 入門套件

想要快速入門嗎？請在全新的 Laravel 專案內安裝一個 [Laravel 專案入門套件](docs/{{version}}/starter-kits)。完成資料庫遷移後，在瀏覽器上開啟 `/register` 或其他任何設定給應用程式的 URL。這些入門套件會幫你搞定整個認證系統的 Scaffolding。

**就算最後不會在 Laravel 專案上使用任何一種入門套件，安裝 [Laravel Breeze](/docs/{{version}}/starter-kits#laravel-breeze) 入門套件也是個學習如何在實際 Laravel 專案上實作所有 Laravel 認證功能的好機會。** 由於 Laravel Breeze 會為你建立好認證 Controller、路由、以及 View，因此可以通過閱讀這些檔案的程式碼來學習可如何實作 Laravel 的認證功能。

<a name="introduction-database-considerations"></a>

### 資料庫選擇

預設情況下，Laravel 在 `app/Models` 資料夾中包含了一個 `App\Models\User` [Eloquent Model](/docs/{{version}}/eloquent)。這個 Model 可以用在預設的 Eloquent 登入 Driver 上。若你的專案不使用 Eloquent，則可以使用 `database` 認證 Provider，該 Provider 使用 Laravel 的 Query Builder。

為 `App\Models\User` 模型建立資料庫結構時，請確保密碼欄位的長度至少有 60 個字元。當然，在新建立的 Laravel 專案中， `users` 資料表遷移檔已經建立了超過這個長度的欄位。

另外，也請求確保 `users`（或其相應的）資料表有包含一個 Nullable、100 字元的 `remember_token` 字串欄位。該欄位會用來在使用者登入時勾選「記住我」選項時使用。同樣地，在新建立的 Laravel 專案中，`users` 資料表遷移檔已經有包含了這個欄位。

<a name="ecosystem-overview"></a>

### 生態鏈概覽

Laravel 提供了多個有關認證的套件。在繼續之前，我們先來看看這些 Laravel 中的一般認證生態鏈，並討論各個套件預設的目的。

首先，我們先來看看認證是怎麼運作的。在使用網頁瀏覽器時，使用者會通過登入表格來提供他們的使用者名稱以及密碼。若帳號密碼正確，則網站會將已認證使用者的資訊儲存在使用者的 [Session](/docs/{{version}}/session) 中。Cookie 會傳給瀏覽器，其中包含了 Session ID。這樣一來，我們就可以通過正確的 Session 來將接下來向網站發起的請求與使用者連結起來。收到 Session Cookie 後，網站會依據 Session ID 來取得 Session 資料。請注意，由於認證資訊已經被保存在 Session 中了，所以該使用者將被視為「已認證」。

當遠端服務需要認證來存取 API 時，通常不會在認證上使用 Cookie，因為這種情況下並沒有網頁瀏覽器。取而代之地，遠端服務會在每個請求時帶上 API 權杖。網站可以通過將傳入的權杖與包含了有效 API 憑證的資料表進行比對來「認證」該請求，並將其視為是有與 API 權杖管理的使用者所進行的操作。

<a name="laravels-built-in-browser-authentication-services"></a>

#### Laravel 的內建瀏覽器認證服務

Laravel 的內建認證與 Session 服務通常會通過 `Auth` 與 `Session` Facade 來存取。這些功能為從瀏覽器發起的請求提供了基於 Cookie 的認證功能。這些功能也提供了能認證使用者憑證與認證使用者的方法。此外，這些服務也會自動將正確的資料儲存在使用者的 Session 內，並為使用者核發 Session Cookie。本文件中包含了如何使用這些服務的討論。

**應用程式入門套件**

我們剛才也在本文中討論過，你可以通過手動操作這些認證服務來為專案建立一套「認證層」。但，為了讓你可以更快入門，我們也釋出了[一些免費套件](/docs/{{version}}/starter-kits)來提供更快速且現代化的完整認證層 Scaffolding：[Laravel Breeze](/docs/{{version}}/starter-kits#laravel-breeze), [Laravel Jetstream](/docs/{{version}}/starter-kits#laravel-jetstream)，與 [Laravel Fortify](/docs/{{version}}/fortify)。

**Laravel Breeze** 是一個簡單且最小化實作出所有 Laravel 驗證功能的套件，包含登入、註冊、密碼重設、電子郵件驗證、以及密碼確認。Laravel Breeze 的檢視器層是通過簡單的 [Blade 樣板](/docs/{{version}}/blade) 搭配 [Tailwind CSS](https://tailwindcss.com) 提供樣式組合而成的。若要瞭解有關 Laravel Breeze，請參考 Laravel 的 [應用程式入門套件](/docs/{{version}}/starter-kits) 說明文件。

**Laravel Fortify** 是一種用於 Laravel 的無周邊（Headless）驗證後端。Laravel Fortify 實作了許多可在此說明文件中找到的功能，包含基於 Cookie 的驗證以及其他如二步驟驗證與電子郵件驗證等功能。Fortify 也為 Laravel Jetstream 提供了驗證後端。或者，也可以通過與 [Laravel Sanctum](/docs/{{version}}/sanctum) 組合使用來為需要在 Laravel 中進行驗證的 SPA（Single Page Application，單一頁面應用程式）提供驗證功能。

**[Laravel Jetstream](https://jetstream.laravel.com)** 是一個強大的入門套件，該套件使用者 Laravel Fortify 的驗證服務，並將其通過 [Tailwind CSS](https://tailwindcss.com), [Livewire](https://laravel-livewire.com)，與／或 [Inertia](https://inertiajs.com) 來提供美麗且現代的 UI。Laravel Jetstream 也提供了對二步驟驗證、團隊支援、瀏覽器啟程管理、個人檔案管理、以及內建與 [Laravel Sanctum](/docs/{{version}}/sunctum) 整合來提供 API 權杖驗證的可選支援。Laravel 提供的 API 驗證功能將在下方討論。

<a name="laravels-api-authentication-services"></a>

#### Laravel 的 API 認證服務

Laravel 提供了兩個可選的套件來協助你管理 API 權杖以及認證以 API 權杖建立的請求：[Passport](/docs/{{version}}/passport) 與 [Sanctum](/docs/{{version}}/sanctum)。請注意，這些函式庫與 Laravel 內建的基於 Cookie 的認證函式庫並不互相衝突。這些函式庫的重點都是在提供 API 進行權杖認證，而內建的認證服務則著重於基於 Cookie 的瀏覽器認證。許多網站都會同時使用 Laravel 內建的基於 Cookie 的認證服務，以及其中一種 Laravel 的 API 認證套件。

**Passport**

Passport 是一個 OAuth2 認證 Provider，提供了多種 OAuth2 的「Grant Type」來讓你簽發各種不同的權杖。通常來說，對於 API 認證來說 Passport 是一個很強大很複雜的套件。但，大多數的網站並不需要 OAuth2 規格所提供的那些複雜的功能。這些功能只會讓使用者與開發人員都搞不清楚要怎麼用。而且，很多開發人員都搞不懂要怎麽樣使用 Passport 這樣的 OAuth2 認證 Provider 來認證 SPA App 或手機 App。

**Sanctum**

為了處理 OAuth2 很複雜的問題以及解決開發者的困惑，我們希望建立一種更簡單、更精簡的認證套件。這個套件不但要可以處理來自網頁瀏覽器的第一方網頁請求，也要可以處理通過權杖發起的 API 請求。我們在 [Laravel Sanctum](/docs/{{version}}/sanctum) 中解決了這些問題。對於「有提供第一方 Web UI 的 API」、「有獨立 Laravel 後端的 SPA」、或是「有提供手機 App 的網站」，Laravel Sanctum 目前是我們推薦與建議的認證套件。

Laravel Sanctum 混合了網頁認證與 API 認證，可以用來處理整個網站的認證流程。怎麼做到的呢？當使用 Sanctum 的網站收到請求後，Sanctum 會先判斷該請求是否有包含了認證 Session 的 Session Cookie。Sanctum 是通過呼叫我們稍早討論過的 Laravel 內建認證服務來達成此一功能的。若該請求的 Session Cookie 未被認證過，則 Sanctum 接著會檢查請求的 API 權杖。若有找到 API 權杖，則 Sanctum 會使用該權杖來認證請求。要瞭解更多有關此一流程的資訊，請參考 Sanctum 的[「運作方式」](/docs/{{version}}/sanctum#how-it-works)說明文件。

Laravel Sanctum 是我們在 [Laravel Jetstream](https://jetstream.laravel.com) 專案入門套件中選擇的 API 套件，因為我們認為該套件最符合大多數網頁 App 的認證需求。

<a name="summary-choosing-your-stack"></a>

#### 總結與選擇你的 Stack

總結一下，若你的專案會通過瀏覽器來存取，而且你只會製作單一一個 Laravel 專案，則應使用 Laravel 的內建認證服務。

若你的專案中包含了會被第三方使用的 API，則應選擇 [Passport](/docs/{{version}}/passport) 或 [Sanctum](/docs/{{version}}/sanctum) 來為專案提供 API 權杖認證。一般來說，應該儘量先選擇 Sanctum，因為 Sanctum 對 API 認證、SPA 認證、以及行動裝置認證來說是最簡單且完整的解決方案，而且也支援「範圍 (Scope)」與「權限 (Ability)」。

如果你正在製作由 Laravel 提供後端的 SPA，則應該使用 [Laravel Sanctum](/docs/{{version}}/sanctum)。在使用 Sanctum 時，會需要[手動實作你自己的認證路由後端](#authenticating-users)，或是使用 [Laravel Fortify](/docs/{{version}}/fortify) 來作為 Headless 的認證後端服務，來為註冊、密碼重設、電子郵件認證等功能提供路由與 Controller。

當你的專案真的需要所有 OAuth2 規格所提供的功能時，就可以選擇使用 Passport。

此外，若想要快速入門，我們誠摯推薦你使用 [Laravel Breeze](/docs/{{version}}/starter-kits#laravel-breeze) 來作為快速建立新 Laravel 專案的方法。Laravel Breeze 已經使用了我們偏好的認證 Stack —— 使用 Laravel 的內建認證服務與 Laravel Sanctum。

<a name="authentication-quickstart"></a>

## 「認證」快速入門

> [!WARNING]  
> 這部分的文件會討論通過 [Laravel 應用程式入門套件](/docs/{{version}}/starter-kits) 來驗證使用者，這些入門套件包含了能協助你快速開始的 UI Scaffolding。若你想要直接與 Laravel 的驗證系統整合，請參考 [手動驗證使用者](#authenticating-users) 內的說明文件。

<a name="install-a-starter-kit"></a>

### 安裝一種入門套件

首先，需要[安裝一個 Laravel 專案入門套件](/docs/{{version}}/starter-kits)。我們目前的入門套件 —— Laravel Breeze 與 Laravel Jetstream —— 都是讓你的 Laravel 新專案有個美觀設計的起始點。

Laravel Breeze 是一個簡單且最小化實作出所有 Laravel 認證功能的套件，包含登入、註冊、密碼重設、電子郵件認證、以及密碼確認。Laravel Breeze 的檢視器層是通過簡單的 [Blade 樣板](/docs/{{version}}/blade) 搭配 [Tailwind CSS](https://tailwindcss.com) 提供樣式組合而成的。Breeze 也提供了一個使用 Vue 或 React 的基於 [Inertia](https://inertiajs.com) 的 Scaffolding 選項。

[Laravel Jetstream](https://jetstream.laravel.com) 是一個更複雜的專案入門套件，其中包含了使用 [Livewire](https://laravel-livewire.com) 或 [Inertia 與 Vue](https://inertiajs.com) 來對應用程式 Scaffolding 的支援。此外，Jetstream 也提供了對二步驟認證、團隊、個人檔案管理、瀏覽器啟程管理、通過 [Laravel Sanctum](/docs/{{version}}/sanctum) 提供的 API 支援、帳號刪除…等功能的可選支援。

<a name="retrieving-the-authenticated-user"></a>

### 取得已登入的使用者

安裝完認證入門套件並讓使用者在網站內註冊與認證後，我們通常需要與目前已登入的使用者進行互動。在處理連入請求時，我們可以通過 `Auth` Facade 的 `user` 方法來存取已登入的使用者：

    use Illuminate\Support\Facades\Auth;
    
    // Retrieve the currently authenticated user...
    $user = Auth::user();
    
    // Retrieve the currently authenticated user's ID...
    $id = Auth::id();
另外，使用者認證後，也可以通過 `Illuminate\Http\Request` 實體來存取已登入的使用者。請記得，有型別提示的類別會自動被插入到 Controller 方法內。只要型別提示 `Illuminate\Http\Request` 物件，就可以方便地通過 Request 的 `user` 方法來在任何 Controller 方法內存取已登入的使用者：

    <?php
    
    namespace App\Http\Controllers;
    
    use Illuminate\Http\Request;
    
    class FlightController extends Controller
    {
        /**
         * Update the flight information for an existing flight.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\Response
         */
        public function update(Request $request)
        {
            // $request->user()
        }
    }
<a name="determining-if-the-current-user-is-authenticated"></a>

#### 判斷目前使用者是否已認證

若要判斷建立連入 HTTP 請求的使用者是否已登入，可以使用 `Auth` Facade 的 `check` 方法。該方法會在使用者已登入的時候回傳 `true`：

    use Illuminate\Support\Facades\Auth;
    
    if (Auth::check()) {
        // The user is logged in...
    }
> [!NOTE]  
> 雖然可以使用 `check` 方法來判斷使用者是否已登入，但通常可以使用 Middleware 來在使用者存取特定 Route 或 Controller 前就先驗證該使用者是否已登入。關更多詳情，參考[保護 Route](/docs/{{version}}/authentication#protecting-routes) 內的說明文件。

<a name="protecting-routes"></a>

### 保護路由

[路由 Middleware](/docs/{{version}}/middleware) 可以用來只允許已認證的使用者存取指定的路由。Laravel 內建了一個 `auth` Middleware，這個 Middleware為 `Illuminate\Auth\Middleware\Authenticate` 類別。由於該 Middleware已預先在專案中的 HTTP Kernel 內註冊好了，所以只需要在路由定義內加上這個 Middleware 即可：

    Route::get('/flights', function () {
        // Only authenticated users may access this route...
    })->middleware('auth');
<a name="redirecting-unauthenticated-users"></a>

#### 重新導向未登入的使用者

當 `auth` Middleware 偵測到未登入的使用者，`auth` Middleware 會將使用者重新導向到 `login` 這個[帶名稱的路由](/docs/{{version}}/routing#named-routes)上。可以通過更新專案中 `app/Http/Middleware/Authenticate.php` 檔案內的 `redirectTo` 方法來更改此一行為。

    /**
     * Get the path the user should be redirected to.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        return route('login');
    }
<a name="specifying-a-guard"></a>

#### 指定 Guard

在將 `auth` 中間層加到路由時，可以指定要使用哪個「Guard」來認證使用者。可以通過指定 `auth.php` 設定檔中 `guards` 陣列內對應的鍵值來指定 Guard：

    Route::get('/flights', function () {
        // Only authenticated users may access this route...
    })->middleware('auth:admin');
<a name="login-throttling"></a>

### 登入頻率限制

若使用 Laravel Breeze 或 Laravel Jetstream [入門套件](/docs/{{version}}/starter-kits)，會自動將頻率限制套用到登入限制上。預設情況下，若使用者嘗試了數次仍未提供正確的帳號密碼，則將在一分鐘之內都無法登入。登入限制是基於每個使用者的使用者名稱或電子郵件，以及其 IP 位址來區分的。

> [!NOTE]  
> 若想在專案中的其他 Route 上提供頻率限制，請參考[頻率限制的說明文件](/docs/{{version}}/routing#rate-limiting)。

<a name="authenticating-users"></a>

## 手動認證使用者

不一定要使用 Laravel [專案入門套件](/docs/{{version}}/starter-kits) 內包含的認證 Scaffolding。若決定不使用這些 Scaffolding 的話，就需要直接使用 Laravel 的認證類別來處理使用者認證。別擔心，這只是小菜一碟！

我們會通過 `Auth` [Facade](/docs/{{version}}/facades) 來存取 Laravel 的認證服務。因此，我們需要確保有在該類別的最上方引入 `Auth` Facade。接著，還要確認一下我們的 `attempt` 方法。這個 `attempt` 方法通常會用來處理來自網站「登入」表單的認證嘗試。若成功認證，則應該重新產生使用者的 [session](/docs/{{version}}/session) 來防止 [Session Fixation (英語)](https://en.wikipedia.org/wiki/Session_fixation)：

    <?php
    
    namespace App\Http\Controllers;
    
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    
    class LoginController extends Controller
    {
        /**
         * Handle an authentication attempt.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\Response
         */
        public function authenticate(Request $request)
        {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);
    
            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();
    
                return redirect()->intended('dashboard');
            }
    
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }
    }
`attempt` 方法接收包含了索引鍵／值配對的陣列作為其第一個引數。陣列中的值會被用來在資料庫資料表中尋找使用者。因此，在上方的範例中，使用者會依照 `email` 欄位中的值來取得。若找到該使用者，則會將資料庫中儲存的密碼雜湊跟陣列中的 `password` 值進行比對。請不要將連入請求的 `password` 進行雜湊，因為框架會自動在與資料庫中雜湊密碼比對時自動對齊進行雜湊。當兩個雜湊密碼相符合時，將開始該使用者的認證 Session。

請記得，Laravel 的認證服務會依據認證 Guard 的「provider」設定來從資料庫中取得使用者。預設的 `config/auth.php` 設定檔中使用了 Eloquent User Provider，並使用 `App\Models\User` Model 來取得使用者。可以依照專案需求來在設定檔中更改這些值。

當認證成功後，`attempt` 方法會回傳 `true`。否則，會回傳 `false`。

Laravel 的重新導向程式中提供的 `intended` 方法可以用來將使用者導向到他們被認證中間層攔截存取前正在嘗試存取的 URL。可以提供一個遞補的 URI 給該方法，以免沒有預期的目的地。

<a name="specifying-additional-conditions"></a>

#### 指定額外條件

若有需要的話，也可以在認證查詢上指定除了使用者的電子郵件與密碼外的額外查詢條件。為此，只需要將查詢條件加到傳給 `attempt` 方法的陣列中即可。如，我們可以認證使用者有被標示為「啟用」：

    if (Auth::attempt(['email' => $email, 'password' => $password, 'active' => 1])) {
        // Authentication was successful...
    }
對於複雜的查詢條件，可以提供在帳號密碼陣列中提供一個閉包。Laravel 會以查詢實體來呼叫此閉包，讓你可以依據專案需求自定查詢：

    if (Auth::attempt([
        'email' => $email, 
        'password' => $password, 
        fn ($query) => $query->has('activeSubscription'),
    ])) {
        // Authentication was successful...
    }
> [!WARNING]  
> 不一定要像這個例子一樣使用 `email` 欄位，此處只是以 E-Mail 當作範例。可以使用任何在資料庫中相當於「使用者名稱」的欄位來認證。

`attemptWhen` 方法所接收的第二個引數為閉包，該閉包可用來在使用者實際登入前，對正在嘗試登入的使用者執行更進一步的檢驗。這個閉包會收到正在嘗試登入的使用者，並應回傳 `true` 或 `false` 來表示使用者是否可登入：

    if (Auth::attemptWhen([
        'email' => $email,
        'password' => $password,
    ], function ($user) {
        return $user->isNotBanned();
    })) {
        // Authentication was successful...
    }
<a name="accessing-specific-guard-instances"></a>

#### 存取特定 Guard 實體

通過 `Auth` Facade 的 `guard` 方法，可以指定使用者登入時要使用哪個 Guard 實體。如此一來便能為專案中不同部分的登入功能使用不同的 Authenticatable Model 或使用者資料表。

傳如 `guard` 方法的 Guard 名稱應為 `auth.php` 設定檔中設定的其中一個 Guard 名稱：

    if (Auth::guard('admin')->attempt($credentials)) {
        // ...
    }
<a name="remembering-users"></a>

### 記住使用者

許多網站的登入表單內都有一個「記住我」勾選框。若想為你的網站提供「記住我」功能，可以傳入布林值給 `attempt` 方法的第二個引數。

當該值為 `true` 時，Laravel 會永久儲存使用者的登入狀態，直到使用者手動登出。你的 `users` 資料表必須包含 `remember_token` 字串欄位，該欄位用來儲存「記住我」權杖。在新的 Laravel 專案中，`users` 資料表的 Migration 已包含了此欄位：

    use Illuminate\Support\Facades\Auth;
    
    if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
        // The user is being remembered...
    }
If your application offers "remember me" functionality, you may use the `viaRemember`  method to determine if the currently authenticated user was authenticated using the "remember me" cookie:

    use Illuminate\Support\Facades\Auth;
    
    if (Auth::viaRemember()) {
        // ...
    }
<a name="other-authentication-methods"></a>

### 其他認證方法

<a name="authenticate-a-user-instance"></a>

#### 認證使用者實體

若需要將目前已認證使用者設為一個現有的使用者實體，可以將該實體傳入 `Auth` Facade 的 `login` 方法內。給定的使用者實體必須要實作 `Illuminate\Contracts\Auth\Authenticatable` [Contract](/docs/{{version}}/contracts)。Laravel 中的 `App\Models\User` Model 已經實作了這個介面。這種認證的方法適用與已有有效使用者實體的情況，如使用者在網站上註冊之後：

    use Illuminate\Support\Facades\Auth;
    
    Auth::login($user);
可以將布林值傳入 `login` 方法的第二個引數。這個布林值會用來判斷該登入 Session 是否可套用「記住我」功能。請記得，啟用該功能就表示這個 Session 將永久可用，直到使用者手動登出：

    Auth::login($user, $remember = true);
若有需要，可以在呼叫 `login` 方法前指定一個認證 Guard：

    Auth::guard('admin')->login($user);
<a name="authenticate-a-user-by-id"></a>

#### 通過 ID 認證使用者

若要使用資料庫中的主索引鍵 (Primary Key) 來認證使用者，可以使用 `loginUsingId` 方法。該方法接受要用來認證使用者的主索引鍵值：

    Auth::loginUsingId(1);
可以將布林值傳入 `loginUsingId` 方法的第二個引數。這個布林值會用來判斷該登入 Session 是否可套用「記住我」功能。請記得，啟用該功能就標示該 Session 將永久可用，直到使用者手動登出：

    Auth::loginUsingId(1, $remember = true);
<a name="authenticate-a-user-once"></a>

#### 僅認證使用者一次

可以使用 `once` 方法來只在單一請求內認證使用者。呼叫此方法時不會使用到 Session 或 Cookie：

    if (Auth::once($credentials)) {
        //
    }
<a name="http-basic-authentication"></a>

## HTTP 基本認證

[HTTP 基本認證](https://zh.wikipedia.org/zh-tw/HTTP%E5%9F%BA%E6%9C%AC%E8%AE%A4%E8%AF%81)提供了一種不需要設定專屬「登入」頁面而快速認證專案中使用者的方法。要進行 HTTP 基本認證，請將 `auth.basic` [Middleware](/docs/{{version}}/middleware) 加到路由上。`auth.basic` Middleware 已包含在 Laravel 框架內，不需要自行定義：

    Route::get('/profile', function () {
        // Only authenticated users may access this route...
    })->middleware('auth.basic');
將該 Middleware 加到路由上後，在瀏覽器上存取該路由時會自動被提示帳號密碼。預設情況下，`auth.basic` 中間層會假設 `email` 欄位是 `users` 資料表中的使用者「帳號」欄位。

<a name="a-note-on-fastcgi"></a>

#### FastCGI 備註

若使用 PHP FastCGI 與 Apache 來執行 Laravel 專案，則 HTTP 基本認證可能不會正確運作。要修正這個問題，請將下列幾行加到專案的 `.htaccess` 檔中：

```apache
RewriteCond %{HTTP:Authorization} ^(.+)$
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
```
<a name="stateless-http-basic-authentication"></a>

### 無周邊 HTTP 基本認證

也可以於不在 Session 內寫入可識別使用者 Cookie 的情況下使用 HTTP 基本認證。這個作法通常適用於想通過 HTTP 認證來認證網站 API 請求時。為此，請先[定義一個 Middleware](/docs/{{version}}/middleware)，並在該 Middleware 中呼叫 `onceBasic` 方法。若 `onceBasic` 方法無回傳值，則該請求才會接著被傳遞到專案中：

    <?php
    
    namespace App\Http\Middleware;
    
    use Illuminate\Support\Facades\Auth;
    
    class AuthenticateOnceWithBasicAuth
    {
        /**
         * Handle an incoming request.
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  \Closure  $next
         * @return mixed
         */
        public function handle($request, $next)
        {
            return Auth::onceBasic() ?: $next($request);
        }
    
    }
接著，請[註冊路由 Middleware](/docs/{{version}}/middleware#registering-middleware)，並將其附加到路由上：

    Route::get('/api/user', function () {
        // Only authenticated users may access this route...
    })->middleware('auth.basic.once');
<a name="logging-out"></a>

## 登出

若要手動將使用者登出網站，可以使用 `Auth` Facade 提供的 `logout` 方法。該方法會從使用者的 Session 中將認證資訊移除，如此一來，接下來的請求都會是已登出的狀態。

除了呼叫 `logout` 方法外，也建議將使用者的 Session 無效化，並為使用者重新產生 [CSRF 權杖](/docs/{{version}}/csrf)。登出使用者後，我們通常會將使用者重新導向回網站根目錄：

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    
    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::logout();
    
        $request->session()->invalidate();
    
        $request->session()->regenerateToken();
    
        return redirect('/');
    }
<a name="invalidating-sessions-on-other-devices"></a>

### 將其他裝置上的 Session 無效化

Laravel 也提供了另一個機制，可以用來在不影響目前裝置上 Session 的情況下將使用者在其他裝置的 Session 無效化並登出使用者。該功能通常適用於使用者更改密碼、或是想將其他 Session 無效化但想讓目前裝置保持認證的情況。

在開始之前，請先確保要收到 Session 身份驗證的 Route 都有包含 `Illuminate\Session\Middleware\AuthenticateSession` Middleware。一般來說，應在 Route 群組定義內放入這個 Middleware，好讓該 Middleware 被套用到專案中大多數的 Route。預設情況下，`AuthenticateSession` Middleware 可使用專案 HTTP Kernel 中所定義的 `auth.session` 這個 Route Middleware 索引鍵來附加到 Route 上：

    Route::middleware(['auth', 'auth.session'])->group(function () {
        Route::get('/', function () {
            // ...
        });
    });
接著，可以使用 `Auth` Facade 提供的 `logoutOtherDevices` 方法。該方法會需要使用者確認目前密碼，而你的網站應通過一個輸入表單來接收密碼：

    use Illuminate\Support\Facades\Auth;
    
    Auth::logoutOtherDevices($currentPassword);
當 `logoutOtherDevices` 方法被叫用後，使用者的其他 Session 將被立即無效化。這代表，使用者會被從其他所有已認證過的 Guard 中被「登出」。

<a name="password-confirmation"></a>

## 密碼確認

在製作網站時，有時可能會需要使用者在執行某個操作前、或是在使用者被重新導向到網站機敏區域前要求使用者確認密碼。Laravel 提供了一個內建的 Middleware 來讓這個過程變得很輕鬆。要實作這項功能會需要定義兩個路由：一個用於顯示並要求使用者確認密碼的路由，另一個則用於確認密碼有效並將使用者重新導向至預期目的地的路由。

> [!NOTE]  
> 下列說明文件討論了如何直接整合 Laravel 的密碼確認功能。但若想更快速地入門， [Laravel 專案入門套件](/docs/{{version}}/starter-kits) 有內建支援這個功能！

<a name="password-confirmation-configuration"></a>

### 設定

確認使用者密碼後，接下來的三小時內就不會再次向使用者詢問密碼了。但是，只需要更改專案中 `config/auth.php` 設定檔的 `password_timeout` 設定，就可以調整要重新詢問使用者密碼的時間長度。

<a name="password-confirmation-routing"></a>

### 路由

<a name="the-password-confirmation-form"></a>

#### 密碼確認表單

首先，我們先定義用來顯示要求使用者確認密碼的路由：

    Route::get('/confirm-password', function () {
        return view('auth.confirm-password');
    })->middleware('auth')->name('password.confirm');
跟我們預期的一樣，這個路由所回傳的 View 內應有一個含有 `password` 欄位的表單。此外，我們也可以隨意在該 View 中加上文字說明，來告訴使用者他們正在進入網站中受保護的區域，必須要輸入密碼來進行確認。

<a name="confirming-the-password"></a>

#### 確認密碼

接著，我們來定義要處理來自「確認密碼」View 傳來的表單請求的路由。該路由會負責認證使用者的密碼，並將使用者重新導向至原本預定的目的地。

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Redirect;
    
    Route::post('/confirm-password', function (Request $request) {
        if (! Hash::check($request->password, $request->user()->password)) {
            return back()->withErrors([
                'password' => ['The provided password does not match our records.']
            ]);
        }
    
        $request->session()->passwordConfirmed();
    
        return redirect()->intended();
    })->middleware(['auth', 'throttle:6,1']);
在繼續之前，來更仔細地看看這個路由。首先，會判斷請求的 `password` 是否真的符合已認證使用者的密碼。若密碼有效，則我們會通知 Laravel 的 Session 該使用者已確認密碼了。`passwordConfirmed` 方法會在使用者的 Session 上設定一個時間戳記，這樣 Laravel 便能判斷使用者上次確認密碼是什麼時候。最後，我們將使用者重新導向至原本預定的目的地。

<a name="password-confirmation-protecting-routes"></a>

### 保護路由

任何有需要確保最近認證過密碼操作的路由都應設定 `password.confirm` 中間層。該中間層已包含在預設 Laravel 安裝內，且會自動將使用者預定的目的地保存在 Session 內。因此，使用者在確認密碼後會被重新導向之該頁面。將使用者預定的目的地保存在 Session 後，該中間層會將使用者重新導向之 `password.confirm` 這個[命名路由](/docs/{{version}}/routing#named-routes)：

    Route::get('/settings', function () {
        // ...
    })->middleware(['password.confirm']);
    
    Route::post('/settings', function () {
        // ...
    })->middleware(['password.confirm']);
<a name="adding-custom-guards"></a>

## 新增自訂 Guard

可以通過 `Auth` Facade 中的 `extend` 方法來定義你自己的認證 Guard。`extend` 方法的呼叫應放置於一個 [Service Provider](/docs/{{version}}/providers) 內。由於 Laravel 預設已附帶了 `AuthServiceProvider`，因此我們可以將程式碼放在這個 Provider 中：

    <?php
    
    namespace App\Providers;
    
    use App\Services\Auth\JwtGuard;
    use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
    use Illuminate\Support\Facades\Auth;
    
    class AuthServiceProvider extends ServiceProvider
    {
        /**
         * Register any application authentication / authorization services.
         *
         * @return void
         */
        public function boot()
        {
            $this->registerPolicies();
    
            Auth::extend('jwt', function ($app, $name, array $config) {
                // Return an instance of Illuminate\Contracts\Auth\Guard...
    
                return new JwtGuard(Auth::createUserProvider($config['provider']));
            });
        }
    }
如同在上方範例中看到的一樣，傳給 `extend` 方法的閉包應回傳 `Illuminate\Contracts\Auth\Guard` 的實作。
`Illuminate\Contracts\Auth\Guard` 這個介面中有一些定義自訂 Guard 所需要實作的方法。定義好自訂 Guard 後，就能在 `auth.php` 設定檔中的 `guards` 設定來參照自訂 Guard。

    'guards' => [
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],
    ],
<a name="closure-request-guards"></a>

### 閉包請求 Guard

要實作一個基於 HTTP 請求的自訂認證系統最簡單的方法，就是通過 `Auth::viaRequest`。通過此方法就可以用單一閉包來快速定義認證流程。

要開始定義自訂 Guard，先在 `AuthServiceProvider` 中的 `boot` 方法內呼叫 `Auth::viaRequest` 方法。`viaRequest` 方法的第一個引數為認證 Driver 的名稱。這個 Driver 名稱可以是用來描述該自訂 Guard 的一個任意字串。傳入該方法的第二個引數則應為接收連入 HTTP 請求的閉包，該閉包應在認證成功時回傳使用者實體、認證失敗時回傳 `null`。

    use App\Models\User;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    
    /**
     * Register any application authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    
        Auth::viaRequest('custom-token', function (Request $request) {
            return User::where('token', (string) $request->token)->first();
        });
    }
定義好自訂認證 Driver 後，可以將其設定在 `auth.php` 設定檔中的 `guards` 設定。

    'guards' => [
        'api' => [
            'driver' => 'custom-token',
        ],
    ],
最後，在 Route 中指定驗證 Middleware 時可參照這個 Guard：

    Route::middleware('auth:api')->group(function () {
        // ...
    }
<a name="adding-custom-user-providers"></a>

## 新增自訂 User Provider

若你不是使用傳統關聯式資料庫來儲存使用者，就需要擴充 Laravel 來新增自訂的認證 User Provider。接下來我們會用 `Auth` Facade 的 `provider` 方法來定義自訂 User Provider。這個 User Provider 的解析程式應回傳一個 `Illuminate\Contracts\Auth\UserProvider` 的實作：

    <?php
    
    namespace App\Providers;
    
    use App\Extensions\MongoUserProvider;
    use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
    use Illuminate\Support\Facades\Auth;
    
    class AuthServiceProvider extends ServiceProvider
    {
        /**
         * Register any application authentication / authorization services.
         *
         * @return void
         */
        public function boot()
        {
            $this->registerPolicies();
    
            Auth::provider('mongo', function ($app, array $config) {
                // Return an instance of Illuminate\Contracts\Auth\UserProvider...
    
                return new MongoUserProvider($app->make('mongo.connection'));
            });
        }
    }
通過 `provider` 方法註冊好 Provider 後，即可在 `auth.php` 設定檔內更改為新的 User Provider。首先，先定義使用這個新 Driver 的 `provider`：

    'providers' => [
        'users' => [
            'driver' => 'mongo',
        ],
    ],
最後，就能在 `guards` 設定中參照這個 Provider：

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],
<a name="the-user-provider-contract"></a>

### User Provider Contract

`Illuminate\Contracts\Auth\UserProvider` 的實作應負責從持續性儲存系統 (如 MySQL、MongoDB…等) 中取出 `Illuminate\Contracts\Auth\Authenticatable` 的實作。有了這兩個介面，不論我們如何儲存使用者資料、不論我們使用什麼類型的 Class 來代表已登入使用者，Laravel 的認證機制都能繼續運作：

我們來看看 `Illuminate\Contracts\Auth\UserProvider` Contract：

    <?php
    
    namespace Illuminate\Contracts\Auth;
    
    interface UserProvider
    {
        public function retrieveById($identifier);
        public function retrieveByToken($identifier, $token);
        public function updateRememberToken(Authenticatable $user, $token);
        public function retrieveByCredentials(array $credentials);
        public function validateCredentials(Authenticatable $user, array $credentials);
    }
`retrieveById` 方法通常會接受一個代表使用者的索引鍵，如 MySQL 資料庫中的 Auto-Increment ID。該方法應回傳一個符合該 ID 的 `Authenticatable` 實作。

`retrieveByToken` 方法通過每個使用者獨立的 `$identifier` 以及一個在資料庫中通常存在 `remember_token` 欄位的「記住我」權杖 `$token` 來取得使用者。與上個方法類似，這個方法應回傳一個符合該權杖的 `Authenticatable` 。

`updateRememberToken` 方法將 `$user` 實體的 `remember_token` 更新為新的 `$token` 。當有勾選「記住我」的登入認證成功、或使用者登出後，會指派新的權杖給使用者。

`retrieveByCredentials` 方法接受一個包含登入憑證的陣列。該陣列是在使用者嘗試登入時傳給 `Auth::attempt` 的憑證。接著該方法內可以向對應的持續性儲存空間以這組憑證進行「查詢」。通常來說，這個方法會執行一個「where」條件句，來搜尋「username」符合 `$credentials['username']` 的使用者記錄。該方法應回傳 `Authenticatable` 的實作。**不應在該方法內認證密碼或進行登入。**

`validateCredentials` 方法應負責使用 `$credentials` 來比對給定的 `$user` 以驗證使用者。舉例來說，該方法通常會使用 `Hash::check` 方法來比對 `$user->getAuthPassword()` 與 `$credentials['password']` 的值。該方法應回傳 `true` 或 `false` 來標示密碼是否有效。

<a name="the-authenticatable-contract"></a>

### Authenticatable Contract

現在我們已經看過 `UserProvider` 內的各個方法了。接著來看看 `Authenticatable` Contract。請記住，User Provider 應在 `retrieveById`, `retrieveByToken` 以及 `retrieveByCredentials` 方法內回傳該介面的實作：

    <?php
    
    namespace Illuminate\Contracts\Auth;
    
    interface Authenticatable
    {
        public function getAuthIdentifierName();
        public function getAuthIdentifier();
        public function getAuthPassword();
        public function getRememberToken();
        public function setRememberToken($value);
        public function getRememberTokenName();
    }
這個介面很簡單。`getAuthIdentifierName` 應回傳使用者「主索引鍵 (Primary Key)」欄位的名稱，而 `getAuthIdentifier` 則回傳使用者的「主索引鍵」。當使用 MySQL 後端時，主索引鍵通常就是指派給使用者記錄的自動遞增 (Auto-Increment) 主索引鍵。

有了這個介面，不論使用什麼 ORM 或儲存抽象層，認證系統都能與任何的「使用者」Class 搭配使用。預設情況下，Laravel 在 `app/Models` 目錄內包含了一個 `App\Models\User` Class，`App\Models\User` 就實作了這個介面。

<a name="events"></a>

## 事件

Laravel 會在認證的過程中分派數個 [事件](/docs/{{version}}/events)。可以在 `EventServiceProvider` 內為這些事件附加監聽程式。

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Illuminate\Auth\Events\Registered' => [
            'App\Listeners\LogRegisteredUser',
        ],
    
        'Illuminate\Auth\Events\Attempting' => [
            'App\Listeners\LogAuthenticationAttempt',
        ],
    
        'Illuminate\Auth\Events\Authenticated' => [
            'App\Listeners\LogAuthenticated',
        ],
    
        'Illuminate\Auth\Events\Login' => [
            'App\Listeners\LogSuccessfulLogin',
        ],
    
        'Illuminate\Auth\Events\Failed' => [
            'App\Listeners\LogFailedLogin',
        ],
    
        'Illuminate\Auth\Events\Validated' => [
            'App\Listeners\LogValidated',
        ],
    
        'Illuminate\Auth\Events\Verified' => [
            'App\Listeners\LogVerified',
        ],
    
        'Illuminate\Auth\Events\Logout' => [
            'App\Listeners\LogSuccessfulLogout',
        ],
    
        'Illuminate\Auth\Events\CurrentDeviceLogout' => [
            'App\Listeners\LogCurrentDeviceLogout',
        ],
    
        'Illuminate\Auth\Events\OtherDeviceLogout' => [
            'App\Listeners\LogOtherDeviceLogout',
        ],
    
        'Illuminate\Auth\Events\Lockout' => [
            'App\Listeners\LogLockout',
        ],
    
        'Illuminate\Auth\Events\PasswordReset' => [
            'App\Listeners\LogPasswordReset',
        ],
    ];