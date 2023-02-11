---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/173/en-zhtw
progress: 100
updatedAt: '2023-02-11T10:28:00Z'
---

# E-Mail 驗證

- [簡介](#introduction)
   - [Model 的準備](#model-preparation)
   - [資料庫的準備](#database-preparation)
- [路由](#verification-routing)
   - [E-Mail 驗證說明](#the-email-verification-notice)
   - [E-Mail 驗證的處理程式](#the-email-verification-handler)
   - [重新傳送 E-Mail 驗證](#resending-the-verification-email)
   - [受保護的 Route](#protecting-routes)
- [自定](#customization)
- [事件](#events)

<a name="introduction"></a>

## 簡介

許多的 Web App 都需要使用者先驗證電子郵件位址後才能繼續使用。使用 Laravel 時，開發人員不需要在每個新專案上都自行為這個功能重造輪子。Laravel 提供了方便的內建服務，可用來傳送與驗證電子郵件驗證的 Request。

> {tip} 想要快速入門嗎？請在全新的 Laravel 應用程式內安裝一個 [Laravel 應用程式入門套件](docs/{{version}}/starter-kits)。這些入門套件會幫你搞定整個驗證系統的 Scaffolding，其中也包含了電子郵件驗證的支援。

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

將該介面加至 Model 後，就會自動傳送一封包含電子郵件驗證連結的 E-Mail 給該使用者。若打開專案的 `App\Providers\EventServiceProvider`，可以看到，Laravel 已經預先包含了一個 `SendEmailVerificationNotification` [Listener](/docs/{{version}}/events)，該 Listener 附加在 `Illuminate\Auth\Events\Registered` 事件上。該 Event Listener 會傳送電子郵件驗證連結給該使用者：

若不使用[入門套件](/docs/{{version}}/starter-kits)，而是手動在專案中實作註冊功能的話，則請確保有在使用者成功註冊後分派 `Illuminate\Auth\Events\Registered` 事件：

    use Illuminate\Auth\Events\Registered;
    
    event(new Registered($user));

<a name="database-preparation"></a>

### 資料庫的準備

接著，`users` 資料表中應包含一個 `email_verified_at` 欄位來儲存使用者驗證其電子郵件位址的日期時間。預設情況下，Laravel 內建的 `users` 資料表的 Migration 中已有該欄位。因此，我們需要做的只有執行資料庫 Migration 而已：

    php artisan migrate

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

> {tip} 手動實作 E-Mail 驗證時，我們需要自行定義驗證提示中的內容。若想要有包含所有必要之身份驗證與 E-Mail 驗證 View 的 Scaffolding，請參考 [Laravel 專案入門套件](/docs/{{version}}/starter-kits)。

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

### 重新寄送驗證電子郵件

有時候，使用者可能會不小心搞丟或刪除了 E-Mail 位址驗證信件。為了這種狀況，我們可以定義一個 Route 來讓使用者要求重新寄送驗證信件。接著，我們就可以在[驗證提示 View](#the-email-verification-notice) 中加上一個按鈕來產生一個 Request 給該 Route：

    use Illuminate\Http\Request;
    
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
    
        return back()->with('message', 'Verification link sent!');
    })->middleware(['auth', 'throttle:6,1'])->name('verification.send');

<a name="protecting-routes"></a>

### 受保護的 Route

可以使用 [Route Middleware](/docs/{{version}}/middleware) 來只讓已通過 E-Mail 驗證的使用者存取給定的 Route。Laravel 中隨附了一個 `verified` Middleware，該 Middleware 參照了 `Illuminate\Auth\Middleware\EnsureEmailIsVerified` 類別。由於該 Middleware 已預先註冊在專案的 HTTP Kernel 中了，因此我們只需要在 Route 定義中附加該 Middleware 即可：

    Route::get('/profile', function () {
        // 只有已驗證的使用者才可存取該 Route...
    })->middleware('verified');

若有未驗證的使用者嘗試存取有指派該 Middleware 的 Route，則使用者會自動被重新導向至 `verification.notice` [命名 Route](/docs/{{version}}/routing#named-routes) 中。

<a name="customization"></a>

## 自定

<a name="verification-email-customization"></a>

#### 自定驗證 E-Mail

雖然預設的 E-Mail 驗證通知應該可以滿足大部分專案的需求，但在 Laravel 中我們也能自定要如何建立 E-Mail 驗證信件的訊息。

若要開始自定郵件內容，請先傳入一個閉包給 `Illuminate\Auth\Notifications\VerifyEmail` 通知中所提供的 `toMailUsing` 方法。該閉包會收到一個會收到該通知的 Notifiable Model 實體，以及一個該使用者必須開啟才能驗證 E-Mail 之簽署過的 E-Mail 驗證網址。該閉包應回傳 `Illuminate\Notifications\Messages\MailMessage` 的實體。一般來說，應在專案的 `App\Providers\AuthServiceProvider` 類別內 `boot` 方法中呼叫 `toMailUsing` 方法：

    use Illuminate\Auth\Notifications\VerifyEmail;
    use Illuminate\Notifications\Messages\MailMessage;
    
    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        // ...
    
        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->subject('Verify Email Address')
                ->line('Click the button below to verify your email address.')
                ->action('Verify Email Address', $url);
        });
    }

> {tip} 若想瞭解更多有關郵件通知的資訊，請參考[郵件通知的說明文件](/docs/{{version}}/notifications#mail-notifications)。

<a name="events"></a>

## 事件

使用 [Laravel 專案入門套件](/docs/{{version}}/starter-kits)時，Laravel 會在 E-Mail 驗證過程中分派多個[事件](/docs/{{version}}/events)。若是在專案中手動處理 E-Mail 驗證，則我們可能需要手動在驗證完成後分派這些事件。我們可以在專案的 `EventServiceProvider` 中將 Listener 附加到這些事件上：

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Illuminate\Auth\Events\Verified' => [
            'App\Listeners\LogVerifiedUser',
        ],
    ];
