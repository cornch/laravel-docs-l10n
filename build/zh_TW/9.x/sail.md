---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/143/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 50.9
---

# Laravel Sail

- [簡介](#introduction)
- [安裝與設定](#installation)
  - [將 Sail 安裝至現有專案](#installing-sail-into-existing-applications)
  - [設定 Shell Alias](#configuring-a-shell-alias)
  
- [啟動與停止 Sail](#starting-and-stopping-sail)
- [執行指令](#executing-sail-commands)
  - [執行 PHP 指令](#executing-php-commands)
  - [執行 Composer 指令](#executing-composer-commands)
  - [執行 Artisan 指令](#executing-artisan-commands)
  - [執行 Node 與 NPM 指令](#executing-node-npm-commands)
  
- [使用資料庫](#interacting-with-sail-databases)
  - [MySQL](#mysql)
  - [Redis](#redis)
  - [MeiliSearch](#meilisearch)
  
- [檔案儲存](#file-storage)
- [執行測試](#running-tests)
  - [Laravel Dusk](#laravel-dusk)
  
- [預覽 E-Mail](#previewing-emails)
- [Container CLI](#sail-container-cli)
- [PHP 版本](#sail-php-versions)
- [Node 版本](#sail-node-versions)
- [共享網站](#sharing-your-site)
- [使用 Xdebug 來進行除錯](#debugging-with-xdebug)
  - [使用 Xdebug CLI](#xdebug-cli-usage)
  - [使用 Xdebug Browser](#xdebug-browser-usage)
  
- [自定](#sail-customization)

<a name="introduction"></a>

## 簡介

[Laravel Sail](https://github.com/laravel/sail) 是一個輕量的命令列介面，可用來操作 Laravel 預設的 Docker 開發環境。對於使用 PHP、MySQL 與 Redis 來建立 Laravel 專案，Sail 是一個不錯的入門選項，且不需預先具備有關 Docker 的知識。

Sail 的核心是保存在專案根目錄的 `docker-compose.yml` 檔案與 `sail` Script 檔。`sail` Script 檔提供了一個有許多方便方法的 CLI 介面，能操作由 `docker-compose.yml` 檔案所定義的 Docker Container。

Laravel Sail 支援 macOS、Linux、與 Windows (通過 [WSL2](https://docs.microsoft.com/zh-tw/windows/wsl/about))。

<a name="installation"></a>

## 安裝與設定

Laravel Sail 已自動安裝到新的 Laravel 專案中，因此你可以馬上開始使用 Sail。若要瞭解如何建立新的 Laravel 專案，請參考 Laravel 的[安裝說明文件](/docs/{{version}}/installation)中對應你的作業系統的部分。在安裝時，Sail 會詢問你的專案要用到哪些 Sail 支援的服務。

<a name="installing-sail-into-existing-applications"></a>

### 將 Sail 安裝到現有的專案

若想在現有的 Laravel 專案中安裝 Sail，只需要使用 Composer 套件管理員安裝 Sail 即可。當然，這個步驟假設你已經有假設好本機開發環境，才能安裝 Composer 套件：

```shell
composer require laravel/sail --dev
```
安裝好 Sail 後，可以執行 `sail:install` Artisan 指令。這個指令會將 Sail 的 `docker-compose.yml` 檔案安裝到專案根目錄：

```shell
php artisan sail:install
```
最後，可啟動 Sail。若要繼續瞭解有關如何使用 Sail 的資訊，請繼續閱讀本說明文件中剩下的部分：

```shell
./vendor/bin/sail up
```
<a name="adding-additional-services"></a>

#### 新增額外服務

若想在現有的 Sail 專案中加上更多服務，可以執行 `sail:add` Artisan 指令：

```shell
php artisan sail:add
```
<a name="using-devcontainers"></a>

#### 使用 Devcontainer

若要使用 [Devcontainer](https://code.visualstudio.com/docs/remote/containers) 來開發，可在執行 `sail:install` 指令時提供 `--devcontainer` 選項。`--devcontainer` 選項會讓 `sail:install` 指令將一個預設的 `.devcontainer/devcontainer.json` 檔案安裝到專案根目錄下：

```shell
php artisan sail:install --devcontainer
```
<a name="configuring-a-shell-alias"></a>

### 設定 Shell Alias

預設情況下，Sail 指令是使用 `vendor/bin/sail` Script 檔來呼叫的。該 Script 檔包含在所有新安裝的 Laravel 專案內：

```shell
./vendor/bin/sail up
```
不過，我們可以設定 Shell Alias 以更輕鬆地執行 Sail 指令，而不需要一直重複鍵入 `vendor/bin/sail`：

```shell
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
```
為了確保此 Alias 設定總是有效，可以將此設定加到你的家目錄中的 Shell 設定檔內。如 `~/.zshrc` 或 `~/.bashrc`，接著重新啟動 Shell。

設定好 Shell Alias 後，只要鍵入 `sail` 就可執行 Sail 指令。此說明文件剩下的部分都假設你已設定好此 Alias：

```shell
sail up
```
<a name="starting-and-stopping-sail"></a>

## 啟動與停止 Sail

Laravel Sail 的 `docker-compose.yml` 中定義了數個 Docker Container，這些 Container 會互相配合來協助你製作 Laravel 專案。在 `docker-compose.yml` 檔案中，`services` 設定內的每一格項目都是一個 Container。`laravel.test` Container 是專案的主要 Container，用來執行你的專案：

啟動 Sail 後，請確定你的本機電腦上沒有執行其他 ^[Web Server](%E7%B6%B2%E9%A0%81%E4%BC%BA%E6%9C%8D%E5%99%A8)或資料庫。若要啟動所有 `docker-compose.yml` 檔案中的 Docker Container，請執行 `up` 指令：

```shell
sail up
```
若要在背景啟動所有 Docker Container，可使用「分離模式 (Detached Mode)」啟動 Sail：

```shell
sail up -d
```
Once the application's containers have been started, you may access the project in your web browser at: [http://localhost](http://localhost).

若要停止所有 Container，只需要按 Ctrl + C 來停止執行 Container 即可。如果 Container 是在背景執行，可使用 `stop` 指令：

```shell
sail stop
```
<a name="executing-sail-commands"></a>

## 執行指令

在使用 Laravel Sail 時，你的專案會被放在 Docker Container 內執行，並與你的本機電腦隔離。不過，Sail 提供了一個方便的方法，可讓你針對你的專案執行各種指令，如：執行任意 PHP 指令、Artisan 指令、Node 與 NPM 指令等。

**在閱讀 Laravel 的說明文件時，有時候會看到一些沒有提到 Sail 的 Composer、Artisan、Node 或 NPM 指令**。這些範例假設這些工具是安裝在你的本機電腦上。使用 Laravel Sail 作為本機開發環境時，應使用 Sail 來執行這些指令：

```shell
# Running Artisan commands locally...
php artisan queue:work

# Running Artisan commands within Laravel Sail...
sail artisan queue:work
```
<a name="executing-php-commands"></a>

### 執行 PHP 指令

可以使用 `php` 指令來執行 PHP 指令。當然，這些指令會使用你的專案所設定的 PHP 版本來執行。有關 Laravel Sail 中可用的 PHP 版本，請參考 [PHP 版本的說明文件](#sail-php-versions)：

```shell
sail php --version

sail php script.php
```
<a name="executing-composer-commands"></a>

### 執行 Composer 指令

Composer 指令可使用 `composer` 指令執行。Laravel Sail 的應用程式 Container 中包含了 Composer 2.x：

```nothing
sail composer require laravel/sanctum
```
<a name="installing-composer-dependencies-for-existing-projects"></a>

#### 為現有專案安裝 Composer 相依性套件

若與團隊一起開發專案，則讀者可能不是最初新建 Laravel 專案的人。因此，當你將專案的存放庫 Clone 到本機電腦上時，專案中包含 Sail 在內的所有 Composer 相依性套件都還未安裝。

打開專案目錄並執行下列指令即可安裝專案的相依性套件。這個指令會使用一個包含 PHP 與 Composer 的小型 Docker Container 來安裝專案的相依性套件：

```shell
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```
使用 `laravelsail/phpXX-composer` ^[Image](%E6%98%A0%E5%83%8F) 時，請使用與你的專案相同的 PHP 版本 (`74`, `80`, `81`, `82`)。

<a name="executing-artisan-commands"></a>

### 執行 Artisan 指令

可使用 `artisan 指令來執行 Laravel Artisan 指令：

```shell
sail artisan queue:work
```
<a name="executing-node-npm-commands"></a>

### 執行 Node 與 NPM 指令

可使用 `node` 指令來執行 Node 指令，而 `npm` 指令可用來執行 NPM 指令：

```shell
sail node --version

sail npm run dev
```
若有需要，除了 NPM 外也可使用 Yarn：

```shell
sail yarn
```
<a name="interacting-with-sail-databases"></a>

## 使用資料庫

<a name="mysql"></a>

### MySQL

讀者可能已經注意到，專案的 `docker-compose.yml` 中有包含 MySQL Container 的設定。這個 Container 使用 [Docker Volume](https://docs.docker.com/storage/volumes/)，這樣一來就算停止或重新啟動 Container，保存在資料庫中的資料也不會不見。

此外，第一次啟動 MySQL Container 時，該 Container 會幫你建立兩個資料庫。第一個資料庫會使用 `DB_DATABASE` 環境變數所設定的名稱，作為本機開發之用。第二個資料庫是專門用來測試的，名稱為 `test`，用來確保測試時不會影響到開發資料。

啟動 Container 後，可以在專案的 `.env` 檔案中將 `DB_HOST` 環境變數設為 `mysql` 來讓網站連線到 MySQL 實體。

若要從本機上連線到專案的 MySQL 資料庫，可使用圖形化的資料庫管理工具，如 [TablePlus](https://tableplus.com)。預設情況下，可以在 `localhost` 的 3306 連接埠上存取 MySQL 資料庫，而帳號密碼則對應到 `DB_USERNAME` 與 `DB_PASSWORD` 環境變數。或者，也可以使用 `root` 使用者來連線，其密碼一樣是 `DB_PASSWORD` 環境變數值。

<a name="redis"></a>

### Redis

專案的 `docker-compose.yml` 檔案中也包含了 [Redis](https://redis.io) Container 的設定。這個 Container 使用了 [Docker volume](https://docs.docker.com/storage/volumes/)，這樣一來即使停止或重新啟動 Container，保存在 Redis 裡的資料也不會不見。啟動 Container 後，只要將 `.env` 檔中的 `REDIS_HOST` 環境變數設為 `redis`，就可讓網站連線到 Redis 實體。

若要從本機電腦連線到專案的 Redis 資料庫，可以使用如 [TablePlus](https://tableplus.com) 等的圖形化資料庫管理程式。預設情況下，可以使用 `localhost` 的 6379 連接埠來存取 Redis 資料庫。

<a name="meilisearch"></a>

### MeiliSearch

若在安裝 Sail 時有選擇安裝 [MeiliSearch](https://www.meilisearch.com)，則專案的 `docker-compose.yml` 檔中也會包含 MeiliSearch 的設定。MeiliSearch 是一個強大的搜尋引擎，與 [Laravel Scout](/docs/{{version}}/scout) [相容](https://github.com/meilisearch/meilisearch-laravel-scout)。啟動 Container 後，只要將 `MEILISEARCH_HOST` 環境變數設為 `http://meilisearch:7700` 即可讓網站連線到 MeiliSearch 實體。

在本機上，只要在瀏覽器上打開 `http://localhost:7700`，就可存取 MeiliSearch 的網頁管理面板。

<a name="file-storage"></a>

## 檔案儲存

若打算在正式環境使用 Amazon S3 來儲存檔案，則建議在安裝 Sail 時安裝 [MinIO](https://min.io) 服務。MinIO 提供了與 S3 相容的 API，讓你可以在本機開發時不用在正式的 S3 環境上建立測試用的 Bucket，就能使用 Laravel 的 `s3` 檔案儲存 Driver。若在安裝 Sail 時有選擇安裝 MinIO，則 `docker-compose.yml` 檔案中就會有 MinIO 相關的設定。

預設情況下，專案中的 `filesystems` 設定檔內已經有包含 `s3` Disk 的設定了。除了通過此 Disk 來使用 Amazon S3 外，只要修改該設定相關的環境變數，就可以通過這個 Disk 來使用任何如 MinIO 等 S3 相容的檔案儲存服務。舉例來說，使用 MinIO 時，應像這樣定義 ^[Filesystem](%E6%AA%94%E6%A1%88%E7%B3%BB%E7%B5%B1) 環境變數：

```ini
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=sail
AWS_SECRET_ACCESS_KEY=password
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=local
AWS_ENDPOINT=http://minio:9000
AWS_USE_PATH_STYLE_ENDPOINT=true
```
為了讓 Laravel 的 Flysystem 整合在使用 MinIO 時整合正確的 URL，請定義 `AWS_URL` 環境變數，並設定適用於專案本機 URL 的值，且該值應在 URL 路徑內包含 Bucket 名稱：

```ini
AWS_URL=http://localhost:9000/local
```
可以使用 MinIO Console 來建立 Bucket。MinIO Console 可從 `http://localhost:8900` 開啟。MinIO Console 預設的使用者名稱是 `sail`，預設密碼為 `password`。

> [!WARNING]  
> 使用 MinIO 時，不支援通過 `temporaryUrl` 方法來產生臨時儲存空間 URL。

<a name="running-tests"></a>

## 執行測試

Laravel 內建了許多測試輔助功能。可以使用 Sail 的 `test` 指令來執行專案的 [Feature Test 與 Unit Test](/docs/{{version}}/testing)。可以傳入任何 PHPUnit 支援的 CLI 選項給 `test` 指令：

```shell
sail test

sail test --group orders
```
Sail 的 `test` 指令與執行 `test` Artisan 指令相同：

```shell
sail artisan test
```
預設情況下，Sail 會建立一個專門的 `testing` 資料庫，以避免測試時影響到目前資料庫的狀態。在預設的 Laravel 專案中，Sail 也會調整 `phpunit.xml` 檔的設定，以在執行測試時使用這個資料庫：

```xml
<env name="DB_DATABASE" value="testing"/>
```
<a name="laravel-dusk"></a>

### Laravel Dusk

[Laravel Dusk](/docs/{{version}}/dusk) 提供了豐富且簡單易用的瀏覽器自動化與測試 API。多虧有 Sail，我們不需要在本機電腦上安裝 Selenium 或其他工具，就能執行這些 Dusk 測試。若要開始使用 Dusk，請先在專案的 `docker-compose.yml` 檔案中將 Slenium 服務取消註解：

```yaml
selenium:
    image: 'selenium/standalone-chrome'
    volumes:
        - '/dev/shm:/dev/shm'
    networks:
        - sail
```
接著，請確保專案的 `docker-compose.yml` 檔案中，`laravel.test` 服務的 `depends_on` 欄位中有 `selenium`：

```yaml
depends_on:
    - mysql
    - redis
    - selenium
```
最後，只要啟動 Sail 並執行 `dusk` 指令，就能執行 Dusk 的測試套件：

```shell
sail dusk
```
<a name="selenium-on-apple-silicon"></a>

#### 在 Apple Silicon 上的 Selenium

如果你的本機設備為 Apple Silicon 晶片，則 `selenium` 服務必須使用 `seleniarm/standalone-chromium` Image：

```yaml
selenium:
    image: 'seleniarm/standalone-chromium'
    volumes:
        - '/dev/shm:/dev/shm'
    networks:
        - sail
```
<a name="previewing-emails"></a>

## 預覽 E-Mail

Laravel Sail 的預設 `docker-compose.yml` 檔案中包含了 [Mailpit](https://github.com/axllent/mailpit) 服務。Mailpit 會在本機開發過程中攔截你的專案所寄出的所有 E-Mail，並提供一個方便的 Web 界面可供你在瀏覽器中預覽這些 E-Mail 訊息。使用 Sail 時，Mailpit 的預設主機名稱為 `mailpit`，且可在連接埠 1025 上使用：

```ini
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_ENCRYPTION=null
```
When Sail is running, you may access the Mailpit web interface at: [http://localhost:8025](http://localhost:8025)

<a name="sail-container-cli"></a>

## Container CLI

有時候，我們可能會需要在專案的 Container 內開啟 Bash 工作階段。可以使用 `shell` 指令來連線到專案的 Container 中，讓我們能檢視其中的檔案與所安裝的服務，並可在 Container 中執行任意 Shell 指令：

```shell
sail shell

sail root-shell
```
若要啟動新的 [Laravel Tinker](https://github.com/laravel/tinker) 工作階段，可執行 `tinker` 指令：

```shell
sail tinker
```
<a name="sail-php-versions"></a>

## PHP 版本

Sail 目前支援使用 PHP 8.2、8.1、PHP 8.0、或 PHP 7.4 來執行你的專案。目前 Sail 所使用的預設 PHP 版本為 PHP 8.2。若要修改專案使用的 PHP 版本，請更新 `docker-compose.yml` 檔案中 `laravel.test` Container 的 `build` 定義：

```yaml
# PHP 8.2
context: ./vendor/laravel/sail/runtimes/8.2

# PHP 8.1
context: ./vendor/laravel/sail/runtimes/8.1

# PHP 8.0
context: ./vendor/laravel/sail/runtimes/8.0

# PHP 7.4
context: ./vendor/laravel/sail/runtimes/7.4
```
此外，也可更新 `image` 的名稱，以反應專案所使用的 PHP 版本。`image` 名稱的設定在專案的 `docker-compose.yml` 檔內：

```yaml
image: sail-8.1/app
```
更新好專案的 `docker-compose.yml` 後，請重新建置 Container Image：

```shell
sail build --no-cache

sail up
```
<a name="sail-node-versions"></a>

## Node 版本

預設情況下 Sail 會安裝 Node 18。若要更改建置 Image 時使用的 Node 版本，請更新專案中 `docker-compose.yml` 檔案內 `laravel.test` 服務的 `build.args` 定義：

```yaml
build:
    args:
        WWWGROUP: '${WWWGROUP}'
        NODE_VERSION: '14'
```
更新好專案的 `docker-compose.yml` 後，請重新建置 Container Image：

```shell
sail build --no-cache

sail up
```
<a name="sharing-your-site"></a>

## 共享網站

有時候，我們需要公開地共享我們的網站，好讓同事能預覽網站，或是能測試專案中的 Webhook 整合。若要共享網站，可以使用 `share` 指令。執行該指令後，會分配一個隨機的 `laravel-sail.site` 網址能讓你存取你的網站：

```shell
sail share
```
在使用 `share` 指令共享網站時，應設定在 `TrustProxies` Middleware 中設定專案的 Trusted Proxies。否則，如 `url` 或 `route` 等產生 URL 用的輔助函式在產生 URL 時將無法判正確的 HTTP 主機名稱：

    /**
     * The trusted proxies for this application.
     *
     * @var array|string|null
     */
    protected $proxies = '*';
若要選擇共享網站時使用的子網域，可在執行 `share` 指令時提供 `subdomain` 選項：

```shell
sail share --subdomain=my-sail-site
```
> [!NOTE]  
> `share` 指令由 [Expose](https://github.com/beyondcode/expose) 驅動。Expose 是由 [BeyondCode](https://beyondco.de) 提供的，開放原始碼的通道 (Tunneling) 服務。

<a name="debugging-with-xdebug"></a>

## 使用 Xdebug 進行除錯

Laravel Sail 的 Docker 設定中也包含了對 [Xdebug](https://xdebug.org/) 的支援。Xdebug 是一個常用且強大的 PHP 除錯工具。若要啟用 Xdebug，需要先在專案的 `.env` 檔中加上一些變數來[設定 Xdebug](https://xdebug.org/docs/step_debug#mode)。若要啟用 Xdebug，必須在啟動 Sail 前先設定適當的模式 (Mode)：

```ini
SAIL_XDEBUG_MODE=develop,debug,coverage
```
#### Linux 主機的 IP 設定

在 Laravel Sail 中，`XDEBUG_CONFIG` 環境變數被設定為 `client_host=host.docker.internal`，好讓 Xdebug 能在 Mac 與 Windows (WSL2) 下被正確設定。如果你的本機裝置使用 Linux，請確保使用 17.06.0 版或更新的 Docker Engine 以及 1.16.0 版或更新的 Composer。否則，就需要像下面這樣手動定義環境變數：

首先，需要先執行下列指令來判斷要加到環境變數中的正確主機 IP 位址。一般來說，`<container-name>` 應為你的專案使用的 Container 名稱，通常以 `_laravel.test_1` 結尾：

```shell
docker inspect -f {{range.NetworkSettings.Networks}}{{.Gateway}}{{end}} <container-name>
```
取得正確的主機 IP 後，請在專案的 `.env` 檔中定義 `SAIL_XDEBUG_CONFIG` 變數：

```ini
SAIL_XDEBUG_CONFIG="client_host=<host-ip-address>"
```
<a name="xdebug-cli-usage"></a>

### 使用 Xdebug CLI

`sail debug` 指令可用來在執行 Artisan 指令時啟動除錯工作階段：

```shell
# Run an Artisan command without Xdebug...
sail artisan migrate

# Run an Artisan command with Xdebug...
sail debug migrate
```
<a name="xdebug-browser-usage"></a>

### 使用 Xdebug Browser

若要在通過 Web 瀏覽器瀏覽網站時對網站進行除錯，請依照[Xdebug 所提供的說明](https://xdebug.org/docs/step_debug#web-application)來在 Web 瀏覽器中啟動 Xdebug 工作階段。

若使用 PhpStorm，請參考 JetBrains 的[零設定除錯](https://www.jetbrains.com/help/phpstorm/zero-configuration-debugging.html)說明文件。

> [!WARNING]  
> Laravel Sail 仰賴 `artisan serve` 來執行網站。只有在 8.53.0 版之後的 Laravel 中，`artisan serve` 指令才會接受 `XDEBUG_CONFIG` 與 `XDEBUG_MODE` 變數。舊版的 Laravel (8.52.0 版以前) 不接受這些變數，且不會接受除錯連線。

<a name="sail-customization"></a>

## 自定

由於 Sail 就是 Docker，因此你幾乎可以對 Sail 中的任何部分進行自定。若要安裝 Sail 的 Dockerfile，可執行 `sail:publish` 指令：

```shell
sail artisan sail:publish
```
執行該指令後，Laravel Sail 所使用的 Dockerfile 與其他設定檔會被放到專案根目錄中的 `docker` 目錄下。調整了 Sail 設定後，你可能會想在 `docker-compose.yml` 中更改專案 Container 所使用的 Image 名稱。之後，請使用 `build` 指令來重新建置專案的 Image。如果你在同一台裝置上開發多個 Laravel 專案，那麼請務必為 Image 設定不重複的名稱：

```shell
sail build --no-cache
```