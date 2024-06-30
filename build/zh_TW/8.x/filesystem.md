---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/73/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:26:00Z'
---

# 檔案存放空間

- [簡介](#introduction)
- [設定](#configuration)
   - [Local Driver](#the-local-driver)
   - [Public Disk](#the-public-disk)
   - [Driver 前置需求](#driver-prerequisites)
   - [與 Amazon S3 相容的檔案系統](#amazon-s3-compatible-filesystems)
   - [快取](#caching)
- [取得 Disk 實體](#obtaining-disk-instances)
   - [隨需建立的 Disk](#on-demand-disks)
- [取得檔案](#retrieving-files)
   - [下載檔案](#downloading-files)
   - [檔案 URL](#file-urls)
   - [檔案的詮釋資料](#file-metadata)
- [保存檔案](#storing-files)
   - [檔案上傳](#file-uploads)
   - [檔案的可見性 (Visibility)](#file-visibility)
- [刪除檔案](#deleting-files)
- [目錄](#directories)
- [自訂檔案系統](#custom-filesystems)

<a name="introduction"></a>

## 簡介

多虧了 [Flysystem](https://github.com/thephpleague/flysystem)，Laravel 提供了強大的檔案系統抽象介面。Flysystem 是 Frank de Jonge 提供的一個 PHP 套件。Laravel 整合 Flysystem 來提供多個簡單的 Driver，可處理本機檔案系統、SFTP、Amazon S3 等。甚至，在本機開發環境與正式伺服器間交換使用各個不同的儲存空間非常地簡單，且每個儲存系統都有相同的 API。

<a name="configuration"></a>

## 設定

Laravel 的檔案系統設定檔位在 `config/filesystems.php`。在這個檔案中，我們可以設定所有的檔案系統「^[Disk](磁碟)」。各個 Disk 都代表了一個特定的儲存空間 Driver 與儲存位置。該設定檔內已包含了各個支援 Driver 的範例設定，讓你能修改這些設定來反映出儲存空間偏好與認證方式。

`local` Driver 負責處理保存在執行該 Laravel 專案之本機伺服器上的檔案。而 `s3` Driver 則用來將檔案寫入 Amazon 的 S3 雲端儲存服務。

> {tip} 可以隨意設定多個 Disk，甚至也可以設定多個使用相同 Driver 的 Disk。

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

    php artisan storage:link

保存檔案並建立好符號連結後，就可以使用 `asset` 輔助函式來建立該檔案的 URL：

    echo asset('storage/file.txt');

也可以在 `filesystems` 設定檔中設定其他符號連結。在執行 `storage:link` 指令時，會建立設定中的各個符號連結：

    'links' => [
        public_path('storage') => storage_path('app/public'),
        public_path('images') => storage_path('app/images'),
    ],

<a name="driver-prerequisites"></a>

### Driver 的前置需求

<a name="composer-packages"></a>

#### Composer 套件

使用 S3 或 SFTP Driver 前，需要使用 Composer 套件管理員安裝適當的套件：

- Amazon S3: `composer require --with-all-dependencies league/flysystem-aws-s3-v3 "^1.0"`
- SFTP: `composer require league/flysystem-sftp "~1.0"`

此外，也可選擇安裝 ^[Cached Adapter](快取配接器) 來增加效能：

- CachedAdapter: `composer require league/flysystem-cached-adapter "~1.0"`

<a name="s3-driver-configuration"></a>

#### S3 Driver 設定

S3 Driver 的設定資訊保存在 `config/filesystems.php` 設定檔內。這個檔案中包含了用於 S3 Driver 的範例設定。可以自行將陣列改為你的 S3 設定與認證資訊。為了方便起見，這些環境變數的名稱都符合 AWS CLI 使用的命名慣例。

<a name="ftp-driver-configuration"></a>

#### FTP Driver 設定

Laravel 的 Flysystem 整合可以完美配合 FTP。不過，Laravel 的預設 `filesystems.php` 設定檔中並未包含 FTP 的範例設定。若有需要設定 FTP 檔案系統，可使用下列範例設定：

    'ftp' => [
        'driver' => 'ftp',
        'host' => env('FTP_HOST'),
        'username' => env('FTP_USERNAME'),
        'password' => env('FTP_PASSWORD'),
    
        // 可選的 FTP 設定...
        // 'port' => env('FTP_PORT', 21),
        // 'root' => env('FTP_ROOT'),
        // 'passive' => true,
        // 'ssl' => true,
        // 'timeout' => 30,
    ],

<a name="sftp-driver-configuration"></a>

#### SFTP Driver 設定

Laravel 的 Flysystem 整合可以完美配合 SFTP。不過，Laravel 的預設 `filesystems.php` 設定檔中並未包含 SFTP 的範例設定。若有需要設定 SFTP 檔案系統，可使用下列範例設定：

    'sftp' => [
        'driver' => 'sftp',
        'host' => env('SFTP_HOST'),
        
        // 設定 Basic 身份認證...
        'username' => env('SFTP_USERNAME'),
        'password' => env('SFTP_PASSWORD'),
    
        // 設定有加密密碼之基於 SSH 金鑰的身份認證...
        'privateKey' => env('SFTP_PRIVATE_KEY'),
        'password' => env('SFTP_PASSWORD'),
    
        // Optional SFTP Settings...
        // 'port' => env('SFTP_PORT', 22),
        // 'root' => env('SFTP_ROOT'),
        // 'timeout' => 30,
    ],

<a name="amazon-s3-compatible-filesystems"></a>

### 相容於 Amazon S3 的檔案系統

預設情況下，專案的 `filesystems` 設定檔中已包含了一個 `s3` Disk 設定。除了以該 Disk 來使用 Amazon S3 外，還可以通過這個 Disk 來使用相容於 S3 的檔案存放服務，如 [MinIO](https://github.com/minio/minio) 或 [DigitalOcean Spaces](https://www.digitalocean.com/products/spaces/)。

一般來說，為 Disk 設定要使用服務的認證資訊後，就只需要更改 `url` 設定選項即可。這個選項值通常是以 `AWS_ENGPOINT` 環境變數定義的：

    'endpoint' => env('AWS_ENDPOINT', 'https://minio:9000'),

<a name="caching"></a>

### 快取

若要在給定 Disk 上啟用快取，只需要在該 Disk 的設定選項中加上 `cache` 指示詞即可。`cache` 選項應為一個快取選項陣列，其中包含：`disk` 名稱、`expire` 單位為秒的有效期間、`prefix` 快取前置詞等：

    's3' => [
        'driver' => 's3',
    
        // 其他的 Disk 選項...
    
        'cache' => [
            'store' => 'memcached',
            'expire' => 600,
            'prefix' => 'cache-prefix',
        ],
    ],

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

> {note} 使用 `local` Driver 時，`url` 的回傳值未經過 URL 編碼。因此，我們建議你只使用能產生有效 URL 的檔名來保存檔案。

<a name="temporary-urls"></a>

#### 時效性 URL

使用 `temporaryUrl` 方法，就可以為儲存在 `s3` Driver 上的檔案建立時效性 URL。這個方法接受一個路徑、以及一個用來指定 URL 何時過期的 `DateTime` 實體：

    use Illuminate\Support\Facades\Storage;
    
    $url = Storage::temporaryUrl(
        'file.jpg', now()->addMinutes(5)
    );

若想指定額外的 [S3 Request 參數](https://docs.aws.amazon.com/AmazonS3/latest/API/RESTObjectGET.html#RESTObjectGET-requests)，只需要將 Request 參數陣列作為第三個引數傳給 `temporaryUrl` 方法即可：

    $url = Storage::temporaryUrl(
        'file.jpg',
        now()->addMinutes(5),
        [
            'ResponseContentType' => 'application/octet-stream',
            'ResponseContentDisposition' => 'attachment; filename=file2.jpg',
        ]
    );

若有需要自訂某個存放 Disk 要如何產生臨時 URL，可以使用 `buildTemporaryUrlsUsing` 方法。舉例來說，若有檔案儲存在不支援時效性 URL 的 Driver 上，而在某個 Controller 上我們又想讓使用者能下載這些檔案，就很適合使用這個方法。一般來說，應在某個 Service Provider 的 `boot` 方法內呼叫這個方法：

    <?php
    
    namespace App\Providers;
    
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Facades\URL;
    use Illuminate\Support\ServiceProvider;
    
    class AppServiceProvider extends ServiceProvider
    {
        /**
         * Bootstrap any application services.
         *
         * @return void
         */
        public function boot()
        {
            Storage::disk('local')->buildTemporaryUrlsUsing(function ($path, $expiration, $options) {
                return URL::temporarySignedRoute(
                    'files.download',
                    $expiration,
                    array_merge($options, ['path' => $path])
                );
            });
        }
    }

<a name="url-host-customization"></a>

#### 自訂 URL 主機

若想為 `Storage` Facade 產生的 URL 預先定義主機，可在 Disk 設定陣列內加上一個 `url` 選項：

    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],

<a name="file-metadata"></a>

### 檔案詮釋資料

除了讀寫檔案外，Laravel 還提供了一些有關檔案本身的資訊。舉例來說，`size` 方法可用來取得單位為^[位元組](Bytes)的檔案大小：

    use Illuminate\Support\Facades\Storage;
    
    $size = Storage::size('file.jpg');

`lastModified` 方法回傳以 UNIX ^[時戳](Timestamp)表示的檔案最後修改時間：

    $time = Storage::lastModified('file.jpg');

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

<a name="automatic-streaming"></a>

#### 自動串流

使用串流將檔案到寫入存放空間可顯著降低記憶體使用。若想讓 Laravel 自動管理存放路徑中給定檔案的串流，可使用 `putFile` 或 `putFileAs` 方法。這兩個方法接受 `Illuminate\Http\File` 或 `Illuminate\Http\UploadedFile` 實體，會自動將該檔案串流到指定的路徑上：

    use Illuminate\Http\File;
    use Illuminate\Support\Facades\Storage;
    
    // 自訂為檔案名稱產生一個不重複的 ID...
    $path = Storage::putFile('photos', new File('/path/to/photo'));
    
    // 手動指定檔案名稱...
    $path = Storage::putFileAs('photos', new File('/path/to/photo'), 'photo.jpg');

有關 `putFile` 方法，還有幾點重要事項要注意。請注意，我們只有指定資料夾名稱，而未指定檔案名稱。預設情況下，`putFile` 會自動產生一個不重複 ID 來作為檔案名稱。檔案的副檔名會依照該檔案的 MIME 來判斷。`putFile` 方法會回傳該檔案包含檔名的路徑，好讓我們能保存該路徑到資料庫中。

`putFile` 與 `putFileAs` 方法也接受一個用來指定保存檔案「^[可見度](Visibility)」的引數。若你使用 Amazon S3 等雲端 Disk 來儲存檔案且想產生能公開存取的 URL，這個功能就特別實用：

    Storage::putFile('photos', new File('/path/to/photo'), 'public');

<a name="prepending-appending-to-files"></a>

#### 將內容加到檔案的最前面或最後面

使用 `prepend` 或 `append` 方法，就可以讓我們將內容寫入到檔案的最前端或最後端：

    Storage::prepend('file.log', 'Prepended Text');
    
    Storage::append('file.log', 'Appended Text');

<a name="copying-moving-files"></a>

#### 複製與移動檔案

可使用 `copy` 方法來將現有的檔案複製到 Disk 中的新路徑。而 `move` 方法則可用來重新命名現有檔案或將現有檔案移至新路徑：

    Storage::copy('old/file.jpg', 'new/file.jpg');
    
    Storage::move('old/file.jpg', 'new/file.jpg');

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
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\Response
         */
        public function update(Request $request)
        {
            $path = $request->file('avatar')->store('avatars');
    
            return $path;
        }
    }

在這個範例中還有幾點重要事項要注意。請注意，我們只有指定資料夾名稱，而未指定檔案名稱。預設情況下，`store` 會自動產生一個不重複 ID 來作為檔案名稱。檔案的副檔名會依照該檔案的 MIME 來判斷。`store` 方法會回傳該檔案包含檔名的路徑，好讓我們能保存該路徑到資料庫中。

也可以呼叫 `Storage` Facade 上的 `putFile` 方法進行與上方範例相同的檔案存放操作：

    $path = Storage::putFile('avatars', $request->file('avatar'));

<a name="specifying-a-file-name"></a>

#### 指定檔案名稱

若不想使用自動指派給保存檔案的檔名，可使用 `storeAs` 方法。該方法的引數是路徑、檔名、以及 (可選的) Disk：

    $path = $request->file('avatar')->storeAs(
        'avatars', $request->user()->id
    );

也可以呼叫 `Storage` Facade 上的 `putFileAs` 方法進行與上方範例相同的檔案存放操作：

    $path = Storage::putFileAs(
        'avatars', $request->file('avatar'), $request->user()->id
    );

> {note} 路徑中若有^[不可列印](Unprintable)或無效的 Unicode 字元，則會被自動移除。因此，在將檔案路徑傳給 Laravel 的檔案存放方法前，我們可能會想先^[消毒](Sanitize)檔案路徑。可使用 `League\Flysystem\Util::normalizePath` 來^[正常化](Normalize)檔案路徑。

<a name="specifying-a-disk"></a>

#### 指定 Disk

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
    
    $name = $file->hashName(); // 產生一個不重複、隨機的名稱...
    $extension = $file->extension(); // 依據檔案的 MIME 型別判斷檔案的副檔名...

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

#### 本機檔案與可見度

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

#### 取得目錄中的所有檔案

`files` 方法回傳一組包含給定目錄中所有檔案的陣列。若想取得包含子目錄在內的給定目錄內所有檔案的清單，可使用 `allFiles` 方法：

    use Illuminate\Support\Facades\Storage;
    
    $files = Storage::files($directory);
    
    $files = Storage::allFiles($directory);

<a name="get-all-directories-within-a-directory"></a>

#### 取得目錄內的所有目錄

`directories` 方法回傳一組包含給定目錄內所有目錄的陣列。此外，也可以使用 `allDirectories` 方法來取得給定目錄內包含子目錄的所有目錄清單：

    $directories = Storage::directories($directory);
    
    $directories = Storage::allDirectories($directory);

<a name="create-a-directory"></a>

#### 建立目錄

`makeDirectory` 方法會建立給定的目錄，包含所有需要的子目錄：

    Storage::makeDirectory($directory);

<a name="delete-a-directory"></a>

#### 刪除目錄

最後，可使用 `deleteDirectory` 方法來移除某個目錄與其中所有檔案：

    Storage::deleteDirectory($directory);

<a name="custom-filesystems"></a>

## 自訂 Filesystem

Laravel 的 Flysystem 整合預設提供了多種可用的「Driver」。不過，Flysystem 也不是只能使用這些 Driver，還有許多其他的存放系統 Adapter 可使用。若想在 Laravel 專案中使用這些額外的 Adapter 的話，則可建立一個自訂的 Driver。

若要定義自訂檔案系統，我們首先需要一個 Flysystem Adapter。我們先來在專案中新增一個由社群維護的 Dropbox Adapter：

    composer require spatie/flysystem-dropbox

接著，我們可以在專案的其中一個 [Service Provider](/docs/{{version}}/providers) 中 `boot` 方法內註冊這個 Driver。若要註冊 Driver，請使用 `Storage` Facade 的 `extend` 方法：

    <?php
    
    namespace App\Providers;
    
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\ServiceProvider;
    use League\Flysystem\Filesystem;
    use Spatie\Dropbox\Client as DropboxClient;
    use Spatie\FlysystemDropbox\DropboxAdapter;
    
    class AppServiceProvider extends ServiceProvider
    {
        /**
         * Register any application services.
         *
         * @return void
         */
        public function register()
        {
            //
        }
    
        /**
         * Bootstrap any application services.
         *
         * @return void
         */
        public function boot()
        {
            Storage::extend('dropbox', function ($app, $config) {
                $client = new DropboxClient(
                    $config['authorization_token']
                );
    
                return new Filesystem(new DropboxAdapter($client));
            });
        }
    }

傳入 `extend` 方法的第一個引數是 Driver 的名稱，而第二個引數則是一本接收了 `$app` 與 `$config` 變數的閉包。該閉包應回傳 `League\Flysystem\Filesystem` 的實體。`$config` 變數則包含了定義在 `config/filesystems.php` 中指定 Disk 的設定值。

建立並註冊好擴充的 Service Provider 後，就可以在 `config/filesystems.php` 設定當中使用 `dropbox` Driver。
