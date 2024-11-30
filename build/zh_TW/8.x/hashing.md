---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/77/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 46.15
---

# 雜湊

- [簡介](#introduction)
- [設定](#configuration)
- [基礎用法](#basic-usage)
  - [雜湊密碼](#hashing-passwords)
  - [驗證密碼是否符合雜湊](#verifying-that-a-password-matches-a-hash)
  - [判斷是否需要重新雜湊密碼](#determining-if-a-password-needs-to-be-rehashed)
  

<a name="introduction"></a>

## 簡介

Laravel 的 `Hash` [Facade](/docs/{{version}}/facades) 提供了安全的 Bcrypt 與 Argon2 雜湊，用以儲存使用者密碼。若使用 [Laravel 專案入門套件](/docs/{{version}}/starter-kits)，則預設會使用 Bcrypt 來註冊與登入。

Bcrypt 是雜湊密碼的一個不錯的選擇，因為其「^[Work Factor](%E5%B7%A5%E4%BD%9C%E5%9B%A0)」是可調整的，這表示，隨著硬體功能的提升，我們也能調整產生雜湊所需的時間。在雜湊密碼時，慢即是好。若演算法需要更多的時間來雜湊密碼，惡意使用者要產生「^[彩虹表](Rainbow Table)」的時間也就更長。彩虹表是一個包含各種可能字串雜湊值的表格，可用來暴力破解密碼。

<a name="configuration"></a>

## 設定

專案中預設的雜湊 Driver 設定在專案的 `config/hashing.php` 設定檔中。目前有支援多個 Driver： [Bcrypt](https://en.wikipedia.org/wiki/Bcrypt) 與 [Argon2](https://en.wikipedia.org/wiki/Argon2) (Argon2i 與 Argon2id 變形)。

> [!NOTE]  
> The Argon2i driver requires PHP 7.2.0 or greater and the Argon2id driver requires PHP 7.3.0 or greater.

<a name="basic-usage"></a>

## 基礎用法

<a name="hashing-passwords"></a>

### 雜湊密碼

可以呼叫 `Hash` Facade 上的 `make` 方法來雜湊密碼：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Hash;
    
    class PasswordController extends Controller
    {
        /**
         * Update the password for the user.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\Response
         */
        public function update(Request $request)
        {
            // Validate the new password length...
    
            $request->user()->fill([
                'password' => Hash::make($request->newPassword)
            ])->save();
        }
    }
<a name="adjusting-the-bcrypt-work-factor"></a>

#### 調整 Bcrypt 的 Work Factor

若使用 Bcrypt 演算法，可使用 `rounds` 選項來在 `make` 方法中控制 Bcrypt 的 Work Factor。不過，Laravel 所控制的預設 Work Factor 對於大多數專案來說應是可接受的值：

    $hashed = Hash::make('password', [
        'rounds' => 12,
    ]);
<a name="adjusting-the-argon2-work-factor"></a>

#### 調整 Argon2 的 Work Factor

若使用 Argon2 演算法，可使用 `memory`、`time`、`threads` 等選項來在 `make` 方法中控制 Argon2 演算法的 Work Factor。不過，Laravel 所控制的預設 Work Factor 對於大多數專案來說應是可接受的值：

    $hashed = Hash::make('password', [
        'memory' => 1024,
        'time' => 2,
        'threads' => 2,
    ]);
> [!TIP]  
> 有關這些選項的詳細資訊，請參考 [PHP 官方說明文件中有關 Argon 雜湊的說明](https://secure.php.net/manual/en/function.password-hash.php)。

<a name="verifying-that-a-password-matches-a-hash"></a>

### 驗證密碼是否符合雜湊

`Hash` Facade 的 `check` 方法可用來驗證給定的純文字字串是否對應給定的雜湊：

    if (Hash::check('plain-text', $hashedPassword)) {
        // The passwords match...
    }
<a name="determining-if-a-password-needs-to-be-rehashed"></a>

### 判斷密碼是否需要重新雜湊

`Hash` Facade 的 `needsRehash` 方法可用來判斷自從該密碼被雜湊以來 Hash 程式的 Work Factor 是否有經過更改。有的專案會在網站的身份驗證過程中做這項檢查：

    if (Hash::needsRehash($hashed)) {
        $hashed = Hash::make('plain-text');
    }