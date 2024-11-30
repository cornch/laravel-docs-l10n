---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/63/en-zhtw'
updatedAt: '2024-06-30T08:26:00Z'
contributors: {  }
progress: 36.11
---

# 加密

- [簡介](#introduction)
- [設定](#configuration)
  - [Gracefully Rotating Encryption Keys](#gracefully-rotating-encryption-keys)
  
- [Using the Encrypter](#using-the-encrypter)

<a name="introduction"></a>

## 簡介

Laravel's encryption services provide a simple, convenient interface for encrypting and decrypting text via OpenSSL using AES-256 and AES-128 encryption. All of Laravel's encrypted values are signed using a message authentication code (MAC) so that their underlying value cannot be modified or tampered with once encrypted.

<a name="configuration"></a>

## 設定

在開始使用 Laravel 的 Encrypter 前，我們必須先在 `config/app.php` 設定檔中設定 `key`。這個設定以 `APP_KEY` 環境變數提供，我們可以使用 `php artisan key:generate` 指令來產生這個變數值。`key:generate` 指令會使用 PHP 的安全隨機位元組產生器來為你的專案建立密碼學上安全的密鑰。一般來說，`APP_KEY` 環境變數會在 [Laravel 的安裝過程](/docs/{{version}}/installation)中就為你產生好了。

<a name="gracefully-rotating-encryption-keys"></a>

### Gracefully Rotating Encryption Keys

If you change your application's encryption key, all authenticated user sessions will be logged out of your application. This is because every cookie, including session cookies, are encrypted by Laravel. In addition, it will no longer be possible to decrypt any data that was encrypted with your previous encryption key.

To mitigate this issue, Laravel allows you to list your previous encryption keys in your application's `APP_PREVIOUS_KEYS` environment variable. This variable may contain a comma-delimited list of all of your previous encryption keys:

```ini
APP_KEY="base64:J63qRTDLub5NuZvP+kb8YIorGS6qFYHKVo6u7179stY="
APP_PREVIOUS_KEYS="base64:2nLsGFGzyoae2ax3EF2Lyq/hH6QghBGLIq5uL+Gp8/w="
```
When you set this environment variable, Laravel will always use the "current" encryption key when encrypting values. However, when decrypting values, Laravel will first try the current key, and if decryption fails using the current key, Laravel will try all previous keys until one of the keys is able to decrypt the value.

This approach to graceful decryption allows users to keep using your application uninterrupted even if your encryption key is rotated.

<a name="using-the-encrypter"></a>

## Using the Encrypter

<a name="encrypting-a-value"></a>

#### Encrypting a Value

可以使用 `Crypt` Facade 提供的 `encryptString` 方法來加密。所有加密的值都使用 OpenSSL 與 AES-256-CBC Cipher 來加密。此外，所有加密的值都使用訊息驗證碼 (MAC, Message Authentiation Code) 簽名。整個在內的 MAC 可以防止我們去解謎任何由惡意使用者修改過的值：

    <?php
    
    namespace App\Http\Controllers;
    
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Crypt;
    
    class DigitalOceanTokenController extends Controller
    {
        /**
         * Store a DigitalOcean API token for the user.
         */
        public function store(Request $request): RedirectResponse
        {
            $request->user()->fill([
                'token' => Crypt::encryptString($request->token),
            ])->save();
    
            return redirect('/secrets');
        }
    }
<a name="decrypting-a-value"></a>

#### Decrypting a Value

You may decrypt values using the `decryptString` method provided by the `Crypt` facade. If the value cannot be properly decrypted, such as when the message authentication code is invalid, an `Illuminate\Contracts\Encryption\DecryptException` will be thrown:

    use Illuminate\Contracts\Encryption\DecryptException;
    use Illuminate\Support\Facades\Crypt;
    
    try {
        $decrypted = Crypt::decryptString($encryptedValue);
    } catch (DecryptException $e) {
        // ...
    }