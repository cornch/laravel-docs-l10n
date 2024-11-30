---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/89/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 48.09
---

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
  - [依環境調整設定](#environment-based-configuration)
  - [資料庫與 Migration](#databases-and-migrations)
  
- [下一步](#next-steps)
  - [Laravel – 全端框架](#laravel-the-fullstack-framework)
  - [Laravel – API 後端](#laravel-the-api-backend)
  

<a name="meet-laravel"></a>

## 認識 Laravel

Laravel 是一個 ^[Web](%E7%B6%B2%E9%A0%81) App 框架，有既簡單又優雅的語法。Web 框架提供了製作網站的起始架構。使用框架，你就能專心製作令人驚艷的作品，而框架則幫你處理掉麻煩的小地方。

Laravel 致力於提供讓人驚豔的 DX (開發體驗, Developer Experience)，並提供多種強大的功能，包含相依性插入 (Dependency Injection)、描述性的資料庫抽象層、佇列與排程任務、單元測試 (Unit Testing) 與整合測試 (Integration Testing)⋯⋯等功能。

不管讀者是 PHP 新手還是網頁框架新手、或是已經有多年的經驗，Laravel 都是可陪伴你進步的框架。我們可以協助你跨出成為網頁開發人員的第一步，或是助你一臂之力，讓你的技術更上一層樓。我們迫不及待想看看你的成果！

> [!NOTE]  
> 是 Laravel 新手嗎？請參考 [Laravel Bootcamp (英語)](https://bootcamp.laravel.com) 來瞭解 Laravel 框架。同時，我們也會帶領你建立你的第一個 Laravel 專案。

<a name="why-laravel"></a>

### 為什麼選擇 Laravel？

市面上有多款工具與框架可用來製作 Web App。不過，我們相信，製作現代化的全端 Web App，Laravel 是最佳選擇。

#### 進步性的框架

我們喜歡把 Laravel 稱為一個「進步性的 (Prograssive)」框架。這麼說，是因為 Laravel 可以伴著你起進步。若你第一次接觸網頁開發，Laravel 有許多的說明文件、教學、以及[影片教學](https://laracasts.com)可以讓你無痛學習 Laravel。

若你是資深開發人員，Laravel 提供了強健的[相依性插入](/docs/{{version}}/container)、[單元測試](/docs/{{version}}/testing)、[佇列](/docs/{{version}}/queues)、[即時事件](/docs/{{version}}/broadcasting)⋯⋯等功能。Laravel 為打造專業的 Web App 做了許多微調，並可處理企業級的任務。

#### 可彈性調整規模的框架

在規模調整上，Laravel 非常彈性。多虧於 PHP 本身可彈性調整規模 (Scalable) 的特性、以及 Laravel 內建對於像 Redis 之類的快速分散式快取系統支援，在 Laravel 中，要水平調整規模非常簡單。其實，使用 Laravel 的專案可以輕鬆地調整到每個月能處理數百萬筆 ^[Request](%E8%AB%8B%E6%B1%82) 的規模。

需要調整到極限的規模嗎？使用 [Laravel Vapor](https://vapor.laravel.com) 等平台，就可以讓你在 AWS 上使用最新的 Serverless 技術，以幾乎不限規模的方式來執行 Laravel 專案。

#### 社群的框架

Laravel 結合了 PHP 生態系統中多個最好的套件來提供強健且對開發人員友善的框架。此外，來自世界各地數千位優秀的開發人員也[參與貢獻了 Laravel 框架](https://github.com/laravel/framework)。或許，你也有機會參與貢獻 Laravel。

<a name="your-first-laravel-project"></a>

## 你的第一個 Laravel 專案

在開始建立第一個 Laravel 專案前，請先確定本機電腦上有安裝 PHP 與 [Composer](https://getcomposer.org)。若使用 macOS 開發，可通過 [Homebrew](https://brew.sh/) 來安裝 PHP 與 Composer。此外，我們也建議[安裝 Node 與 NPM](https://nodejs.org)。

安裝好 PHP 與 Composer 後，可使用 Composer 的 `create-project` 指令來建立新的 Laravel 專案：

```nothing
composer create-project laravel/laravel:^9.0 example-app
```
或者，也可以使用 Composer 來全域性地安裝 Laravel Installer，以建立新 Laravel 專案：

```nothing
composer global require laravel/installer

laravel new example-app
```
建立好專案後，可使用 Laravel 的 Artisan CLI `serve` 指令來開始 Laravel 的本機開發伺服器：

```nothing
cd example-app

php artisan serve
```
開啟 Artisan 開發伺服器後，就可以在瀏覽器中開啟 `http://localhost:8000` 來存取專案。接著，我們就可以[開始進入 Laravel 生態系統的下一步](#next-steps)。當然，也可以先來[設定一下資料庫](#databases-and-migrations)。

> [!NOTE]  
> 在開發 Laravel 專案時，若想先快速地起個頭，可以考慮使用 Laravel 的[入門套件](/docs/{{version}}/starter-kits)。Laravel 的入門套件可以為新專案提供後端與前端的登入 Scaffolding。

<a name="laravel-and-docker"></a>

## Laravel 與 Docker

我們希望不論使用什麼作業系統，都可以儘可能地用簡單的方式開始入門 Laravel。因此，有幾個選項可讓你在本機上開發並執行 Laravel 專案。你可以稍後再來進一步瞭解這些選項。Laravel 提供了 [Sail](/docs/{{version}}/sail)，Sail 是 Laravel 專案內建的方法，可以使用 [Docker](https://www.docker.com) 來執行 Laravel 專案。

Docker 這款工具使用小型、輕量的「Container (容器)」來執行網站與服務。使用 Container 就不會影響到本機上所安裝的軟體或設定。這表示，使用 Docker，讀者就不需擔心如何在自己電腦上設定一些如網頁伺服器或資料庫等複雜的開發工具。要開始使用 Sail，只需要先安裝 [Docker Desktop](https://www.docker.com/products/docker-desktop) 即可。

Laravel Sail 是一個輕量的命令列介面，可用來操作 Laravel 預設的 Docker 設定。對於使用 PHP、MySQL 與 Redis 來建立 Laravel 專案，Sail 是一個不錯的入門選項，且不需預先具備有關 Docker 的知識。

> [!NOTE]  
> 已經是 Docker 大師了嗎？別擔心！在 `docker-compose.yml` 內能對 Sail 的所有東西進行客製化。

<a name="getting-started-on-macos"></a>

### 使用 macOS 入門

若要 Mac 上進行開發，且已安裝了 [Docker Desktop](https://www.docker.com/products/docker-desktop)，則可以使用一個簡單的終端機指令來建立新的 Laravel 專案。舉例來說，要在一個名為「example-app」的檔案夾內建立新的 Laravel 專案，可以在終端機內執行下列指令：

```shell
curl -s "https://laravel.build/example-app" | bash
```
當然，我們任意修改該網址的「example-app」為任意值 —— 不過要注意，這個值只能包含字母、數字、減號 (`-`)、底線 (`_`)。Laravel 專案目錄會被建立在執行該指令的目錄下。

由於 Sail 的應用程式 Container 是在你的本機電腦上建置的，因此 Sail 可能會花費數分鐘來安裝。

建立好專案後，就可以打開該檔案夾並開啟 Laravel Sail。Laravel Sail 提供了一個可與 Laravel 預設的 Docker 設定互動的簡單指令列介面：

```shell
cd example-app

./vendor/bin/sail up
```
Once the application's Docker containers have been started, you can access the application in your web browser at: [http://localhost](http://localhost).

> [!NOTE]  
> 若要繼續瞭解更多有關 Laravel Sail 的資訊，請參考 Laravel Sail 的[完整說明文件](/docs/{{version}}/sail)。

<a name="getting-started-on-windows"></a>

### 使用 Windows 入門

在 Windows 裝置上建立新的 Laravel 專案前，請先確認一下是否有安裝 [Docker Desktop](https://www.docker.com/products/docker-desktop)。接著，請確認是否有安裝並啟用 WSL2 (適用於 Linux 的 Windows 子系統 2，Windows Subsystem for Linux 2)。使用 WSL 就可以在 Windows 10 上原生地執行 Linux 二進位可執行檔。可以在 Microsoft 的《[開發人員環境說明文件](https://docs.microsoft.com/zh-tw/windows/wsl/install-win10)》瞭解有關如何安裝並啟用 WSL2 的資訊。

> [!NOTE]  
> 安裝並啟用 WSL2 後，請確認是否有將 Docker Desktop [設為使用 WSL2 後端](https://docs.docker.com/docker-for-windows/wsl/)。

接著，我們就可以來建立你的第一個 Laravel 專案。請先開啟  [Windows Terminal](https://www.microsoft.com/zh-tw/p/windows-terminal/9n0dx20hk701?rtc=1&activetab=pivot:overviewtab)，然後為 WSL2 Linux 作業系統開啟一個新的終端機工作階段。接著，可以使用一個簡單的終端機命令來建立新的 Laravel 專案。舉例來說，若要在名為「example-app」的資料夾內建立一個新的 Laravel 專案，請在終端機內執行下列命令：

```shell
curl -s https://laravel.build/example-app | bash
```
當然，我們任意修改該網址的「example-app」為任意值 —— 不過要注意，這個值只能包含字母、數字、減號 (`-`)、底線 (`_`)。Laravel 專案目錄會被建立在執行該指令的目錄下。

由於 Sail 的應用程式 Container 是在你的本機電腦上建置的，因此 Sail 可能會花費數分鐘來安裝。

建立好專案後，就可以打開該檔案夾並開啟 Laravel Sail。Laravel Sail 提供了一個可與 Laravel 預設的 Docker 設定互動的簡單指令列介面：

```shell
cd example-app

./vendor/bin/sail up
```
Once the application's Docker containers have been started, you can access the application in your web browser at: [http://localhost](http://localhost).

> [!NOTE]  
> 若要繼續瞭解更多有關 Laravel Sail 的資訊，請參考 Laravel Sail 的[完整說明文件](/docs/{{version}}/sail)。

#### 在 WSL2 中進行開發

當然，之後你還需要能修改在 WSL2 內所建立的 Laravel 專案檔案。若要修改這些 WSL2 內的檔案，我們建議使用 Microsoft 的 [Visual Studio Code](https://code.visualstudio.com) 編輯器，並使用用於[遠端開發](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.vscode-remote-extensionpack)的第一方擴充功能。

安裝好這些工具後，就可以用 Windows Terminal 在專案根目錄下執行 `code` 命令來開啟 Laravel 專案。

<a name="getting-started-on-linux"></a>

### 使用 Linux 入門

若要 Linux 上進行開發，且已安裝了[Docker Compose](https://docs.docker.com/compose/install)，就可以使用一個簡單的終端機指令來建立新的 Laravel 專案。舉例來說，要在一個名為「example-app」的目錄內建立新的 Laravel 專案，可以在終端機內執行下列指令：

```shell
curl -s https://laravel.build/example-app | bash
```
當然，我們任意修改該網址的「example-app」為任意值 —— 不過要注意，這個值只能包含字母、數字、減號 (`-`)、底線 (`_`)。Laravel 專案目錄會被建立在執行該指令的目錄下。

由於 Sail 的應用程式 Container 是在你的本機電腦上建置的，因此 Sail 可能會花費數分鐘來安裝。

建立好專案後，就可以打開該檔案夾並開啟 Laravel Sail。Laravel Sail 提供了一個可與 Laravel 預設的 Docker 設定互動的簡單指令列介面：

```shell
cd example-app

./vendor/bin/sail up
```
Once the application's Docker containers have been started, you can access the application in your web browser at: [http://localhost](http://localhost).

> [!NOTE]  
> 若要繼續瞭解更多有關 Laravel Sail 的資訊，請參考 Laravel Sail 的[完整說明文件](/docs/{{version}}/sail)。

<a name="choosing-your-sail-services"></a>

### 選擇 Sail 服務

通過 Sail 建立新的 Laravel 專案時，可以使用 `with` 查詢字串變數來選擇新專案的 `docker-compose.yml` 檔案內要設定哪些服務。可用的服務包含 `mysql`, `pgsql`, `mariadb`, `redis`, `memcached`, `meilisearch`, `minio`, `selenium`, 與 `mailpit`：

```shell
curl -s "https://laravel.build/example-app?with=mysql,redis" | bash
```
若未指定要設定哪些服務，則預設將設定 `mysql`, `redis`, `meilisearch`, `mailpit`, 與 `selenium`。

只要在網址後加上 `devcontainer` 參數，就可以讓 Sail 安裝一個預設的 [Devcontainer](/docs/{{version}}/sail#using-devcontainers)：

```shell
curl -s "https://laravel.build/example-app?with=mysql,redis&devcontainer" | bash
```
<a name="initial-configuration"></a>

## 初始設定

Laravel 框架的所有設定檔都儲存在 `config` 目錄內。各個選項都有說明文件，歡迎閱讀這些檔案並熟悉可用的選項。

Laravel 預設幾乎不需要進行額外設定。你現在已經可以開始開發了！不過，可以先看看 `config/app.php` 檔案以及其中的說明。該檔案中包含了一些我們可能需要依據不同專案進行修改的設定選項，如： `timezone` (時區) 以及 `locale` (語系) 等。

<a name="environment-based-configuration"></a>

### 隨環境調整的設定

根據專案是在本機還是線上環境執行，Laravel 中許多的設定值都需要作出對應的調整。因此，許多重要的設定值都使用 `.env` 檔案來定義。該檔案位在專案根目錄。

由於每個使用專案開發人員／伺服器都可能需要不同的環境組態設定，因此，`.env` 檔不應被簽入 (Commit) 到專案的版本控制中。此外，若將 `.env` 檔簽入版本控制的話，當有入侵者取得了版本控制儲存庫的存取權限，就可能會造成安全性風險，因為其中的機敏認證資料都會被暴露。

> [!NOTE]  
> 更多有關 `.env` 檔案以及基於環境的設定資訊，請參考完整的[設定說明文件](/docs/{{version}}/configuration#environment-configuration)。

<a name="databases-and-migrations"></a>

### 資料庫與 Migration

現在，我們已經建立好 Laravel 專案了。接下來，你可能會想在資料庫中儲存一些資料。預設情況下，專案的 `.env` 檔中已經指定要讓 Laravel 連線到 `127.0.0.1` 上的 MySQL。若在 macOS 上開發，且需要在本機上使用 MySQL、Postgres、Redis 的話，或許可以考慮看看使用方便的 [DBngin](https://dbngin.com/)。

If you do not want to install MySQL or Postgres on your local machine, you can always use a [SQLite](https://www.sqlite.org/index.html) database. SQLite is a small, fast, self-contained database engine. To get started, update your `.env` configuration file to use Laravel's `sqlite` database driver. You may remove the other database configuration options:

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
If an SQLite database does not exist for your application, Laravel will ask you if you would like the database to be created. Typically, the SQLite database file will be created at `database/database.sqlite`.

<a name="next-steps"></a>

## 接下來

現在，你已經建立好 Laravel 專案了，你可能會想知道接下來該學些什麼。首先，我們強烈建議你先閱讀下列說明文件來熟悉一下 Laravel 是怎麼運作的：

<div class="content-list" markdown="1">
- [Request 的生命週期](/docs/{{version}}/lifecycle)
- [設定](/docs/{{version}}/configuration)
- [目錄架構](/docs/{{version}}/structure)
- [前端](/docs/{{version}}/frontend)
- [Service Container](/docs/{{version}}/container)
- [Facade](/docs/{{version}}/facades)

</div>
你想要如何使用 Laravel 也會影響學習的下一步。使用 Laravel 的方法不只一種，我們稍後也會來探索一下幾種使用 Laravel 的主要方法。

> [!NOTE]  
> 是 Laravel 新手嗎？請參考 [Laravel Bootcamp (英語)](https://bootcamp.laravel.com) 來瞭解 Laravel 框架。同時，我們也會帶領你建立你的第一個 Laravel 專案。

<a name="laravel-the-fullstack-framework"></a>

### Laravel - 全端框架

可以將 Laravel 當作全端框架使用。我們說「全端框架」，是指你會使用 Laravel 來將 ^[Request](%E8%AB%8B%E6%B1%82) 導向到專案中，並使用 [Blade 樣板](/docs/{{version}}/blade)來轉譯前端界面，或是使用如 [Inertia](https://inertiajs.com) 這類的 SPA (單頁面應用程式，Single-Page Application) 混合技術。這種使用 Laravel 的方法是最常見的。而且，在我們看來，這也是最有效率的一種使用 Laravel 的方法。

若讀者就是這麼打算使用 Laravel 的，則可能會想看看有關[前端開發](/docs/{{version}}/frontend)、[路由](/docs/{{version}}/routing)、[View](/docs/{{version}}/views)、或 [Eloquent ORM](/docs/{{version}}/eloquent) 的說明文件。此外，你可能也有興趣想了解一下如 [Livewire](https://laravel-livewire.com) 或 [Inertia](https://inertiajs.com) 等由社群開發的套件。這些套件能讓你在使用 Laravel 作為全端框架的同時，還能享受到許多由 JavaScript SPA 提供 UI 的好處。

若要使用 Laravel 作為全端框架，我們也強烈建議你瞭解一下如何使用 [Vite](/docs/{{version}}/vite) 來編譯網站的 CSS 與 JavaScript。

> [!NOTE]  
> 若想要有個起始點可以開始寫網站，請參考看看我們的官方[專案入門套件](/docs/{{version}}/starter-kits)。

<a name="laravel-the-api-backend"></a>

### Laravel - API 後端

也可以將 Laravel 作為 API 後端來提供給 JavaScript SPA 或手機 App 使用。舉例來說，你可以使用 Laravel 作為 [Next.js](https://nextjs.org) App 的 API 後端來使用。在這種情況下，你可以使用 Laravel 來提供[身份認證](/docs/{{version}}/sanctum)，並為 App 提供儲存與取得資料的功能，同時也能使用到 Laravel 的一些如佇列、E-Mail、通知⋯⋯等強大的功能。

若你打算這樣使用 Laravel，則可以看看有關[路由](/docs/{{version}}/routing)、[Laravel Sanctum](/docs/{{version}}/sanctum)、以及 [Eloquent ORM](/docs/{{version}}/eloquent) 的說明文件。

> [!NOTE]  
> 需要使用 Laravel 後端與 Next.js 前端的入門 Scaffolding 嗎？Laravel Breeze 提供了 [API Stack](/docs/{{version}}/starter-kits#breeze-and-next) 以及一個 [Next.js 的前端實作](https://github.com/laravel/breeze-next)，能讓你快速上手。
