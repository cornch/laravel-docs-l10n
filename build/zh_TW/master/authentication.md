# 驗證

- [簡介](#introduction)
    - [入門套件](#starter-kits)
    - [資料庫考量](#introduction-database-considerations)
    - [生態鏈概覽](#ecosystem-overview)
- [「驗證」快速入門](#authentication-quickstart)
    - [安裝一個入門套件](#install-a-starter-kit)
    - [取得已登入的使用者](#retrieving-the-authenticated-user)
    - [保護路由](#protecting-routes)
    - [登入頻率限制](#login-throttling)
- [手動驗證使用者](#authenticating-users)
    - [記住使用者](#remembering-users)
    - [手動驗證使用者](#other-authentication-methods)
- [HTTP 基本驗證](#http-basic-authentication)
    - [Stateless HTTP 基本驗證](#stateless-http-basic-authentication)
- [登出](#logging-out)
    - [無效化其他裝置上的 Session](#invalidating-sessions-on-other-devices)
- [密碼確認](#password-confirmation)
    - [組態設定](#password-confirmation-configuration)
    - [路由](#password-confirmation-routing)
    - [保護路由](#password-confirmation-protecting-routes)
- [新增自定 Guard](#adding-custom-guards)
    - [閉包請求 Guard](#closure-request-guards)
- [新增自定 User Providers](#adding-custom-user-providers)
    - [User Provider Contract](#the-user-provider-contract)
    - [Authenticatable Contract](#the-authenticatable-contract)
- [社群登入](/docs/{{version}}/socialite)
- [事件](#events)

<a name="introduction"></a>
## 簡介

許多網頁應用程式都提供了讓使用者向應用程式驗證以及「登入」的功能。在網頁應用程式上實作這些功能可能會很複雜，而且可能會有些風險。為此，Laravel
竭力為你提供了用於快速、安全、且簡單地實作驗證功能的工具。

在 Laravel 的核心中，驗證功能是通過「Guard」與「Provider」來提供的。Guard
用來定義使用者在每個請求上是如何被驗證的。舉例來說，Laravel 附帶了一個 `session` Guard，會通過 Session 儲存空間與
Cookie 來維護驗證狀態。

Provider 則定義了要如何從長期儲存空間內取得使用者。Laravel 附帶了以
[Eloquent](/docs/{{version}}/eloquent)
以及通過資料庫查詢構造器取得使用者的支援。但是，你也可以自行依據需求額外定期其他 Provider。

你的應用程式的驗證組態設定檔位於 `config/auth.php` 內。該檔案包含了多個有文件說明的選項，可以調整 Laravel 驗證服務的行為。

> {tip} Guard 與 Provider 與「角色」以及「權限」不同，不應溷肴。要瞭解如何依照權限來授權使用者的方法，請參考 [授權](/docs/{{version}}/authorization) 說明文件。

<a name="starter-kits"></a>
### 入門套件

想要快速入門嗎？請在全新的 Laravel 應用程式內安裝 [Laravel
應用程式入門套件](docs/{{version}}/starter-kits)。完成資料庫遷移後，在瀏覽器上開啟 `/register`
或其他任何指派到應用程式的 URL。這些入門套件會幫你搞定整個驗證系統的 Scaffolding。

**就算最後不會在 Laravel 應用程式上使用其中一種入門套件，也可以安裝 [Laravel Breeze](/docs/{{version}}/starter-kits#laravel-breeze) 入門套件也可以個學習如何在實際 Laravel 專案上實作所有 Laravel 驗證功能的好機會。** 由於 Laravel Breeze 會為你建立好驗證控制器、路由、以及檢視器，因此可以通過閱讀這些檔案的程式碼來學習 Laravel 的驗證功能可以怎麼進行實作。

<a name="introduction-database-considerations"></a>
### 資料庫選擇

預設情況下，Laravel 在 `app/Models` 資料夾中包含了一個 `App\Models\User` [Eloquent
模型](/docs/{{version}}/eloquent)。這個模型可以用在預設的 Eloquent 驗證驅動器上。若你的應用程式不使用
Eloquent，則可以使用 `database` 驗證 Provider，該 Provider 使用 Laravel 的查詢構造器。

為 `App\Models\User` 模型建立資料庫結構時，請確保密碼欄位的長度至少有 60 個字元。當然，在新 Laravel 應用程式內所包含的
`users` 資料表遷移檔已經建立了超過這個長度的欄位。

另外，也請求確保 `users`（或其相應的）資料表有包含一個 Nullable、100 字元的 `remember_token`
字串欄位。該欄位會用來在使用者登入時勾選「記住我」選項時使用。同樣地，在新 Laravel 應用程式中包含的 `users`
資料表遷移檔已經有包含了這個欄位。

<a name="ecosystem-overview"></a>
### 生態鏈概覽

Laravel 提供了多個有關驗證的套件。在繼續之前，我們先來看看這些 Laravel 中的一般驗證生態鏈，並討論各個套件預設的目的。

首先，我們先來看看驗證是怎麼運作的。在使用網頁瀏覽器時，使用者會通過登入表格來提供他們的使用者名稱以及密碼。若這些憑證正確，則應用程式會將已驗證使用者的資訊儲存在使用者的
[Session](/docs/{{version}}/session) 中。Cookie 會被傳給瀏覽器，其中包含了 Session
ID，這樣一來接下來向應用程式發起的請求就能通過正確的 Session 來連結上使用者。收到 Session Cookie 厚，應用程式會依據
Session ID 來取得 Session 資料。請注意，由於驗證這些已經被保存在 Session 中了，所以該使用者將被視為「已驗證」。

當遠端服務需要驗證來存取 API 時，通常不會在驗證上使用 Cookie，因為這種情況下並沒有網頁瀏覽器。取而代之地，遠端服務會在每個請求時帶上 API
權杖。應用程式可以通過將傳入的權杖與包含了有效 API 憑證的資料表進行比對來「驗證」該請求，並將其視為是有與 API 權杖管理的使用者所進行的操作。

<a name="laravels-built-in-browser-authentication-services"></a>
#### Laravel 的內建瀏覽器驗證服務

Laravel 的內建驗證與 Session 服務通常會通過 `Auth` 與 `Session` Facade
來存取。這些功能為從瀏覽器發起的請求提供了基於 Cookie
的驗證功能。這些功能也提供了能驗證使用者憑證與驗證使用者的方法。此外，這些服務也會自動將正確的資料儲存在使用者的 Session 內，並為使用者核發
Session Cookie。本文件中包含了如何使用這些服務的討論。

**應用程式入門套件**

就像先前在本文件中討論的一樣，你可以通過手動操作這些驗證服務來為應用程式建立你自己的驗證層。但，為了讓你可以更快入門，我們也釋出了
[一些免費套件](/docs/{{version}}/starter-kits) 來提供更快速且現代化的完整驗證層 Scaffolding。這些套件就是
[Laravel Breeze](/docs/{{version}}/starter-kits#laravel-breeze), [Laravel
Jetstream](/docs/{{version}}/starter-kits#laravel-jetstream)，與 [Laravel
Fortify](/docs/{{version}}/fortify)。

**Laravel Breeze** 是一個簡單且最小化實作出所有 Laravel
驗證功能的套件，包含登入、註冊、密碼重設、電子郵件驗證、以及密碼確認。Laravel Breeze 的檢視器層是通過簡單的 [Blade
樣板](/docs/{{version}}/blade) 搭配 [Tailwind CSS](https://tailwindcss.com)
提供樣式組合而成的。若要瞭解有關 Laravel Breeze，請參考 Laravel 的
[應用程式入門套件](/docs/{{version}}/starter-kits) 說明文件。

**Laravel Fortify** 是一種用於 Laravel 的 Headless（無周邊）驗證後端。Laravel Fortify
實作了許多可在此說明文件中找到的功能，包含基於 Cookie 的驗證以及其他如二步驟驗證與電子郵件驗證等功能。Fortify 也為 Laravel
Jetstream 提供了驗證後端。或者，也可以通過與 [Laravel Sanctum](/docs/{{version}}/sanctum)
組合使用來為需要在 Laravel 中進行驗證的 SPA（Single Page Application，單一頁面應用程式）提供驗證功能。

**[Laravel Jetstream](https://jetstream.laravel.com)** 是一個強大的入門套件，該套件使用者
Laravel Fortify 的驗證服務，並將其通過 [Tailwind CSS](https://tailwindcss.com),
[Livewire](https://laravel-livewire.com)，與／或
[Inertia.js](https://inertiajs.com) 來提供美麗且現代的 UI。Laravel Jetstream
也提供了對二步驟驗證、團隊功能、瀏覽器期程管理、個人檔案管理、以及內建 [Laravel
Sanctum](/docs/{{version}}/sunctum) 整合來提供 API 權杖驗證的可選支援。我們稍後會討論 Laravel 提供的
API 驗證功能。

<a name="laravels-api-authentication-services"></a>
#### Laravel 的 API 驗證服務

Laravel 提供了兩個可選的套件來協助你管理 API 權杖以及驗證以 API
權杖建立的請求：[Passport](/docs/{{version}}/passport) 與
[Sanctum](/docs/{{version}}/sanctum)。請注意，這些函式庫與 Laravel 內建的基於 Cookie
的驗證函式庫並不互相衝突。這些函式庫的重點都是在提供 API 權杖驗證，而內建的驗證服務則重點在基於 Cookie
的瀏覽器驗證。許多應用程式都會同時使用 Laravel 內建的基於 Cookie 的驗證服務以及其中一種 Laravel 的 API 驗證套件。

**Passport**

Passport 是一個 OAuth2 驗證 Provider，提供了多種 OAuth2 的「Grant
Type」來讓你簽發各種不同的權杖。通常來說，對於 API 驗證來說 Passport 是一個很強大很複雜的套件。但，大多數應用程式並不需要
OAuth2 規格所提供的那些複雜的功能，這些功能只會讓使用者與開發者都感到混亂。另外，開發者過去也對於如何通過如 Passport 這樣的
OAuth2 驗證 Provider 來驗證 SPA 應用程式或行動應用程式而感到混亂。

**Sanctum**

為了處理 OAuth2
很複雜的問題以及解決開發者的困惑，我們希望建立一種更簡單、更精簡的驗證套件。這個套件不但可以處理來自網頁瀏覽器的第一方網頁請求，也可以處理通過權杖來發起的
API 請求。這一個目標在 [Laravel Sanctum](/docs/{{version}}/sanctum) 發佈後實現了，對於一些需要提供除了
API 外第一方網頁 UI、由有獨立 Laravel 後端應用程式的單一頁面應用程式 (SPA)
提供服務的網頁、或是提供網頁用戶端的應用程式，Laravel Sanctum 目前是我們推薦與建議的驗證套件。

Laravel Sanctum 是一個混合了網頁與 API 驗證的套件，可以用來處理應用程式的整個驗證流程。可以這麼做是因為當基於 Sanctum
的應用程式收到請求後，Sanctum 會先判斷該請求是否包含了驗證 Session 的 Session Cookie。Sanctum
是通過呼叫我們稍早討論過的 Laravel 內建驗證服務來達成此一功能的。若該請求的 Session Cookie 未被驗證過，則 Sanctum
接著會檢查請求的 API 權杖。若有找到 API 權杖，則 Sanctum 會使用該權杖來驗證請求。要瞭解更多有關此一流程的資訊，請參考 Sanctum
的 [「運作方式」](/docs/{{version}}/sanctum#how-it-works) 說明文件。

Laravel Sanctum 是我們在 [Laravel Jetstream](https://jetstream.laravel.com)
應用程式入門套件中選擇的 API 套件，因為我們認為該套件最符合大多數網頁應用程式的驗證需求。

<a name="summary-choosing-your-stack"></a>
#### 總結與選擇你的 Stack

總的來說，若你的應用程式會通過瀏覽器來存取，且你正在建立單一的 Laravel 應用程式，則你的應用程式會使用 Laravel 的內建驗證服務。

接著，若應用程式有包含了會被第三方使用的 API，則會選擇 [Passport](/docs/{{version}}/passport) 或
[Sanctum](/docs/{{version}}/sanctum) 來為應用程式提供 API 權杖驗證。一般來說，Sanctum
應該儘量是預設的選擇，因為 Sanctum 比較簡單、且對 API 驗證、SPA
驗證，以及行動裝置驗證來說是完整的解決方案，並且支援「範圍（Scope）」與「權限（Ability）」。

如果是想建立由 Laravel 提供後端的單一頁面應用程式 (SPA)，則應該使用 [Laravel
Sanctum](/docs/{{version}}/sanctum)。在使用 Sanctum 時，會需要
[手動實作你自己的驗證路由後端](#authenticating-users)，或是使用 [Laravel
Fortify](/docs/{{version}}/fortify)
來作為無周邊（Headless）驗證後端服務以為如註冊、密碼重設、電子郵件驗證等功能提供路由與控制器。

當應用程式真的需要所有 OAuth2 規格所提供的功能時，則可以選擇使用 Passport。

此外，若想要快速入門，我們非常高興地推薦你使用 [Laravel Jetstream](https://jetstream.laravel.com)
來作為快速建立新 Laravel 應用程式的方法。Laravel Jetstream 已經使用了我們偏好的驗證 Stack，即為使用 Laravel
的內建驗證服務與 Laravel Sanctum。

<a name="authentication-quickstart"></a>
## 「驗證」快速入門

> {note} 這部分的文件會討論通過 [Laravel 應用程式入門套件](/docs/{{version}}/starter-kits) 來驗證使用者，這些入門套件包含了能協助你快速開始的 UI Scaffolding。若你想要直接與 Laravel 的驗證系統整合，請參考 [手動驗證使用者](#authenticating-users) 內的說明文件。

<a name="install-a-starter-kit"></a>
### 安裝一種入門套件

首先，需要 [安裝一個 Laravel
應用程式入門套件](/docs/{{version}}/starter-kits)。我們目前的入門套件，Laravel Breeze 與 Laravel
Jetstream，都提供了能為你的全新 Laravel 應用程式帶來漂亮設計的開始點。

Laravel Breeze 是一個簡單且最小化實作出所有 Laravel
驗證功能的套件，包含登入、註冊、密碼重設、電子郵件驗證、以及密碼確認。Laravel Breeze 的檢視器層是通過簡單的 [Blade
樣板](/docs/{{version}}/blade) 搭配 [Tailwind CSS](https://tailwindcss.com)
提供樣式組合而成的。

[Laravel Jetstream](https://jetstream.laravel.com) 是一個更複雜的應用程式入門套件，其中包含了使用
[Livewire](https://laravel-livewire.com) 或 [Inertia.js 與
Vue](https://inertiajs.com) 來對應用程式 Scaffolding 的支援。此外，Jetstream
也提供了對二步驟驗證、團隊、個人檔案管理、瀏覽器啟程管理、通過 [Laravel Sanctum](/docs/{{version}}/sanctum)
提供的 API 支援、帳號刪除…等功能的可選支援。

<a name="retrieving-the-authenticated-user"></a>
### 取得已登入的使用者

安裝完驗證入門套件並讓使用者在應用程式內註冊與驗證後，我們通常需要與目前已登入的使用者進行互動。在處理連入請求時，我們可以通過 `Auth`
Facade 的 `user` 方法來存取已登入的使用者：

    use Illuminate\Support\Facades\Auth;

    // 取得目前登入的使用者...
    $user = Auth::user();

    // 取得目前登入使用者的 ID...
    $id = Auth::id();

另外，使用者驗證後，也可以通過 `Illuminate\Http\Request` 實體來存取已登入的使用者。請記得，有型別提示的類別會自動被插入到
Controller 方法內。只要型別提示 `Illuminate\Http\Request` 物件，就可以方便地通過 Request 的 `user`
方法來在任何 Controller 方法內存取已登入的使用者：

    <?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    class FlightController extends Controller
    {
        /**
         * 為現有航班更新航班資訊。
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
#### 判斷目前使用者是否已驗證

若要判斷建立連入 HTTP 請求的使用者是否已登入，可以使用 `Auth` Facade 的 `check` 方法。該方法會在使用者已登入的時候回傳
`true`：

    use Illuminate\Support\Facades\Auth;

    if (Auth::check()) {
        // 使用者已登入…
    }

> {tip} 雖然可以通過使用 `check` 方法來判斷使用者是否已登入，但通常可以使用 Middleware 來在使用者存取特定路由或 Controller 前驗證該使用者是否已登入。有關更多詳情，請參考 [保護路由](/docs/{{version}}/authentication#protecting-routes) 內的說明文件。

<a name="protecting-routes"></a>
### 保護路由

[路由 Middleware](/docs/{{version}}/middleware) 可以用來只允許已驗證的使用者存取指定的路由。Laravel
內建了一個 `auth` Middleware，這個 Middleware為
`Illuminate\Auth\Middleware\Authenticate` 類別。由於該 Middleware已預先在你的應用程式 HTTP
核心內註冊好了，所以只需要在路由定義內加上這個 Middleware 即可：

    Route::get('/flights', function () {
        // 只有已登入的使用者才能存取此路由…
    })->middleware('auth');

<a name="redirecting-unauthenticated-users"></a>
#### 重新導向未登入的使用者

當 `auth` Middleware檢測到未驗證的使用者，該 Middleware會將使用者重新導向到 `login` 這個
[帶名稱的路由](/docs/{{version}}/routing#named-routes) 上。可以通過更新應用程式中
`app/Http/Middleware/Authenticate.php` 檔案內的 `redirectTo` 方法來更改此一行為。

    /**
     * 取得使用者應被重新導向的路徑。
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

在將 `auth` 中間層加到路由時，可以指定要使用哪個「Guard」來驗證使用者。可以通過指定 `auth.php` 組態設定檔中 `guards`
陣列內對應的鍵值來指定 Guard：

    Route::get('/flights', function () {
        // 只有已登入使用者才可存取此路由…
    })->middleware('auth:admin');

<a name="login-throttling"></a>
### 登入頻率限制

若使用 Laravel Breeze 或 Laravel Jetstream
[入門套件](/docs/{{version}}/starter-kits)，會自動將頻率限制套用到登入限制上。預設情況下，若使用者嘗試了數次仍未提供正確的帳號密碼，則將在一分鐘之內都無法登入。登入限制是基於每個使用者的使用者名稱或電子郵件，以及其
IP 位址來區分的。

> {tip} 若想在應用程式中的其他路由上提供頻率限制，請參考 [頻率限制說明文件](/docs/{{version}}/routing#rate-limiting)。

<a name="authenticating-users"></a>
## 手動驗證使用者

不一定要使用 Laravel [應用程式入門套件](/docs/{{version}}/starter-kits) 內包含的驗證
Scaffolding。若選擇不使用這些 Scaffolding 的話，則需要直接通過 Laravel
的驗證類別來處理使用者驗證。別擔心，這只是小菜一碟！

我們會通過 `Auth` [Facade](/docs/{{version}}/facades) 來存取 Laravel
的驗證服務。因此，我們需要確保有在該類別的最上方引入 `Auth` Facade。接著，還要確認一下我們的 `attempt` 方法。這個
`attempt` 方法通常是用來處理來自應用程式「登入」表單的驗證嘗試。若成功驗證，則應該重新產生使用者的
[session](/docs/{{version}}/session) 來防止 [Session
Fixation（英語）](https://en.wikipedia.org/wiki/Session_fixation)：

    <?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;

    class LoginController extends Controller
    {
        /**
         * 處理登入嘗試。
         *
         * @param  \Illuminate\Http\Request $request
         * @return \Illuminate\Http\Response
         */
        public function authenticate(Request $request)
        {
            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();

                return redirect()->intended('dashboard');
            }

            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }
    }

The `attempt` method accepts an array of key / value pairs as its first
argument. The values in the array will be used to find the user in your
database table. So, in the example above, the user will be retrieved by the
value of the `email` column. If the user is found, the hashed password
stored in the database will be compared with the `password` value passed to
the method via the array. You should not hash the incoming request's
`password` value, since the framework will automatically hash the value
before comparing it to the hashed password in the database. If the two
hashed passwords match an authenticated session will be started for the
user.

請記得，Laravel 的驗證服務會依據驗證 Guard 的「provider」組態設定來從資料庫中取得使用者。預設的
`config/auth.php` 組態設定檔中使用了 Eloquent User Provider，並使用 `App\Models\User`
模型來取得使用者。可以依照應用程式的需求來在組態設定檔中更改這些值。

當驗證成功後，`attempt` 方法會回傳 `true`。否則，會回傳 `false`。

Laravel 的重新導向程式中提供的 `intended` 方法可以用來將使用者導向到他們被 Auth Middleware 攔截存取前正在嘗試存取的
URL。可以提供一個遞補的 URI 給該方法，以免沒有預期的目的地。

<a name="specifying-additional-conditions"></a>
#### 指定額外條件

若有需要的話，也可以在驗證查詢上指定除了使用者的電子郵件與密碼外的額外查詢條件。為此，只需要將查詢條件加到傳給 `attempt`
方法的陣列中即可。如，我們可以驗證使用者有被標示為「啟用（Active）」：

    if (Auth::attempt(['email' => $email, 'password' => $password, 'active' => 1])) {
        // 驗證成功…
    }

> {note} 在這些例子中，`email` 都不是必填的選項，只是拿來當作例子。你可以使用任何在資料庫中相當於「使用者名稱」的欄位。

<a name="accessing-specific-guard-instances"></a>
#### 存取特定 Guard 實體

通過 `Auth` Facade 的 `guard` 方法，可以指定使用者登入時要使用哪個 Guard
實體。如此一來便能為應用程式中不同部分的登入功能使用不同的 Authenticatable Model 或使用者資料表。

傳入 `guard` 方法的 Guard 名稱應為 `auth.php` 組態設定檔中設定的其中一個 Guard 名稱：

    if (Auth::guard('admin')->attempt($credentials)) {
        // ...
    }

<a name="remembering-users"></a>
### 記住使用者

許多應用程式都在登入表單內提供了一個「記住我」勾選框。若想為你的應用程式提供「記住我」功能，可以傳入布林值給 `attempt` 方法的第二個引數。

當該值為 `true` 時，Laravel 會永久儲存使用者的登入狀態，直到使用者手動登出。你的 `users` 資料表必須包含
`remember_token` 字串欄位，該欄位用來儲存「記住我」權杖。新 Laravel 應用程式中包含的 `users` 資料表
Migration 已包含了此欄位：

    use Illuminate\Support\Facades\Auth;

    if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
        // 已記住使用者…
    }

<a name="other-authentication-methods"></a>
### 其他驗證方法

<a name="authenticate-a-user-instance"></a>
#### 驗證使用者實體

若需要將目前已驗證使用者設為一個現有的使用者實體，可以將該實體傳入 `Auth` Facade 的 `login` 方法內。給定的使用者實體必須要實作
`Illuminate\Contracts\Auth\Authenticatable`
[Contract](/docs/{{version}}/contracts)。Laravel 中的 `App\Models\User`
模型已經實作了這個介面。這種驗證的方法適用與已有有效使用者實體的情況，如使用者註冊完應用程式之後：

    use Illuminate\Support\Facades\Auth;

    Auth::login($user);

可以將布林值傳入 `login` 方法的第二個引數。這個布林值會用來判斷該登入 Session 是否可套用「記住我」功能。請記得，啟用該功能就標示該
Session 將永久可用，直到使用者手動登出應用程式：

    Auth::login($user, $remember = true);

若有需要，可以在呼叫 `login` 方法前指定一個驗證 Guard：

    Auth::guard('admin')->login($user);

<a name="authenticate-a-user-by-id"></a>
#### 通過 ID 驗證使用者

若要使用資料庫中的主鍵（Primary Key）來驗證使用者，可以使用 `loginUsingId` 方法。該方法接受要驗證使用者的主鍵：

    Auth::loginUsingId(1);

可以將布林值傳入 `loginUsingId` 方法的第二個引數。這個布林值會用來判斷該登入 Session
是否可套用「記住我」功能。請記得，啟用該功能就標示該 Session 將永久可用，直到使用者手動登出應用程式：

    Auth::loginUsingId(1, $remember = true);

<a name="authenticate-a-user-once"></a>
#### 僅登入使用者一次

可以使用 `once` 方法來只在單一請求內登入使用者。呼叫此方法時不會使用到 Session 或 Cookie：

    if (Auth::once($credentials)) {
        //
    }

<a name="http-basic-authentication"></a>
## HTTP 基本驗證

[HTTP
基本驗證](https://zh.wikipedia.org/zh-tw/HTTP%E5%9F%BA%E6%9C%AC%E8%AE%A4%E8%AF%81)
提供了一種不需要設定專屬「登入」頁面而快速驗證應用程式使用者的方法。要進行 HTTP 基本驗證，請將 `auth.basic`
[Middleware](/docs/{{version}}/middleware) 加到路由上。`auth.basic` Middleware
已包含在 Laravel 框架內，不需要自行定義：

    Route::get('/profile', function () {
        // 只有已登入的使用者可以存取此路由…
    })->middleware('auth.basic');

將該 Middleware 加到路由上後，在瀏覽器上存取該路由時會自動被提示帳號密碼。預設情況下，`auth.basic` Middleware 會假設
`email` 欄位是 `users` 資料表中的使用者「帳號」欄位。

<a name="a-note-on-fastcgi"></a>
#### FastCGI 備註

若使用 PHP FastCGI 與 Apache 來提供 Laravel 應用程式，則 HTTP
基本驗證可能不會正確運作。要修正此一問題，可以將下列幾行加到應用程式的 `.htaccess` 檔案中：

    RewriteCond %{HTTP:Authorization} ^(.+)$
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

<a name="stateless-http-basic-authentication"></a>
### Stateless HTTP 基本驗證

也可以於不在 Session 內寫入可識別使用者 Cookie 的情況下使用 HTTP 基本驗證。通常適用於想通過 HTTP 驗證來驗證應用程式 API
請求時。為此，請先[定義一個 Middleware](/docs/{{version}}/middleware)，並於該 Middleware內呼叫
`onceBasic` 方法。若 `onceBasic` 方法無回傳值，則該請求將會進一步被傳遞到應用程式中：

    <?php

    namespace App\Http\Middleware;

    use Illuminate\Support\Facades\Auth;

    class AuthenticateOnceWithBasicAuth
    {
        /**
         * 處理連入請求。
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

接著，請[註冊路由 Middleware](/docs/{{version}}/middleware#registering-middleware)
並將其附加到路由上：

    Route::get('/api/user', function () {
        // 只有已登入使用者才能存取此路由…
    })->middleware('auth.basic.once');

<a name="logging-out"></a>
## 登出

若要手動將使用者自應用程式登出，可以使用 `Auth` Facade 提供的 `logout` 方法。該方法會從使用者的 Session
中將驗證資訊移除，如此一來，接下來的請求都會是已登出的狀態。

除了呼叫 `logout` 方法外，也建議將使用者的 Session 無效化，並為使用者重新產生 [CSRF
權杖](/docs/{{version}}/csrf)。登出使用者後，我們通常會將使用者重新導向回應用程式根目錄：

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;

    /**
     * 將使用者登出應用程式。
     *
     * @param  \Illuminate\Http\Request $request
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

Laravel 也提供了另一個機制，可以用來在不影響目前裝置上 Session 的情況下將使用者在其他裝置的 Session
無效化並登出使用者。該功能通常適用於使用者更改密碼、或是想將其他 Session 無效化但想讓目前裝置保持驗證的情況。

在開始前，請先確保 `Illuminate\Session\Middleware\AuthenticateSession` Middleware 有在
`App\Http\Kernel` 類別的 `web` Middleware 群組中，並且未被註解掉：

    'web' => [
        // ...
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        // ...
    ],

接著，可以使用 `Auth` Facade 提供的 `logoutOtherDevices`
方法。該方法會需要使用者確認目前密碼，而你的應用程式應通過一個輸入表單來接收密碼：

    use Illuminate\Support\Facades\Auth;

    Auth::logoutOtherDevices($currentPassword);

當 `logoutOtherDevices` 方法被叫用後，使用者的其他 Session 將被立即無效化。這代表，使用者會被從其他所有已驗證過的
Guard 中被「登出」。

<a name="password-confirmation"></a>
## 密碼確認

在建立應用程式時，有時可能會需要使用者在執行某個操作前、或是在使用者被重新導向到應用程式機敏區域前要求使用者確認密碼。Laravel 提供了一個內建的
Middleware
來讓這個過程變得很輕鬆。要實作這項功能會需要定義兩個路由：一個路由用於顯示並要求使用者確認密碼，另一個路由則用於確認密碼有效並將使用者重新導向至預期目的地。

> {tip} 下列說明文件討論了如何直接整合 Laravel 的密碼驗證功能。但若你想更快速地入門，[Laravel 應用程式入門套件](/docs/{{version}}/starter-kits) 包含了這項功能的支援！

<a name="password-confirmation-configuration"></a>
### 組態設定

確認使用者密碼後，接下來的三小時內就不會再次向使用者詢問密碼了。但是，只需要更改應用程式 `config/auth.php` 組態設定檔中的
`password_timeout` 組態設定，就可以調整要重新詢問使用者密碼的時間長度。

<a name="password-confirmation-routing"></a>
### 路由

<a name="the-password-confirmation-form"></a>
#### 密碼確認表單

首先，我們先定義用來顯示要求使用者確認密碼的路由：

    Route::get('/confirm-password', function () {
        return view('auth.confirm-password');
    })->middleware('auth')->name('password.confirm');

如同預想的一樣，這個路由所回傳的 View 內應該有一個含有 `password` 欄位的表單。此外，也可以隨意在該 View
內加上文字說明來告訴使用者正在進入應用程式中受保護的區域，必須要使用者輸入密碼進行確認。

<a name="confirming-the-password"></a>
#### 確認密碼

接著，我們來定義要處理來自「確認密碼」View 傳來的表單請求的路由。該路由會負責驗證使用者的密碼，並將使用者重新導向至原本預定的目的地。

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
    })->middleware(['auth', 'throttle:6,1'])->name('password.confirm');

在繼續之前，來更仔細地看看這個路由。首先，會判斷請求的 `password` 是否真的符合已登入使用者的密碼。若密碼有效，則我們會通知 Laravel
的 Session 該使用者已確認密碼了。`passwordConfirmed` 方法會在使用者的 Session 上設定一個時間戳記，這樣
Laravel 便能判斷使用者上次確認密碼是什麼時候。最後，我們將使用者重新導向至原本預定的目的地。

<a name="password-confirmation-protecting-routes"></a>
### 保護路由

任何有需要確保最近驗證過密碼操作的路由都應設定 `password.confirm` Middleware。該 Middleware 已包含在預設
Laravel 安裝內，且會自動將使用者預定的目的地保存在 Session 內。因此，使用者在確認密碼後會被重新導向之該頁面。將使用者預定的目的地保存在
Session 後，該 Middleware 會將使用者重新導向至 `password.confirm`
這個[命名路由](/docs/{{version}}/routing#named-routes)：

    Route::get('/settings', function () {
        // ...
    })->middleware(['password.confirm']);

    Route::post('/settings', function () {
        // ...
    })->middleware(['password.confirm']);

<a name="adding-custom-guards"></a>
## 新增自定 Guard

可以通過 `Auth` Facade 中的 `extend` 方法來定義你自己的驗證 Guard。`extend` 方法的呼叫應放置於一個
[Service Provider](/docs/{{version}}/providers) 內。由於 Laravel 預設已附帶了
`AuthServiceProvider`，因此我們可以將程式碼放在這個 Provider 內：

    <?php

    namespace App\Providers;

    use App\Services\Auth\JwtGuard;
    use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
    use Illuminate\Support\Facades\Auth;

    class AuthServiceProvider extends ServiceProvider
    {
        /**
         * 註冊任何應用程式的驗證／授權服務。
         *
         * @return void
         */
        public function boot()
        {
            $this->registerPolicies();

            Auth::extend('jwt', function ($app, $name, array $config) {
                // 回傳一個 Illuminate\Contracts\Auth\Guard 的實體…

                return new JwtGuard(Auth::createUserProvider($config['provider']));
            });
        }
    }

如同在上方範例中看到的一樣，傳給 `extend` 方法的閉包應回傳 `Illuminate\Contracts\Auth\Guard` 的實作。
`Illuminate\Contracts\Auth\Guard` 這個界面中有一些定義自定 Guard 所需要實作的方法。定義好自定 Guard
後，就能在 `auth.php` 組態設定檔中的 `guards` 設定來參照自定 Guard。

    'guards' => [
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],
    ],

<a name="closure-request-guards"></a>
### 閉包請求 Guard

要實作一個基於 HTTP 請求的自定驗證系統最簡單的方法，就是通過 `Auth::viaRequest`。通過此方法就可以用單一閉包來快速定義驗證流程。

要開始定義自定 Guard，先在 `AuthServiceProvider` 中的 `boot` 方法內呼叫 `Auth::viaRequest`
方法。`viaRequest` 方法的第一個引數為驗證 Driver 的名稱。這個 Driver 名稱可以是用來描述該自定 Guard
的一個任意字串。傳入該方法的第二個引數則應為接收連入 HTTP 請求的閉包，該閉包應在驗證成功時回傳使用者實體、驗證失敗時回傳 `null`。

    use App\Models\User;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;

    /**
     * 註冊任何應用程式的驗證／授權服務。
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::viaRequest('custom-token', function (Request $request) {
            return User::where('token', $request->token)->first();
        });
    }

定義好自定驗證 Driver 後，可以將其設定在 `auth.php` 組態設定檔中的 `guards` 設定。

    'guards' => [
        'api' => [
            'driver' => 'custom-token',
        ],
    ],

<a name="adding-custom-user-providers"></a>
## 新增自定 User Provider

若未使用傳統的關聯式資料庫來儲存使用者，則需要擴充 Laravel 來新增自定的驗證 User Provider。接下來我們會用 `Auth`
Facade 的 `provider` 方法來定義自定 User Provider。這個 User Provider 的解析程式應回傳一個
`Illuminate\Contracts\Auth\UserProvider` 的實作：

    <?php

    namespace App\Providers;

    use App\Extensions\MongoUserProvider;
    use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
    use Illuminate\Support\Facades\Auth;

    class AuthServiceProvider extends ServiceProvider
    {
        /**
         * 註冊任何應用程式的驗證／授權服務。
         *
         * @return void
         */
        public function boot()
        {
            $this->registerPolicies();

            Auth::provider('mongo', function ($app, array $config) {
                // 回傳一個 Illuminate\Contracts\Auth\UserProvider 的實體...

                return new MongoUserProvider($app->make('mongo.connection'));
            });
        }
    }

通過 `provider` 方法註冊好 Provider 後，即可在 `auth.php` 組態設定檔內更改為新的 User
Provider。首先，先定義使用這個新 Driver 的 `provider`：

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

`Illuminate\Contracts\Auth\UserProvider` 的實作應負責從持續性儲存系統（如
MySQL、MongoDB…等）中取出 `Illuminate\Contracts\Auth\Authenticatable`
的實作。有了這兩個介面，Laravel 的驗證機制就能在不論使用者的資料是如何儲存、以及不論使用什麼類型的 Class
來表示已驗證使用者的情況下繼續運作：

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

`retrieveById` 方法通常會接受一個代表使用者的索引鍵，如 MySQL 資料庫中的 Auto-Increment
ID。該方法應回傳一個符合該 ID 的 `Authenticatable` 實作。

`retrieveByToken` 方法通過每個使用者獨立的 `$identifier` 以及一個在資料庫中通常存在 `remember_token`
欄位的「記住我」權杖 `$token` 來取得使用者。與上個方法類似，這個方法應回傳一個符合該權杖的 `Authenticatable` 。

`updateRememberToken` 方法將 `$user` 實體的 `remember_token` 更新為新的 `$token`
。當有勾選「記住我」的登入驗證成功、或使用者登出後，會指派新的權杖給使用者。

`retrieveByCredentials` 方法接受一個包含登入憑證的陣列。該陣列是在使用者嘗試登入時傳給 `Auth::attempt`
的憑證。接著該方法內可以向對應的持續性儲存空間以這組憑證進行「查詢」。通常來說，這個方法會執行一個「where」條件句，來搜尋「username」符合
`$credentials['username']` 的使用者記錄。該方法應回傳 `Authenticatable`
的實作。**不應在該方法內驗證密碼或進行登入。**

`validateCredentials` 方法應負責使用 `$credentials` 來比對給定的 `$user` 以驗證使用者。舉例來說，該方法通常會使用 `Hash::check` 方法來比對 `$user->getAuthPassword()` 與 `$credentials['password']` 的值。該方法應回傳 `true` 或 `false` 來標示密碼是否有效。

<a name="the-authenticatable-contract"></a>
### Authenticatable Contract

現在我們已經看過 `UserProvider` 內的各個方法了。接著來看看 `Authenticatable` Contract。請記住，User
Provider 應在 `retrieveById`, `retrieveByToken` 以及 `retrieveByCredentials`
方法內回傳該介面的實作：

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

這個介面很簡單。`getAuthIdentifierName` 應回傳使用者「主鍵（Primary Key）」欄位的名稱，而
`getAuthIdentifier` 則回傳使用者的「主鍵」。當使用 MySQL
後端時，主鍵通常就是指派給使用者記錄的自動遞增（Auto-Increment）主鍵。

有了這個介面，不論使用什麼 ORM 或儲存抽象層，驗證系統都能與任何的「使用者」類別搭配使用。預設情況下，Laravel 在 `app/Models`
目錄內包含了一個 `App\Models\User` 類別，該類別就實作了這個介面。

<a name="events"></a>
## 事件

Laravel 會在驗證的過程中分派數個 [事件](/docs/{{version}}/events)。可以在
`EventServiceProvider` 內為這些事件附加監聽程式。

    /**
     * 應用程式的事件監聽程式。
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
