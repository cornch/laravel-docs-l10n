# 安裝

- [認識 Laravel](#meet-laravel)

   - [為什麼要選擇 Laravel？](#why-laravel)

- [第一個 Laravel 專案](#your-first-laravel-project)

- [Laravel 與 Docker](#laravel-and-docker)

   - [使用 macOS 入門](#getting-started-on-macos)

   - [使用 Windows 入門](#getting-started-on-windows)

   - [使用 Linux 入門](#getting-started-on-linux)

   - [選擇 Sail 服務](#choosing-your-sail-services)

- [初始設定](#initial-configuration)

   - [基於環境的組態設定](#environment-based-configuration)

   - [目錄的組態設定](#directory-configuration)

   - [資料庫與 Migration](#databases-and-migrations)

- [下一步](#next-steps)

   - [Laravel - 全端框架](#laravel-the-fullstack-framework)

   - [Laravel - API 後端](#laravel-the-api-backend)

<a name="meet-laravel"></a>

## 認識 Laravel

Laravel 是一個擁有豐富優雅語法的 Web App 框架。網頁框架提供了製作網站的起始架構，讓你專心製作讓人驚艷的作品，而我們則幫你處理掉麻煩的小地方。

Laravel 致力於提供優質的開發體驗，並提供多種強大的功能，包含相依性插入 (Dependency Injection)、描述性的資料庫抽象層、佇列與排程任務、單元測試 (Unit Testing) 與整合測試 (Integration Testing)⋯⋯等功能。

不管你是 PHP 新手還是網頁框架新手、或者你已經有多年的經驗，Laravel 都是可陪伴你進步的框架。我們可以協助你作為網頁開發人員跨出第一步，或是助你一臂之力，讓你的技術更勝一層樓。我們迫不及待想看看你的成果！

<a name="why-laravel"></a>

### 為什麼選擇 Laravel？

市面上有很多種用來製作 Web App 的網頁框架了。不過，我們相信 Laravel 是製作現代、全端 Web App 的最佳選擇。

#### 進步的框架

我們喜歡把 Laravel 稱為一個「進步的 (Prograssive)」框架。我們這麼說是因為 Laravel 可以跟你一起進步。若你是初次踏入網頁開發，Laravel 有許多的說明文件、教學、以及[影片教學](https://laracasts.com)可以讓你無痛學習 Laravel。

若你是資深開發人員，Laravel 提供了強健的[相依性插入](/docs/{{version}}/container)、[單元測試](/docs/{{version}}/testing)、[佇列](/docs/{{version}}/queues)、[即時事件](/docs/{{version}}/broadcasting)⋯⋯等功能。Laravel 為打造專業的 Web App 做了許多微調，並可處理企業級的任務。

#### 可彈性調整規模的框架

Laravel 對於規模調整非常地有彈性。多虧於 PHP 本身可彈性調整規模的特性、以及 Laravel 內建了對於如 Redis 等快速、分散式快去系統的支援，在 Laravel 中要水平調整規模非常簡單。其實，使用 Laravel 的專案可以輕鬆地調整為能處理每月數百萬筆請求的等級。

需要極限的可調整性？[Laravel Vapor](https://vapor.laravel.com) 等平台可讓你在 AWS 上最新的 Serverless 技術中以幾乎無限可調整性的方式來執行 Laravel 專案。

#### 社群的框架

Laravel 結合了 PHP 生態系統中多個最好的套件來提供強健且對開發人員友善的框架。此外，來自世界各地數千位優秀的開發人員也[參與貢獻了 Laravel 框架](https://github.com/laravel/framework)。或許，你也有機會成為 Laravel 的貢獻者。

<a name="your-first-laravel-project"></a>

## 你的第一個 Laravel 專案

在開始建立第一個 Laravel 專案前，請先確定本機電腦上有安裝 PHP 與 [Composer](https://getcomposer.org)。若使用 macOS 開發，可通過 [Homebrew](https://brew.sh/) 來安裝 PHP 與 Composer。此外，我們也建議[安裝 Node 與 NPM](https://nodejs.org)。

安裝好 PHP 與 Composer 後，可使用 Composer 的 `create-project` 指令來建立新的 Laravel 專案：

```nothing
composer create-project laravel/laravel example-app
```

建立好專案後，可使用 Laravel 的 Artisan CLI `serve` 指令來開始 Laravel 的本機開發伺服器：

```nothing
cd example-app

php artisan serve
```

開啟 Artisan 開發伺服器後，就可以在瀏覽器中開啟 `http://localhost:8000` 來存取專案。接著，我們就可以[開始進入 Laravel 生態系統的下一步](#next-steps)。當然，也可以先來[設定一下資料庫](#databases-and-migrations)。

> **Note** 在開發 Laravel 專案時，若想先快速地起個頭，可以考慮使用 Laravel 的[入門套件](/docs/{{version}}/starter-kits)。Laravel 的入門套件可以為新專案提供後端與前端的登入 Scaffolding。


<a name="laravel-and-docker"></a>

## Laravel 與 Docker

我們希望不論使用什麼作業系統，開始入門 Laravel 都可以盡可能地簡單。因此，有幾個選項可讓你在本機上開發並執行 Laravel 專案。你可以稍後再來進一步瞭解這些選項。Laravel 提供了 [Sail](/docs/{{version}}/sail)，Sail 是一個可以使用 [Docker](https://www.docker.com) 來執行 Laravel 專案的內建方法。

Docker 是一個可以以小型、輕量的「Container (容器)」執行網站與服務的工具。使用 Container 就不會影響到本機上所安裝的軟體或設定。這表示，我們就不需要擔心如何在自己的電腦上設定複雜的開發工具，如網頁伺服器或資料庫。要開始使用 Sail，只需要先安裝 [Docker Desktop](https://www.docker.com/products/docker-desktop)。

Laravel Sail 是一個輕量的命令列介面，可用來操作 Laravel 預設的 Docker 設定。對於使用 PHP、MySQL 與 Redis 來建立 Laravel 專案，Sail 是一個不錯的入門選項，且不需預先具備有關 Docker 的知識。

> **Note** 已經是 Docker 大師了嗎？別擔心！有關 Sail 的所有東西都可以通過 Laravel 內的 `docker-compose.yml` 進行客製化。


<a name="getting-started-on-macos"></a>

### 使用 macOS 入門

若要 Mac 上進行開發，且已安裝了 [Docker Desktop](https://www.docker.com/products/docker-desktop)，則可以使用一個簡單的終端機指令來建立新的 Laravel 專案。舉例來說，要在一個名為「example-app」的檔案夾內建立新的 Laravel 專案，可以在終端機內執行下列指令：

```shell
curl -s "https://laravel.build/example-app" | bash
```

當然，我們任意修改該網址的「example-app」為任意值 —— 只要注意這個值只能包含字母數字、減號 (`-`)、底線 (`_`) 即可。Laravel 專案目錄會被建立在執行該指令的目錄下。

建立好專案後，就可以打開該檔案夾並開始 Laravel Sail。Laravel Sail 提供了一個可與 Laravel 預設的 Docker 設定互動的簡單的指令列介面：

```shell
cd example-app

./vendor/bin/sail up
```

首次執行 Sail 的 `up` 指令時，會在本機上建置 Sail 的專案 Container。這個過程可能需要花費數分鐘的時間。**不過別擔心，之後要開始 Sail 會變得很快。**

開啟專案的 Docker Container 後，就可以在瀏覽器中開啟 http://localhost 來存取專案網站。

> **Note** 若要繼續瞭解更多有關 Laravel Sail 的資訊，請參考 Laravel Sail 的[完整說明文件](/docs/{{version}}/sail)。


<a name="getting-started-on-windows"></a>

### 使用 Windows 入門

在 Windows 裝置上建立新的 Laravel 專案前，請先確認一下是否有安裝 [Docker Desktop](https://www.docker.com/products/docker-desktop)。接著，請確保有安裝並啟用適用於 Linux 的 Windows 子系統 2 (WSL2)。使用 WSL 就可以在 Windows 10 上原生地執行 Linux 二進位可執行檔。有關如何安裝並啟用 WSL2 的資訊可以在 Microsoft 的《[開發人員環境說明文件](https://docs.microsoft.com/zh-tw/windows/wsl/install-win10)》。

> **Note** 安裝並啟用 WSL2 後，請確保將 Docker Desktop [設定為使用 WSL2 後端](https://docs.docker.com/docker-for-windows/wsl/)。


接著，我們就可以來建立你的第一個 Laravel 專案。請先開啟 [Windows Terminal](https://www.microsoft.com/zh-tw/p/windows-terminal/9n0dx20hk701?rtc=1&activetab=pivot:overviewtab)，然後為 WSL2 Linux 作業系統開啟一個新的終端機工作階段。接著，可以使用一個簡單的終端機命令來建立新的 Laravel 專案。舉例來說，若要在名為「example-app」的資料夾內建立一個新的 Laravel 專案，請在終端機內執行下列指令：

```shell
curl -s https://laravel.build/example-app | bash
```

當然，我們任意修改該網址的「example-app」為任意值 —— 只要注意這個值只能包含字母數字、減號 (`-`)、底線 (`_`) 即可。Laravel 專案目錄會被建立在執行該指令的目錄下。

建立好專案後，就可以打開該檔案夾並開始 Laravel Sail。Laravel Sail 提供了一個可與 Laravel 預設的 Docker 設定互動的簡單的指令列介面：

```shell
cd example-app

./vendor/bin/sail up
```

首次執行 Sail 的 `up` 指令時，會在本機上建置 Sail 的專案 Container。這個過程可能需要花費數分鐘的時間。**不過別擔心，之後要開始 Sail 會變得很快。**

開啟專案的 Docker Container 後，就可以在瀏覽器中開啟 http://localhost 來存取專案網站。

> **Note** 若要繼續瞭解更多有關 Laravel Sail 的資訊，請參考 Laravel Sail 的[完整說明文件](/docs/{{version}}/sail)。


#### 在 WSL2 中進行開發

當然，我們還需要能修改建立在 WSL2 安裝內的 Laravel 專案檔案。為此，我們建議使用 Microsoft 的 [Visual Studio Code](https://code.visualstudio.com) 編輯器，並使用用於[遠端開發](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.vscode-remote-extensionpack)的第一方擴充功能。

安裝好這些工具後，就可以用 Windows Terminal 在專案根目錄下執行 `code` 命令來開啟 Laravel 專案。

<a name="getting-started-on-linux"></a>

### 使用 Linux 入門

若要 Linux 上進行開發，且已安裝了[Docker Compose](https://docs.docker.com/compose/install)，則可以使用一個簡單的終端機指令來建立新的 Laravel 專案。舉例來說，要在一個名為「example-app」的目錄內建立新的 Laravel 專案，可以在終端機內執行下列指令：

```shell
curl -s https://laravel.build/example-app | bash
```

當然，我們任意修改該網址的「example-app」為任意值 —— 只要注意這個值只能包含字母數字、減號 (`-`)、底線 (`_`) 即可。Laravel 專案目錄會被建立在執行該指令的目錄下。

建立好專案後，就可以打開該檔案夾並開始 Laravel Sail。Laravel Sail 提供了一個可與 Laravel 預設的 Docker 設定互動的簡單的指令列介面：

```shell
cd example-app

./vendor/bin/sail up
```

首次執行 Sail 的 `up` 指令時，會在本機上建置 Sail 的專案 Container。這個過程可能需要花費數分鐘的時間。**不過別擔心，之後要開始 Sail 會變得很快。**

開啟專案的 Docker Container 後，就可以在瀏覽器中開啟 http://localhost 來存取專案網站。

> **Note** 若要繼續瞭解更多有關 Laravel Sail 的資訊，請參考 Laravel Sail 的[完整說明文件](/docs/{{version}}/sail)。


<a name="choosing-your-sail-services"></a>

### 選擇 Sail 服務

通過 Sail 建立新的 Laravel 專案時，可以使用 `with` 查詢字串變數來選擇新專案的 `docker-compose.yml` 檔案內要設定哪些服務。可用的服務包含 `mysql`, `pgsql`, `mariadb`, `redis`, `memcached`, `meilisearch`, `minio`, `selenium`, 與 `mailhog`：

```shell
curl -s "https://laravel.build/example-app?with=mysql,redis" | bash
```

若未指定要設定哪些服務，則預設將設定 `mysql`, `redis`, `meilisearch`, `mailhog`, 與 `selenium`。

只要在網址後加上 `devcontainer` 參數，就可以讓 Sail 安裝一個預設的 [Devcontainer](/docs/{{version}}/sail#using-devcontainers)：

```shell
curl -s "https://laravel.build/example-app?with=mysql,redis&devcontainer" | bash
```

<a name="initial-configuration"></a>

## 初始化設定

Laravel 框架的所有組態設定檔都儲存在 `config` 目錄內。各個選項都有說明文件，歡迎閱讀這些檔案並熟悉可用的選項。

Laravel 預設幾乎不需要進行額外設定。你現在已經可以開始開發了！不過，可以先看看 `config/app.php` 檔案以及其中的說明。該檔案中包含了一些我們可能需要依據不同專案進行修改的設定選項，如： `timezone` (時區) 以及 `locale` (語系) 等。

<a name="environment-based-configuration"></a>

### 基於環境的組態設定

由於許多的 Laravel 設定選項值都會因為專案執行的環境是本機或正式環境而有所不同，因此許多重要的組態設定值都使用 `.env` 檔案來定義，該檔案位在專案根目錄。

`.env` 檔不應被簽入應用程式的版本控制中，因為每個使用應用程式的開發人員／伺服器都可能需要不同的環境組態。此外，若將 `.env` 檔簽入版本控制的話，當有入侵者取得了版本控制儲存庫的存取權限，就可能會造成安全性風險，因為其中的機敏認證資料都會被暴露。

> **Note** 更多有關 `.env` 檔案以及基於環境的組態設定資訊，請參考完整的[組態設定說明文件](/docs/{{version}}/configuration#environment-configuration)。


<a name="directory-configuration"></a>

### 目錄組態設定

Laravel 只能架設在「網頁目錄」的根目錄下。請不要嘗試將 Laravel 專案架設在「網頁目錄」的子目錄下。若嘗試這麼做可能會將專案的機敏檔案暴露在外。

<a name="databases-and-migrations"></a>

### 資料庫與 Migration

現在，我們已經建立好 Laravel 專案了，我們可能會想接著在資料庫中儲存一些資料。預設情況下，專案的 `.env` 檔中已經指定要讓 Laravel 連線到 `127.0.0.1` 上的 MySQL。若在 macOS 上開發，且需要在本機上使用 MySQL、Postgres、Redis 的話，或許可以考慮看看使用方便的 [DBngin](https://dbngin.com/)。

若不想在本機上安裝 MySQL 或 Postgres，那麼使用 [SQLite](https://www.sqlite.org/index.html) 資料庫也沒問題。SQLite 是一個小型、快速、單一檔案的資料庫引擎。若要開始使用 SQLite，請先建立一個空的 SQLite 檔案。一般來說，這個檔案會放在 Laravel 專案中的 `database` 目錄：

```shell
touch database/database.sqlite
```

接著，更新 `.env` 設定檔來使用 Laravel 的 `sqlite` 資料庫 Driver。我們可以移除其他的資料庫設定選項：

```ini
DB_CONNECTION=sqlite # [tl! add]
DB_CONNECTION=mysql # [tl! remove]
DB_HOST=127.0.0.1 # [tl! remove]
DB_PORT=3306 # [tl! remove]
DB_DATABASE=laravel # [tl! remove]
DB_USERNAME=root # [tl! remove]
DB_PASSWORD= # [tl! remove]
```

設定好 SQLite 資料庫後，就可以執行專案的[資料庫 Migration](/docs/{{version}}/migrations)。資料庫 Migration 會建立專案的資料表：

```shell
php artisan migrate
```

<a name="next-steps"></a>

## 接下來

現在，你已經建立好 Laravel 專案了，你可能會想知道接下來該學些什麼。首先，我們強烈建議你先閱讀下列說明文件來熟悉一下 Laravel 是怎麼運作的：

- [Request 的生命週期](/docs/{{version}}/lifecycle)

- [組態設定](/docs/{{version}}/configuration)

- [目錄架構](/docs/{{version}}/structure)

- [前端](/docs/{{version}}/frontend)

- [Service Container](/docs/{{version}}/container)

- [Facade](/docs/{{version}}/facades)

你想要如何使用 Laravel 也會影響學習的下一步。使用 Laravel 的方法不只一種，我們稍後也會來探索一下幾種使用 Laravel 的主要方法。

<a name="laravel-the-fullstack-framework"></a>

### Laravel - 全端框架

可以將 Laravel 當作全端框架使用。我們說「全端框架」，是指你會使用 Laravel 來將 ^[Request](請求) 導向到專案中，並使用 [Blade 樣板](/docs/{{version}}/blade)來轉譯前端界面，或是使用如 [Inertia](https://inertiajs.com) 這類的 SPA (單頁面應用程式，Single-Page Application) 混合技術。這種使用 Laravel 的方法是最常見的。而且，在我們看來，這也是使用 Laravel 最有生產力的方法。

若你就是這麼打算使用 Laravel 的，則你可能會想看看有關[前端開發](/docs/{{version}}/frontend)、[路由](/docs/{{version}}/routing)、[View](/docs/{{version}}/views)、或 [Eloquent ORM](/docs/{{version}}/eloquent) 的說明文件。此外，你可能也有興趣想了解一下如 [Livewire](https://laravel-livewire.com) 或 [Inertia](https://inertiajs.com) 等由社群開發的套件。這些套件能讓你在使用 Laravel 作為全端框架的同時，還能享受到許多由 JavaScript SPA 提供的 UI 的好處。

若你要使用 Laravel 作為全端框架，我們也強烈建議你瞭解一下如何使用 [Vite](/docs/{{version}}/vite) 來編譯網站的 CSS 與 JavaScript。

> **Note** 若你想要有個起始點可以開始寫網站，請參考看看我們的官方[專案入門套件](/docs/{{version}}/starter-kits)。


<a name="laravel-the-api-backend"></a>

### Laravel - API 後端

也可以將 Laravel 作為 API 後端來提供給 JavaScript SPA 或手機 App 使用。舉例來說，你可以使用 Laravel 作為 [Next.js](https://nextjs.org) App 的 API 後端來使用。在這種情況下，你可以使用 Laravel 來提供[登入認證](/docs/{{version}}/sanctum)，並為 App 提供資料的儲存、取得功能，同時也能使用到 Laravel 的一些如佇列、E-Mail、通知⋯⋯等強大的功能。

若你打算這樣使用 Laravel，則可以看看有關[路由](/docs/{{version}}/routing)、[Laravel Sanctum](/docs/{{version}}/sanctum)、以及 [Eloquent ORM](/docs/{{version}}/eloquent) 的說明文件。

> **Note** 需要使用 Laravel 後端與 Next.js 前端的入門 Scaffolding 嗎？Laravel Breeze 提供了 [API Stack](/docs/{{version}}/starter-kits#breeze-and-next) 以及一個 [Next.js 的前端實作](https://github.com/laravel/breeze-next)，能讓你快速上手。

