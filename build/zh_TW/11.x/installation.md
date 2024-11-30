---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/89/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 48.09
---

# 安裝

- [認識 Laravel](#meet-laravel)
  - [為什麼要選擇 Laravel？](#why-laravel)
  
- [Creating a Laravel Application](#creating-a-laravel-project)
  - [Installing PHP and the Laravel Installer](#installing-php)
  - [Creating an Application](#creating-an-application)
  
- [初始設定](#initial-configuration)
  - [依環境調整設定](#environment-based-configuration)
  - [Databases and Migrations](#databases-and-migrations)
  - [目錄的設定](#directory-configuration)
  
- [Local Installation Using Herd](#local-installation-using-herd)
  - [Herd on macOS](#herd-on-macos)
  - [Herd on Windows](#herd-on-windows)
  
- [Docker Installation Using Sail](#docker-installation-using-sail)
  - [Sail on macOS](#sail-on-macos)
  - [Sail on Windows](#sail-on-windows)
  - [Sail on Linux](#sail-on-linux)
  - [選擇 Sail 服務](#choosing-your-sail-services)
  
- [IDE 支援](#ide-support)
- [下一步](#next-steps)
  - [Laravel the Full Stack Framework](#laravel-the-fullstack-framework)
  - [Laravel the API Backend](#laravel-the-api-backend)
  

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

<a name="creating-a-laravel-project"></a>

## Creating a Laravel Application

<a name="installing-php"></a>

### Installing PHP and the Laravel Installer

Before creating your first Laravel application, make sure that your local machine has [PHP](https://php.net), [Composer](https://getcomposer.org), and [the Laravel installer](https://github.com/laravel/installer) installed. In addition, you should install either [Node and NPM](https://nodejs.org) or [Bun](https://bun.sh/) so that you can compile your application's frontend assets.

If you don't have PHP and Composer installed on your local machine, the following commands will install PHP, Composer, and the Laravel installer on macOS, Windows, or Linux:

```shell
/bin/bash -c "$(curl -fsSL https://php.new/install/mac)"
```
```shell
# Run as administrator...
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows'))
```
```shell
/bin/bash -c "$(curl -fsSL https://php.new/install/linux)"
```
After running one of the commands above, you should restart your terminal session. To update PHP, Composer, and the Laravel installer after installing them via `php.new`, you can re-run the command in your terminal.

If you already have PHP and Composer installed, you may install the Laravel installer via Composer:

```shell
composer global require laravel/installer
```
> [!NOTE]  
> For a fully-featured, graphical PHP installation and management experience, check out [Laravel Herd](#local-installation-using-herd).

<a name="creating-an-application"></a>

### Creating an Application

After you have installed PHP, Composer, and the Laravel installer, you're ready to create a new Laravel application. The Laravel installer will prompt you to select your preferred testing framework, database, and starter kit:

```nothing
laravel new example-app
```
Once the application has been created, you can start Laravel's local development server, queue worker, and Vite development server using the `dev` Composer script:

```nothing
cd example-app
npm install && npm run build
composer run dev
```
Once you have started the development server, your application will be accessible in your web browser at [http://localhost:8000](http://localhost:8000). Next, you're ready to [start taking your next steps into the Laravel ecosystem](#next-steps). Of course, you may also want to [configure a database](#databases-and-migrations).

> [!NOTE]  
> 在開發 Laravel 專案時，若想先快速地起個頭，可以考慮使用 Laravel 的[入門套件](/docs/{{version}}/starter-kits)。Laravel 的入門套件可以為新專案提供後端與前端的登入 Scaffolding。

<a name="initial-configuration"></a>

## 初始設定

Laravel 框架的所有設定檔都儲存在 `config` 目錄內。各個選項都有說明文件，歡迎閱讀這些檔案並熟悉可用的選項。

Laravel 預設幾乎不需要進行額外設定。你現在已經可以開始開發了！不過，可以先看看 `config/app.php` 檔案以及其中的說明。該檔案中包含了一些我們可能需要依據不同專案進行修改的設定選項，如： `timezone` (時區) 以及 `locale` (語系) 等。

<a name="environment-based-configuration"></a>

### 隨環境調整的設定

根據專案是在本機還是線上環境執行，Laravel 中許多的設定值都需要作出對應的調整。因此，許多重要的設定值都使用 `.env` 檔案來定義。該檔案位在專案根目錄。

Your `.env` file should not be committed to your application's source control, since each developer / server using your application could require a different environment configuration. Furthermore, this would be a security risk in the event an intruder gains access to your source control repository, since any sensitive credentials would be exposed.

> [!NOTE]  
> 更多有關 `.env` 檔案以及基於環境的設定資訊，請參考完整的[設定說明文件](/docs/{{version}}/configuration#environment-configuration)。

<a name="databases-and-migrations"></a>

### Databases and Migrations

Now that you have created your Laravel application, you probably want to store some data in a database. By default, your application's `.env` configuration file specifies that Laravel will be interacting with an SQLite database.

During the creation of the application, Laravel created a `database/database.sqlite` file for you, and ran the necessary migrations to create the application's database tables.

If you prefer to use another database driver such as MySQL or PostgreSQL, you can update your `.env` configuration file to use the appropriate database. For example, if you wish to use MySQL, update your `.env` configuration file's `DB_*` variables like so:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```
If you choose to use a database other than SQLite, you will need to create the database and run your application's [database migrations](/docs/{{version}}/migrations):

```shell
php artisan migrate
```
> [!NOTE]  
> If you are developing on macOS or Windows and need to install MySQL, PostgreSQL, or Redis locally, consider using [Herd Pro](https://herd.laravel.com/#plans).

<a name="directory-configuration"></a>

### 目錄設定

Laravel 只能架設在「網頁目錄」的根目錄下。請不要嘗試將 Laravel 專案架設在「網頁目錄」的子目錄下。若嘗試這麼做可能會將專案的機敏檔案暴露在外。

<a name="local-installation-using-herd"></a>

## Local Installation Using Herd

[Laravel Herd](https://herd.laravel.com) is a blazing fast, native Laravel and PHP development environment for macOS and Windows. Herd includes everything you need to get started with Laravel development, including PHP and Nginx.

Once you install Herd, you're ready to start developing with Laravel. Herd includes command line tools for `php`, `composer`, `laravel`, `expose`, `node`, `npm`, and `nvm`.

> [!NOTE]  
> [Herd Pro](https://herd.laravel.com/#plans) augments Herd with additional powerful features, such as the ability to create and manage local MySQL, Postgres, and Redis databases, as well as local mail viewing and log monitoring.

<a name="herd-on-macos"></a>

### Herd on macOS

If you develop on macOS, you can download the Herd installer from the [Herd website](https://herd.laravel.com). The installer automatically downloads the latest version of PHP and configures your Mac to always run [Nginx](https://www.nginx.com/) in the background.

Herd for macOS uses [dnsmasq](https://en.wikipedia.org/wiki/Dnsmasq) to support "parked" directories. Any Laravel application in a parked directory will automatically be served by Herd. By default, Herd creates a parked directory at `~/Herd` and you can access any Laravel application in this directory on the `.test` domain using its directory name.

After installing Herd, the fastest way to create a new Laravel application is using the Laravel CLI, which is bundled with Herd:

```nothing
cd ~/Herd
laravel new my-app
cd my-app
herd open
```
Of course, you can always manage your parked directories and other PHP settings via Herd's UI, which can be opened from the Herd menu in your system tray.

You can learn more about Herd by checking out the [Herd documentation](https://herd.laravel.com/docs).

<a name="herd-on-windows"></a>

### Herd on Windows

You can download the Windows installer for Herd on the [Herd website](https://herd.laravel.com/windows). After the installation finishes, you can start Herd to complete the onboarding process and access the Herd UI for the first time.

The Herd UI is accessible by left-clicking on Herd's system tray icon. A right-click opens the quick menu with access to all tools that you need on a daily basis.

During installation, Herd creates a "parked" directory in your home directory at `%USERPROFILE%\Herd`. Any Laravel application in a parked directory will automatically be served by Herd, and you can access any Laravel application in this directory on the `.test` domain using its directory name.

After installing Herd, the fastest way to create a new Laravel application is using the Laravel CLI, which is bundled with Herd. To get started, open Powershell and run the following commands:

```nothing
cd ~\Herd
laravel new my-app
cd my-app
herd open
```
You can learn more about Herd by checking out the [Herd documentation for Windows](https://herd.laravel.com/docs/windows).

<a name="docker-installation-using-sail"></a>

## Docker Installation Using Sail

We want it to be as easy as possible to get started with Laravel regardless of your preferred operating system. So, there are a variety of options for developing and running a Laravel application on your local machine. While you may wish to explore these options at a later time, Laravel provides [Sail](/docs/{{version}}/sail), a built-in solution for running your Laravel application using [Docker](https://www.docker.com).

Docker 這款工具使用小型、輕量的「Container (容器)」來執行網站與服務。使用 Container 就不會影響到本機上所安裝的軟體或設定。這表示，使用 Docker，讀者就不需擔心如何在自己電腦上設定一些如網頁伺服器或資料庫等複雜的開發工具。要開始使用 Sail，只需要先安裝 [Docker Desktop](https://www.docker.com/products/docker-desktop) 即可。

Laravel Sail 是一個輕量的命令列介面，可用來操作 Laravel 預設的 Docker 設定。對於使用 PHP、MySQL 與 Redis 來建立 Laravel 專案，Sail 是一個不錯的入門選項，且不需預先具備有關 Docker 的知識。

> [!NOTE]  
> 已經是 Docker 大師了嗎？別擔心！在 `docker-compose.yml` 內能對 Sail 的所有東西進行客製化。

<a name="sail-on-macos"></a>

### Sail on macOS

If you're developing on a Mac and [Docker Desktop](https://www.docker.com/products/docker-desktop) is already installed, you can use a simple terminal command to create a new Laravel application. For example, to create a new Laravel application in a directory named "example-app", you may run the following command in your terminal:

```shell
curl -s "https://laravel.build/example-app" | bash
```
當然，我們任意修改該網址的「example-app」為任意值 —— 不過要注意，這個值只能包含字母、數字、減號 (`-`)、底線 (`_`)。Laravel 專案目錄會被建立在執行該指令的目錄下。

由於 Sail 的應用程式 Container 是在你的本機電腦上建置的，因此 Sail 可能會花費數分鐘來安裝。

After the application has been created, you can navigate to the application directory and start Laravel Sail. Laravel Sail provides a simple command-line interface for interacting with Laravel's default Docker configuration:

```shell
cd example-app

./vendor/bin/sail up
```
Once the application's Docker containers have started, you should run your application's [database migrations](/docs/{{version}}/migrations):

```shell
./vendor/bin/sail artisan migrate
```
Finally, you can access the application in your web browser at: [http://localhost](http://localhost).

> [!NOTE]  
> 若要繼續瞭解更多有關 Laravel Sail 的資訊，請參考 Laravel Sail 的[完整說明文件](/docs/{{version}}/sail)。

<a name="sail-on-windows"></a>

### Sail on Windows

在 Windows 裝置上建立新的 Laravel 專案前，請先確認一下是否有安裝 [Docker Desktop](https://www.docker.com/products/docker-desktop)。接著，請確認是否有安裝並啟用 WSL2 (適用於 Linux 的 Windows 子系統 2，Windows Subsystem for Linux 2)。使用 WSL 就可以在 Windows 10 上原生地執行 Linux 二進位可執行檔。可以在 Microsoft 的《[開發人員環境說明文件](https://docs.microsoft.com/zh-tw/windows/wsl/install-win10)》瞭解有關如何安裝並啟用 WSL2 的資訊。

> [!NOTE]  
> 安裝並啟用 WSL2 後，請確認是否有將 Docker Desktop [設為使用 WSL2 後端](https://docs.docker.com/docker-for-windows/wsl/)。

Next, you are ready to create your first Laravel application. Launch [Windows Terminal](https://www.microsoft.com/en-us/p/windows-terminal/9n0dx20hk701?rtc=1&activetab=pivot:overviewtab) and begin a new terminal session for your WSL2 Linux operating system. Next, you can use a simple terminal command to create a new Laravel application. For example, to create a new Laravel application in a directory named "example-app", you may run the following command in your terminal:

```shell
curl -s https://laravel.build/example-app | bash
```
當然，我們任意修改該網址的「example-app」為任意值 —— 不過要注意，這個值只能包含字母、數字、減號 (`-`)、底線 (`_`)。Laravel 專案目錄會被建立在執行該指令的目錄下。

由於 Sail 的應用程式 Container 是在你的本機電腦上建置的，因此 Sail 可能會花費數分鐘來安裝。

After the application has been created, you can navigate to the application directory and start Laravel Sail. Laravel Sail provides a simple command-line interface for interacting with Laravel's default Docker configuration:

```shell
cd example-app

./vendor/bin/sail up
```
Once the application's Docker containers have started, you should run your application's [database migrations](/docs/{{version}}/migrations):

```shell
./vendor/bin/sail artisan migrate
```
Finally, you can access the application in your web browser at: [http://localhost](http://localhost).

> [!NOTE]  
> 若要繼續瞭解更多有關 Laravel Sail 的資訊，請參考 Laravel Sail 的[完整說明文件](/docs/{{version}}/sail)。

#### 在 WSL2 中進行開發

當然，之後你還需要能修改在 WSL2 內所建立的 Laravel 專案檔案。若要修改這些 WSL2 內的檔案，我們建議使用 Microsoft 的 [Visual Studio Code](https://code.visualstudio.com) 編輯器，並使用用於[遠端開發](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.vscode-remote-extensionpack)的第一方擴充功能。

Once these tools are installed, you may open any Laravel application by executing the `code .` command from your application's root directory using Windows Terminal.

<a name="sail-on-linux"></a>

### Sail on Linux

If you're developing on Linux and [Docker Compose](https://docs.docker.com/compose/install/) is already installed, you can use a simple terminal command to create a new Laravel application.

首先，若使用 Docker Desktop for Linux，則請執行下列指令。若不使用 Docker Desktop for Linux，則可跳過次步驟：

```shell
docker context use default
```
接著，若要將新的 Laravel 專案建立在名為「example-app」的目錄中，可在終端機內執行下列指令：

```shell
curl -s https://laravel.build/example-app | bash
```
當然，我們任意修改該網址的「example-app」為任意值 —— 不過要注意，這個值只能包含字母、數字、減號 (`-`)、底線 (`_`)。Laravel 專案目錄會被建立在執行該指令的目錄下。

由於 Sail 的應用程式 Container 是在你的本機電腦上建置的，因此 Sail 可能會花費數分鐘來安裝。

After the application has been created, you can navigate to the application directory and start Laravel Sail. Laravel Sail provides a simple command-line interface for interacting with Laravel's default Docker configuration:

```shell
cd example-app

./vendor/bin/sail up
```
Once the application's Docker containers have started, you should run your application's [database migrations](/docs/{{version}}/migrations):

```shell
./vendor/bin/sail artisan migrate
```
Finally, you can access the application in your web browser at: [http://localhost](http://localhost).

> [!NOTE]  
> 若要繼續瞭解更多有關 Laravel Sail 的資訊，請參考 Laravel Sail 的[完整說明文件](/docs/{{version}}/sail)。

<a name="choosing-your-sail-services"></a>

### 選擇 Sail 服務

When creating a new Laravel application via Sail, you may use the `with` query string variable to choose which services should be configured in your new application's `docker-compose.yml` file. Available services include `mysql`, `pgsql`, `mariadb`, `redis`, `memcached`, `meilisearch`, `typesense`, `minio`, `selenium`, and `mailpit`:

```shell
curl -s "https://laravel.build/example-app?with=mysql,redis" | bash
```
若未指定要設定哪些服務，則預設將設定 `mysql`, `redis`, `meilisearch`, `mailpit`, 與 `selenium`。

只要在網址後加上 `devcontainer` 參數，就可以讓 Sail 安裝一個預設的 [Devcontainer](/docs/{{version}}/sail#using-devcontainers)：

```shell
curl -s "https://laravel.build/example-app?with=mysql,redis&devcontainer" | bash
```
<a name="ide-support"></a>

## IDE 支援

在開發 Laravel 專案時，可以自由選擇要使用什麼程式碼編輯器。不過，對 Laravel 與 Laravel 生態系統來說，[PhpStorm](https://www.jetbrains.com/phpstorm/laravel/) 提供了最廣泛的支援，其中也包含了 [Laravel Pint](https://www.jetbrains.com/help/phpstorm/using-laravel-pint.html) 支援。

此外，還有一個由社群維護的 [Laravel Idea](https://laravel-idea.com/) PhpStorm 外掛程式，提供了多種實用的 IDE 功能，如程式碼陳昇、Eloquent 語法補全、驗證規則的自動補全⋯⋯等。

<a name="next-steps"></a>

## 接下來

Now that you have created your Laravel application, you may be wondering what to learn next. First, we strongly recommend becoming familiar with how Laravel works by reading the following documentation:

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

### Laravel the Full Stack Framework

可以將 Laravel 當作全端框架使用。我們說「全端框架」，是指你會使用 Laravel 來將 ^[Request](%E8%AB%8B%E6%B1%82) 導向到專案中，並使用 [Blade 樣板](/docs/{{version}}/blade)來轉譯前端界面，或是使用如 [Inertia](https://inertiajs.com) 這類的 SPA (單頁面應用程式，Single-Page Application) 混合技術。這種使用 Laravel 的方法是最常見的。而且，在我們看來，這也是最有效率的一種使用 Laravel 的方法。

若讀者就是這麼打算使用 Laravel 的，則可能會想看看有關[前端開發](/docs/{{version}}/frontend)、[路由](/docs/{{version}}/routing)、[View](/docs/{{version}}/views)、或 [Eloquent ORM](/docs/{{version}}/eloquent) 的說明文件。此外，你可能也有興趣想了解一下如 [Livewire](https://liveware.laravel.com) 或 [Inertia](https://inertiajs.com) 等由社群開發的套件。這些套件能讓你在使用 Laravel 作為全端框架的同時，還能享受到許多由 JavaScript SPA 提供 UI 的好處。

若要使用 Laravel 作為全端框架，我們也強烈建議你瞭解一下如何使用 [Vite](/docs/{{version}}/vite) 來編譯網站的 CSS 與 JavaScript。

> [!NOTE]  
> 若想要有個起始點可以開始寫網站，請參考看看我們的官方[專案入門套件](/docs/{{version}}/starter-kits)。

<a name="laravel-the-api-backend"></a>

### Laravel the API Backend

也可以將 Laravel 作為 API 後端來提供給 JavaScript SPA 或手機 App 使用。舉例來說，你可以使用 Laravel 作為 [Next.js](https://nextjs.org) App 的 API 後端來使用。在這種情況下，你可以使用 Laravel 來提供[身份認證](/docs/{{version}}/sanctum)，並為 App 提供儲存與取得資料的功能，同時也能使用到 Laravel 的一些如佇列、E-Mail、通知⋯⋯等強大的功能。

若你打算這樣使用 Laravel，則可以看看有關[路由](/docs/{{version}}/routing)、[Laravel Sanctum](/docs/{{version}}/sanctum)、以及 [Eloquent ORM](/docs/{{version}}/eloquent) 的說明文件。

> [!NOTE]  
> 需要使用 Laravel 後端與 Next.js 前端的入門 Scaffolding 嗎？Laravel Breeze 提供了 [API Stack](/docs/{{version}}/starter-kits#breeze-and-next) 以及一個 [Next.js 的前端實作](https://github.com/laravel/breeze-next)，能讓你快速上手。
