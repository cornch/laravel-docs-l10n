---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/169/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 65.16
---

# Laravel Valet

- [簡介](#introduction)
- [安裝](#installation)
  - [更新 Valet](#upgrading-valet)
  
- [執行網站](#serving-sites)
  - [「Park」指令](#the-park-command)
  - [「Link」指令](#the-link-command)
  - [使用 TLS 來提供安全連線](#securing-sites)
  - [執行預設網站](#serving-a-default-site)
  
- [共享網站](#sharing-sites)
  - [使用 Ngrok 來貢獻網站](#sharing-sites-via-ngrok)
  - [使用 Expose 來共享網站](#sharing-sites-via-expose)
  - [在區域網路上共享網站](#sharing-sites-on-your-local-network)
  
- [個別網站的環境變數](#site-specific-environment-variables)
- [為服務提供代理伺服器](#proxying-services)
- [自定 Valet Driver](#custom-valet-drivers)
  - [區域 Driver](#local-drivers)
  
- [其他 Valet 指令](#other-valet-commands)
- [Valet 的目錄與檔案](#valet-directories-and-files)

<a name="introduction"></a>

## 簡介

[Laravel Valet](https://github.com/laravel/valet) 是一個為 macOS 極簡主義者提供的開發環境。Laravel Valet 會將你的 Mac 設定為在裝置啟動後自動執行 [Nginx](https://www.nginx.com/)。接著，Valet 會通過 [DnsMasq](https://en.wikipedia.org/wiki/Dnsmasq)來將所有 `*.test` 網域都指向本機裝置上安裝的網站。

換句話說，Valet 是一個只佔用約 7MB 記憶體的急速 Laravel 開發環境。Valet 並非 [Sail] 或 [Homestead](/docs/{{version}}/homestead) 的完整替代方案。不過，如果你想要有彈性、基本的、急速的開發環境，或是要在記憶體容量有限的裝置上進行開發，Valet 是一個不錯的選擇。

Valet 內建對下列軟體的支援，但除了本列表外亦支援更多：

<style>
    #valet-support > ul {
        column-count: 3; -moz-column-count: 3; -webkit-column-count: 3;
        line-height: 1.9;
    }
</style>
<div id="valet-support" markdown="1">
- [Laravel](https://laravel.com)
- [Lumen](https://lumen.laravel.com)
- [Bedrock](https://roots.io/bedrock/)
- [CakePHP 3](https://cakephp.org)
- [Concrete5](https://www.concrete5.org/)
- [Contao](https://contao.org/en/)
- [Craft](https://craftcms.com)
- [Drupal](https://www.drupal.org/)
- [ExpressionEngine](https://www.expressionengine.com/)
- [Jigsaw](https://jigsaw.tighten.co)
- [Joomla](https://www.joomla.org/)
- [Katana](https://github.com/themsaid/katana)
- [Kirby](https://getkirby.com/)
- [Magento](https://magento.com/)
- [OctoberCMS](https://octobercms.com/)
- [Sculpin](https://sculpin.io/)
- [Slim](https://www.slimframework.com)
- [Statamic](https://statamic.com)
- 靜態 HTML
- [Symfony](https://symfony.com)
- [WordPress](https://wordpress.org)
- [Zend](https://framework.zend.com)

</div>
不過，也可以使用你自己的[自定 Driver](#custom-valet-drivers) 來擴充 Valet。

<a name="installation"></a>

## 安裝

> [!NOTE]  
> 必須在 macOS 並有安裝 [Homebrew](https://brew.sh/)，才可安裝 Valet。在安裝前，請先確保沒有其他如 Apache 或 Nginx 等的程式繫結到你本機裝置上的 80 連接埠。

若要開始安裝 Vlaet，請先使用 `update` 指令來確保 Homebrew 已更新：

    brew update
接著，請使用 Homebrew 來安裝 PHP：

    brew install php
安裝好 PHP 後，就可以來安裝 [Composer 套件管理員](https://getcomposer.org)。此外，也請確保 `~/.composer/vendor/bin` 有在你的系統「PATH」環境變數中。裝好 Composer 後，就可將 Laravel Valet 作為全域 Composer 套件安裝：

    composer global require laravel/valet
最後，可執行 Valet 的 `install` 指令。這個指令會設定並安裝 Valet 與 DnsMasq。此外，也會將 Valet 所需的常駐程式 (Daemon) 設為隨系統啟動：

    valet install
安裝好 Valet 後，可嘗試在終端機上使用如 `ping foobar.test` 等的指令來 pint 任意的 `*.test` 網域。若 Valet 成功安裝，應該可以看到該網域在 `127.0.0.1` 上回應。

你的裝置每次開機時，Valet 都會自動啟動其需要的服務。

<a name="php-versions"></a>

#### PHP 版本

在 Valet 中，也可以使用 `valet use php@版本` 指令來切換 PHP 的版本。如果尚未安裝所選擇的 PHP 版本，Valet 會自動通過 Homebrew 安裝：

    valet use php@7.2
    
    valet use php
可以在專案根目錄建立一個 `.valetphprc` 檔。`.valetphprc` 檔應包含該網站要使用的 PHP 版本：

    php@7.2
建立好該檔案後，只要執行 `valet use` 指令，`valet use` 就會讀取這個檔案來判斷該網站要使用哪個 PHP 版本。

> [!NOTE]  
> 即使安裝了多個 PHP 版本，Valet 一次也只能執行一個 PHP 版本。

<a name="database"></a>

#### 資料庫

若你的專案需要資料庫，請參考看看 [DBngin](https://dbngin.com)。DBngin 提供了免費，^[All-in-One](%E9%9B%86%E5%A4%9A%E5%8A%9F%E8%83%BD%E7%82%BA%E4%B8%80%E9%AB%94) 的資料庫管理工具，包含 MySQL、PostgreSQL、與 Redis。安裝好 DBnginx 後，就可以在 `127.0.0.1` 上使用 `root` 帳號與空字串密碼來連線到資料庫。

<a name="resetting-your-installation"></a>

#### 重設安裝

若無法正常執行 Valet，請執行 `composer global update` 指令，然後再執行 `valet install`。這兩個指令會重設 Valet 安裝，並可解決大多數的問題。在極少數的狀況下，也可能有需要執行 `valet uninstall --force`，再執行 `valet isntall` 來「硬重設」Valet。

<a name="upgrading-valet"></a>

### 升級 Valet

在終端機中執行 `composer global update` 指令即可更新 Valet。升級完後，建議執行 `valet install` 指令好讓 Valet 能在必要的情況下針對你的設定檔進行額外的升級。

<a name="serving-sites"></a>

## 執行網站

安裝完 Valet 後，就可開始執行你的 Laravel 專案。Valet 提供了兩個指令能讓你用來執行專案：`park` 與 `link`。

<a name="the-park-command"></a>

### `park` 指令

`park` 指令會在你的裝置上註冊一個目錄，該目錄下包含了你所有的專案。將目錄「^[park](%E5%AD%98%E6%94%BE)」到 Valet 後，該目錄下的所有目錄都可通過 `http://<目錄名稱>.test` 來在你的 Web 瀏覽器上存取：

    cd ~/Sites
    
    valet park
就這樣，不需要其他設定。現在，在已「Park」目錄下的任何專案，都會自動使用 `http://<目錄名稱>.test` 這樣的管理來執行。所以，如果你的 Park 目錄中有一個「laravel」目錄，則可通過 `http://laravel.test` 來存取該目錄中的專案。此外，Valet 也自動讓你能使用任何子網域來存取網站 (`http://foo.laravel.test`)。

<a name="the-link-command"></a>

### `link` 指令

`link` 指令也可用來執行 Laravel 專案。如果你只想要執行某個目錄下的單一網站而不是整個目錄的話，就適合使用該指令：

    cd ~/Sites/laravel
    
    valet link
使用 `link` 指令來將專案 ^[Link](%E9%80%A3%E7%B5%90) 到 Valet 後，就可使用其目錄名稱存取該專案。所以，上述範例所 Link 的網站可使用 `http://laravel.test` 存取。此外，Valet 也會自動讓你能使用任意子網域來存取該網站 (`http://foo.laravel.test`)。

若想使用不同的主機名稱來執行網站，可將主機名稱傳給 `link` 指令。舉例來說，執行下列指令即可在 `http://application.test` 上存取該專案：

    cd ~/Sites/laravel
    
    valet link application
可以執行 `links` 指令來顯示目前已連結的目錄清單：

    valet links
`unlink` 指令可用來刪除網站的符號連結 (Symbolic Link)：

    cd ~/Sites/laravel
    
    valet unlink
<a name="securing-sites"></a>

### 使用 TLS 來為網站提供安全連線

預設情況下，Valet 會通過 HTTP 來執行網站。不過，如果你想要使用 HTTP/2 來通過加密的 TLS 執行網站的話，可以使用 `secure` 指令。舉例來說，若某個 Valet 執行的網站在 `laravel.test` 網域上的話，可執行下列指令來讓該網站使用安全連線：

    valet secure laravel
若要關閉某個網站的安全連線，將其還原為使用一般 HTTP 來執行的話，請使用 `unsecure` 指令。與 `secure` 指令類似，該指令接受要關閉安全連線的網域名稱：

    valet unsecure laravel
<a name="serving-a-default-site"></a>

### 執行預設網站

有時候，我們可能會想讓 Valet 在瀏覽未知的 `test` 網域時使用一個「預設」網站而不是顯示 `404`。這時，可以在 `~/.config/valet/config.json` 設定檔中加上一個 `default` 選項，將該選項設定為要作為預設網站執行的網站路徑：

    "default": "/Users/Sally/Sites/foo",
<a name="sharing-sites"></a>

## 共享網站

Valet 中也提供了一個指令能讓你共享本機上的網站，並讓你能輕鬆地在行動裝置上測試網站，或是與團隊成員或客戶共享網站。

<a name="sharing-sites-via-ngrok"></a>

### 通過 Ngrok 共享網站

若要貢獻網站，請先在終端機內打開該網站的目錄，然後執行 Valet 的 `share` 指令。Valet 會將一個可公開存取的 URL 拷貝到你的剪貼簿內，可讓你直接貼到瀏覽器內或是與團隊共享：

    cd ~/Sites/laravel
    
    valet share
若要停止共享網址，請按一下 `Control + C`。使用 Ngrok 共享網站時，你需要先[建立 Ngrok 帳號](https://dashboard.ngrok.com/signup)並[設定驗證 Token](https://dashboard.ngrok.com/get-started/your-authtoken)。

> [!TIP]  
> 可以傳入額外的 Ngrok 引數給 share 指令，如 `valet share --region=eu`。更多資訊請參考 [Ngrok 的說明文件](https://ngrok.com/docs)。

<a name="sharing-sites-via-expose"></a>

### 使用 Expose 來共享網站

如果你有安裝 [Expose](https://expose.dev)，可以在終端機內打開你的網站並執行 `expose` 指令。有關該指令支援的其他額外指令列參數，請參考 [Expose 說明文件](https://expose.dev/docs)。共享網站後，Expose 會顯示可共享的 URL，讓你可在其他裝置上使用，或是供團隊成員使用：

    cd ~/Sites/laravel
    
    expose
若要停止共享網站，請按一下 `Control + C`。

<a name="sharing-sites-on-your-local-network"></a>

### 在區域網路上共享網站

預設情況下，Valet 會將連入的流量限制在 `127.0.0.1` 網路介面上，已避免你的開發環境暴露在有安全風險的 ^[Internet](%E7%B6%B2%E9%9A%9B%E7%B6%B2%E8%B7%AF) 上。

若想讓區域網路上其他裝置能通過你的 IP 位址來存取你裝置上的 Valet 網站 (如：`192.168.1.10/application.test`)，你需要先手動編輯該網站相應的 Nginx 設定檔，並移除 `listen` 指示詞中的限制。請移除 `listen` 指示詞在 80 與 443 連接埠上的 `127.0.0.1:` 前置詞。

若沒有在該專案上執行 `valet secure`，你可以編輯 `/usr/local/etc/nginx/valet/valet.conf` 來允許在區域網路上存取所有非 HTTPS 的網站。不過，如果有通過 HTTPS 來執行該專案 (對該網站執行了 `valet secure`)，則請編輯 `~/.config/valet/Nginx/app-name.test` 檔案。

更新 Nginx 設定後，執行 `valet restart` 指令即可套用所修改的設定。

<a name="site-specific-environment-variables"></a>

## 個別網站的環境變數

有的專案使用了其他的框架，而這些框架可能會需要在 Server 上設定環境變數，但這些框架又未提供能在專案內修改環境變數的方法。在 Valet 中，我們可以在專案根目錄加上一個 `.valet-env.php` 檔案來針對個別網站設定環境變數。該檔案應回傳一組網站／環境變數配對的變數，該陣列會被所設定的內容會被加到其指定的各個網站上的全域 `$_SERVER` 陣列：

    <?php
    
    return [
        // Set $_SERVER['key'] to "value" for the laravel.test site...
        'laravel' => [
            'key' => 'value',
        ],
    
        // Set $_SERVER['key'] to "value" for all sites...
        '*' => [
            'key' => 'value',
        ],
    ];
<a name="proxying-services"></a>

## 為服務提供代理伺服器

有時候，我們可能會想將某個 Valet 網域代理到本機裝置上的另一個服務。舉例來說，我們有時候可能會需要在執行 Valet 的同時又在 Docker 上執行另一個網站。不過，我們無法同時將 Valet 與 Docker 繫結到 80 通訊埠上。

為了解決這個問題，我們可以使用 `proxy` 指令來產生代理伺服器。舉例來說，我們可以將所有 `http://elasticsearch.test` 的流量代理到 `http://127.0.0.1:9200` 上：

```bash
// Proxy over HTTP...
valet proxy elasticsearch http://127.0.0.1:9200

// Proxy over TLS + HTTP/2...
valet proxy elasticsearch http://127.0.0.1:9200 --secure
```
可以使用 `unproxy` 來移除代理：

    valet unproxy elasticsearch
可以使用 `proxies` 指令來列出所有被代理的網站設定：

    valet proxies
<a name="custom-valet-drivers"></a>

## 自定 Valet Driver

可以撰寫你自己的 Valet「^[Driver](%E9%A9%85%E5%8B%95%E7%A8%8B%E5%BC%8F)」來執行一些 Valet 未提供原生支援的框架或 CMS。在安裝 Valet 時，會建立一個 `~/.config/valet/Drivers` 目錄，其中包含了一個 `SampleValetDriver.php` 檔案。該檔案中包含了範例 Driver 實作，用來示範如何撰寫自定 Driver。要撰寫 Driver，你只需要實作三個方法即可：`serves`、`isStaticFile`、與 `frontControllerPath`。

這三個方法都會收到 `$sitePath`, `$siteName`, 與 `$uri` 值作為其引數。`$sitePath` 是正在你裝置上執行的網站完整路徑，如 `/Users/Lisa/Sites/my-project`。`$siteName` 為 Domain 中「^[Host](%E4%B8%BB%E6%A9%9F)」或「網站名稱」的部分 (`my-project`)。而 `$uri` 則是連入 Request 的 URI (`/foo/bar`)。

完成自定 Valet Driver 後，請使用 `FrameworkValetDriver.php` 這樣的慣例為其命名，並放入 `~/.config/valet/Drivers` 目錄。舉例來說，若要為 WordPress 撰寫自定 Valet Driver，則檔案名稱應為 `WordPressValetDriver.php`。

來看看一個簡單的實作，瞭解自定 Valet Driver 中各個方法應如何實作。

<a name="the-serves-method"></a>

#### `serves` 方法

如果要讓該 Driver 負責處理連入 Request 的話，`serves` 方法應回傳 `true`。否則應回傳 `false`。所以，在此方法中，應嘗試判斷給定的 `$sitePath` 是否包含要嘗試執行的專案類型。

舉例來說，假設我們正在撰寫 `WordPressValetDriver`，則 `serves` 方法可能會長這樣：

    /**
     * Determine if the driver serves the request.
     *
     * @param  string  $sitePath
     * @param  string  $siteName
     * @param  string  $uri
     * @return bool
     */
    public function serves($sitePath, $siteName, $uri)
    {
        return is_dir($sitePath.'/wp-admin');
    }
<a name="the-isstaticfile-method"></a>

#### `isStaticFile` 方法

`isStaticFile` 應判斷連入 Request 是否是在存取「靜態 (Static)」檔案，如圖片或 CSS。若為靜態檔案，則該方法應回傳這個靜態檔案在硬碟上的完整路徑。若連入 Request 不是靜態檔案，則該方法應回傳 `false`：

    /**
     * Determine if the incoming request is for a static file.
     *
     * @param  string  $sitePath
     * @param  string  $siteName
     * @param  string  $uri
     * @return string|false
     */
    public function isStaticFile($sitePath, $siteName, $uri)
    {
        if (file_exists($staticFilePath = $sitePath.'/public/'.$uri)) {
            return $staticFilePath;
        }
    
        return false;
    }
> [!NOTE]  
> 只有在 `serves` 方法回傳 `true` 且連入 Request 所要求的 URI 不是 `/` 時，`isStaticFile` 方法才會被呼叫。

<a name="the-frontcontrollerpath-method"></a>

#### `frontControllerPath` 方法

`frontControllerPath` 方法應回傳該專案「前端 Controller (Front Controller)」的完整路徑，一般就是「index.php」檔案或其他同等的檔案：

    /**
     * Get the fully resolved path to the application's front controller.
     *
     * @param  string  $sitePath
     * @param  string  $siteName
     * @param  string  $uri
     * @return string
     */
    public function frontControllerPath($sitePath, $siteName, $uri)
    {
        return $sitePath.'/public/index.php';
    }
<a name="local-drivers"></a>

### 區域 Driver

若想為單一專案定義自定 Valet Driver，請在該專案的根目錄下建立一個 `LocalValetDriver.php` 檔案。這個自定 Driver 可以繼承基礎的 `ValetDriver` 類別，或是繼承如 `LaravelValetDriver` 等其他現有的、個別專案的 Driver：

    class LocalValetDriver extends LaravelValetDriver
    {
        /**
         * Determine if the driver serves the request.
         *
         * @param  string  $sitePath
         * @param  string  $siteName
         * @param  string  $uri
         * @return bool
         */
        public function serves($sitePath, $siteName, $uri)
        {
            return true;
        }
    
        /**
         * Get the fully resolved path to the application's front controller.
         *
         * @param  string  $sitePath
         * @param  string  $siteName
         * @param  string  $uri
         * @return string
         */
        public function frontControllerPath($sitePath, $siteName, $uri)
        {
            return $sitePath.'/public_html/index.php';
        }
    }
<a name="other-valet-commands"></a>

## 其他 Valet 指令

| 指令 | 說明 |
| --- | --- |
| `valet forget` | 在已「Park」的目錄下執行此指令會將其從已 Park 的目錄清單中移除。 |
| `valet log` | 檢視由 Valet 服務所寫入的日誌列表。 |
| `valet paths` | 檢視所有已「Park」的路徑。 |
| `valet restart` | 重新啟動 Valet 常駐程式 (Daemon)。 |
| `valet start` | 啟動 Valet 常駐程式 (Daemon)。 |
| `valet stop` | 停止 Valet 常駐程式 (Daemon)。 |
| `valet trust` | 為 Brew 與 Valet 新增 sudoer 檔案，以讓 Valet 指令能在不要求輸入密碼的情況下執行。 |
| `valet uninstall` | 解除安裝 Valet：顯示手動解除安裝的說明。傳入 `--force` 選項以積極刪除所有 Valet 的資源。 |

<a name="valet-directories-and-files"></a>

## Valet 的目錄與檔案

在針對 Valet 環境進行故障排除時，下列路徑與檔案可能會是有用的資訊：

#### `~/.config/valet`

包含所有 Valet 的設定。可針對此目錄進行備份。

#### `~/.config/valet/dnsmasq.d/`

此目錄包含 DNSMasq 的設定。

#### `~/.config/valet/Drivers/`

此目錄包含 Valet 的 Driver。Driver 用來判斷要怎麼執行某個特定的框架或 CMS。

#### `~/.config/valet/Extensions/`

此目錄包含自定的 Valet 擴充程式與指令。

#### `~/.config/valet/Nginx/`

此目錄包含了所有 Valet 的 Nginx 網站設定。這些檔案在執行 `install`、`secure`、與 `tld` 指令時會被重新建立。

#### `~/.config/valet/Sites/`

此目錄包含了所有[已 Link 的專案](#the-link-command)的符號連結 (Symbolic Link)。

#### `~/.config/valet/config.json`

此檔案為 Valet 的主要設定檔。

#### `~/.config/valet/valet.sock`

此檔案為 Valet 的 Nginx 所使用的 PHP-FPM Socket。只有在 PHP 有正確執行時，才會有這個檔案。

#### `~/.config/valet/Log/fpm-php.www.log`

此檔案為 PHP 錯誤的使用者日誌檔。

#### `~/.config/valet/Log/nginx-error.log`

此檔案為 Nginx 錯誤的使用者日誌檔。

#### `/usr/local/var/log/php-fpm.log`

此檔案為 PHP-FPM 錯誤的系統日誌檔。

#### `/usr/local/var/log/nginx`

此目錄包含了 Nginx 的 ^[Access](%E5%AD%98%E5%8F%96) 與 ^[Error](%E9%8C%AF%E8%AA%A4) ^[Log](%E6%97%A5%E8%AA%8C)。

#### `/usr/local/etc/php/X.X/conf.d`

此目錄包含了用於各種 PHP 設定的 `*.ini` 檔。

#### `/usr/local/etc/php/X.X/php-fpm.d/valet-fpm.conf`

此檔案為 PHP-FPM 的 ^[Pool](%E9%9B%86%E5%8D%80) 設定檔。

#### `~/.composer/vendor/laravel/valet/cli/stubs/secure.valet.conf`

此檔案為預設的 Nginx 設定檔，用來為網站建立 SSL 憑證。
