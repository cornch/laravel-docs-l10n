---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/155/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 56.73
---

# Laravel Socialite

- [簡介](#introduction)
- [安裝](#installation)
- [更新 Socialite](#upgrading-socialite)
- [設定](#configuration)
- [身份認證](#authentication)
  - [Routing](#routing)
  - [Authentication and Storage](#authentication-and-storage)
  - [存取範圍 (Scope)](#access-scopes)
  - [Slack Bot 的 Scope](#slack-bot-scopes)
  - [可選參數](#optional-parameters)
  
- [取得使用者的詳細資訊](#retrieving-user-details)

<a name="introduction"></a>

## 簡介

In addition to typical, form based authentication, Laravel also provides a simple, convenient way to authenticate with OAuth providers using [Laravel Socialite](https://github.com/laravel/socialite). Socialite currently supports authentication via Facebook, X, LinkedIn, Google, GitHub, GitLab, Bitbucket, and Slack.

> [!NOTE]  
> [Socialite Providers](https://socialiteproviders.com/) 網站上還提供了由社群維護的其他平台的 Adapter。

<a name="installation"></a>

## 安裝

若要開始使用 Socialite，請使用 Composer 套件管理器將 Socialite 套件新增至專案的相依性套件中：

```shell
composer require laravel/socialite
```
<a name="upgrading-socialite"></a>

## 更新 Socialite

將 Telescope 升級到新的主要 (Major) 版本時，請務必仔細閱讀[升級指南](https://github.com/laravel/socialite/blob/master/UPGRADE.md)。

<a name="configuration"></a>

## 設定

在使用 Socialite 之前，需要新增你的網站要使用的 OAuth Provider 憑證 (Credential)。一般來說，可以在要用來登入的服務的儀表板中建立「開發者應用程式」來取得這些憑證。

These credentials should be placed in your application's `config/services.php` configuration file, and should use the key `facebook`, `x`, `linkedin-openid`, `google`, `github`, `gitlab`, `bitbucket`, `slack`, or `slack-openid`, depending on the providers your application requires:

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => 'http://example.com/callback-url',
    ],
> [!NOTE]  
> 如果 `redirect` 選項包含相對路徑，則會為自動解析成完整的 URL。

<a name="authentication"></a>

## 登入

<a name="routing"></a>

### Routing

要使用 OAuth Provider 來登入使用者，需要兩個 Route：一個用來將使用者重新導向到 OAuth Provider，另一個用來接收登入後 Provider 傳回來的回呼。下面的範例說明如何實作這兩個 Route：

    use Laravel\Socialite\Facades\Socialite;
    
    Route::get('/auth/redirect', function () {
        return Socialite::driver('github')->redirect();
    });
    
    Route::get('/auth/callback', function () {
        $user = Socialite::driver('github')->user();
    
        // $user->token
    });
`Socialite` Facade 上的 `redirect` 方法負責將使用者重新導向到 OAuth Provider。當使用者同意登入要求後，`user` 方法會檢查傳入的 Request，並向 OAuth Provider 取得使用者的資訊。

<a name="authentication-and-storage"></a>

### Authentication and Storage

從 OAuth Provider 取得使用者後，就可以判斷該使用者是否存在我們的網站中，並[登入該使用者](/docs/{{version}}/authentication#authenticate-a-user-instance)。如果使用者不存在網站資料庫中，可以在資料庫中建立：

    use App\Models\User;
    use Illuminate\Support\Facades\Auth;
    use Laravel\Socialite\Facades\Socialite;
    
    Route::get('/auth/callback', function () {
        $githubUser = Socialite::driver('github')->user();
    
        $user = User::updateOrCreate([
            'github_id' => $githubUser->id,
        ], [
            'name' => $githubUser->name,
            'email' => $githubUser->email,
            'github_token' => $githubUser->token,
            'github_refresh_token' => $githubUser->refreshToken,
        ]);
    
        Auth::login($user);
    
        return redirect('/dashboard');
    });
> [!NOTE]  
> 有關各個 OAuth Provider 所提供的使用者資訊，請參考說明文件中有關[取得使用者詳細資料](#retrieving-user-details)的部分。

<a name="access-scopes"></a>

### Access Scope (存取範圍)

在為使用者重新導向前，我們可以使用 `scopes` 方法來指定這個登入驗證 Request 中要包含的「Scope (範圍)」。此方法會將所指定的 Scopes 與先前指定的所有 Scopes 合併起來：

    use Laravel\Socialite\Facades\Socialite;
    
    return Socialite::driver('github')
        ->scopes(['read:user', 'public_repo'])
        ->redirect();
可以使用 `setScopes` 方法來複寫登入驗證 Request 上的所有已存在的 Scopes：

    return Socialite::driver('github')
        ->setScopes(['read:user', 'public_repo'])
        ->redirect();
<a name="slack-bot-scopes"></a>

### Slack Bot 的 Scope

Slack's API provides [different types of access tokens](https://api.slack.com/authentication/token-types), each with their own set of [permission scopes](https://api.slack.com/scopes). Socialite is compatible with both of the following Slack access tokens types:

<div class="content-list" markdown="1">
- Bot (prefixed with `xoxb-`)
- User (prefixed with `xoxp-`)

</div>
By default, the `slack` driver will generate a `user` token and invoking the driver's `user` method will return the user's details.

Bot tokens are primarily useful if your application will be sending notifications to external Slack workspaces that are owned by your application's users. To generate a bot token, invoke the `asBotUser` method before redirecting the user to Slack for authentication:

    return Socialite::driver('slack')
        ->asBotUser()
        ->setScopes(['chat:write', 'chat:write.public', 'chat:write.customize'])
        ->redirect();
In addition, you must invoke the `asBotUser` method before invoking the `user` method after Slack redirects the user back to your application after authentication:

    $user = Socialite::driver('slack')->asBotUser()->user();
When generating a bot token, the `user` method will still return a `Laravel\Socialite\Two\User` instance; however, only the `token` property will be hydrated. This token may be stored in order to [send notifications to the authenticated user's Slack workspaces](/docs/{{version}}/notifications#notifying-external-slack-workspaces).

<a name="optional-parameters"></a>

### 可選的參數

有一些 OAuth Provider 還支援在重新導向 Request 上設定其他可選的參數。若要在 Request 中包含任何可選的參數，請呼叫 `with` 方法並提供一個關聯式陣列：

    use Laravel\Socialite\Facades\Socialite;
    
    return Socialite::driver('google')
        ->with(['hd' => 'example.com'])
        ->redirect();
> [!WARNING]  
> 在使用 `with` 方法時，請小心不要傳入任何保留字 (Reserved Keywords)，如 `state` 或 `response_type` 等。

<a name="retrieving-user-details"></a>

## 取得使用者詳細資料

使用者被重新導向到我們網站上的登入驗證 ^[Callback](%E5%9B%9E%E5%91%BC) Route 後，就可以使用 Socialite 的 `user` 方法來取得使用者的詳細資料。`user` 方法回傳的使用者物件提供了多種屬性與方法，我們可以將與該使用者有關的資訊存在資料庫中。

根據所使用的 OAuth Provider 是支援 OAuth 1.0 還是 OAuth 2.0，該物件上所提供的屬性與方法可能會有所不同：

    use Laravel\Socialite\Facades\Socialite;
    
    Route::get('/auth/callback', function () {
        $user = Socialite::driver('github')->user();
    
        // OAuth 2.0 providers...
        $token = $user->token;
        $refreshToken = $user->refreshToken;
        $expiresIn = $user->expiresIn;
    
        // OAuth 1.0 providers...
        $token = $user->token;
        $tokenSecret = $user->tokenSecret;
    
        // All providers...
        $user->getId();
        $user->getNickname();
        $user->getName();
        $user->getEmail();
        $user->getAvatar();
    });
<a name="retrieving-user-details-from-a-token-oauth2"></a>

#### Retrieving User Details From a Token

若你已經擁有使用者的有效 ^[Access Token](%E5%AD%98%E5%8F%96%E6%AC%8A%E6%9D%96)，就可使用 Socialite 的 `userFromToken` 方法來取得該使用者的詳細資料：

    use Laravel\Socialite\Facades\Socialite;
    
    $user = Socialite::driver('github')->userFromToken($token);
If you are using Facebook Limited Login via an iOS application, Facebook will return an OIDC token instead of an access token. Like an access token, the OIDC token can be provided to the `userFromToken` method in order to retrieve user details.

<a name="stateless-authentication"></a>

#### ^[Stateless](%E7%84%A1%E5%91%A8%E9%82%8A) 的登入驗證

使用 `stateless` 方法可關閉 Session 狀態驗證。此方法適合用於在不使用 Cookie 的 Stateless API 工作階段中加入社群網站登入。

    use Laravel\Socialite\Facades\Socialite;
    
    return Socialite::driver('google')->stateless()->user();