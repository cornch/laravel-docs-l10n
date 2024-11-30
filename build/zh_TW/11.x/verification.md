---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/173/en-zhtw'
updatedAt: '2023-02-11T10:28:00Z'
contributors: {  }
progress: 58.97
---

# E-Mail 驗證

- [簡介](#introduction)
  - [Model 的準備](#model-preparation)
  - [資料庫的準備](#database-preparation)
  
- [路由](#verification-routing)
  - [E-Mail 驗證說明](#the-email-verification-notice)
  - [E-Mail 驗證的處理程式](#the-email-verification-handler)
  - [Resending the Verification Email](#resending-the-verification-email)
  - [受保護的 Route](#protecting-routes)
  
- [自定](#customization)
- [事件](#events)

<a name="introduction"></a>

## 簡介

許多的 Web App 都需要使用者先驗證電子郵件位址後才能繼續使用。使用 Laravel 時，開發人員不需要在每個新專案上都自行為這個功能重造輪子。Laravel 提供了方便的內建服務，可用來傳送與驗證電子郵件驗證的 Request。

> [!NOTE]  
> 想要快速入門嗎？請在全新的 Laravel 應用程式內安裝一個 [Laravel 應用程式入門套件](docs/{{version}}/starter-kits)。這些入門套件會幫你搞定整個驗證系統的 Scaffolding，其中也包含了電子郵件驗證的支援。

<a name="model-preparation"></a>

### Model 的準備

在開始之前，請先確定 `App\Models\User` Model 有實作 `Illuminate\Contracts\Auth\MustVerifyEmail` Contract：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Contracts\Auth\MustVerifyEmail;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;
    
    class User extends Authenticatable implements MustVerifyEmail
    {
        use Notifiable;
    
        // ...
    }
Once this interface has been added to your model, newly registered users will automatically be sent an email containing an email verification link. This happens seamlessly because Laravel automatically registers the `Illuminate\Auth\Listeners\SendEmailVerificationNotification` [listener](/docs/{{version}}/events) for the `Illuminate\Auth\Events\Registered` event.

若不使用[入門套件](/docs/{{version}}/starter-kits)，而是手動在專案中實作註冊功能的話，則請確保有在使用者成功註冊後分派 `Illuminate\Auth\Events\Registered` 事件：

    use Illuminate\Auth\Events\Registered;
    
    event(new Registered($user));
<a name="database-preparation"></a>

### 資料庫的準備

Next, your `users` table must contain an `email_verified_at` column to store the date and time that the user's email address was verified. Typically, this is included in Laravel's default `0001_01_01_000000_create_users_table.php` database migration.

<a name="verification-routing"></a>

## 路由

若要正確實作 E-Mail 驗證，我們需要定義三個 Route。第一個 Route 用來顯示提示，告訴使用者必須先點擊驗證郵件中的驗證連結。

第二個 Route 則用來處理使用者點擊信件中 E-Mail 驗證連結時所產生的 Request：

第三個 Route 則用來重新傳送驗證連結，以防使用者不小心遺失之前寄出的驗證連結。

<a name="the-email-verification-notice"></a>

### E-Mail 驗證提示

剛才提到過，我們需要定義一個 Route，用來回傳一個 View 告知使用者應點擊註冊後 Laravel 寄出之驗證郵件中的驗證連結。若使用者在沒有驗證 E-Mail 位址的情況下存取了專案中的某部分時，會顯示這個 View 給使用者。請記得，若 `App\Models\User` Model 有實作 `MustVerifyEmail` 介面，則驗證連結會自動寄給使用者：

    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->middleware('auth')->name('verification.notice');
回傳這個 E-Mail 驗證提示的 Route 應命名為 `verification.notice`。將 Route 命名為這個名稱非常重要，因為 [Laravel 中內建的](#protecting-routes) `verified` Middleware 會在使用者尚未驗證 E-Mail 位址時自動重新導向至該 Route 上。

> [!NOTE]  
> 手動實作 E-Mail 驗證時，我們需要自行定義驗證提示中的內容。若想要有包含所有必要之身份驗證與 E-Mail 驗證 View 的 Scaffolding，請參考 [Laravel 專案入門套件](/docs/{{version}}/starter-kits)。

<a name="the-email-verification-handler"></a>

### E-Mail 驗證的處理常式

接著，我們需要定義一個 Route 來處理使用者點擊 E-Mail 中驗證連結時會產生的 Request。該 Route 應命名為 `verification.verify`，且應指派 `auth` 與 `signed` Middleware 給該 Route：

    use Illuminate\Foundation\Auth\EmailVerificationRequest;
    
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
    
        return redirect('/home');
    })->middleware(['auth', 'signed'])->name('verification.verify');
在繼續之前，我們先來進一步看看這個 Route。首先，我們可以注意到我們用的是 `EmailVerificationRequest` Request 型別，而不是 `Illuminate\Http\Request` 實體。`EmailVerificationRequest` 是一個 Laravel 中包含的 [Form Request](/docs/{{version}}/validation#form-request-validation)。這個 Request 會自動驗證 Request 的 `id` 與 `hash` 引數。

接著，可直接呼叫該 Request 上的 `fulfill` 方法。該方法會呼叫已登入使用者上的 `markEmailAsVerified` 方法，並分派 `Illuminate\Auth\Event\Verified` 事件。`App\Models\User` 上經由 `Illuminate\Foundation\Auth\User` 基礎類別提供了 `markEmailAsVerified` 方法。驗證好使用者的 E-Mail 位址後，就可隨意將使用者重新導向至其他頁面。

<a name="resending-the-verification-email"></a>

### Resending the Verification Email

有時候，使用者可能會不小心搞丟或刪除了 E-Mail 位址驗證信件。為了這種狀況，我們可以定義一個 Route 來讓使用者要求重新寄送驗證信件。接著，我們就可以在[驗證提示 View](#the-email-verification-notice) 中加上一個按鈕來產生一個 Request 給該 Route：

    use Illuminate\Http\Request;
    
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
    
        return back()->with('message', 'Verification link sent!');
    })->middleware(['auth', 'throttle:6,1'])->name('verification.send');
<a name="protecting-routes"></a>

### 受保護的 Route

[Route middleware](/docs/{{version}}/middleware) may be used to only allow verified users to access a given route. Laravel includes a `verified` [middleware alias](/docs/{{version}}/middleware#middleware-aliases), which is an alias for the `Illuminate\Auth\Middleware\EnsureEmailIsVerified` middleware class. Since this alias is already automatically registered by Laravel, all you need to do is attach the `verified` middleware to a route definition. Typically, this middleware is paired with the `auth` middleware:

    Route::get('/profile', function () {
        // Only verified users may access this route...
    })->middleware(['auth', 'verified']);
若有未驗證的使用者嘗試存取有指派該 Middleware 的 Route，則使用者會自動被重新導向至 `verification.notice` [命名 Route](/docs/{{version}}/routing#named-routes) 中。

<a name="customization"></a>

## 自定

<a name="verification-email-customization"></a>

#### 自定驗證 E-Mail

雖然預設的 E-Mail 驗證通知應該可以滿足大部分專案的需求，但在 Laravel 中我們也能自定要如何建立 E-Mail 驗證信件的訊息。

To get started, pass a closure to the `toMailUsing` method provided by the `Illuminate\Auth\Notifications\VerifyEmail` notification. The closure will receive the notifiable model instance that is receiving the notification as well as the signed email verification URL that the user must visit to verify their email address. The closure should return an instance of `Illuminate\Notifications\Messages\MailMessage`. Typically, you should call the `toMailUsing` method from the `boot` method of your application's `AppServiceProvider` class:

    use Illuminate\Auth\Notifications\VerifyEmail;
    use Illuminate\Notifications\Messages\MailMessage;
    
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ...
    
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Verify Email Address')
                ->line('Click the button below to verify your email address.')
                ->action('Verify Email Address', $url);
        });
    }
> [!NOTE]  
> 若想瞭解更多有關郵件通知的資訊，請參考[郵件通知的說明文件](/docs/{{version}}/notifications#mail-notifications)。

<a name="events"></a>

## 事件

When using the [Laravel application starter kits](/docs/{{version}}/starter-kits), Laravel dispatches an `Illuminate\Auth\Events\Verified` [event](/docs/{{version}}/events) during the email verification process. If you are manually handling email verification for your application, you may wish to manually dispatch these events after verification is completed.
