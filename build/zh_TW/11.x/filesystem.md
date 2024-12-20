---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/73/en-zhtw'
updatedAt: '2024-06-30T08:26:00Z'
contributors: {  }
progress: 51.96
---

# 檔案存放空間

- [簡介](#introduction)
- [設定](#configuration)
  - [Local Driver](#the-local-driver)
  - [Public Disk](#the-public-disk)
  - [Driver 前置需求](#driver-prerequisites)
  - [Scoped and Read-Only Filesystems](#scoped-and-read-only-filesystems)
  - [與 Amazon S3 相容的檔案系統](#amazon-s3-compatible-filesystems)
  
- [取得 Disk 實體](#obtaining-disk-instances)
  - [隨需建立的 Disk](#on-demand-disks)
  
- [取得檔案](#retrieving-files)
  - [下載檔案](#downloading-files)
  - [檔案 URL](#file-urls)
  - [時效性 URL](#temporary-urls)
  - [檔案的詮釋資料](#file-metadata)
  
- [保存檔案](#storing-files)
  - [Prepending and Appending To Files](#prepending-appending-to-files)
  - [Copying and Moving Files](#copying-moving-files)
  - [自動串流](#automatic-streaming)
  - [檔案上傳](#file-uploads)
  - [檔案的可見性 (Visibility)](#file-visibility)
  
- [刪除檔案](#deleting-files)
- [目錄](#directories)
- [測試](#testing)
- [自訂檔案系統](#custom-filesystems)

<a name="introduction"></a>

## 簡介

多虧了 [Flysystem](https://github.com/thephpleague/flysystem)，Laravel 提供了強大的檔案系統抽象介面。Flysystem 是 Frank de Jonge 提供的一個 PHP 套件。Laravel 整合 Flysystem 來提供多個簡單的 Driver，可處理本機檔案系統、SFTP、Amazon S3 等。甚至，在本機開發環境與正式伺服器間交換使用各個不同的儲存空間非常地簡單，且每個儲存系統都有相同的 API。

<a name="configuration"></a>

## 設定

Laravel 的檔案系統設定檔位在 `config/filesystems.php`。在這個檔案中，我們可以設定所有的檔案系統「^[Disk](%E7%A3%81%E7%A2%9F)」。各個 Disk 都代表了一個特定的儲存空間 Driver 與儲存位置。該設定檔內已包含了各個支援 Driver 的範例設定，讓你能修改這些設定來反映出儲存空間偏好與認證方式。

`local` Driver 負責處理保存在執行該 Laravel 專案之本機伺服器上的檔案。而 `s3` Driver 則用來將檔案寫入 Amazon 的 S3 雲端儲存服務。

> [!NOTE]  
> 可以隨意設定多個 Disk，甚至也可以設定多個使用相同 Driver 的 Disk。

<a name="the-local-driver"></a>

### 「Local」Driver

使用 `local` Driver 時，所有的檔案操作都相對於 `filesystems` 設定檔中定義的 `root` 根目錄。預設情況下，這個值設為 `storage/app` 目錄。因此，下列方法會寫入 `storage/app/example.txt`：

    use Illuminate\Support\Facades\Storage;
    
    Storage::disk('local')->put('example.txt', 'Contents');
<a name="the-public-disk"></a>

### 「Public」Disk

專案中的 `filesystems` 設定檔內有個 `public` Disk，`public` Disk 是用來處理要提供公開存取的檔案。預設情況下，`public` Disk 使用 `local` Driver，並將檔案保存在 `storage/app/public`。

為了這些檔案可在網頁上存取，請建立一個 `public/storage` 到 `storage/app/public` 的^[符號連結](Symbolic Link)。使用這個資料夾慣例來把所有可公開存取的檔案放到同一個資料夾內，就你在使用如 [Envoyer](https://envoyer.io) 這類不停機部署系統時也能輕鬆的在多個部署間共用這個資料夾。

若要建立符號連結，可使用 `storage:link` Artisan 指令：

```shell
php artisan storage:link
```
保存檔案並建立好符號連結後，就可以使用 `asset` 輔助函式來建立該檔案的 URL：

    echo asset('storage/file.txt');
也可以在 `filesystems` 設定檔中設定其他符號連結。在執行 `storage:link` 指令時，會建立設定中的各個符號連結：

    'links' => [
        public_path('storage') => storage_path('app/public'),
        public_path('images') => storage_path('app/images'),
    ],
The `storage:unlink` command may be used to destroy your configured symbolic links:

```shell
php artisan storage:unlink
```
<a name="driver-prerequisites"></a>

### Driver 的前置需求

<a name="s3-driver-configuration"></a>

#### S3 Driver 設定

在使用 S3 Driver 前，需要先使用 Composer 套件管理員安裝 Flysystem S3 套件：

```shell
composer require league/flysystem-aws-s3-v3 "^3.0" --with-all-dependencies
```
An S3 disk configuration array is located in your `config/filesystems.php` configuration file. Typically, you should configure your S3 information and credentials using the following environment variables which are referenced by the `config/filesystems.php` configuration file:

```
AWS_ACCESS_KEY_ID=<your-key-id>
AWS_SECRET_ACCESS_KEY=<your-secret-access-key>
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=<your-bucket-name>
AWS_USE_PATH_STYLE_ENDPOINT=false
```
For convenience, these environment variables match the naming convention used by the AWS CLI.

<a name="ftp-driver-configuration"></a>

#### FTP Driver 設定

在使用 FTP Driver 前，需要先使用 Composer 套件管理員安裝 Flysystem FTP 套件：

```shell
composer require league/flysystem-ftp "^3.0"
```
Laravel's Flysystem integrations work great with FTP; however, a sample configuration is not included with the framework's default `config/filesystems.php` configuration file. If you need to configure an FTP filesystem, you may use the configuration example below:

    'ftp' => [
        'driver' => 'ftp',
        'host' => env('FTP_HOST'),
        'username' => env('FTP_USERNAME'),
        'password' => env('FTP_PASSWORD'),
    
        // Optional FTP Settings...
        // 'port' => env('FTP_PORT', 21),
        // 'root' => env('FTP_ROOT'),
        // 'passive' => true,
        // 'ssl' => true,
        // 'timeout' => 30,
    ],
<a name="sftp-driver-configuration"></a>

#### SFTP Driver 設定

在使用 SFTP Driver 前，需要先使用 Composer 套件管理員安裝 Flysystem SFTP 套件：

```shell
composer require league/flysystem-sftp-v3 "^3.0"
```
Laravel's Flysystem integrations work great with SFTP; however, a sample configuration is not included with the framework's default `config/filesystems.php` configuration file. If you need to configure an SFTP filesystem, you may use the configuration example below:

    'sftp' => [
        'driver' => 'sftp',
        'host' => env('SFTP_HOST'),
    
        // Settings for basic authentication...
        'username' => env('SFTP_USERNAME'),
        'password' => env('SFTP_PASSWORD'),
    
        // Settings for SSH key based authentication with encryption password...
        'privateKey' => env('SFTP_PRIVATE_KEY'),
        'passphrase' => env('SFTP_PASSPHRASE'),
    
        // Settings for file / directory permissions...
        'visibility' => 'private', // `private` = 0600, `public` = 0644
        'directory_visibility' => 'private', // `private` = 0700, `public` = 0755
    
        // Optional SFTP Settings...
        // 'hostFingerprint' => env('SFTP_HOST_FINGERPRINT'),
        // 'maxTries' => 4,
        // 'passphrase' => env('SFTP_PASSPHRASE'),
        // 'port' => env('SFTP_PORT', 22),
        // 'root' => env('SFTP_ROOT', ''),
        // 'timeout' => 30,
        // 'useAgent' => true,
    ],
<a name="scoped-and-read-only-filesystems"></a>

### Scoped and Read-Only Filesystems

使用限定範圍的 Disk，我們可以定義一個檔案系統，在該檔案系統中，所有的路徑都會自動被加上給定的路徑前置詞。在建立限定範圍的檔案系統 Disk 前，我們需要先使用 Composer 套件管理員安裝一個額外的 Flysystem 套件：

```shell
composer require league/flysystem-path-prefixing "^3.0"
```
只要使用 `scoped` Driver，我們就可以使用任何現有的檔案系統 Disk 來定義限定路徑範圍的 Disk。舉例來說，我們可以建立一個 Disk，該 Disk 使用現有的 `s3` Disk，並將路徑限定在特定的路徑前置詞內。接著，使用這個限定範圍 Disk 的所有檔案操作都會在這個指定的前置詞下：

```php
's3-videos' => [
    'driver' => 'scoped',
    'disk' => 's3',
    'prefix' => 'path/to/videos',
],
```
使用「唯讀」Disk，我們就能建立不允許任何寫入操作的檔案系統 Disk。在使用 `read-only` 組態設定選項前，我們還需要使用 Composer 套件管理員安裝一個額外的 Flysystem 套件：

```shell
composer require league/flysystem-read-only "^3.0"
```
接著，我們可以在任何一個或多個 Disk 設定內加上 `read-only` 設定選項：

```php
's3-videos' => [
    'driver' => 's3',
    // ...
    'read-only' => true,
],
```
<a name="amazon-s3-compatible-filesystems"></a>

### 相容於 Amazon S3 的檔案系統

預設情況下，專案的 `filesystems` 設定檔中已包含了一個 `s3` Disk 設定。除了以該 Disk 來使用 Amazon S3 外，還可以通過這個 Disk 來使用相容於 S3 的檔案存放服務，如 [MinIO](https://github.com/minio/minio) 或 [DigitalOcean Spaces](https://www.digitalocean.com/products/spaces/)。

一般來說，為 Disk 設定要使用服務的認證資訊後，就只需要更改 `endpoint` 設定選項即可。這個選項值通常是以 `AWS_ENDPOINT` 環境變數定義的：

    'endpoint' => env('AWS_ENDPOINT', 'https://minio:9000'),
<a name="minio"></a>

#### MinIO

為了讓 Laravel 的 Flysystem 整合在使用 MinIO 時整合正確的 URL，請定義 `AWS_URL` 環境變數，並設定適用於專案本機 URL 的值，且該值應在 URL 路徑內包含 Bucket 名稱：

```ini
AWS_URL=http://localhost:9000/local
```
> [!WARNING]  
> Generating temporary storage URLs via the `temporaryUrl` method may not work when using MinIO if the `endpoint` is not accessible by the client.

<a name="obtaining-disk-instances"></a>

## 取得 Disk 實體

可通過 `Storage` Facade 來與設定好的任一 Disk 互動。舉例來說，可以使用 Facade 上的 `put` 方法來將使用者圖片保存在預設 Disk 內。若在呼叫方法時沒有在 `Storage` Facade 上先呼叫 `disk` 方法，則這個方法呼叫會自動被傳到預設的 Disk 上：

    use Illuminate\Support\Facades\Storage;
    
    Storage::put('avatars/1', $content);
若你的專案使用多個 Disk，可使用 `Storage` Facade 上的 `disk` 方法來在特定 Disk 上處理檔案：

    Storage::disk('s3')->put('avatars/1', $content);
<a name="on-demand-disks"></a>

### 隨需提供的 Disk

有時候，我們會想在不實際將設定寫入 `filesystems` 設定檔的情況下，在執行階段直接通過給定的一組設定來建立 Disk。若要在執行階段建立 Disk，請將一組設定陣列傳給 `Storage` Facade 的 `build` 方法：

```php
use Illuminate\Support\Facades\Storage;

$disk = Storage::build([
    'driver' => 'local',
    'root' => '/path/to/root',
]);

$disk->put('image.jpg', $content);
```
<a name="retrieving-files"></a>

## 取得檔案

`get` 方法可用來取得檔案內容。該方法會回傳檔案的原始字串內容。請記得，所有檔案路徑都是相對於該 Disk 所指定的「root」根目錄：

    $contents = Storage::get('file.jpg');
若要取得的檔案包含 JSON，可使用 `json` 方法來取得該檔案並解碼其內容：

    $orders = Storage::json('orders.json');
`exists` 方法可用來判斷某個檔案是否存在於 Disk 上：

    if (Storage::disk('s3')->exists('file.jpg')) {
        // ...
    }
可使用 `missing` 方法來判斷 Disk 上是否不存在這個檔案：

    if (Storage::disk('s3')->missing('file.jpg')) {
        // ...
    }
<a name="downloading-files"></a>

### 下載檔案

可使用 `download` 方法來產生一個強制使用者在給定路徑上下載檔案的 Response。`download` 方法接受檔案名稱作為其第二個引數，該引數用來判斷使用者看到的檔案名稱。最後，我們可以傳入一組包含 HTTP 標頭的陣列作為該方法的第三個引數：

    return Storage::download('file.jpg');
    
    return Storage::download('file.jpg', $name, $headers);
<a name="file-urls"></a>

### 檔案 URL

可以使用 `url` 來取得給定檔案的 URL。若使用 `local` Driver，通常這個網址就只是在給定路徑前方加上 `/storage` 然後回傳該檔案的相對 URL 而已。若使用 `s3` Driver，則會回傳完整的遠端 URL：

    use Illuminate\Support\Facades\Storage;
    
    $url = Storage::url('file.jpg');
使用 `local` Driver 時，所有要供公開存取的檔案都應放在 `storage/app/public` 目錄內。此外，也應[建立一個符號連結](#the-public-disk)來將 `public/storage` 指向 `storage/app/public` 目錄。

> [!WARNING]  
> 使用 `local` Driver 時，`url` 的回傳值未經過 URL 編碼。因此，我們建議你只使用能產生有效 URL 的檔名來保存檔案。

<a name="url-host-customization"></a>

#### 自訂 URL 主機

If you would like to modify the host for URLs generated using the `Storage` facade, you may add or change the `url` option in the disk's configuration array:

    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
        'throw' => false,
    ],
<a name="temporary-urls"></a>

### 時效性 URL

Using the `temporaryUrl` method, you may create temporary URLs to files stored using the `local` and `s3` drivers. This method accepts a path and a `DateTime` instance specifying when the URL should expire:

    use Illuminate\Support\Facades\Storage;
    
    $url = Storage::temporaryUrl(
        'file.jpg', now()->addMinutes(5)
    );
<a name="enabling-local-temporary-urls"></a>

#### Enabling Local Temporary URLs

If you started developing your application before support for temporary URLs was introduced to the `local` driver, you may need to enable local temporary URLs. To do so, add the `serve` option to your `local` disk's configuration array within the `config/filesystems.php` configuration file:

```php
'local' => [
    'driver' => 'local',
    'root' => storage_path('app/private'),
    'serve' => true, // [tl! add]
    'throw' => false,
],
```
<a name="s3-request-parameters"></a>

#### S3 Request Parameters

若想指定額外的 [S3 Request 參數](https://docs.aws.amazon.com/AmazonS3/latest/API/RESTObjectGET.html#RESTObjectGET-requests)，只需要將 Request 參數陣列作為第三個引數傳給 `temporaryUrl` 方法即可：

    $url = Storage::temporaryUrl(
        'file.jpg',
        now()->addMinutes(5),
        [
            'ResponseContentType' => 'application/octet-stream',
            'ResponseContentDisposition' => 'attachment; filename=file2.jpg',
        ]
    );
<a name="customizing-temporary-urls"></a>

#### Customizing Temporary URLs

若有需要自訂某個存放 Disk 要如何產生臨時 URL，可以使用 `buildTemporaryUrlsUsing` 方法。舉例來說，若有檔案儲存在不支援時效性 URL 的 Driver 上，而在某個 Controller 上我們又想讓使用者能下載這些檔案，就很適合使用這個方法。一般來說，應在某個 Service Provider 的 `boot` 方法內呼叫這個方法：

    <?php
    
    namespace App\Providers;
    
    use DateTime;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Facades\URL;
    use Illuminate\Support\ServiceProvider;
    
    class AppServiceProvider extends ServiceProvider
    {
        /**
         * Bootstrap any application services.
         */
        public function boot(): void
        {
            Storage::disk('local')->buildTemporaryUrlsUsing(
                function (string $path, DateTime $expiration, array $options) {
                    return URL::temporarySignedRoute(
                        'files.download',
                        $expiration,
                        array_merge($options, ['path' => $path])
                    );
                }
            );
        }
    }
<a name="temporary-upload-urls"></a>

#### 時效性的上傳 URL

> [!WARNING]  
> 目前只有 `s3` Driver 支援產生時效性的上傳 URL。

若有需要產生直接從用戶端應用程式上傳檔案的時效性 URL，可使用 `temporaryUploadUrl` 方法。該方法可傳入一個路徑與 `DateTime` 實體來指定該 URL 的時效。`temporaryUploadUrl` 方法會回傳一個關聯式陣列，可用來解構為 URL 與上傳 Request 要包含的 Header：

    use Illuminate\Support\Facades\Storage;
    
    ['url' => $url, 'headers' => $headers] = Storage::temporaryUploadUrl(
        'file.jpg', now()->addMinutes(5)
    );
此方法特別適合用在需要直接從用戶端應用程式將檔案上傳到如 Amazon S3 等雲端儲存系統的 ^[Serverless](%E7%84%A1%E4%BC%BA%E6%9C%8D%E5%99%A8) 環境。

<a name="file-metadata"></a>

### 檔案詮釋資料

除了讀寫檔案外，Laravel 還提供了一些有關檔案本身的資訊。舉例來說，`size` 方法可用來取得單位為^[位元組](Bytes)的檔案大小：

    use Illuminate\Support\Facades\Storage;
    
    $size = Storage::size('file.jpg');
`lastModified` 方法回傳以 UNIX ^[時戳](Timestamp)表示的檔案最後修改時間：

    $time = Storage::lastModified('file.jpg');
使用 `mimeType` 方法，就可取得給定檔案的 MIME 型別：

    $mime = Storage::mimeType('file.jpg');
<a name="file-paths"></a>

#### 檔案路徑

可以使用 `path` 方法來取得給定檔案的路徑。若使用 `local` Driver，該方法會回傳檔案的絕對路徑。若使用 `s3` Driver，該方法會回傳在 S3 Bucket 中的相對路徑：

    use Illuminate\Support\Facades\Storage;
    
    $path = Storage::path('file.jpg');
<a name="storing-files"></a>

## 保存檔案

可使用 `put` 方法來將檔案內容保存到 Disk 上。也可以傳入一個 PHP `resource` 給 `put` 方法，Laravel 會使用 Flysystem 的底層串流支援來保存檔案。請記得，所有的檔案路徑都是相對於 Disk 設定中「root」根目錄的路徑：

    use Illuminate\Support\Facades\Storage;
    
    Storage::put('file.jpg', $contents);
    
    Storage::put('file.jpg', $resource);
<a name="failed-writes"></a>

#### 寫入失敗

若 `put` 方法 (或其他「寫入」動作) 無法將檔案寫入到磁碟上，則該方法會回傳 `false`：

    if (! Storage::put('file.jpg', $contents)) {
        // The file could not be written to disk...
    }
若有需要的話，也可以在檔案系統 Disk 的設定陣列中定義 `throw` 選項。當該選項定義為 `true` 時，如 `put` 等的「寫入」方法會在寫入動作失敗時擲回一個 `League\Flysystem\UnableToWriteFile` 實體：

    'public' => [
        'driver' => 'local',
        // ...
        'throw' => true,
    ],
<a name="prepending-appending-to-files"></a>

### Prepending and Appending To Files

使用 `prepend` 或 `append` 方法，就可以讓我們將內容寫入到檔案的最前端或最後端：

    Storage::prepend('file.log', 'Prepended Text');
    
    Storage::append('file.log', 'Appended Text');
<a name="copying-moving-files"></a>

### Copying and Moving Files

可使用 `copy` 方法來將現有的檔案複製到 Disk 中的新路徑。而 `move` 方法則可用來重新命名現有檔案或將現有檔案移至新路徑：

    Storage::copy('old/file.jpg', 'new/file.jpg');
    
    Storage::move('old/file.jpg', 'new/file.jpg');
<a name="automatic-streaming"></a>

### 自動串流

使用串流將檔案到寫入存放空間可顯著降低記憶體使用。若想讓 Laravel 自動管理存放路徑中給定檔案的串流，可使用 `putFile` 或 `putFileAs` 方法。這兩個方法接受 `Illuminate\Http\File` 或 `Illuminate\Http\UploadedFile` 實體，會自動將該檔案串流到指定的路徑上：

    use Illuminate\Http\File;
    use Illuminate\Support\Facades\Storage;
    
    // Automatically generate a unique ID for filename...
    $path = Storage::putFile('photos', new File('/path/to/photo'));
    
    // Manually specify a filename...
    $path = Storage::putFileAs('photos', new File('/path/to/photo'), 'photo.jpg');
有關 `putFile` 方法，還有幾點重要事項要注意。請注意，我們只有指定資料夾名稱，而未指定檔案名稱。預設情況下，`putFile` 會自動產生一個不重複 ID 來作為檔案名稱。檔案的副檔名會依照該檔案的 MIME 來判斷。`putFile` 方法會回傳該檔案包含檔名的路徑，好讓我們能保存該路徑到資料庫中。

`putFile` 與 `putFileAs` 方法也接受一個用來指定保存檔案「^[可見度](Visibility)」的引數。若你使用 Amazon S3 等雲端 Disk 來儲存檔案且想產生能公開存取的 URL，這個功能就特別實用：

    Storage::putFile('photos', new File('/path/to/photo'), 'public');
<a name="file-uploads"></a>

### 檔案上傳

在 Web App 中，儲存檔案最常見的例子就是保存使用者上傳的檔案了 (如：照片、文件)。在 Laravel 中，要保存上傳的檔案非常簡單，只要在上傳的檔案實體上使用 `store` 方法即可。呼叫 `store` 方法並傳入要上傳檔案要保存的位置即可：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    
    class UserAvatarController extends Controller
    {
        /**
         * Update the avatar for the user.
         */
        public function update(Request $request): string
        {
            $path = $request->file('avatar')->store('avatars');
    
            return $path;
        }
    }
在這個範例中還有幾點重要事項要注意。請注意，我們只有指定資料夾名稱，而未指定檔案名稱。預設情況下，`store` 會自動產生一個不重複 ID 來作為檔案名稱。檔案的副檔名會依照該檔案的 MIME 來判斷。`store` 方法會回傳該檔案包含檔名的路徑，好讓我們能保存該路徑到資料庫中。

也可以呼叫 `Storage` Facade 上的 `putFile` 方法進行與上方範例相同的檔案存放操作：

    $path = Storage::putFile('avatars', $request->file('avatar'));
<a name="specifying-a-file-name"></a>

#### Specifying a File Name

若不想使用自動指派給保存檔案的檔名，可使用 `storeAs` 方法。該方法的引數是路徑、檔名、以及 (可選的) Disk：

    $path = $request->file('avatar')->storeAs(
        'avatars', $request->user()->id
    );
也可以呼叫 `Storage` Facade 上的 `putFileAs` 方法進行與上方範例相同的檔案存放操作：

    $path = Storage::putFileAs(
        'avatars', $request->file('avatar'), $request->user()->id
    );
> [!WARNING]  
> 路徑中若有不可列印 (Unprintable) 或無效的 Unicode 字元，則會被自動移除。因此，在將檔案路徑傳給 Laravel 的檔案存放方法前，我們可能會想先消毒 (Sanitize) 檔案路徑。可使用 `League\Flysystem\WhitespacePathNormalizer::normalizePath` 來正常化 (Normalize) 檔案路徑。

<a name="specifying-a-disk"></a>

#### Specifying a Disk

預設情況下，上傳檔案的 `store` 方法會使用預設的 Disk。若想指定另一個 Disk，請將 Disk 名稱作為第三個引數傳給 `store` 方法：

    $path = $request->file('avatar')->store(
        'avatars/'.$request->user()->id, 's3'
    );
若使用 `storeAs` 方法，則可將 Disk 名稱作為第三引數傳給該方法：

    $path = $request->file('avatar')->storeAs(
        'avatars',
        $request->user()->id,
        's3'
    );
<a name="other-uploaded-file-information"></a>

#### 其他上傳檔案的資訊

若想取得上傳檔案的原始名稱與副檔名，可使用 `getClientOriginalName` 與 `getClientOriginalExtension` 方法：

    $file = $request->file('avatar');
    
    $name = $file->getClientOriginalName();
    $extension = $file->getClientOriginalExtension();
不過，請注意，應將 `getClientOriginalName` 與 `getClientOriginalExtension` 方法視為不安全的，因為惡意使用者可以偽造檔案名稱與副檔名。因此，建議一般還是使用 `hashName` 與 `extension` 方法來取得給定上傳檔案的檔名與副檔名：

    $file = $request->file('avatar');
    
    $name = $file->hashName(); // Generate a unique, random name...
    $extension = $file->extension(); // Determine the file's extension based on the file's MIME type...
<a name="file-visibility"></a>

### 檔案可見度

在 Laravel 的 Flysystem 整合中，「^[可見度](Visibility)」是在多個平台間抽象化的檔案權限。檔案可以被定義為 `public`，或是被定義為 `private`。若將檔案定義為 `public`，即代表該檔案是可以被其他人正常存取的。舉例來說，若使用 S3 Driver，可以取得 `public` 檔案的 URL。

在使用 `put` 方法寫入檔案時，可以設定可見度：

    use Illuminate\Support\Facades\Storage;
    
    Storage::put('file.jpg', $contents, 'public');
若檔案已被保存，則可使用 `getVisibility` 來取得可見度，並使用 `setVisibility` 來設定可見度：

    $visibility = Storage::getVisibility('file.jpg');
    
    Storage::setVisibility('file.jpg', 'public');
在處理上傳的檔案時，應使用 `storePublicly` 與 `storePubliclyAs` 方法來以 `public` 可見度保存上傳的檔案：

    $path = $request->file('avatar')->storePublicly('avatars', 's3');
    
    $path = $request->file('avatar')->storePubliclyAs(
        'avatars',
        $request->user()->id,
        's3'
    );
<a name="local-files-and-visibility"></a>

#### Local Files and Visibility

使用 `local` Driver 時，`public` [可見度](#file-visibility) 對於目錄來說可翻譯為 `0755` 權限，而檔案則可翻譯為 `0644` 權限。可以在 `filesystems` 設定當中修改這個權限映射：

    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
        'permissions' => [
            'file' => [
                'public' => 0644,
                'private' => 0600,
            ],
            'dir' => [
                'public' => 0755,
                'private' => 0700,
            ],
        ],
        'throw' => false,
    ],
<a name="deleting-files"></a>

## 刪除檔案

`delete` 方法接受要刪除的單一檔案名稱，或是一組檔案名稱陣列：

    use Illuminate\Support\Facades\Storage;
    
    Storage::delete('file.jpg');
    
    Storage::delete(['file.jpg', 'file2.jpg']);
若有需要，也可指定要在哪個 Disk 上刪除檔案：

    use Illuminate\Support\Facades\Storage;
    
    Storage::disk('s3')->delete('path/file.jpg');
<a name="directories"></a>

## 目錄

<a name="get-all-files-within-a-directory"></a>

#### Get All Files Within a Directory

`files` 方法回傳一組包含給定目錄中所有檔案的陣列。若想取得包含子目錄在內的給定目錄內所有檔案的清單，可使用 `allFiles` 方法：

    use Illuminate\Support\Facades\Storage;
    
    $files = Storage::files($directory);
    
    $files = Storage::allFiles($directory);
<a name="get-all-directories-within-a-directory"></a>

#### Get All Directories Within a Directory

`directories` 方法回傳一組包含給定目錄內所有目錄的陣列。此外，也可以使用 `allDirectories` 方法來取得給定目錄內包含子目錄的所有目錄清單：

    $directories = Storage::directories($directory);
    
    $directories = Storage::allDirectories($directory);
<a name="create-a-directory"></a>

#### Create a Directory

`makeDirectory` 方法會建立給定的目錄，包含所有需要的子目錄：

    Storage::makeDirectory($directory);
<a name="delete-a-directory"></a>

#### Delete a Directory

最後，可使用 `deleteDirectory` 方法來移除某個目錄與其中所有檔案：

    Storage::deleteDirectory($directory);
<a name="testing"></a>

## 測試

使用 `Storage` Facade 的 `fake` 方法就可輕鬆地產生 Fake Disk。Fake Disk 可以與 `Illuminate\Http\UploadedFile` 類別的檔案產生工具來搭配使用，讓我們能非常輕鬆地測試檔案上傳。舉例來說：

```php
<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('albums can be uploaded', function () {
    Storage::fake('photos');

    $response = $this->json('POST', '/photos', [
        UploadedFile::fake()->image('photo1.jpg'),
        UploadedFile::fake()->image('photo2.jpg')
    ]);

    // Assert one or more files were stored...
    Storage::disk('photos')->assertExists('photo1.jpg');
    Storage::disk('photos')->assertExists(['photo1.jpg', 'photo2.jpg']);

    // Assert one or more files were not stored...
    Storage::disk('photos')->assertMissing('missing.jpg');
    Storage::disk('photos')->assertMissing(['missing.jpg', 'non-existing.jpg']);

    // Assert that a given directory is empty...
    Storage::disk('photos')->assertDirectoryEmpty('/wallpapers');
});
```
```php
<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_albums_can_be_uploaded(): void
    {
        Storage::fake('photos');

        $response = $this->json('POST', '/photos', [
            UploadedFile::fake()->image('photo1.jpg'),
            UploadedFile::fake()->image('photo2.jpg')
        ]);

        // Assert one or more files were stored...
        Storage::disk('photos')->assertExists('photo1.jpg');
        Storage::disk('photos')->assertExists(['photo1.jpg', 'photo2.jpg']);

        // Assert one or more files were not stored...
        Storage::disk('photos')->assertMissing('missing.jpg');
        Storage::disk('photos')->assertMissing(['missing.jpg', 'non-existing.jpg']);

        // Assert that a given directory is empty...
        Storage::disk('photos')->assertDirectoryEmpty('/wallpapers');
    }
}
```
預設情況下，`fake` 方法會刪除其臨時目錄下的所有檔案。若想保留這些檔案，可使用「persistentFake」方法。更多有關測試檔案上傳的資訊，可參考 [HTTP 測試說明文件中有關檔案上傳的部分](/docs/{{version}}/http-tests#testing-file-uploads)。

> [!WARNING]  
> 需要有 [GD 擴充程式](https://www.php.net/manual/en/book.image.php) 才可使用 `image` 方法。

<a name="custom-filesystems"></a>

## 自訂 Filesystem

Laravel 的 Flysystem 整合預設提供了多種可用的「Driver」。不過，Flysystem 也不是只能使用這些 Driver，還有許多其他的存放系統 Adapter 可使用。若想在 Laravel 專案中使用這些額外的 Adapter 的話，則可建立一個自訂的 Driver。

若要定義自訂檔案系統，我們首先需要一個 Flysystem Adapter。我們先來在專案中新增一個由社群維護的 Dropbox Adapter：

```shell
composer require spatie/flysystem-dropbox
```
接著，我們可以在專案的其中一個 [Service Provider](/docs/{{version}}/providers) 中 `boot` 方法內註冊這個 Driver。若要註冊 Driver，請使用 `Storage` Facade 的 `extend` 方法：

    <?php
    
    namespace App\Providers;
    
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Filesystem\FilesystemAdapter;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\ServiceProvider;
    use League\Flysystem\Filesystem;
    use Spatie\Dropbox\Client as DropboxClient;
    use Spatie\FlysystemDropbox\DropboxAdapter;
    
    class AppServiceProvider extends ServiceProvider
    {
        /**
         * Register any application services.
         */
        public function register(): void
        {
            // ...
        }
    
        /**
         * Bootstrap any application services.
         */
        public function boot(): void
        {
            Storage::extend('dropbox', function (Application $app, array $config) {
                $adapter = new DropboxAdapter(new DropboxClient(
                    $config['authorization_token']
                ));
    
                return new FilesystemAdapter(
                    new Filesystem($adapter, $config),
                    $adapter,
                    $config
                );
            });
        }
    }
傳入 `extend` 方法的第一個引數是 Driver 的名稱，而第二個引數則是一本接收了 `$app` 與 `$config` 變數的閉包。該閉包應回傳 `Illuminate\Filesystem\FilesystemAdapter` 的實體。`$config` 變數則包含了定義在 `config/filesystems.php` 中指定 Disk 的設定值。

建立並註冊好擴充的 Service Provider 後，就可以在 `config/filesystems.php` 設定當中使用 `dropbox` Driver。
