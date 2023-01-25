---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/75/en-zhtw
progress: 35
updatedAt: '2023-01-25T07:02:00Z'
---

# Laravel Fortify

- [簡介](#introduction)
   - [什麼是 Fortify？](#what-is-fortify)
   - [什麼時候該用 Fortify？](#when-should-i-use-fortify)
- [安裝](#installation)
   - [Fortify Service Provider](#the-fortify-service-provider)
   - [Fortify 功能](#fortify-features)
   - [禁用 View](#disabling-views)
- [身份認證](#authentication)
   - [自定使用者身份認證](#customizing-user-authentication)
   - [自定身份認證的 Pipeline](#customizing-the-authentication-pipeline)
   - [自定 Redirect](#customizing-authentication-redirects)
- [二步驟認證](#two-factor-authentication)
   - [啟用二步驟認證](#enabling-two-factor-authentication)
   - [使用二步驟認證來登入](#authenticating-with-two-factor-authentication)
   - [禁用二步驟認證](#disabling-two-factor-authentication)
- [註冊](#registration)
   - [自定註冊](#customizing-registration)
- [重設密碼](#password-reset)
   - [產生密碼重設連結](#requesting-a-password-reset-link)
   - [重設密碼](#resetting-the-password)
   - [自定密碼重設功能](#customizing-password-resets)
- [E-Mail 驗證](#email-verification)
   - [保護路由](#protecting-routes)
- [確認密碼](#password-confirmation)

<a name="introduction"></a>

## 簡介

[Laravel Fortify](https://github.com/laravel/fortify) 是一個可搭配任意前端的 Laravel 登入後端實作。Fortify 會註冊實作了所有 Laravel 登入功能的路由與 Controller，包含登入、註冊、密碼重設、E-Mail 驗證等功能。安裝完 Fortify 後，可以執行 `route:list` Artisan 指令，來看看 Fortify 註冊了哪些路由。

由於 Fortify 並不提供 UI，因此你需要自行實作使用這些路由的 UI。在本說明文件中，我們會在稍後討論如何向這些路由建立 Request。

> {tip} 請記得，Fortify 的功能是要讓你能在實作 Laravel 的登入功能時能更快上手。**你也可以不使用 Foritfy**。若你想要的話，也可以參考 [登入驗證](/docs/{{version}}/authentication)、[密碼重設](/docs/{{version}}/passwords)、與 [E-Mail 驗證](/docs/{{version}}/verification)等說明文件來手動使用 Laravel 的登入服務。

<a name="what-is-fortify"></a>

### Fortify 是什麼？

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

#### Laravel Fortify 與 Laravel Sanctum

有的開發人員會搞不清楚 [Laravel Sanctum](/docs/{{version}}/sanctum) 與 Laravel Fortify 間的差異。這是因為，這兩個套件分別解決了兩個不同但又相關的問題，而 Laravel Fortify 與 Laravel Sanctum 間並不互斥，也不是彼此的替代品。

Laravel Sanctum 只關心如何管理 API Token，以及如何以 Session Cookies 或 Token 來登入現有的使用者。Sanctum 不提供任何處理使用者註冊、密碼重設等的路由。

若要嘗試為有提供 API 或 SPA (單一頁面應用程式，Single-Page Application) 等網站自行建立登入功能，那麼你很有可能會同時用到 Laravel Fortify (用於註冊、重設密碼等) 與 Laravel Sanctum (管理 API Token、Session 登入)。

<a name="installation"></a>

## 安裝

若要開始使用 Fortify，可使用 Composer 套件管理員來安裝：

```shell
composer require laravel/fortify
```

接著，使用 `vendor:publish` 指令來安裝 Fortify 的資源：

```shell
php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"
```

這個指令會將 Fortify 的 ^[Action](動作) 安裝到 `app/Actions` 目錄下，如果這個目錄不存在的話，該指令也會一併建立。然後，也會安裝 Fortify 的設定檔與 Migration 檔案。

接著，請 Migrate 資料庫：

```shell
php artisan migrate
```

<a name="the-fortify-service-provider"></a>

### Fortify Service Provider

剛才提到的 `vendor:publish` 指令也會安裝 `App\Providers\FortifyServiceProvider` 類別。請確認是否有在 `config/app.php` 設定檔中的 `providers` 陣列中註冊這個類別。

Fortify Service Provider 會註冊 Fortify 所安裝的 Action，並告訴 Fortify 要如何使用這些 Action，以讓 Fortify 來執行並完成其所對應的任務。

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

#### 禁用 View 與密碼重設

若你選擇禁用 Fortify 的 View，但又有需要實作網站的密碼重設功能，則還是需要定義一個名稱為 `password.reset` 的路由，以用於顯示網站的「重設密碼」View。定義這個路由是有必要的，因為 Laravel 的 `Illuminate\Auth\Notifications\ResetPassword` 通知會使用這個 `password.reset` 命名路由來產生密碼重設連結。

<a name="authentication"></a>

## 登入

若要開始製作登入功能，我們需要告訴 Fortify 如何回傳「登入」View。請記得，Fortify 是一個無周邊 (Headless) 的登入函式庫。若你想要現成的完整 Laravel 登入功能前端實作，請使用[專案入門套件](/docs/{{version}}/starter-kits)。

對於所有登入功能的 View，都可使用 `Laravel\Fortify\Fortify` 類別中相應的方法來自定其轉譯邏輯一般來說，你可以在專案的 `App\Providers\FortifyServiceProvider` 類別中的 `boot` 方法內呼叫這些方法。Fortify 會處理並定義 `/login` 路由，來回傳這個 View：

    use Laravel\Fortify\Fortify;
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Fortify::loginView(function () {
            return view('auth.login');
        });
    
        // ...
    }

Your login template should include a form that makes a POST request to `/login`. The `/login` endpoint expects a string `email` / `username` and a `password`. The name of the email / username field should match the `username` value within the `config/fortify.php` configuration file. In addition, a boolean `remember` field may be provided to indicate that the user would like to use the "remember me" functionality provided by Laravel.

If the login attempt is successful, Fortify will redirect you to the URI configured via the `home` configuration option within your application's `fortify` configuration file. If the login request was an XHR request, a 200 HTTP response will be returned.

If the request was not successful, the user will be redirected back to the login screen and the validation errors will be available to you via the shared `$errors` [Blade template variable](/docs/{{version}}/validation#quick-displaying-the-validation-errors). Or, in the case of an XHR request, the validation errors will be returned with the 422 HTTP response.

<a name="customizing-user-authentication"></a>

### Customizing User Authentication

Fortify will automatically retrieve and authenticate the user based on the provided credentials and the authentication guard that is configured for your application. However, you may sometimes wish to have full customization over how login credentials are authenticated and users are retrieved. Thankfully, Fortify allows you to easily accomplish this using the `Fortify::authenticateUsing` method.

This method accepts a closure which receives the incoming HTTP request. The closure is responsible for validating the login credentials attached to the request and returning the associated user instance. If the credentials are invalid or no user can be found, `null` or `false` should be returned by the closure. Typically, this method should be called from the `boot` method of your `FortifyServiceProvider`:

```php
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Fortify;

/**
 * Bootstrap any application services.
 *
 * @return void
 */
public function boot()
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

#### Authentication Guard

You may customize the authentication guard used by Fortify within your application's `fortify` configuration file. However, you should ensure that the configured guard is an implementation of `Illuminate\Contracts\Auth\StatefulGuard`. If you are attempting to use Laravel Fortify to authenticate an SPA, you should use Laravel's default `web` guard in combination with [Laravel Sanctum](https://laravel.com/docs/sanctum).

<a name="customizing-the-authentication-pipeline"></a>

### Customizing The Authentication Pipeline

Laravel Fortify authenticates login requests through a pipeline of invokable classes. If you would like, you may define a custom pipeline of classes that login requests should be piped through. Each class should have an `__invoke` method which receives the incoming `Illuminate\Http\Request` instance and, like [middleware](/docs/{{version}}/middleware), a `$next` variable that is invoked in order to pass the request to the next class in the pipeline.

To define your custom pipeline, you may use the `Fortify::authenticateThrough` method. This method accepts a closure which should return the array of classes to pipe the login request through. Typically, this method should be called from the `boot` method of your `App\Providers\FortifyServiceProvider` class.

The example below contains the default pipeline definition that you may use as a starting point when making your own modifications:

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

### Customizing Redirects

If the login attempt is successful, Fortify will redirect you to the URI configured via the `home` configuration option within your application's `fortify` configuration file. If the login request was an XHR request, a 200 HTTP response will be returned. After a user logs out of the application, the user will be redirected to the `/` URI.

If you need advanced customization of this behavior, you may bind implementations of the `LoginResponse` and `LogoutResponse` contracts into the Laravel [service container](/docs/{{version}}/container). Typically, this should be done within the `register` method of your application's `App\Providers\FortifyServiceProvider` class:

```php
use Laravel\Fortify\Contracts\LogoutResponse;

/**
 * Register any application services.
 *
 * @return void
 */
public function register()
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

## Two Factor Authentication

When Fortify's two factor authentication feature is enabled, the user is required to input a six digit numeric token during the authentication process. This token is generated using a time-based one-time password (TOTP) that can be retrieved from any TOTP compatible mobile authentication application such as Google Authenticator.

Before getting started, you should first ensure that your application's `App\Models\User` model uses the `Laravel\Fortify\TwoFactorAuthenticatable` trait:

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

Next, you should build a screen within your application where users can manage their two factor authentication settings. This screen should allow the user to enable and disable two factor authentication, as well as regenerate their two factor authentication recovery codes.

> By default, the `features` array of the `fortify` configuration file instructs Fortify's two factor authentication settings to require password confirmation before modification. Therefore, your application should implement Fortify's [password confirmation](#password-confirmation) feature before continuing.

<a name="enabling-two-factor-authentication"></a>

### Enabling Two Factor Authentication

To enable two factor authentication, your application should make a POST request to the `/user/two-factor-authentication` endpoint defined by Fortify. If the request is successful, the user will be redirected back to the previous URL and the `status` session variable will be set to `two-factor-authentication-enabled`. You may detect this `status` session variable within your templates to display the appropriate success message. If the request was an XHR request, `200` HTTP response will be returned:

```html
@if (session('status') == 'two-factor-authentication-enabled')
    <div class="mb-4 font-medium text-sm text-green-600">
        Two factor authentication has been enabled.
    </div>
@endif
```

Next, you should display the two factor authentication QR code for the user to scan into their authenticator application. If you are using Blade to render your application's frontend, you may retrieve the QR code SVG using the `twoFactorQrCodeSvg` method available on the user instance:

```php
$request->user()->twoFactorQrCodeSvg();
```

If you are building a JavaScript powered frontend, you may make an XHR GET request to the `/user/two-factor-qr-code` endpoint to retrieve the user's two factor authentication QR code. This endpoint will return a JSON object containing an `svg` key.

<a name="displaying-the-recovery-codes"></a>

#### Displaying The Recovery Codes

You should also display the user's two factor recovery codes. These recovery codes allow the user to authenticate if they lose access to their mobile device. If you are using Blade to render your application's frontend, you may access the recovery codes via the authenticated user instance:

```php
(array) $request->user()->recoveryCodes()
```

If you are building a JavaScript powered frontend, you may make an XHR GET request to the `/user/two-factor-recovery-codes` endpoint. This endpoint will return a JSON array containing the user's recovery codes.

To regenerate the user's recovery codes, your application should make a POST request to the `/user/two-factor-recovery-codes` endpoint.

<a name="authenticating-with-two-factor-authentication"></a>

### Authenticating With Two Factor Authentication

During the authentication process, Fortify will automatically redirect the user to your application's two factor authentication challenge screen. However, if your application is making an XHR login request, the JSON response returned after a successful authentication attempt will contain a JSON object that has a `two_factor` boolean property. You should inspect this value to know whether you should redirect to your application's two factor authentication challenge screen.

To begin implementing two factor authentication functionality, we need to instruct Fortify how to return our two factor authentication challenge view. All of Fortify's authentication view rendering logic may be customized using the appropriate methods available via the `Laravel\Fortify\Fortify` class. Typically, you should call this method from the `boot` method of your application's `App\Providers\FortifyServiceProvider` class:

```php
use Laravel\Fortify\Fortify;

/**
 * Bootstrap any application services.
 *
 * @return void
 */
public function boot()
{
    Fortify::twoFactorChallengeView(function () {
        return view('auth.two-factor-challenge');
    });

    // ...
}
```

Fortify will take care of defining the `/two-factor-challenge` route that returns this view. Your `two-factor-challenge` template should include a form that makes a POST request to the `/two-factor-challenge` endpoint. The `/two-factor-challenge` action expects a `code` field that contains a valid TOTP token or a `recovery_code` field that contains one of the user's recovery codes.

If the login attempt is successful, Fortify will redirect the user to the URI configured via the `home` configuration option within your application's `fortify` configuration file. If the login request was an XHR request, a 204 HTTP response will be returned.

If the request was not successful, the user will be redirected back to the two factor challenge screen and the validation errors will be available to you via the shared `$errors` [Blade template variable](/docs/{{version}}/validation#quick-displaying-the-validation-errors). Or, in the case of an XHR request, the validation errors will be returned with a 422 HTTP response.

<a name="disabling-two-factor-authentication"></a>

### Disabling Two Factor Authentication

To disable two factor authentication, your application should make a DELETE request to the `/user/two-factor-authentication` endpoint. Remember, Fortify's two factor authentication endpoints require [password confirmation](#password-confirmation) prior to being called.

<a name="registration"></a>

## Registration

To begin implementing our application's registration functionality, we need to instruct Fortify how to return our "register" view. Remember, Fortify is a headless authentication library. If you would like a frontend implementation of Laravel's authentication features that are already completed for you, you should use an [application starter kit](/docs/{{version}}/starter-kits).

All of the Fortify's view rendering logic may be customized using the appropriate methods available via the `Laravel\Fortify\Fortify` class. Typically, you should call this method from the `boot` method of your `App\Providers\FortifyServiceProvider` class:

```php
use Laravel\Fortify\Fortify;

/**
 * Bootstrap any application services.
 *
 * @return void
 */
public function boot()
{
    Fortify::registerView(function () {
        return view('auth.register');
    });

    // ...
}
```

Fortify will take care of defining the `/register` route that returns this view. Your `register` template should include a form that makes a POST request to the `/register` endpoint defined by Fortify.

The `/register` endpoint expects a string `name`, string email address / username, `password`, and `password_confirmation` fields. The name of the email / username field should match the `username` configuration value defined within your application's `fortify` configuration file.

If the registration attempt is successful, Fortify will redirect the user to the URI configured via the `home` configuration option within your application's `fortify` configuration file. If the login request was an XHR request, a 200 HTTP response will be returned.

If the request was not successful, the user will be redirected back to the registration screen and the validation errors will be available to you via the shared `$errors` [Blade template variable](/docs/{{version}}/validation#quick-displaying-the-validation-errors). Or, in the case of an XHR request, the validation errors will be returned with a 422 HTTP response.

<a name="customizing-registration"></a>

### Customizing Registration

The user validation and creation process may be customized by modifying the `App\Actions\Fortify\CreateNewUser` action that was generated when you installed Laravel Fortify.

<a name="password-reset"></a>

## Password Reset

<a name="requesting-a-password-reset-link"></a>

### Requesting A Password Reset Link

To begin implementing our application's password reset functionality, we need to instruct Fortify how to return our "forgot password" view. Remember, Fortify is a headless authentication library. If you would like a frontend implementation of Laravel's authentication features that are already completed for you, you should use an [application starter kit](/docs/{{version}}/starter-kits).

All of Fortify's view rendering logic may be customized using the appropriate methods available via the `Laravel\Fortify\Fortify` class. Typically, you should call this method from the `boot` method of your application's `App\Providers\FortifyServiceProvider` class:

```php
use Laravel\Fortify\Fortify;

/**
 * Bootstrap any application services.
 *
 * @return void
 */
public function boot()
{
    Fortify::requestPasswordResetLinkView(function () {
        return view('auth.forgot-password');
    });

    // ...
}
```

Fortify will take care of defining the `/forgot-password` endpoint that returns this view. Your `forgot-password` template should include a form that makes a POST request to the `/forgot-password` endpoint.

The `/forgot-password` endpoint expects a string `email` field. The name of this field / database column should match the `email` configuration value within your application's `fortify` configuration file.

<a name="handling-the-password-reset-link-request-response"></a>

#### Handling The Password Reset Link Request Response

If the password reset link request was successful, Fortify will redirect the user back to the `/forgot-password` endpoint and send an email to the user with a secure link they can use to reset their password. If the request was an XHR request, a 200 HTTP response will be returned.

After being redirected back to the `/forgot-password` endpoint after a successful request, the `status` session variable may be used to display the status of the password reset link request attempt. The value of this session variable will match one of the translation strings defined within your application's `passwords` [language file](/docs/{{version}}/localization):

```html
@if (session('status'))
    <div class="mb-4 font-medium text-sm text-green-600">
        {{ session('status') }}
    </div>
@endif
```

If the request was not successful, the user will be redirected back to the request password reset link screen and the validation errors will be available to you via the shared `$errors` [Blade template variable](/docs/{{version}}/validation#quick-displaying-the-validation-errors). Or, in the case of an XHR request, the validation errors will be returned with a 422 HTTP response.

<a name="resetting-the-password"></a>

### Resetting The Password

To finish implementing our application's password reset functionality, we need to instruct Fortify how to return our "reset password" view.

All of Fortify's view rendering logic may be customized using the appropriate methods available via the `Laravel\Fortify\Fortify` class. Typically, you should call this method from the `boot` method of your application's `App\Providers\FortifyServiceProvider` class:

```php
use Laravel\Fortify\Fortify;

/**
 * Bootstrap any application services.
 *
 * @return void
 */
public function boot()
{
    Fortify::resetPasswordView(function ($request) {
        return view('auth.reset-password', ['request' => $request]);
    });

    // ...
}
```

Fortify will take care of defining the route to display this view. Your `reset-password` template should include a form that makes a POST request to `/reset-password`.

The `/reset-password` endpoint expects a string `email` field, a `password` field, a `password_confirmation` field, and a hidden field named `token` that contains the value of `request()->route('token')`. The name of the "email" field / database column should match the `email` configuration value defined within your application's `fortify` configuration file.

<a name="handling-the-password-reset-response"></a>

#### Handling The Password Reset Response

If the password reset request was successful, Fortify will redirect back to the `/login` route so that the user can log in with their new password. In addition, a `status` session variable will be set so that you may display the successful status of the reset on your login screen:

```blade
@if (session('status'))
    <div class="mb-4 font-medium text-sm text-green-600">
        {{ session('status') }}
    </div>
@endif
```

If the request was an XHR request, a 200 HTTP response will be returned.

If the request was not successful, the user will be redirected back to the reset password screen and the validation errors will be available to you via the shared `$errors` [Blade template variable](/docs/{{version}}/validation#quick-displaying-the-validation-errors). Or, in the case of an XHR request, the validation errors will be returned with a 422 HTTP response.

<a name="customizing-password-resets"></a>

### Customizing Password Resets

The password reset process may be customized by modifying the `App\Actions\ResetUserPassword` action that was generated when you installed Laravel Fortify.

<a name="email-verification"></a>

## Email Verification

After registration, you may wish for users to verify their email address before they continue accessing your application. To get started, ensure the `emailVerification` feature is enabled in your `fortify` configuration file's `features` array. Next, you should ensure that your `App\Models\User` class implements the `Illuminate\Contracts\Auth\MustVerifyEmail` interface.

Once these two setup steps have been completed, newly registered users will receive an email prompting them to verify their email address ownership. However, we need to inform Fortify how to display the email verification screen which informs the user that they need to go click the verification link in the email.

All of Fortify's view's rendering logic may be customized using the appropriate methods available via the `Laravel\Fortify\Fortify` class. Typically, you should call this method from the `boot` method of your application's `App\Providers\FortifyServiceProvider` class:

```php
use Laravel\Fortify\Fortify;

/**
 * Bootstrap any application services.
 *
 * @return void
 */
public function boot()
{
    Fortify::verifyEmailView(function () {
        return view('auth.verify-email');
    });

    // ...
}
```

Fortify will take care of defining the route that displays this view when a user is redirected to the `/email/verify` endpoint by Laravel's built-in `verified` middleware.

Your `verify-email` template should include an informational message instructing the user to click the email verification link that was sent to their email address.

<a name="resending-email-verification-links"></a>

#### Resending Email Verification Links

If you wish, you may add a button to your application's `verify-email` template that triggers a POST request to the `/email/verification-notification` endpoint. When this endpoint receives a request, a new verification email link will be emailed to the user, allowing the user to get a new verification link if the previous one was accidentally deleted or lost.

If the request to resend the verification link email was successful, Fortify will redirect the user back to the `/email/verify` endpoint with a `status` session variable, allowing you to display an informational message to the user informing them the operation was successful. If the request was an XHR request, a 202 HTTP response will be returned:

```blade
@if (session('status') == 'verification-link-sent')
    <div class="mb-4 font-medium text-sm text-green-600">
        A new email verification link has been emailed to you!
    </div>
@endif
```

<a name="protecting-routes"></a>

### Protecting Routes

To specify that a route or group of routes requires that the user has verified their email address, you should attach Laravel's built-in `verified` middleware to the route. This middleware is registered within your application's `App\Http\Kernel` class:

```php
Route::get('/dashboard', function () {
    // ...
})->middleware(['verified']);
```

<a name="password-confirmation"></a>

## Password Confirmation

While building your application, you may occasionally have actions that should require the user to confirm their password before the action is performed. Typically, these routes are protected by Laravel's built-in `password.confirm` middleware.

To begin implementing password confirmation functionality, we need to instruct Fortify how to return our application's "password confirmation" view. Remember, Fortify is a headless authentication library. If you would like a frontend implementation of Laravel's authentication features that are already completed for you, you should use an [application starter kit](/docs/{{version}}/starter-kits).

All of Fortify's view rendering logic may be customized using the appropriate methods available via the `Laravel\Fortify\Fortify` class. Typically, you should call this method from the `boot` method of your application's `App\Providers\FortifyServiceProvider` class:

```php
use Laravel\Fortify\Fortify;

/**
 * Bootstrap any application services.
 *
 * @return void
 */
public function boot()
{
    Fortify::confirmPasswordView(function () {
        return view('auth.confirm-password');
    });

    // ...
}
```

Fortify will take care of defining the `/user/confirm-password` endpoint that returns this view. Your `confirm-password` template should include a form that makes a POST request to the `/user/confirm-password` endpoint. The `/user/confirm-password` endpoint expects a `password` field that contains the user's current password.

If the password matches the user's current password, Fortify will redirect the user to the route they were attempting to access. If the request was an XHR request, a 201 HTTP response will be returned.

If the request was not successful, the user will be redirected back to the confirm password screen and the validation errors will be available to you via the shared `$errors` Blade template variable. Or, in the case of an XHR request, the validation errors will be returned with a 422 HTTP response.
