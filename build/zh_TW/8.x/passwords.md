---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/119/en-zhtw'
updatedAt: '2024-06-30T08:18:00Z'
contributors: {  }
progress: 58.51
---

# 重設密碼

- [簡介](#introduction)
  - [Model 的準備](#model-preparation)
  - [資料庫的準備](#database-preparation)
  - [設定 Trusted Hosts](#configuring-trusted-hosts)
  
- [路由](#routing)
  - [產生密碼重設連結](#requesting-the-password-reset-link)
  - [重設密碼](#resetting-the-password)
  
- [刪除過期的 Token](#deleting-expired-tokens)
- [自定](#password-customization)

<a name="introduction"></a>

## 簡介

大多數的 Web App 都提供了能讓使用者重設密碼的功能。在 Laravel 中，我們不需要為密碼重設重造輪子，Laravel 已提供了方便的服務，可讓我們傳送密碼重設連結並安全地重設密碼。

> [!TIP]  
> 想要快速入門嗎？請在全新的 Laravel 應用程式內安裝一個 [Laravel 應用程式入門套件](docs/{{version}}/starter-kits)。這些入門套件會幫你搞定整個驗證系統的 Scaffolding，其中也包含了重設忘記密碼的支援。

<a name="model-preparation"></a>

### Model 的準備

在使用 Laravel 的密碼重設功能前，專案中的 `App\Models\User` Model 必須使用 `Illuminate\Notifications\Notifiable` Trait。一般來說，在 Laravel 新專案中，預設的 `App\Models\User` 內已包含了該 Trait。

接著，請確認一下 `App\Models\User` Model 是否有實作 `Illuminate\Contracts\Auth\CanResetPassword` Contract。Laravel 中包含的 `App\Models\User` Model 已經有實作該介面了，且這個 Model 還使用了 `Illuminate\Auth\Passwords\CanResetPassword` Trait 來提供實作該介面所需要的各個方法。

<a name="database-preparation"></a>

### 資料庫的準備

我們需要一個資料表來保存網站的密碼重設 ^[Token](%E6%AC%8A%E6%9D%96)。在預設 Laravel 中已包含了用於建立該資料表的 Migration，因此我們只需要進行 Migrate 即可建立該資料表：

    php artisan migrate
<a name="configuring-trusted-hosts"></a>

### 設定信任的主機 (Trusted Hosts)

預設情況下，無論收到的 HTTP Request 中 `Host` 標頭內容為何，Laravel 都會回應所有收到的 Request。此外，Laravel 還會使用 `Host` 標頭的值來在 Request 中產生絕對路徑的網址。

一般來說，應在 Web Server 上 (如 Nginx 或 Apache) 設定只有特定的主機名稱時才將 Request 送往你的程式中。不過，若沒機會能自訂 Web Server，則需要讓 Laravel 只對特定主機名稱作回應。為此，可以啟用專案中的 `App\Http\Middleware\TrustHosts` Middleware。若網站要提供密碼重設功能，設定 Trust Host 就非常重要。

若要瞭解更多有關該 Middleware 的資訊，請參考 [`TrustHosts` Middleware 的說明文件](/docs/{{version}}/requests#configuring-trusted-hosts)。

<a name="routing"></a>

## 路由

若要正確實作讓使用者重設密碼的功能，我們需要定義多個 Route。首先，我們需要一組用來讓使用者通過 E-Mail 位址要求密碼重設連結的 Route。接著，我們需要定義一組 Route 來實際處理密碼重設功能，即，處理使用者點擊信件中的密碼重設連結後顯示與送出密碼重設表單的 Route。

<a name="requesting-the-password-reset-link"></a>

### 要求密碼重設連結

<a name="the-password-reset-link-request-form"></a>

#### 要求密碼重設連結的表單

首先，我們需要定義一個用來要求密碼重設連結的 Route。首先，我們先定義一個回傳 View 的 Route，該 View 即為要求密碼重設連結的表單：

    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->middleware('guest')->name('password.request');
該 Route 所回傳的 View 應包含一個 `email` 欄位，該欄位用來讓使用者可使用給定的 E-Mail 位址要求密碼重設連結。

<a name="password-reset-link-handling-the-form-submission"></a>

#### 處理表單的送出

接著，我們需要定義一個 Route 來處理「忘記密碼」View 送出的表單。這個 Route 會負責驗證 E-Mail 位址，並寄出密碼重設連結給對應的使用者：

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Password;
    
    Route::post('/forgot-password', function (Request $request) {
        $request->validate(['email' => 'required|email']);
    
        $status = Password::sendResetLink(
            $request->only('email')
        );
    
        return $status === Password::RESET_LINK_SENT
                    ? back()->with(['status' => __($status)])
                    : back()->withErrors(['email' => __($status)]);
    })->middleware('guest')->name('password.email');
在繼續之前，我們先來詳細看看這個 Route。首先，會先驗證 Request 的 `email` 屬性。接著，我們使用 Laravel 內建的「Password Broker」(在 `Password` Facade 上) 來傳送密碼重設連結給該使用者。Password Broker 會負責使用給定的欄位 (在這個例子中是 E-Mail 位址) 來取得使用者，並使用 Laravel 內建的[通知系統](/docs/{{version}}/notifications)來傳送密碼重設連結給使用者。

`sendResetLink` 方法會回傳一個「status」slug。我們可以使用 Laravel 的[本土化]輔助函式來翻譯這個「status」，以顯示使用者友善的訊息告知使用者其密碼要求狀態。密碼重設的翻譯會依據專案中的 `resources/lang/{lang}/passwords.php` 語系檔來判斷。在 `passwords` 語系檔中，所有可能的 status slug 值都有對應的欄位。

讀者可能會想，在呼叫 `Password` Facade 的 `sendResetLink` 時，Laravel 是怎麼知道要如何從專案資料庫中取得使用者記錄的？其實，Laravel 的 Password Broker 使用了身份驗證系統的「User Providers」來取得資料庫記錄。Password Broker 使用的 User Provider 設定在 `config/auth.php` 設定檔中的 `password` 設定陣列中。如欲瞭解更多有關如何撰寫自定 User Provider 的資訊，請參考[身份驗證說明文件](/docs/{{version}}/authentication#adding-custom-user-providers)。

> [!TIP]  
> 手動實作密碼重設功能時，我們需要自行定義這些 View 的內容與 Route。若想要有包含所有必要之身份驗證與驗證 View 的 Scaffolding，請參考 [Laravel 專案入門套件](/docs/{{version}}/starter-kits)。

<a name="resetting-the-password"></a>

### 重設密碼

<a name="the-password-reset-form"></a>

#### 密碼重設表單

接著，我們還需要定義一個 Route，能讓使用者在點擊信件中的密碼重設連結後能真的重設密碼。首先，我們先定義一個在使用者點擊密碼重設連結後顯示密碼重設表單的 Route。這個 Route 會收到一個 `token` 參數，我們在稍後會用來驗證這個密碼重設 Request。

    Route::get('/reset-password/{token}', function ($token) {
        return view('auth.reset-password', ['token' => $token]);
    })->middleware('guest')->name('password.reset');
該表單回傳的 View 應顯示一個表單，其中需包含 `email`、`password`、`password_confirmation` 等欄位，以及一個隱藏的 `token` 等欄位，該 `token` 欄位中應包含該 Route 所收到的私密 Token `$token` 值。

<a name="password-reset-handling-the-form-submission"></a>

#### 處理表單的送出

當然，我們還需要定義一個實際用來處理表單送出的 Route。這個 Route 要負責驗證連入的 Request，並更新資料庫中該使用者的密碼：

    use Illuminate\Auth\Events\PasswordReset;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Password;
    use Illuminate\Support\Str;
    
    Route::post('/reset-password', function (Request $request) {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);
    
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
    
                $user->save();
    
                event(new PasswordReset($user));
            }
        );
    
        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withErrors(['email' => [__($status)]]);
    })->middleware('guest')->name('password.update');
在繼續之前，我們先來仔細看看這個 Route。首先，在這個 Route 中會先驗證 Request 的 `token`、`email`、`password` 等欄位。接著，​我們 (通過 `Password` Facade) 使用 Laravel 內建的「Password Broker」來驗證密碼重設 Request 是否有效。

若傳給 Password Broker 的 Token、E-Mail 位址、密碼等均正確，則會呼叫傳給 `reset` 方法的閉包。這個閉包會收到 User 實體以及傳給密碼重設表單的明文密碼。在該閉包中，我們就可以更新資料庫中該使用者的密碼。

`reset` 方法會回傳一個「status」slug。我們可以使用 Laravel 的[本土化](/docs/{{version}}/localization)輔助函式來翻譯這個「status」，以顯示使用者友善的訊息告知使用者其密碼要求狀態。密碼重設的翻譯會依據專案中的 `resources/lang/{lang}/passwords.php` 語系檔來判斷。在 `passwords` 語系檔中，所有可能的 status slug 值都有對應的欄位。

在繼續之前，讀者可能會想，在呼叫 `Password` Facade 的 `reset` 時，Laravel 是怎麼知道要如何從專案資料庫中取得使用者記錄的？其實，Laravel 的 Password Broker 使用了身份驗證系統的「User Providers」來取得資料庫記錄。Password Broker 使用的 User Provider 設定在 `config/auth.php` 設定檔中的 `password` 設定陣列中。如欲瞭解更多有關如何撰寫自定 User Provider 的資訊，請參考[身份驗證說明文件](/docs/{{version}}/authentication#adding-custom-user-providers)。

<a name="deleting-expired-tokens"></a>

## 刪除過期的 Token

過期的密碼重設連結會繼續保存在資料庫中。不過，我們可以使用 `auth:clear-resets` Artisan 指令來輕鬆刪除這些記錄：

    php artisan auth:clear-resets
若想自動化這個過程，可以考慮將該指令加入專案的[排程任務](/docs/{{version}}/scheduling)中：

    $schedule->command('auth:clear-resets')->everyFifteenMinutes();
<a name="password-customization"></a>

## 自定

<a name="reset-link-customization"></a>

#### 自定重設連結

我們可以使用 `ResetPassword` 通知類別的 `createUrlUsing` 方法來自定密碼重設連結的網址。這個方法接受一個閉包，該閉包會收到要接收通知的使用者實體，以及密碼重設連結的 Token。一般來說，我們可以在 `App\Providers\AuthServiceProvider` Service Provider 的 `boot` 方法內呼叫這個方法：

    use Illuminate\Auth\Notifications\ResetPassword;
    
    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return 'https://example.com/reset-password?token='.$token;
        });
    }
<a name="reset-email-customization"></a>

#### 自定重設 E-Mail

我們也可以輕鬆調整用來傳送密碼重設連結給使用者的通知類別。若要自定該通知，請在 `App\Models\User` Model 中複寫 `sendPasswordResetNotification` 方法。在該方法中，我們可以使用任何一個自行建立的 [Notification 類別](/docs/{{version}}/notifications)來傳送通知。該方法的第一個引數是密碼重設連結的 `$token`。我們可以使用這個 `$token` 來建立我們自已的密碼重設連結並用來傳送通知給使用者：

    use App\Notifications\ResetPasswordNotification;
    
    /**
     * Send a password reset notification to the user.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $url = 'https://example.com/reset-password?token='.$token;
    
        $this->notify(new ResetPasswordNotification($url));
    }