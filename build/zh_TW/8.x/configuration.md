---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/27/en-zhtw'
updatedAt: '2024-06-30T08:17:00Z'
contributors: {  }
progress: 58.47
---

# 設定

- [簡介](#introduction)
- [環境組態](#environment-configuration)
  - [環境變數型別](#environment-variable-types)
  - [取得環境設定](#retrieving-environment-configuration)
  - [判斷目前的環境](#determining-the-current-environment)
  
- [存取設定值](#accessing-configuration-values)
- [設定快取](#configuration-caching)
- [偵錯模式](#debug-mode)
- [維護模式](#maintenance-mode)

<a name="introduction"></a>

## 簡介

在 Laravel 框架中，所有設定檔都儲存在 `config` 目錄內。各個選項都有說明文件，歡迎閱讀這些檔案並熟悉可用的選項。

這些設定檔能讓你設定一些東西，像是資料庫連線資訊、郵件伺服器設定，以及其他數種核心設定值，如網站的時區、以及加密金鑰。

<a name="environment-configuration"></a>

## 環境組態

若我們能根據網站正在執行的環境不同，而載入不同的設定值，那麼會有很多好處。舉例來說，在本機與正式環境上我們可能會想使用不同的快取 Driver。

為了輕鬆達成此目標，Laravel 使用了 [DotEnv](https://github.com/vlucas/phpdotenv) PHP 函式庫。在全新的 Laravel 安裝上，專案的根目錄中會包含一個定義了許多常見環境變數的 `.env.example` 檔案。在 Laravel 的安裝過程中，這個檔案會自動被複製為 `.env`。

Laravel 的預設 `.env` 檔包含了一些可能會依據專案是在本機還是正式環境上執行而不同的常見設定值。這些值接著會在 `config` 目錄中的  Laravel 設定檔內通過 Laravel 的 `env` 函式來取用。

若是與團隊協作開發，可能會想繼續將 `.env.example` 檔案包含在專案中。只要在範例設定檔中填入一些預留位置值，團隊中的其他開發人員就能清楚地知道要執行專案需要哪些環境變數。

> [!TIP]  
> 任何 `.env` 檔內的變數都可通過外部環境變數來複寫，如伺服器等級或是系統等級的環境變數。

<a name="environment-file-security"></a>

#### 環境檔安全性

`.env` 檔不應被簽入應用程式的版本控制中，因為每個使用應用程式的開發人員／伺服器都可能需要不同的環境組態。此外，若將 `.env` 檔簽入版本控制的話，當有入侵者取得了版本控制儲存庫的存取權限，就可能會造成安全性風險，因為其中的機敏認證資料都會被暴露。

<a name="additional-environment-files"></a>

#### 額外的環境檔

在載入專案的環境變數時，Laravel 會判斷是否有從外部提供 `APP_ENV` 環境變數，或是從 `--env` CLI 引數指定環境，若有從外部指定環境，則 Laravel 會嘗試載入 `.env.[APP_ENV]` 檔案（若有的話）。若該檔案不存在，則會載入 `.env` 檔。

<a name="environment-variable-types"></a>

### 環境變數型別

在 `.env` 檔中的所有變數通常都會被解析為字串，因此，有一些保留值可以用來在 `env()` 函式中回傳更多類型的型別：

| `.env` 值 | `env()` 值 |
| --- | --- |
| true | (bool) true |
| (true) | (bool) true |
| false | (bool) false |
| (false) | (bool) false |
| empty | (string) '' |
| (空值) | (string) '' |
| null | (null) null |
| (null) | (null) null |

若有需要定義包含空白的環境變數值，則需要將值以雙引號圍起來：

    APP_NAME="My Application"
<a name="retrieving-environment-configuration"></a>

### 取得環境設定

該檔案中列出的所有變數都會在網站接收到請求的時候被載入進 `$_ENV` PHP 超全域變數內。不過，可以使用 `env` 輔助函式來在設定檔中取得這些變數。事實上，若閱讀 Laravel 的設定檔，就會發現其中許多選項已經使用到這個輔助函式了：

    'debug' => env('APP_DEBUG', false),
傳入 `env` 函式的第二個值為「預設值」。該值會在環境變數中沒有給定索引鍵的時候被回傳。

<a name="determining-the-current-environment"></a>

### 判斷目前環境

網站目前的環境是通過 `.env` 檔中的 `APP_ENV` 變數來判斷的。可以通過 `App` [Facade](/docs/{{version}}/facades) 上的 `environment` 方法來存取這個值：

    use Illuminate\Support\Facades\App;
    
    $environment = App::environment();
可以傳入引數給 `environment` 方法來判斷目前環境是否符合給定的值。該方法會在目前環境符合任何一個給定值的時候回傳 `true`：

    if (App::environment('local')) {
        // The environment is local
    }
    
    if (App::environment(['local', 'staging'])) {
        // The environment is either local OR staging...
    }
> [!TIP]  
> 可以通過在伺服器等級上定義 `APP_ENV` 環境變數來複寫網站偵測到的應用程式環境。

<a name="accessing-configuration-values"></a>

## 存取設定值

可以輕鬆的在專案內的任何地方通過 `config` 全域輔助函式來存取設定值。設定值可以通過「點」語法來存取，即包含設定檔名稱與欲存取的選項名。也可以指定設定選項不存在時要回傳的預設值：

    $value = config('app.timezone');
    
    // Retrieve a default value if the configuration value does not exist...
    $value = config('app.timezone', 'Asia/Seoul');
若要在執行階段修改設定值，可以傳入陣列進 `config` 輔助函式：

    config(['app.timezone' => 'America/Chicago']);
<a name="configuration-caching"></a>

## 設定快取

為了加速網站執行，應使用 `config:cache` Artisan 指令來將所有的設定檔快取為單一檔案。這個指令會將所有的設定選項合併為單一檔案，以讓 Laravel 可快速載入。

`php artisan config:cache` 指令通常應放在部署流程中。該指令不應在本機開發時執行，因為在專案開發的時候會時常需要修改設定值。

> [!NOTE]  
> 若在部署流程中執行了 `config:cache` 指令，應確保只有在設定檔中呼叫 `env` 函式。設定檔被快取後，就不會再載入 `.env` 檔了。因此，`env` 函式只會回傳外部的、系統等級的環境變數。

<a name="debug-mode"></a>

## 偵錯模式

`config/app.php` 設定檔中的 `debug` 選項用來判斷錯誤在實際顯示給使用者時要包含多少資訊。預設情況下，這個選項被設為依照 `APP_DEBUG` 環境變數值，該環境變數儲存於 `.env` 檔內。

在本機上開發時，應將 `APP_DEBUG` 環境變數設為 `true`。 **在正式環境上，這個值一定要是 `false`。若在正式環境上將該變數設為 `true`，則會有將機敏設定值暴露給網站終端使用者的風險。**

<a name="maintenance-mode"></a>

## 維護模式

當網站在維護模式下時，所有連入網站的請求都會顯示一個自訂的 View。這樣一來便能在進行維護時輕鬆地「禁用」網站。維護模式的檢查包含在專案的預設 Middleware 堆疊中。若應用程式在維護模式，則會擲回 `Symfony\Component\HttpKernel\Exception\HttpException`，以及狀態碼 503。

若要啟用維護模式，請執行 `down` Artisan 指令：

    php artisan down
若想要在所有維護模式回應中傳送 `Refresh` HTTP 標頭，則請在叫用 `down` 指令時提供 `refresh` 選項。`Refresh` 表頭會告訴瀏覽器：在指定 N 秒後，重新整理頁面：

    php artisan down --refresh=15
也可以傳入一個 `retry` 選項給 `down` 指令，會用來設為 `Retry-After` HTTP 標頭的值，雖然一般的瀏覽器都會忽略這個標頭：

    php artisan down --retry=60
<a name="bypassing-maintenance-mode"></a>

#### 繞過維護模式

即時在維護模式下，也可以使用 `secret` 選項來指定一個用來繞過維護模式的權杖：

    php artisan down --secret="1630542a-246b-4b66-afa1-dd72a4c43515"
將應用程式放入維護模式後，可以瀏覽符合該權杖的應用程式網址，Laravel 會簽發一個繞過維護模式的 Cookie 給瀏覽器：

    https://example.com/1630542a-246b-4b66-afa1-dd72a4c43515
在存取該隱藏路由時，會接著被重新導向至應用程式的 `/` 路由。該 Cookie 被簽發給瀏覽器後，就可以像沒有在維護模式一樣正常地瀏覽應用程式。

> [!TIP]  
> 維護模式的密碼通常來說應該要由字母與數字字元組成，並可選地包含減號 (`-`, Dash)。應避免一些在 URL 中由特殊意義的字元，如 `?` 或 `&`。

<a name="pre-rendering-the-maintenance-mode-view"></a>

#### 預轉譯維護模式 View

若在部署過程中使用了 `php artisan down` 指令，若使用者在 Composer 依賴或其他基礎設施元件更新時存取了應用程式，則可能會遇到錯誤。這是因為 Laravel 框架中重要的部分必須要先啟動才能判斷應用程式是否在維護模式下，並才能接著使用樣板引擎來轉譯維護模式的 View。

因為如此，Laravel 提供了可以預先轉譯維護模式 View 的功能，並能在整個請求週期的一開始就將其回傳。這個 View 會在任何應用程式的相依性套件載入前就預先被轉譯。可以使用 `down` 指令的 `render` 選項來預轉譯所選的樣板：

    php artisan down --render="errors::503"
<a name="redirecting-maintenance-mode-requests"></a>

#### 重新導向維護模式的請求

在維護模式時，不管使用者嘗試存取什麼網址，Laravel 都會顯示維護模式 View。若由需要的話，也可以讓 Laravel 將所有請求都重新導向到一個特定的 URL。可以通過使用 `redirect` 選項來完成。舉例來說，我們可能會想將所有請求都重新導向至 `/` URL：

    php artisan down --redirect=/
<a name="disabling-maintenance-mode"></a>

#### 禁用維護模式

若要禁用維護模式，請使用 `up` 指令：

    php artisan up
> [!TIP]  
> 可以通過在 `resources/views/errors/503.blade.php` 中定義你自己的樣板來自定預設的維護模式樣板。

<a name="maintenance-mode-queues"></a>

#### 維護模式與佇列

當網站在維護模式下，所有[佇列任務](/docs/{{version}}/queues)都不會被處理。當網站離開維護模式後，任務就會繼續被正常處理。

<a name="alternatives-to-maintenance-mode"></a>

#### 維護模式替代

由於維護模式會導致應用程式有數秒的停機時間，可考慮使用像是 [Laravel Vapor](https://vapor.laravel.com) 與 [Envoyer](https://envoyer.io) 等替代方案來在 Laravel 中達成不需停機的部署。
