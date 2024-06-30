---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/165/en-zhtw
progress: 92
updatedAt: '2024-06-30T08:27:00Z'
---

# 升級指南

- [從 8.x 升級至 9.0](#upgrade-9.0)

<a name="high-impact-changes"></a>

## 高度影響的更改

<div class="content-list" markdown="1">

- [更新相依性套件](#updating-dependencies)
- [Flysystem 3.x](#flysystem-3)
- [Symfony Mailer](#symfony-mailer)

</div>

<a name="medium-impact-changes"></a>

## 中度影響的更改

<div class="content-list" markdown="1">

- [Belongs To Many 的 `firstOrNew`、`firstOrCreate`、`updateOrCreate` 等方法](#belongs-to-many-first-or-new)
- [自訂型別轉換與 `null`](#custom-casts-and-null)
- [預設 HTTP 用戶端的逾時設定](#http-client-default-timeout)
- [PHP 回傳型別](#php-return-types)
- [Postgres的「Schema」設定](#postgres-schema-configuration)
- [`assertDeleted` 方法](#the-assert-deleted-method)
- [`lang` 目錄](#the-lang-directory)
- [`password` 規則](#the-password-rule)
- [`when` 與 `unless` 方法](#when-and-unless-methods)
- [未驗證的陣列索引鍵](#unvalidated-array-keys)

</div>

<a name="upgrade-9.0"></a>

## 從 8.x 升級到 9.0

<a name="estimated-upgrade-time-30-minutes"></a>

#### 預計升級所需時間：30 分鐘

> **Note** 雖然我們已經儘可能地在本說明文件中涵蓋所有^[中斷性變更](Breaking Change)。不過，在 Laravel 中，有些中斷性變更存在一些比較不明顯的地方，且這些更改中幾乎不太會影響到你的專案。 想節省時間嗎？可以使用 [Laravel Shift](https://laravelshift.com/) 來協助你快速升級你的專案。

<a name="updating-dependencies"></a>

### 相依性套件更新

**受影響的可能：高**

#### 最低版本要求為 PHP 8.0.2

Laravel 先已要求 PHP 最小版本為 8.0.2。

#### Composer 相依性套件

請在專案的 `composer.json` 檔案中更新下列相依性套件：

<div class="content-list" markdown="1">

- `laravel/framework` 升級為 `^9.0`
- `nunomaduro/collision` 升級為 `^6.1`

</div>

此外，請在專案的 `composer.json` 檔中將 `facade/ignition` 改為 `"spatie/laravel-ignition": "^1.0"`，並將 `pusher/pusher-php-server` (若有的話) 改為`"pusher/pusher-php-server": "^5.0"`。

此外，下列第一方專案也有更新新的版本以支援 Laravel 9.x。若有使用這些套件，請在升級前先閱讀各套件的升級指南：

<div class="content-list" markdown="1">

- [Vonage 通知通道 (v3.0)](https://github.com/laravel/vonage-notification-channel/blob/3.x/UPGRADE.md) (用以取代 Nexmo)

</div>

最後，請檢視你的專案使用的其他第三方套件，確認一下是否有使用支援 Laravel 9 的版本。

<a name="php-return-types"></a>

#### PHP Return 型別

對於一些如 `offsetGet`、`offSet` ⋯⋯等方法，PHP 已經開始進入一個要求回傳型別的過渡階段。因此，Laravel 9 也開始在程式碼中實作這些回傳型別。一般來說，應該是不會影響使用者的程式碼。不過，若你有複寫 Laravel 核心類別中的這些要求回傳型別的方法，則請在你的專案或套件程式碼中加上回傳型別：

<div class="content-list" markdown="1">

- `count(): int`
- `getIterator(): Traversable`
- `getSize(): int`
- `jsonSerialize(): array`
- `offsetExists($key): bool`
- `offsetGet($key): mixed`
- `offsetSet($key, $value): void`
- `offsetUnset($key): void`

</div>

此外，PHP `SessionHandlerInterface` 的方法實作中也有新增回傳型別。一樣，通常這不會影響到你的專案或套件程式碼：

<div class="content-list" markdown="1">

- `open($savePath, $sessionName): bool`
- `close(): bool`
- `read($sessionId): string|false`
- `write($sessionId, $data): bool`
- `destroy($sessionId): bool`
- `gc($lifetime): int`

</div>

<a name="application"></a>

### Application

<a name="the-application-contract"></a>

#### `Application` Contract

**受影響的可能：低**

`Illuminate\Contracts\Foundation\Application` 介面的 `storagePath` 方法已更新為接受一個 `$path` 引數。若你有實作這個介面，請更新該實作：

    public function storagePath($path = '');

類似地，`Illuminate\Foundation\Application` 類別的 `langPath` 方法現在也更新為接受一個 `$path` 引數：

    public function langPath($path = '');

#### ^[Exception Handler](例外處理常式) 的 `ignore` 方法

**受影響的可能：低**

Exception Handler 的 `ignore` 方法現在已從 `protected` 改為 `public`。該方法不包含在預設的專案 Skeleton 內。不過，若你有手動定義這個方法，請將其可見度更改為 `public`：

```php
public function ignore(string $class);
```

#### Exception Handler 的 Contract 繫結

**受影響的可能：非常低**

在之前版本的 Laravel 中，若要複寫 Laravel 預設的 Exception Handler，就必須使用 `\App\Exceptions\Handler::class` 型別來向 Service Container 繫結自定實作。不過，現在請改用 `\Illuminate\Contracts\Debug\ExceptionHandler::class` 型別來繫結自定實作。

### Blade

#### Lazy Collection 與 `$loop` 變數

**受影響的可能：低**

在 Blade 樣板中迭代 `LazyCollection` 實體時，將不再提供 `$loop` 變數。因為存取 `$loop` 變數會讓整個 `LazyCollection` 都被載入進記憶體內，因此在這種情況下使用 Lazy Collection 來^[轉譯](Render)是沒意義的。

#### Checked / Disabled / Selected Blade 指示詞

**受影響的可能：低**

新的 `@checked`、`@disabled`、與 `@selected` Blade 指示詞可能會擁有相同名稱的 Vue 事件衝突。可使用 `@@` 來逸出這些指示詞，以避免衝突：`@@selected`。

### Collections

#### `Enumerable` Contract

**受影響的可能：低**

`Illuminate\Support\Enumerable` Contract 現在定義了一個 `sole` 方法。若你有手動實作這個介面，請更新你的實作以加上這個新方法：

```php
public function sole($key = null, $operator = null, $value = null);
```

#### `reduceWithKeys` 方法

`reduceWithKeys` 方法已被移除，因為 `reduce` 方法提供的功能與 `reduceWithKeys` 相同。只要將呼叫 `reduceWithKeys` 的程式碼改成呼叫 `reduce` 即可。

#### `reduceMany` 方法

`reduceMany` 方法已更名為 `reduceSpread`，以與其他類似方法維持命名的一貫性。

### Container

#### `Container` Contract

**受影響的可能：非常低**

`Illuminate\Contracts\Container\Container` Contract 現在多了兩個方法定義：`scoped` 與 `scopedIf`。若你有手動實作這個 Contract，請更新你的實作以加上這些新方法。

#### `ContextualBindingBuilder` Contract

**受影響的可能：非常低**

`Illuminate\Contracts\Container\ContextualBindingBuilder` Contract 現在定義了一個 `giveConfig` 方法。若你有手動實作這個方法，請更新你的實作以反應這個新方法：

```php
public function giveConfig($key, $default = null);
```

### 資料庫

<a name="postgres-schema-configuration"></a>

#### Postgres 的「Schema」設定

**受影響的可能性：中等**

在 `config/database.php` 設定檔中，用來設定 Postgress 連線搜尋路徑的 `schema` 設定選項已改名為 `search_path`。

<a name="schema-builder-doctrine-method"></a>

#### Schema Builder `registerCustomDoctrineType` 方法

**受影響的可能：低**

`registerCustomDoctrineType` 方法已從 `Illuminate\Database\Schema\Builder` 類別內移除。可使用 `DB` Facade 上的 `registerDoctrineType` 方法來代替，或是在 `config/database.php` 設定檔內註冊自訂的 Doctrine 型別。

### Eloquent

<a name="custom-casts-and-null"></a>

#### 自訂 Cast 與 `null`

**受影響的可能性：中等**

在之前版本的 Laravel中，在將 Cast 屬性設為 `null` 時不會叫用自訂 Cast 類別的 `set` 方法。不過，這個行為與 Laravel 說明文件中的不一致，因此在 Laravel 9.x 中，會叫用 Cast 類別的 `set` 方法，並提供 `null` 作為 `$value` 引數。因此，請確保你的自訂 Cast 可處理這類狀況：

```php
/**
 * Prepare the given value for storage.
 *
 * @param  \Illuminate\Database\Eloquent\Model  $model
 * @param  string  $key
 * @param  AddressModel  $value
 * @param  array  $attributes
 * @return array
 */
public function set($model, $key, $value, $attributes)
{
    if (! $value instanceof AddressModel) {
        throw new InvalidArgumentException('The given value is not an Address instance.');
    }

    return [
        'address_line_one' => $value->lineOne,
        'address_line_two' => $value->lineTwo,
    ];
}
```

<a name="belongs-to-many-first-or-new"></a>

#### Belongs To Many `firstOrNew`、`firstOrCreate`、`updateOrCreate` 方法

**受影響的可能性：中等**

`belongsToMany` 關聯的 `firstOrNew`、`firstOrCreate`、`updateOrCreate` 等方法都接受傳入一組屬性陣列作為第一個引數。在之前版本的 Laravel 中，這組屬性變數會先與「^[Pivot](樞紐)」/中介資料表上現有的紀錄做比較。

不過，由於這個行為是未預期的，且一般來說我們不會想要這個行為。因此，現在這幾個方法已改為以 Model 上對應的資料表來跟屬性陣列做比較：

```php
$user->roles()->updateOrCreate([
    'name' => 'Administrator',
]);
```

此外，`firstOrCreate` 方法現已接受一個 `$values` 陣列作為其第二個引數。建立關聯 Model 時若還未有關聯 Model，會將這個陣列與該方法的第一個引數 (`$attributes`) 合併。這個更改即讓該方法與其他關聯型別上提供的 `firstOrCreate` 保持一致：

```php
$user->roles()->firstOrCreate([
    'name' => 'Administrator',
], [
    'created_by' => $user->id,
]);
```

#### `touch` 方法

**受影響的可能：低**

`touch` 方法現已接受一個要被 Touch 的屬性。若你之前有複寫這個方法，請更新方法^[簽章](Signature)以反應出此引數：

```php
public function touch($attribute = null);
```

### Encryption

#### `Encrypter` Contract

**受影響的可能：低**

`Illuminate\Contracts\Encryption\Encrypter` Contract 現已定義了一個 `getKey` 方法。若你有手動實作該介面，請更新該實作：

```php
public function getKey();
```

### Facade

#### `getFacadeAccessor` 方法

**受影響的可能：低**

`getFacadeAccessor` 方法只能回傳 Container 的^[繫結索引鍵](Binding Key)。在之前版本的 Laravel 中，這個方法可以回傳物件實體。不過，現在已不支援這個行為。若你有自行撰寫 Facade，請確保該方法回傳的是 Container 的繫結索引鍵字串：

```php
/**
 * Get the registered name of the component.
 *
 * @return string
 */
protected static function getFacadeAccessor()
{
    return Example::class;
}
```

### Filesystem

#### `FILESYSTEM_DRIVER` 環境變數

**受影響的可能：低**

`FILESYSTEM_DRIVER` 環境變數已改名為 `FILESYSTEM_DISK`，以更確切反映出其用途。該更改只會影響專案 Skeleton。不過，若你想要的話，也歡迎你更新你專案上的環境變數以反映此更改。

#### 「Cloud」Disk

**受影響的可能：低**

從 2020 年 11 月起，`cloud` Disk 設定選項已自預設的專案 Skeleton 中移除。這個更改只影響專案 Skeleton。若你有在專案中使用 `cloud` Disk，可在你自己的專案 Skeleton 中保留此設定值。

<a name="flysystem-3"></a>

### Flysystem 3.x

**受影響的可能：高**

Laravel 9.x 以從 [Flysystem](https://flysystem.thephpleague.com/v2/docs/) 1.x 更新為 3.x。Flysystem 在幕後驅動了 `Storage` Facade 的所有檔案處理方法。有鑑於此，有可能會需要你修改你的專案中的一些地方。不過，我們已盡量讓這些修改變得容易。

#### Driver 前置需求

在使用 S3、FTP、SFTP 等 Driver 時，需要使用 Composer 套件管理員安裝適當的套件：

- Amazon S3: `composer require -W league/flysystem-aws-s3-v3 "^3.0"`
- FTP: `composer require league/flysystem-ftp "^3.0"`
- SFTP: `composer require league/flysystem-sftp-v3 "^3.0"`

#### 覆寫已存在的檔案

如 `put`、`write`、與 `writeStream` 等寫入操作現在會預設覆蓋現有的檔案。若不想複寫現有的檔案，請在執行寫入動作前先確認檔案是否存在。

#### 寫入 Exception

`put`、`write`、與 `writeStream` 等的寫入操作現在已不會在寫入操作失敗時擲回 Exception，而會回傳 `false`。若想保留此一擲回 Exception 的行為，可在 filesystem 的 disk 設定陣列中定義 `throw` 選項：

```php
'public' => [
    'driver' => 'local',
    // ...
    'throw' => true,
],
```

#### 讀取不存在的檔案

若嘗試讀取不存在的檔案，現在會回傳 `null`。在之前版本的 Laravel 中，會^[擲回](Throw) `Illuminate\Contracts\Filesystem\FileNotFoundException`。

#### 刪除不存在的檔案

嘗試使用 `delete` 刪除不存在的檔案時，現在會回傳 `true`。

#### Cached Adapter

Flysystem 現在不支援「Cached Adapter」了。因此，Laravel 中已移除這些 Adapter，你可以移除一些與其相關的設定 (如 Disk 設定中的 `cache` 索引鍵等)。

#### 自訂 Filesystem

要註冊自訂 Filesystem Driver 所需的步驟有一些小更改。因此，若你有定義你自己的自訂 Filesystem Driver，或是使用有定義自訂 Driver 的套件，請更新你的程式碼或相依性套件。

舉例來說，Laravel 8.x 中，可以像這樣註冊 Filesystem Driver：

```php
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use Spatie\Dropbox\Client as DropboxClient;
use Spatie\FlysystemDropbox\DropboxAdapter;

Storage::extend('dropbox', function ($app, $config) {
    $client = new DropboxClient(
        $config['authorization_token']
    );

    return new Filesystem(new DropboxAdapter($client));
});
```

不過，在 Laravel 9.x 中，傳給 `Storage::extend` 方法的回呼應直接回傳一個 `Illuminate\Filesystem\FilesystemAdapter` 的實體：

```php
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use Spatie\Dropbox\Client as DropboxClient;
use Spatie\FlysystemDropbox\DropboxAdapter;

Storage::extend('dropbox', function ($app, $config) {
    $adapter = new DropboxAdapter(
        new DropboxClient($config['authorization_token'])
    );

    return new FilesystemAdapter(
        new Filesystem($adapter, $config),
        $adapter,
        $config
    );
});
```

#### SFTP Private-Public Key Passphrase

If your application is using Flysystem's SFTP adapter and private-public key authentication, the `password` configuration item that is used to decrypt the private key should be renamed to `passphrase`.

### 輔助函式

<a name="data-get-function"></a>

#### `data_get` 輔助函式與 Iterable 的物件

**受影響的可能：非常低**

之前，`data_get` 輔助函式可用來在陣列與 `Collection` 實體上取得巢狀資料。不過，`data_get` 現在已可在所有 Iterable 的物件上取得巢狀資料了。

<a name="str-function"></a>

#### `str` 輔助函式

**受影響的可能：非常低**

Laravel 9.x 現在包含了一個全域的 `str` [輔助函式](/docs/{{version}}/helpers#method-str)。若你有在專案中定義全域的 `str` 輔助函式，請重新命名或移除該函式以避免與 Laravel 的 `str` 輔助函式衝突。

<a name="when-and-unless-methods"></a>

#### `when` / `unless` 方法

**受影響的可能性：中等**

讀者可能已經知道，在 Laravel 中有許多類別都提供了 `when` 與 `unless` 方法。可以使用這些方法來依據提供給該方法之第一個引數的布林值是 `true` 還是 `false` 來有條件地進行一些動作：

```php
$collection->when(true, function ($collection) {
    $collection->merge([1, 2, 3]);
});
```

因此，在之前版本的 Laravel 中，若傳遞閉包給 `when` 或 `unless` 方法，則一定會執行給定的條件式動作，因為在對閉包物件 (或其他物件) 做鬆散比較時，結果一定會是 `true`。通常來說這會導致一些未預期的結果，因為開發人員通常會預期使用閉包的 **結果** 來作為判斷是否執行條件式動作的布林值。

因此，在 Laravel 9.x 中，傳給 `when` 或 `unless` 方法的閉包會被執行，而該閉包回傳的值會被用來當作 `when` 與 `unless` 方法要判斷的布林值：

```php
$collection->when(function ($collection) {
    // 會執行這個閉包...
    return false;
}, function ($collection) {
    // 因為第一個閉包回傳「false」，因此不會執行這個閉包...
    $collection->merge([1, 2, 3]);
});
```

### HTTP 用戶端

<a name="http-client-default-timeout"></a>

#### 預設的逾時

**受影響的可能性：中等**

[HTTP 用戶端](/docs/{{version}}/http-client) 現在的預設逾時設定為 30 秒。換句話說，若伺服器在 30 秒內未回應，會^[擲回](Throw)一個 Exception。以前，HTTP 用戶端上並沒有設定逾時，因此有時候 Request 可能會無限地「當掉」。

若想為給定 Request 指定更長的逾時設定，可使用 `timeout` 方法：

    $response = Http::timeout(120)->get(/* ... */);

#### HTTP Fake 與 Middleware

**受影響的可能：低**

之前，若使用「^[假的](Fake)」[HTTP 用戶端](/docs/{{version}}/http-client)，Laravel 就不會執行任何提供的 Guzzle HTTP Middleware。不過，在 Laravel 9.x 中，就算有 Fake HTTP 用戶端，還是會執行 Guzzle HTTP Middleware。

#### HTTP Fake 與相依性插入

**受影響的可能：低**

在之前版本的 Laravel 中，叫用 `Http::fake()` 方法不會影響到插入到類別 ^[Constructor](建構函式) 上的 `Illuminate\Http\Client\Factory` 實體。不過，在 Laravel 9.x 中，`Http::fake()` 會確保使用相依性插入所插入到其他類別的 HTTP 用戶端回傳的是 Fake 過的 Response。這個行為也讓 `Http::fake()` 與其他 Facade、Fake 更一致。

<a name="symfony-mailer"></a>

### Symfony Mailer

**受影響的可能：高**

Laravel 9.x 中，其中一個最大的更改就是將 SwiftMailer 更改為 Symfony Mailer。自從 2021 年 12 月起，SwiftMailer 就不再維護了。不過，我們儘量讓你在專案中可以無縫將 SwiftMailer 改為 Symfony Mailer。因此，請檢視下列更改，以確保你的專案有完整支援 Symfony Mailer。

#### Driver 前置需求

若要繼續使用 Mailgun Transport，請在專案中 Require `symfony/mailgun-mailer` 與 `symfony/http-client` Composer 套件：

```shell
composer require symfony/mailgun-mailer symfony/http-client
```

請從專案中移除 `wildbit/swiftmailer-postmark` Composer 套件，並改 Require `symfony/postmark-mailer` 與 `symfony/http-client` Composer 套件：

```shell
composer require symfony/postmark-mailer symfony/http-client
```

#### 回傳型別的更新

`Illuminate\Mail\Mailer` 的 `send`、`html`、`raw`、`plain` 等方法將不再回傳 `void`，而是回傳一個 `Illuminate\Mail\SentMessage` 實體。這個物件中包含了一個 `Symfony\Component\Mailer\SentMessage` 實體，可以通過 `getSymfonySentMessage` 方法來取得該實體，或是在該物件上動態呼叫方法：

#### 「Swift」方法的更名

有多個與 SwiftMailer 相關的方法都已改名為與 Symfony Mailer 相應的名稱，其中有些是未包含在說明文件內的。舉例來說，`withSwiftMessage` 方法已更名為 `withSymfonyMessage`：

    // Laravel 8.x...
    $this->withSwiftMessage(function ($message) {
        $message->getHeaders()->addTextHeader(
            'Custom-Header', 'Header Value'
        );
    });
    
    // Laravel 9.x...
    use Symfony\Component\Mime\Email;
    
    $this->withSymfonyMessage(function (Email $message) {
        $message->getHeaders()->addTextHeader(
            'Custom-Header', 'Header Value'
        );
    });

> **Warning** 請稍微檢視一下 [Symfony Mailer 說明文件](https://symfony.com/doc/6.0/mailer.html#creating-sending-messages)以瞭解所有使用 `Symfony\Component\Mime\Email` 物件的方法。

下面詳細列出了針對這些改名過的方法。其中許多方法都是用來直接使用 SwiftMailer / Symfony Mailer 的低階方法，所以在大多數 Laravel 專案中並不常用：

    Message::getSwiftMessage();
    Message::getSymfonyMessage();
    
    Mailable::withSwiftMessage($callback);
    Mailable::withSymfonyMessage($callback);
    
    MailMessage::withSwiftMessage($callback);
    MailMessage::withSymfonyMessage($callback);
    
    Mailer::getSwiftMailer();
    Mailer::getSymfonyTransport();
    
    Mailer::setSwiftMailer($swift);
    Mailer::setSymfonyTransport(TransportInterface $transport);
    
    MailManager::createTransport($config);
    MailManager::createSymfonyTransport($config);

#### 經過代理的 `Illuminate\Mail\Message` 方法

`Illuminate\Mail\Message` 一般來說都會將不存在的方法代理到底層的 `Swift_Message` 實體上。不過，現在，不存在的方法會改為代理到 `Symfony\Component\Mime\Email` 實體上。因此，若先前有任何仰賴這個將不存在方法代理到 SwiftMailer 的程式碼都應改為使用其在 Symfony Mailer 中相應的部分。

同樣的，許多專案應該都不會使用到這些方法，因為這些方法並沒有寫在 Laravel 說明文件中：

    // Laravel 8.x...
    $message
        ->setFrom('taylor@laravel.com')
        ->setTo('example@example.org')
        ->setSubject('Order Shipped')
        ->setBody('<h1>HTML</h1>', 'text/html')
        ->addPart('Plain Text', 'text/plain');
    
    // Laravel 9.x...
    $message
        ->from('taylor@laravel.com')
        ->to('example@example.org')
        ->subject('Order Shipped')
        ->html('<h1>HTML</h1>')
        ->text('Plain Text');

#### 產生的 Message ID

SwiftMailer 提供了可使用 `mime.idgenerator.idright` 設定選項來定義要包含在產生之 Message ID 中的自訂網域。Symfony Mailer 不支援這個功能。Symfony Mailer 只會依據寄件人自動產生 Message ID。

#### `MessageSent` Event 的更改

`Illuminate\Mail\Events\MessageSent` 事件的 `message` 屬性現在已不再包含 `Swift_Message` 實體，而改為 `Symfony\Component\Mime\Email` 實體。此訊息表示了 E-Mail **寄出前** 的狀態。

此外，`MessageSent` 事件中也加上了一個新的 `sent` 屬性。此屬性包含了 `Illuminate\Mail\SentMessage` 實體，其中有已寄出 E-Mail 的資訊，如 Message ID。

#### 強制重新連線

現在已經無法再強制 Transport 重新連線了 (如使用 Daemon 處理程序執行 Mailer 時)。Symfony Mailer 會自動嘗試重新連線，並在重新連線失敗時^[擲回](Throw) Exception。

#### SMTP 串流選項

現在已不支援為 SMTP Transport 定義串流選項。若有支援相應的選項，請改為定義這些選項。舉例來說，若要禁用 TLS Peer Verification：

    'smtp' => [
        // Laravel 8.x...
        'stream' => [
            'ssl' => [
                'verify_peer' => false,
            ],
        ],
    
        // Laravel 9.x...
        'verify_peer' => false,
    ],

若要瞭解更多可用的選項，請參考 [Symfony Mailer 說明文件](https://symfony.com/doc/6.0/mailer.html#transport-setup)。

> **note** 雖然有上述這樣的範例，但一般來說建議不要禁用 SSL 驗證，因為有可能會導致「^[中間人](man-in-the-middle, MITM)」攻擊

#### SMTP `auth_mode`

現在已不支援在 `mail` 設定中定義 SMTP 的 `auth_mode`。Symfony Mailer 會自動與 SMTP 伺服器取得認證方法。

#### 寄送失敗的收件人

送出訊息後，現在已無法取得無法寄出的收件人列表。若訊息送出失敗，現在只會^[擲回](Throw)一個 `Symfony\Component\Mailer\Exception\TransportExceptionInterface` Exception。我們建議你不要在送出訊息後去取得無效的 E-Mail 位址，而是在送出訊息前就驗證 E-Mail 位址。

### 套件

<a name="the-lang-directory"></a>

#### `lang` 目錄

**受影響的可能性：中等**

在新的 Laravel 專案中，`resources/lang` 目錄現在改放在專案根目錄了 (`lang`)。若你的專案有將語系檔安裝到這個資料夾，請確保是使用 `app()->langPath()` 來安裝，而不是使用^[硬式編碼](Hard-Coded)的路徑。

<a name="queue"></a>

### 佇列

<a name="the-opis-closure-library"></a>

#### `opis/closure` 套件

**受影響的可能：低**

Laravel 的 `opis/closure` 相依性套件現已改為 `laravel/serializable-closure`。這應該不會對你的程式造成任何的^[破壞性變更](Breaking Change)，除非你有直接使用到 `opis/closure` 套件。此外，現已移除之前^[已棄用](Deprecated)的 `Illuminate\Queue\SerializableClosureFactory` 與 `Illuminate\Queue\SerializableClosure`。若你有直接使用到 `opis/closure` 函式庫，或是有使用到任何已移除的類別，可使用 [Laravel Serializable Closure](https://github.com/laravel/serializable-closure) 來代替。

#### Failed Job Provider 的 `flush` 方法

**受影響的可能：低**

`Illuminate\Queue\Failed\FailedJobProviderInterface` 介面中定義的 `flush` 方法現已支援一個 `$hours` 引數。使用該引數可用來判斷執行失敗的任務在被 `queue:flush` 指令清除前必須要保留多久 (單位：小時)。若你有手動實作 `FailedJobProviderInterface`，請確保有更新該實作以反映這個新引數：

```php
public function flush($hours = null);
```

### Session

#### `getSession` 方法

**受影響的可能：低**

Laravel 的 `Illuminate\Http\Request` 類別所繼承的 `Symfony\Component\HttpFoundaton\Request` 類別現已提供了一個 `getSession` 方法，可用來取得目前的 Session ^[Storage Handler](存放空間處理常式)。Laravel 說明文件中並未提及該方法，因為大多數的 Laravel 專案都使用 Laravel 自己的 `session` 方法來處理 Session。

之前，`getSession` 方法會回傳 `Illuminate\Session\Store` 實體或 `null`。不過，由於 Symfony 6.x 版本強制回傳型別為 `Symfony\Component\HttpFoundation\Session\SessionInterface`，因此 `getSession` 現在會回傳一個 `SessionInterface` 實作，或是當沒有可用的 Session 時會^[擲回](Throw) `\Symfony\Component\HttpFoundation\Exception\SessionNotFoundException` Exception。

### 測試

<a name="the-assert-deleted-method"></a>

#### `assertDeleted` 方法

**受影響的可能性：中等**

`assertDeleted` 方法的呼叫應更改為 `assertModelMissing`。

### Trusted Proxies

**受影響的可能：低**

若你是通過將現有程式碼複製到新安裝的 Laravel 9 應用程式 Skeletong 中來從 Laravel 8 升級到 Laravel 9 的話，需要更新專案的「Trusted Proxy」Middleware。

在 `app/Http/Middleware/TrustProxies.php` 檔案中，請將 `use Fideloper\Proxy\TrustProxies as Middleware` 更新為 `use Illuminate\Http\Middleware\TrustProxies as Middleware`。

接著，在 `app/Http/Middleware/TrustProxies.php` 中，請更新 `$headers` 屬性的定義：

```php
// 舊的...
protected $headers = Request::HEADER_X_FORWARDED_ALL;

// 新的...
protected $headers =
    Request::HEADER_X_FORWARDED_FOR |
    Request::HEADER_X_FORWARDED_HOST |
    Request::HEADER_X_FORWARDED_PORT |
    Request::HEADER_X_FORWARDED_PROTO |
    Request::HEADER_X_FORWARDED_AWS_ELB;
```

最後，請從專案中移除 `fideloper/proxy` Composer 相依性套件：

```shell
composer remove fideloper/proxy
```

### 表單驗證

#### Form Request 的 `validated` 方法

**受影響的可能：低**

Form Request 提供的 `validated` 方法現已接受 `$key` 與 `$default` 兩個引數。若有手動複寫該方法的定義，請更新方法^[簽章](Signature)以反映這些新引數：

```php
public function validated($key = null, $default = null)
```

<a name="the-password-rule"></a>

#### `password` 規則

**受影響的可能性：中等**

用來驗證給定輸入值是否符合已登入使用者目前密碼的 `password` 規則，現已更名為 `current_password`。

<a name="unvalidated-array-keys"></a>

#### 未驗證的陣列索引鍵

**受影響的可能性：中等**

在之前版本的 Laravel 中，我們可能會需要手動讓 Laravel 的 Validator 從「^[已驗證](Validated)」資料中排除一些未驗證的陣列索引鍵。特別是當我們在使用 `array` 規則時未指定允許的索引鍵時。

不過，在 Laravel 9.x 中，未驗證的陣列索引鍵一定會被從「已驗證」陣列中排除。就算在 `array` 規則上未指定允許的索引鍵也一樣。一般來說，這是我們最直覺會預期的行為。而之前在 Laravel 8.x 中加上的 `excludeUnvalidatedArrayKeys` 方法只是一個用來保持向下相容性的臨時方案。

雖然我們不建議，但你可以在專案中任何一個 Service Provider 的 `boot` 方法內叫用 `includeUnvalidatedArrayKeys` 方法來使用以前 Laravel 8.x 的行為：

```php
use Illuminate\Support\Facades\Validator;

/**
 * Register any application services.
 *
 * @return void
 */
public function boot()
{
    Validator::includeUnvalidatedArrayKeys();
}
```

<a name="miscellaneous"></a>

### 其他

我們也鼓勵你檢視 `laravel/laravel` [GitHub 存放庫](https://github.com/laravel/laravel)上的更改。雖然這些更改中大多數都不是必須要進行的，但你可能也會想讓專案中的這些檔案保持同步。其中一些修改有在本升級指南中提到，但有些其他的更改（如設定檔的更改或註解等）就沒有提到。可以通過 [GitHub 的比較工具](https://github.com/laravel/laravel/compare/8.x...9.x)來輕鬆地檢視這些更改，並自行評估哪些更改對你來說比較重要。
