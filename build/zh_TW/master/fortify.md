---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/75/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 63.6
---

# Laravel Fortify

- [簡介](#introduction)
  - [What is Fortify?](#what-is-fortify)
  - [什麼時候該用 Fortify？](#when-should-i-use-fortify)
  
- [安裝](#installation)
  - [Fortify 功能](#fortify-features)
  - [禁用 View](#disabling-views)
  
- [身份認證](#authentication)
  - [自定使用者身份認證](#customizing-user-authentication)
  - [Customizing the Authentication Pipeline](#customizing-the-authentication-pipeline)
  - [自定 Redirect](#customizing-authentication-redirects)
  
- [二步驟認證](#two-factor-authentication)
  - [啟用二步驟認證](#enabling-two-factor-authentication)
  - [使用二步驟認證來登入](#authenticating-with-two-factor-authentication)
  - [禁用二步驟認證](#disabling-two-factor-authentication)
  
- [註冊](#registration)
  - [自定註冊](#customizing-registration)
  
- [重設密碼](#password-reset)
  - [Requesting a Password Reset Link](#requesting-a-password-reset-link)
  - [Resetting the Password](#resetting-the-password)
  - [自定密碼重設功能](#customizing-password-resets)
  
- [E-Mail 驗證](#email-verification)
  - [保護路由](#protecting-routes)
  
- [確認密碼](#password-confirmation)

<a name="introduction"></a>

## 簡介

[Laravel Fortify](https://github.com/laravel/fortify) 是一個可搭配任意前端的 Laravel 登入後端實作。Fortify 會註冊實作了所有 Laravel 登入功能的路由與 Controller，包含登入、註冊、密碼重設、E-Mail 驗證等功能。安裝完 Fortify 後，可以執行 `route:list` Artisan 指令，來看看 Fortify 註冊了哪些路由。

由於 Fortify 並不提供 UI，因此你需要自行實作使用這些路由的 UI。在本說明文件中，我們會在稍後討論如何向這些路由建立 Request。

> [!NOTE]  
> 請記得，Fortify 的功能是要讓你能在實作 Laravel 的登入功能時能更快上手。**你也可以不使用 Foritfy**。若你想要的話，也可以參考 [登入驗證](/docs/{{version}}/authentication)、[密碼重設](/docs/{{version}}/passwords)、與 [E-Mail 驗證](/docs/{{version}}/verification)等說明文件來手動使用 Laravel 的登入服務。

<a name="what-is-fortify"></a>

### What is Fortify?

剛才也提到過，Laravel Fortify 是一個可搭配任意前端的 Laravel 登入後端實作。Fortify 會註冊實作了所有 Laravel 登入功能的路由與 Controller，包含登入、註冊、密碼重設、E-Mail 驗證等功能。

**要使用 Laravel 的登入功能，不一定要用 Foritfy**。若想要的話，也可以參考 [登入驗證](/docs/{{version}}/authentication)、[密碼重設](/docs/{{version}}/passwords)、與 [E-Mail 驗證](/docs/{{version}}/verification)等說明文件來手動使用 Laravel 的登入服務。

如果你剛開始接觸 Laravel，你可能會想先看看 [Laravel Breeze](/docs/{{version}}/starter-kits) 專案入門套件，再來嘗試使用 Laravel Fortify。Laravel Breeze 提供了可在專案內使用的登入功能 Scaffolding，包含了使用 [Tailwind CSS](https://tailwindcss.com) 製作的 UI。與 Fortify 不同的是，Breeze 會直接將其路由與 Controller 安裝到你的專案內。使用 Breeze，你就可以在實際使用 Laravel Fortify 來實作這些登入功能前，先學習並熟悉 Laravel 的登入功能。

Laravel Fortify 基本上就是將 Laravel Breeze 中的路由與 Controller 拆出來以套件的形式提供給你，並且不包含 UI。這樣一來，使用 Fortify，你還是可以快速的 Scaffold 網站登入功能的後端實作，而不需被綁在某個特定的前端工具。

<a name="when-should-i-use-fortify"></a>

### 我該使用 Fortify 嗎？

你可能會想知道，什麼時候才適合使用 Laravel Fortify 呢？首先，如果你已經在使用 Laravel 的其中一個[專案入門套件](/docs/{{version}}/starter-kits)，就不需要再安裝 Laravel Fortify 了。因為，所有的 Laravel 專案入門套件都已經提供了完整的登入實作。

如果你沒有使用專案入門套件，而你的專案需要登入功能的話，則有兩個選項。第一個是你可以手動實作專案的登入功能，另一個選項則是使用 Laravel Fortify 來提供這些功能的後端實作。

若選擇使用 Fortify，則你的 UI 會向本文件中詳細說明的 Fortify 登入路由建立 Request，以登入或註冊使用者。

若選擇不使用 Fortify，手動使用 Laravel 的登入服務，則可參考 [登入驗證](/docs/{{version}}/authentication)、[密碼重設](/docs/{{version}}/passwords)、與 [E-Mail 驗證](/docs/{{version}}/verification)等說明文件。

<a name="laravel-fortify-and-laravel-sanctum"></a>

#### Laravel Fortify and Laravel Sanctum

有的開發人員會搞不清楚 [Laravel Sanctum](/docs/{{version}}/sanctum) 與 Laravel Fortify 間的差異。這是因為，這兩個套件分別解決了兩個不同但又相關的問題，而 Laravel Fortify 與 Laravel Sanctum 間並不互斥，也不是彼此的替代品。

Laravel Sanctum 只關心如何管理 API Token，以及如何以 Session Cookies 或 Token 來登入現有的使用者。Sanctum 不提供任何處理使用者註冊、密碼重設等的路由。

若要嘗試為有提供 API 或 SPA (單一頁面應用程式，Single-Page Application) 等網站自行建立登入功能，那麼你很有可能會同時用到 Laravel Fortify (用於註冊、重設密碼等) 與 Laravel Sanctum (管理 API Token、Session 登入)。

<a name="installation"></a>

## 安裝

若要開始使用 Fortify，可使用 Composer 套件管理員來安裝：

```shell
composer require laravel/fortify
```
Next, publish Fortify's resources using the `fortify:install` Artisan command:

```shell
php artisan fortify:install
```
這個指令會將 Fortify 的 ^[Action](%E5%8B%95%E4%BD%9C) 安裝到 `app/Actions` 目錄下，如果這個目錄不存在的話，該指令也會一併建立。此外，也會安裝 `FortifyServiceProvider`、設定檔、以及所需的 Migration 檔案。

接著，請 Migrate 資料庫：

```shell
php artisan migrate
```
<a name="fortify-features"></a>

### Fortify 功能

`fortify` 設定檔中包含了一個 `features` 設定陣列。這個陣列定義了 Fortify 預設會提供的後端路由與功能。若不與 [Laravel Jetstream](https://jetstream.laravel.com) 搭配使用 Fortify 的話，我們建議你只啟用下列功能，這些是大多數 Laravel 專案中會提供的基本登入功能：

```php
'features' => [
    Features::registration(),
    Features::resetPasswords(),
    Features::emailVerification(),
],
```
<a name="disabling-views"></a>

### 禁用 View

預設情況下，Fortify 會定義用於回傳 View 的路由，例如登入畫面、或是註冊畫面。不過，若你正在製作以 JavaScript 驅動的 SPA，則不需要註冊這些路由。這時，可在專案的 `config/fortify.php` 設定檔中將 `views` 設定值設為 `false` 來完全禁用這些路由：

```php
'views' => false,
```
<a name="disabling-views-and-password-reset"></a>

#### Disabling Views and Password Reset

若你選擇禁用 Fortify 的 View，但又有需要實作網站的密碼重設功能，則還是需要定義一個名稱為 `password.reset` 的路由，以用於顯示網站的「重設密碼」View。定義這個路由是有必要的，因為 Laravel 的 `Illuminate\Auth\Notifications\ResetPassword` 通知會使用這個 `password.reset` 命名路由來產生密碼重設連結。

<a name="authentication"></a>

## 登入

若要開始製作登入功能，我們需要告訴 Fortify 如何回傳「登入」View。請記得，Fortify 是一個無周邊 (Headless) 的登入函式庫。若你想要現成的完整 Laravel 登入功能前端實作，請使用[專案入門套件](/docs/{{version}}/starter-kits)。

對於所有登入功能的 View，都可使用 `Laravel\Fortify\Fortify` 類別中相應的方法來自定其轉譯邏輯一般來說，你可以在專案的 `App\Providers\FortifyServiceProvider` 類別中的 `boot` 方法內呼叫這些方法。Fortify 會處理並定義 `/login` 路由，來回傳這個 View：

    use Laravel\Fortify\Fortify;
    
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::loginView(function () {
            return view('auth.login');
        });
    
        // ...
    }
你的登入樣板應該包含一個表單，用於向 `/login` 傳送 POST Request。`/login` Endpoint 預期一組 `email` / `username` 與 `password` 的字串輸入。 E-Mail 或帳號 (Username) 的欄位名稱應與 `config/fortify.php` 中的 `username` 值相同。此外，也可以提供一個 `remember` 布林值欄位來表示是否要讓使用者使用 Laravel 提供的「記住我」功能。

若登入嘗試成功，Fortify 會將你重新導向到專案 `fortify` 設定檔中 `home` 設定選項所指定的 URI。若登入 Request 是 XHR Request，則會回傳 200 HTTP Response。

若 Request 未成功，則使用者會被重新導向回登入畫面，而共用的 `$errors` [Blade 樣板變數](/docs/{{version}}/validation#quick-displaying-the-validation-errors) 中會包含驗證錯誤訊息。或者，若使用的是 XHR Request，則會使用 422 HTTP Response 來回傳驗證錯誤。

<a name="customizing-user-authentication"></a>

### 自定使用者登入

Fortify 會自動依據提供的帳號密碼與專案所設定的登入 Guard 來取得並登入使用者。不過，有時候我們也會需要能完全自定如何驗證帳號密碼與如何取得使用者的邏輯。幸好，在 Fortify 中，我們只要使用 `Fortify::authenticateUsing` 方法就可以輕鬆達成。

該方法接受一個閉包，該閉包會收到連入的 HTTP Request，並且應負責驗證 Reuqest 中包含的帳號密碼，然後回傳相應的使用者實體。若登入無效，或是找不到使用者，則該閉包應回傳 `null` 或 `false`。一般來說，該方法應在 `FortifyServiceProvider` 中呼叫：

```php
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Fortify;

/**
 * Bootstrap any application services.
 */
public function boot(): void
{
    Fortify::authenticateUsing(function (Request $request) {
        $user = User::where('email', $request->email)->first();

        if ($user &&
            Hash::check($request->password, $user->password)) {
            return $user;
        }
    });

    // ...
}
```
<a name="authentication-guard"></a>

#### 登入 Guard

可以在專案的 `fortify` 設定檔中自定 Fortify 使用的登入 Guard。不過，請確保將其設定為有實作 `Illuminate\Contracts\Auth\StatefulGuard` 的 Guard。若嘗試使用 Fortify 來登入 SPA，則應使用 Laravel 的預設 `web` Guard，並搭配使用 [Laravel Sanctum](https://laravel.com/docs/sanctum)。

<a name="customizing-the-authentication-pipeline"></a>

### Customizing the Authentication Pipeline

Laravel Fortify 會通過一組由 Invokable 類別組成的 ^[Pipeline](%E7%AE%A1%E9%81%93)來驗證登入 Request。若有需要，可定義一組自定的類別 Pipeline 來讓登入 Request 通過。每個類別都應有 `__invoke` 方法，該方法會收到 `Illuminate\Http\Request` 實體。然後，像 [Middleware](/docs/{{version}}/middleware) 一樣，呼叫 `$next` 變數來將 Request 傳到 Pipeline 中的下一個類別。

若要定義自定 Pipeline，可以使用 `Fortify::authenticateThrough` 方法。該方法接受一個閉包，用來回傳一組類別陣列，好讓登入 Request 可通過該 Pipeline。一般來說，該方法應在 `App\Providers\FortifyServiceProvider` 類別的 `boot` 方法內呼叫。

下方的範例包含了一組預設的 Pipeline 定義，可讓你在自行修改 Pipeline 時作為參考：

```php
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use Illuminate\Http\Request;

Fortify::authenticateThrough(function (Request $request) {
    return array_filter([
            config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
            Features::enabled(Features::twoFactorAuthentication()) ? RedirectIfTwoFactorAuthenticatable::class : null,
            AttemptToAuthenticate::class,
            PrepareAuthenticatedSession::class,
    ]);
});
```
<a name="customizing-authentication-redirects"></a>

### 自定重新導向

若登入嘗試成功，Fortify 會將你重新導向到專案 `fortify` 設定檔中 `home` 設定選項所指定的 URI。若登入 Request 是 XHR Request，則會回傳 200 HTTP Response。使用者登出後網站後，會被重新導向到 `/` URI。

若有需要自定此行為，可將 `LoginResponse` 與 `LogoutResponse` Contract 的實作繫結到 Laravel 的 [Service Container](/docs/{{version}}/container) 中。一般來說，這些繫結應放在專案 `App\Providers\FortifyServiceProvider` 的 `register` 方法內：

```php
use Laravel\Fortify\Contracts\LogoutResponse;

/**
 * Register any application services.
 */
public function register(): void
{
    $this->app->instance(LogoutResponse::class, new class implements LogoutResponse {
        public function toResponse($request)
        {
            return redirect('/');
        }
    });
}
```
<a name="two-factor-authentication"></a>

## 兩階段驗證

當啟用了 Fortify 的兩步驟驗證時，使用者在登入過程中需要輸入一組 6 位數的 Token。這個 Token 是使用 TOTP (基於時間的一次性密碼，Time-based One-Time Password) 來產生的，可使用如 Google Authenticator 之類任何的 TOTP 相容行動驗證應用程式來取得。

在開始前，請先確保專案的 `App\Models\User` Model 有 use  `Laravel\Fortify\TwoFactorAuthenticatable` Trait：

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use Notifiable, TwoFactorAuthenticatable;
}
```
接著，我們需要在網站中加上一個畫面，讓使用者可管理其兩步驟驗證設定。該畫面應可讓使用者啟用與禁用兩步驟驗證，以及重新產生其兩步驟驗證恢復代碼。

> 預設情況下，`fortify` 設定檔中的 `features` 陣列應有設定讓 Fortify 要求使用者先驗證密碼，才可修改兩步驟驗證設定。因此，你的專案應先實作 Fortify 的[密碼確認](#password-confirmation)功能，才能繼續使用此功能。
<a name="enabling-two-factor-authentication"></a>

### 啟用兩步驟驗證

若要啟用兩步驟驗證，你的網站需要先向 Fortify 所定義的 `/user/two-factor-authentication` Endpoint 傳送 POST Request。若 Request 成功，則使用者會被重新導向回之前的 URL，並且 `status` Session 變數會被設為 `two-factor-authentication-enabled`。我們可以在樣板中檢查這個 `status` Session 變數，並顯示適當的成功訊息。若 Request 為 XHR Request，則會回傳 `200` HTTP Response：

若選擇啟用兩步驟驗證，則使用者必須先提供一個有效的 2FA 代碼來「確認」其 2FA 設定。因此，畫面上的「成功」訊息應提示使用者仍需進一步確認 2FA：

```html
@if (session('status') == 'two-factor-authentication-enabled')
    <div class="mb-4 font-medium text-sm">
        Please finish configuring two factor authentication below.
    </div>
@endif
```
接著，應顯示兩步驟驗證的 QR Code，來供使用者的驗證 App 掃描。若使用 Blade 來轉譯專案的前端，可使用使用者實體上的 `twoFactorQrCodeSvg` 方法來取得 QR Code 的 SVG：

```php
$request->user()->twoFactorQrCodeSvg();
```
若使用基於 JavaScript 驅動的前端，可以向 `/user/two-factor-qr-code` Endpoint 建立一個 XHR GET Request，以取得使用者的兩步驟驗證 QR Code。該 Endpoint 會回傳一個包含 `svg` 索引鍵的 JSON 物件。

<a name="confirming-two-factor-authentication"></a>

#### 確認 2FA

除了顯示使用者的 2FA QR Code 外，也應提供一個文字輸入框來讓使用者提供有效的 2FA 代碼以「確認」其 2FA 設定。此代碼應通過向 Fortify 所定義的 `/user/confirmed-two-factor-authentication` Endpoint 傳送 POST Request 來提供給 Laravel。

若 Request 成功，則使用者會被重新導向回其之前瀏覽的 URL，而 `status` Session 變數會被設為 `two-factor-authentication-confirmed`：

```html
@if (session('status') == 'two-factor-authentication-confirmed')
    <div class="mb-4 font-medium text-sm">
        Two factor authentication confirmed and enabled successfully.
    </div>
@endif
```
若使用 XHR Request 來向 2FA 確認 Endpoint 傳送 Request，則會回傳 `200` HTTP Response。

<a name="displaying-the-recovery-codes"></a>

#### Displaying the Recovery Codes

應在網站上顯示使用者的兩步驟驗證恢復代碼。這些恢復代碼可讓使用者在無法存取其行動裝置時用來登入。若使用 Blade 來轉譯專案前端，可以使用已登入使用者實體來存取這些恢復代碼：

```php
(array) $request->user()->recoveryCodes()
```
在製作基於 JavaScript 驅動的前端時，可以向 `/user/two-factor-recovery-codes` Endpoint 傳送 XHR GET Request。該 Endpoint 會回傳一個包含使用者恢復代碼的 JSON 陣列。

若要重新產生使用者的恢復代碼，請向 `/user/two-factor-recovery-codes` Endpoint 建立 POST Request。

<a name="authenticating-with-two-factor-authentication"></a>

### 使用兩步驟驗證進行登入

在登入過程，Fortify 會自動將使用者重新導向到兩步驟驗證畫面。不過，若你的網站使用 XHR 的登入 Request，則成功登入時會回傳一個 JSON Response，其中包含一個擁有 `two_factor` 布林屬性的 JSON 物件。請檢查該值，以判斷是否應將使用者重新導向到網站的兩步驟驗證畫面。

若要開始實作兩步驟驗證功能，我們需要先告訴 Fortify 如何回傳兩步驟驗證畫面。Fortify 中所有的登入驗證 View 轉譯邏輯都可使用 `Laravel\Fortify\Fortify` 類別上相應的方法來自定。一般來說，應在專案的 `App\Providers\FortifyServiceProvider` 類別的 `boot` 方法內呼叫該方法：

```php
use Laravel\Fortify\Fortify;

/**
 * Bootstrap any application services.
 */
public function boot(): void
{
    Fortify::twoFactorChallengeView(function () {
        return view('auth.two-factor-challenge');
    });

    // ...
}
```
Fortify 會負責處理 `/two-factor-challenge` Route 定義，並回傳該 View。`two-factor-challenge` 樣板應包含一個表單，用於向 `/two-factor-challenge` Endpoint 傳送 POST Request。`/two-factor-challenge` 動作預期收到一個 `code` 欄位，其中包含有效的 TOTP Token，或是一個包含使用者恢復代碼的 `recovery_code` 欄位。

若登入嘗試成功，Fortify 會將使用者重新導向到專案 `fortify` 設定檔中 `home` 設定選項所指定的 URI。若登入 Request 是 XHR Request，則會回傳 204 HTTP Response。

若 Request 未成功，則使用者會被重新導向回兩步驟驗證畫面，而共用的 `$errors` [Blade 樣板變數](/docs/{{version}}/validation#quick-displaying-the-validation-errors) 中會包含驗證錯誤訊息。或者，若使用的是 XHR Request，則會使用 422 HTTP Response 來回傳驗證錯誤。

<a name="disabling-two-factor-authentication"></a>

### 禁用兩步驟驗證

若要禁用兩步驟驗證，則你的網站需要向 `/user/two-factor-authentication` Endpoint 傳送一個 DELETE Request。請記得，需要先[確認密碼](#password-confirmation)，才能呼叫 Fortify 的兩步驟驗證 Endpoint。

<a name="registration"></a>

## 註冊

若要開始實作網站的登入功能，我們需要告訴 Fortify 如何回傳「註冊」View。請記得，Fortify 是一個無周邊 (Headless) 的登入函式庫。若你想要現成的完整 Laravel 登入功能前端實作，請使用[專案入門套件](/docs/{{version}}/starter-kits)。

Fortify 中所有的 View 轉譯邏輯都可使用 `Laravel\Fortify\Fortify` 類別中相應的方法來客製化。一般來說，應該在專案的 `App\Providers\FortifyServiceProvider` 類別中的 `boot` 方法內呼叫這些方法：

```php
use Laravel\Fortify\Fortify;

/**
 * Bootstrap any application services.
 */
public function boot(): void
{
    Fortify::registerView(function () {
        return view('auth.register');
    });

    // ...
}
```
Fortify 會處理好 `/register` Route 的定義，並回傳這個 View。`register` 樣板中應包含一個表單，用來向 Fortify 定義的 `/register` Endpoint 建立 POST Request。

應傳入一組字串的 `name`、字串的 E-Mail 位址或使用者名稱、`password` 與 `password_confirmation` 欄位給 `/register` Endpoint。E-Mail 位址或使用者名稱欄位的名稱應符合專案中 `fortify` 設定檔內定義的 `username` 設定值一致。

若註冊嘗試成功，Fortify 會將使用者重新導向到專案 `fortify` 設定檔中 `home` 設定選項所指定的 URI。若 Request 是 XHR Request，則會回傳 201 HTTP Response。

若 Request 未成功，則使用者會被重新導向回註冊畫面，而共用的 `$errors` [Blade 樣板變數](/docs/{{version}}/validation#quick-displaying-the-validation-errors) 中會包含驗證錯誤訊息。或者，若使用的是 XHR Request，則會使用 422 HTTP Response 來回傳驗證錯誤。

<a name="customizing-registration"></a>

### 自定註冊

只要修改安裝 Laravel Fortify 時產生的 `App\Actions\Fortify\CreateNewUser` 動作，就可自定驗證與建立使用者的流程。

<a name="password-reset"></a>

## 重設密碼

<a name="requesting-a-password-reset-link"></a>

### Requesting a Password Reset Link

若要開始實作網站的密碼重設功能，我們需要告訴 Fortify 如何回傳「忘記密碼」View。請記得，Fortify 是一個無周邊 (Headless) 的登入函式庫。若你想要現成的完整 Laravel 登入功能前端實作，請使用[專案入門套件](/docs/{{version}}/starter-kits)。

Fortify 中所有的 View 轉譯邏輯都可使用 `Laravel\Fortify\Fortify` 類別中相應的方法來客製化。一般來說，應在 `App\Providers\FortifyServiceProvider` 類別中的 `boot` 方法內呼叫這些方法：

```php
use Laravel\Fortify\Fortify;

/**
 * Bootstrap any application services.
 */
public function boot(): void
{
    Fortify::requestPasswordResetLinkView(function () {
        return view('auth.forgot-password');
    });

    // ...
}
```
Fortify 會處理好用來回傳此 View 的 `/forgot-password` Endpoint。`forgot-password` 樣板中應包含一個表單，用來向 `/forgot-password` Endpoint 建立 POST Request。

應傳入一個字串的 `email` 欄位給 `/forgot-password` Endpoint。該欄位與資料庫欄位的名稱應符合專案中 `fortify` 設定檔內的 `email` 設定值。

<a name="handling-the-password-reset-link-request-response"></a>

#### Handling the Password Reset Link Request Response

若要求密碼重設連結成功，Fortify 會將使用者重新導向回 `/forgot-password` Endpoint，並傳送一封包含安全連結的 E-Mail 給該使用者，以供重設密碼。若使用 XHR Request，則會回傳 200 HTTP Response。

Request 成功並被重新導向回 `/forget-password` Enpoint 後，可以使用 `status` Session 變數來顯示密碼重設連結 Request 嘗試的狀態：

`$status` Session 的值會是專案中 `passwords` [語系檔](/docs/{{version}}/localization)內的其中一個翻譯字串。如果你還沒安裝 Laravel 的語系檔，並想自定這個值的話，可以使用 `lang:publish` Artisan 指令來將 Laravel 的語系檔安裝到專案內：

```html
@if (session('status'))
    <div class="mb-4 font-medium text-sm text-green-600">
        {{ session('status') }}
    </div>
@endif
```
若 Request 未成功，則使用者會被重新導向回密碼重設連結請求畫面，而共用的 `$errors` [Blade 樣板變數](/docs/{{version}}/validation#quick-displaying-the-validation-errors) 中會包含驗證錯誤訊息。或者，若使用的是 XHR Request，則會使用 422 HTTP Response 來回傳驗證錯誤。

<a name="resetting-the-password"></a>

### Resetting the Password

要實作網站密碼重設功能的最後一個步驟，我們需要告訴 Fortify 如何回傳「重設密碼」View。

Fortify 中所有的 View 轉譯邏輯都可使用 `Laravel\Fortify\Fortify` 類別中相應的方法來客製化。一般來說，應在 `App\Providers\FortifyServiceProvider` 類別中的 `boot` 方法內呼叫這些方法：

```php
use Laravel\Fortify\Fortify;
use Illuminate\Http\Request;

/**
 * Bootstrap any application services.
 */
public function boot(): void
{
    Fortify::resetPasswordView(function (Request $request) {
        return view('auth.reset-password', ['request' => $request]);
    });

    // ...
}
```
Fortify 會處理好用來顯示此 View 的 Route。`reset-password` 樣板中應包含一個表單，用來向 `/reset-password` Endpoint 建立 POST Request。

應傳入一組字串的 `email`、`password`、`password_confirmation`、以及一個隱藏的 `token` 欄位給 /reset-password` Endpoint。`token`欄位應包含`request()->route('token')`的值。而「電子郵件」欄位與資料庫欄位的名稱應與專案中`fortify`設定檔的`email` 設定值相同。

<a name="handling-the-password-reset-response"></a>

#### Handling the Password Reset Response

成功重設密碼後，Fortify 會重新導向回 `/login` Route，好讓使用者能以新密碼登入。此外，Fortify 也會設定 `status` Session 變數，讓你能在登入畫面上顯示重設的成功狀態：

```blade
@if (session('status'))
    <div class="mb-4 font-medium text-sm text-green-600">
        {{ session('status') }}
    </div>
@endif
```
若 Request 為 XHR Request，則會回傳 200 HTTP Response。

若 Request 未成功，則使用者會被重新導向回重設密碼畫面，而共用的 `$errors` [Blade 樣板變數](/docs/{{version}}/validation#quick-displaying-the-validation-errors) 中會包含驗證錯誤訊息。或者，若使用的是 XHR Request，則會使用 422 HTTP Response 來回傳驗證錯誤。

<a name="customizing-password-resets"></a>

### 自定密碼重設

只要修改安裝 Laravel Fortify 時產生的 `App\Actions\Fortify\ResetUserPassword` 動作，就可自定重設密碼的流程。

<a name="email-verification"></a>

## E-Mail 驗證

註冊好後，你可能也會想讓使用者在繼續使用網站前先驗證其 E-Mail 位址。若要驗證使用者的 `E-Mail` 位址，請在 `fortify` 設定檔中的 `features` 陣列內啟用 `emailVerification` 功能。接著，請確保有 `App\Models\User` 類別有實作 `Illuminate\Contracts\Auth\MustVerifyEmail` 介面。

完成這兩個步驟後，新註冊的使用者就會收到一封 E-Mail，提醒他們要驗證其 E-Mail 位址。不過，我們還需要告訴 Fortify 如何顯示 E-Mail 驗證畫面，用來告訴使用者要點擊 E-Mail 中的驗證連結。

Fortify 中所有的 View 轉譯邏輯都可使用 `Laravel\Fortify\Fortify` 類別中相應的方法來客製化。一般來說，應在 `App\Providers\FortifyServiceProvider` 類別中的 `boot` 方法內呼叫這些方法：

```php
use Laravel\Fortify\Fortify;

/**
 * Bootstrap any application services.
 */
public function boot(): void
{
    Fortify::verifyEmailView(function () {
        return view('auth.verify-email');
    });

    // ...
}
```
Fortify 會處理好用來使用者被 Laravel 內建的 `verified` Middleware 重新導向到 `/email/verify` Endpoint 時顯示這個 View 的 Route 定義。

`verify-email` 樣板中應顯示提示訊息，告訴使用者要點擊寄送到其 E-Mail 位址內的 E-Mail 驗證連結。

<a name="resending-email-verification-links"></a>

#### 重新寄送驗證電子郵件

若有需要，也可以在 `verify-email` 樣板內加上一個按鈕，用來向 `/email/verification-notification` Endpoint 傳送 POST Request。當此 Endpoint 收到 Request 後，會寄送一封新的 E-Mail 驗證連結給該使用者，以免使用者不小心刪除或沒收到之前寄送的驗證連結。

若重新寄送驗證連結的 Request 成功，則 Fortify 會將使用者重新導向回 `/email/verify` Endpoint，並設定 `status` Session 變數，讓你可以顯示提示訊息告訴使用者此操作已成功執行。若 Request 為 XHR Request，則會回傳 202 HTTP Response：

```blade
@if (session('status') == 'verification-link-sent')
    <div class="mb-4 font-medium text-sm text-green-600">
        A new email verification link has been emailed to you!
    </div>
@endif
```
<a name="protecting-routes"></a>

### 保護 Route

To specify that a route or group of routes requires that the user has verified their email address, you should attach Laravel's built-in `verified` middleware to the route. The `verified` middleware alias is automatically registered by Laravel and serves as an alias for the `Illuminate\Routing\Middleware\ValidateSignature` middleware:

```php
Route::get('/dashboard', function () {
    // ...
})->middleware(['verified']);
```
<a name="password-confirmation"></a>

## 密碼確認

在製作網站時，有時候會有一些動作是在執行前需要先向使用者要求確認密碼的。一般來說，這些 Route 是由 Laravel 的內建 `password.confirm` Middleware 所保護的。

若要開始實作網站的密碼確認功能，我們需要告訴 Fortify 如何回傳「確認密碼」View。請記得，Fortify 是一個無周邊 (Headless) 的登入函式庫。若你想要現成的完整 Laravel 登入功能前端實作，請使用[專案入門套件](/docs/{{version}}/starter-kits)。

Fortify 中所有的 View 轉譯邏輯都可使用 `Laravel\Fortify\Fortify` 類別中相應的方法來客製化。一般來說，應在 `App\Providers\FortifyServiceProvider` 類別中的 `boot` 方法內呼叫這些方法：

```php
use Laravel\Fortify\Fortify;

/**
 * Bootstrap any application services.
 */
public function boot(): void
{
    Fortify::confirmPasswordView(function () {
        return view('auth.confirm-password');
    });

    // ...
}
```
Fortify 會處理好用來回傳此 View 的 `/user/confirm-password` Endpoint。`confirm-password` 樣板中應包含一個表單，用來向 `/user/confirm-password` Endpoint 建立 POST Request。應傳送一個包含使用者目前密碼的 `password` 欄位給 `/user/confirm-password` Endpoint。

若送出的密碼符合該使用者目前的密碼，則 Fortify 會將使用者重新導向回該使用者原本嘗試存取的 Route。若 Request 為 XHR Request，則會回傳 201 HTTP Response。

若 Request 未成功，則使用者會被重新導向回確認密碼畫面，而共用的 `$errors` Blade 樣板變數中會包含驗證錯誤訊息。或者，若使用的是 XHR Request，則會使用 422 HTTP Response 來回傳驗證錯誤。
