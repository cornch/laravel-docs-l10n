# 安裝

- [認識 Laravel](#meet-laravel)
    - [為什麼選擇 Laravel？](#why-laravel)
- [第一個 Laravel 專案](#your-first-laravel-project)
    - [使用 macOS 入門](#getting-started-on-macos)
    - [使用 Windows 入門](#getting-started-on-windows)
    - [使用 Linux 入門](#getting-started-on-linux)
    - [選擇 Sail 服務](#choosing-your-sail-services)
    - [通過 Composer 安裝](#installation-via-composer)
- [初試設定](#initial-configuration)
    - [依照環境的組態設定](#environment-based-configuration)
    - [資料夾組態設定](#directory-configuration)
- [下一步](#next-steps)
    - [Laravel - 全端框架](#laravel-the-fullstack-framework)
    - [Laravel - API 後端](#laravel-the-api-backend)

<a name="meet-laravel"></a>
## 認識 Laravel

Laravel 是一個擁有豐富優雅語法的 Web App
框架。網頁框架提供了製作網站的起始架構，讓你專心製作讓人驚艷的作品，而我們則幫你處理掉麻煩的小地方。

Laravel 致力於提供優質的開發體驗，並提供多種強大的功能，包含相依性插入 (Dependency
Injection)、描述性的資料庫抽象層、佇列與排程任務、單元測試 (Unit Testing) 與整合測試 (Integration
Testing)⋯⋯等功能。

不管你是 PHP 新手還是網頁框架新手、或者你已經有多年的經驗，Laravel
都是可陪伴你進步的框架。我們可以協助你作為網頁開發人員跨出第一步，或是助你一臂之力，讓你的技術更勝一層樓。我們迫不及待想看看你的成果！

<a name="why-laravel"></a>
### 為什麼選擇 Laravel？

市面上有很多種用來製作 Web App 的網頁框架了。不過，我們相信 Laravel 是製作現代、全端 Web App 的最佳選擇。

#### 進步的框架

我們喜歡把 Laravel 稱為一個「進步的 (Prograssive)」框架。我們這麼說是因為 Laravel
可以跟你一起進步。若你是初次踏入網頁開發，Laravel
有許多的說明文件、教學、以及[影片教學](https://laracasts.com)可以讓你無痛學習 Laravel。

若你是資深開發人員，Laravel
提供了強健的[相依性插入](/docs/{{version}}/container)、[單元測試](/docs/{{version}}/testing)、[佇列](/docs/{{version}}/queues)、[即時事件](/docs/{{version}}/broadcasting)⋯⋯等功能。Laravel
為打造專業的 Web App 做了許多微調，並可處理企業級的任務。

#### 可彈性調整規模的框架

Laravel 對於規模調整非常地有彈性。多虧於 PHP 本身可彈性調整規模的特性、以及 Laravel 內建了對於如 Redis
等快速、分散式快去系統的支援，在 Laravel 中要水平調整規模非常簡單。其實，使用 Laravel
的專案可以輕鬆地調整為能處理每月數百萬筆請求的等級。

需要極限的可調整性？[Laravel Vapor](https://vapor.laravel.com) 等平台可讓你在 AWS 上最新的
Serverless 技術中以幾乎無限可調整性的方式來執行 Laravel 專案。

#### 社群的框架

Laravel 結合了 PHP 生態系統中多個最好的套件來提供強健且對開發人員友善的框架。此外，來自世界各地數千位優秀的開發人員也[參與貢獻了
Laravel 框架](https://github.com/laravel/framework)。或許，你也有機會成為 Laravel 的貢獻者。

<a name="your-first-laravel-project"></a>
## 你的第一個 Laravel 專案

我們想要讓開始入門 Laravel 盡可能地簡單。有幾個選項可讓你在電腦上開發並執行 Laravel
專案。你可以稍後再來進一步瞭解這些選項。Laravel 提供了 [Sail](/docs/{{version}}/sail)，Sail 是一個可以使用
[Docker](https://www.docker.com) 來執行 Laravel 專案的內建方法。

Docker 是一個可以以小型、輕量的「Container (容器)」執行網站與服務的工具。使用 Container
就不會影響到本機電腦上所安裝的軟體或設定。這表示，我們就不需要擔心如何在自己的電腦上設定複雜的開發工具，如網頁伺服器或資料庫。要開始使用
Sail，只需要先安裝 [Docker
Desktop](https://www.docker.com/products/docker-desktop)。

Laravel Sail 是一個輕量的命令列介面，可用來操作 Laravel 預設的 Docker 設定。對於使用 PHP、MySQL 與 Redis
來建立 Laravel 專案，Sail 是一個不錯的入門選項，且不需預先具備有關 Docker 的知識。

> {tip} 已經是 Docker 大師了嗎？別擔心！有關 Sail 的所有東西都可以通過 Laravel 內的 `docker-compose.yml` 進行客製化。

<a name="getting-started-on-macos"></a>
### 使用 macOS 入門

若要 Mac 上進行開發，且已安裝了 [Docker
Desktop](https://www.docker.com/products/docker-desktop)，則可以使用一個簡單的終端機指令來建立新的
Laravel 專案。舉例來說，要在一個名為「example-app」的檔案夾內建立新的 Laravel 專案，可以在終端機內執行下列指令：

```nothing
curl -s "https://laravel.build/example-app" | bash
```

當然，可以在這個網址中隨意修改「example-app」。Laravel 專案的檔案夾會被建立在執行該指令的檔案夾內。

建立好專案後，就可以打開該檔案夾並開始 Laravel Sail。Laravel Sail 提供了一個可與 Laravel 預設的 Docker
設定互動的簡單的指令列介面：

```nothing
cd example-app

./vendor/bin/sail up
```

首次執行 Sail 的 `up` 指令時，會在你的電腦上建置 Sail 的專案
Container。這個過程可能需要花費數分鐘的時間。**不過別擔心，之後要開始 Sail 會變得很快。**

開啟專案的 Docker Container 後，就可以在瀏覽器中開啟 http://localhost 來存取專案網站。

> {tip} 若要繼續瞭解更多有關 Laravel Sail 的資訊，請參考 Laravel Sail 的[完整說明文件](/docs/{{version}}/sail)。

<a name="getting-started-on-windows"></a>
### 使用 Windows 入門

在 Windows 裝置上建立新的 Laravel 專案前，請先確認一下是否有安裝 [Docker
Desktop](https://www.docker.com/products/docker-desktop)。接著，請確保有安裝並啟用適用於
Linux 的 Windows 子系統 2 (WSL2)。使用 WSL 就可以在 Windows 10 上原生地執行 Linux
二進位可執行檔。有關如何安裝並啟用 WSL2 的資訊可以在 Microsoft
的《[開發人員環境說明文件](https://docs.microsoft.com/zh-tw/windows/wsl/install-win10)》。

> {tip} 安裝並啟用 WSL2 後，請確保將 Docker Desktop [設定為使用 WSL2 後端](https://docs.docker.com/docker-for-windows/wsl/)。

接著，我們就可以來建立你的第一個 Laravel 專案。請先開啟  [Windows
Terminal](https://www.microsoft.com/zh-tw/p/windows-terminal/9n0dx20hk701?rtc=1&activetab=pivot:overviewtab)，然後為
WSL2 Linux 作業系統開啟一個新的終端機工作階段。接著，可以使用一個簡單的終端機命令來建立新的 Laravel
專案。舉例來說，若要在名為「example-app」的資料夾內建立一個新的 Laravel 專案，請在終端機內執行下列指令：

```nothing
curl -s https://laravel.build/example-app | bash
```

當然，可以在這個網址中隨意修改「example-app」。Laravel 專案的檔案夾會被建立在執行該指令的檔案夾內。

建立好專案後，就可以打開該檔案夾並開始 Laravel Sail。Laravel Sail 提供了一個可與 Laravel 預設的 Docker
設定互動的簡單的指令列介面：

```nothing
cd example-app

./vendor/bin/sail up
```

首次執行 Sail 的 `up` 指令時，會在你的電腦上建置 Sail 的專案
Container。這個過程可能需要花費數分鐘的時間。**不過別擔心，之後要開始 Sail 會變得很快。**

開啟專案的 Docker Container 後，就可以在瀏覽器中開啟 http://localhost 來存取專案網站。

> {tip} 若要繼續瞭解更多有關 Laravel Sail 的資訊，請參考 Laravel Sail 的[完整說明文件](/docs/{{version}}/sail)。

#### 在 WSL2 中進行開發

當然，我們還需要能修改建立在 WSL2 安裝內的 Laravel 專案檔案。為此，我們建議使用 Microsoft 的 [Visual Studio
Code](https://code.visualstudio.com)
編輯器，並使用用於[遠端開發](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.vscode-remote-extensionpack)的第一方擴充功能。

安裝好這些工具後，就可以用 Windows Terminal 在專案根目錄下執行 `code` 命令來開啟 Laravel 專案。

<a name="getting-started-on-linux"></a>
### 使用 Linux 入門

若要 Linux 上進行開發，且已安裝了 [Docker](https://www.docker.com)，則可以使用一個簡單的終端機指令來建立新的
Laravel 專案。舉例來說，要在一個名為「example-app」的目錄內建立新的 Laravel 專案，可以在終端機內執行下列指令：

```nothing
curl -s https://laravel.build/example-app | bash
```

當然，可以在這個網址中隨意修改「example-app」。Laravel 專案的檔案夾會被建立在執行該指令的檔案夾內。

建立好專案後，就可以打開該檔案夾並開始 Laravel Sail。Laravel Sail 提供了一個可與 Laravel 預設的 Docker
設定互動的簡單的指令列介面：

```nothing
cd example-app

./vendor/bin/sail up
```

首次執行 Sail 的 `up` 指令時，會在你的電腦上建置 Sail 的專案
Container。這個過程可能需要花費數分鐘的時間。**不過別擔心，之後要開始 Sail 會變得很快。**

開啟專案的 Docker Container 後，就可以在瀏覽器中開啟 http://localhost 來存取專案網站。

> {tip} 若要繼續瞭解更多有關 Laravel Sail 的資訊，請參考 Laravel Sail 的[完整說明文件](/docs/{{version}}/sail)。

<a name="choosing-your-sail-services"></a>
### 選擇 Sail 服務

通過 Sail 建立新的 Laravel 專案時，可以使用 `with` 查詢字串變數來選擇新專案的 `docker-compose.yml`
檔案內要設定哪些服務。可用的服務包含 `mysql`, `pgsql`, `mariadb`, `redis`, `memcached`,
`meilisearch`, `minio`, `selenium`, 與 `mailhog`：

```nothing
curl -s "https://laravel.build/example-app?with=mysql,redis" | bash
```

若未指定要設定哪些服務，則預設將設定 `mysql`, `redis`, `meilisearch`, `mailhog`, 與 `selenium`。

只要在網址後加上 `devcontainer` 參數，就可以讓 Sail 安裝一個預設的
[Devcontainer](/docs/{{version}}/sail#using-devcontainers)：

```nothing
curl -s "https://laravel.build/example-app?with=mysql,redis&devcontainer" | bash
```

<a name="installation-via-composer"></a>
### 使用 Composer 安裝

若你的電腦上已安裝了 PHP 與 Composer，則可以直接使用 Composer 來建立新的 Laravel 專案。專案建立好後，可以使用
Artisan CLI 的 `serve` 指令來開啟 Laravel 的本機開發伺服器：

    composer create-project laravel/laravel example-app

    cd example-app

    php artisan serve

<a name="the-laravel-installer"></a>
#### Laravel 安裝程式

或者，也可以將 Laravel 安裝程式安裝為全域的 Composer 相依性套件：

```nothing
composer global require laravel/installer

laravel new example-app

cd example-app

php artisan serve
```

請確保 Composer 的系統等級 vendor bin 資料夾有放在 `$PATH` 中，這樣作業系統才能找到 `laravel`
可執行檔。這個資料夾在不同作業系統上會在不同位置。不過，常見的位置如下：

<div class="content-list" markdown="1">
- macOS: `$HOME/.composer/vendor/bin`
- Windows: `%USERPROFILE%\AppData\Roaming\Composer\vendor\bin`
- GNU / Linux 發行版: `$HOME/.config/composer/vendor/bin` 或 `$HOME/.composer/vendor/bin`
</div>

為了方便起見，Laravel 安裝程式也可以幫你的新專案建立 Git 存放庫。要指定 Laravel 安裝程式建立 Git 存放庫，請在建立新專案時傳入
`--git` 旗標：

```bash
laravel new example-app --git
```

該指令會在你的專案內初始化一個新的 Git 存放庫，並自動簽入 (commit) Laravel 的基礎架構。使用 `git` 旗標時，Laravel
安裝程式會假設你已經有正確安裝並設定了 Git。也可以使用 `--branch` 起標來設定初試的分支名稱：

```bash
laravel new example-app --git --branch="main"
```

除了使用 `--git` 旗標之外，也可以使用 `--github` 起標來建立 Git 存放庫，並同時在 GitHub 上建立相對應的私人存放庫：

```bash
laravel new example-app --github
```

建立好的存放庫將位在 `https://github.com/<你的帳號>/example-app` 內。使用 `github` 旗標時，Laravel 安裝程式會假設你已經安裝了 [GitHub CLI](https://cli.github.com)，且已使用 GitHub 登入。此外，你也應安裝並正確設定 `git`。若有需要，也可以傳入 GitHub CLI 所支援的其他額外旗標：

```bash
laravel new example-app --github="--public"
```

可以使用 `--organization` 旗標來在指定的 GitHub 組織內建立存放庫：

```bash
laravel new example-app --github="--public" --organization="laravel"
```

<a name="initial-configuration"></a>
## 初始化設定

Laravel 框架的所有組態設定檔都儲存在 `config` 目錄內。各個選項都有說明文件，歡迎閱讀這些檔案並熟悉可用的選項。

Laravel 預設幾乎不需要進行額外設定。你現在已經可以開始開發了！不過，可以先看看 `config/app.php`
檔案以及其中的說明。該檔案中包含了一些我們可能需要依據不同專案進行修改的設定選項，如： `timezone` (時區) 以及 `locale` (語系)
等。

<a name="environment-based-configuration"></a>
### 基於環境的組態設定

由於許多的 Laravel 設定選項值都會因為專案執行的環境是本機或正式環境而有所不同，因此許多重要的組態設定值都使用 `.env`
檔案來定義，該檔案位在專案根目錄。

`.env` 檔不應被簽入應用程式的版本控制中，因為每個使用應用程式的開發人員／伺服器都可能需要不同的環境組態。此外，若將 `.env`
檔簽入版本控制的話，當有入侵者取得了版本控制儲存庫的存取權限，就可能會造成安全性風險，因為其中的機敏認證資料都會被暴露。

> {tip} 更多有關 `.env` 檔案以及基於環境的組態設定資訊，請參考完整的[組態設定說明文件](/docs/{{version}}/configuration#environment-configuration)。

<a name="directory-configuration"></a>
### 目錄組態設定

Laravel 只能架設在「網頁目錄」的根目錄下。請不要嘗試將 Laravel
專案架設在「網頁目錄」的子目錄下。若嘗試這麼做可能會將專案的機敏檔案暴露在外。

<a name="next-steps"></a>
## 接下來

現在，你已經建立好 Laravel 專案了，你可能會想知道接下來該學些什麼。首先，我們強烈建議你先閱讀下列說明文件來熟悉一下 Laravel
是怎麼運作的：

<div class="content-list" markdown="1">
- [請求的生命週期](/docs/{{version}}/lifecycle)
- [組態設定](/docs/{{version}}/configuration)
- [資料夾架構](/docs/{{version}}/structure)
- [Service Container](/docs/{{version}}/container)
- [Facade](/docs/{{version}}/facades)
</div>

你想要如何使用 Laravel 也會影響學習的下一步。使用 Laravel 的方法不只一種，我們稍後也會來探索一下幾種使用 Laravel 的主要方法。

<a name="laravel-the-fullstack-framework"></a>
### Laravel - 全端框架

可以將 Laravel 當作全端框架使用。我們說「全端框架」是指你會使用 Laravel 來將請求路由到專案中，並使用 [Blade
樣板](/docs/{{version}}/blade)來轉譯前端界面。或是使用如
[Inertia.js](https://inertiajs.com) 這類的 SPA (單頁面應用程式，Single-Page
Application) 混合技術。這種使用 Laravel 的方法是最常見的。

若你就是這麼打算使用 Laravel
的，則你可能會想看看有關[路由](/docs/{{version}}/routing)、[View](/docs/{{version}}/views)、或
[Eloquent ORM](/docs/{{version}}/eloquent) 的說明文件。此外，你可能也有興趣想了解一下如
[Livewire](https://laravel-livewire.com) 或
[Inertia.js](https://inertiajs.com) 等由社群開發的套件。這些套件能讓你在使用 Laravel
作為全端框架的同時，還能享受到許多由 JavaScript SPA 提供的 UI 的好處。

若你要使用 Laravel 作為全端框架，我們也強烈建議你瞭解一下如何使用 [Laravel Mix](/docs/{{version}}/mix)
來編譯網站的 CSS 與 JavaScript。

> {tip} 若你想要有個起始點可以開始寫網站，請參考看看我們的官方[專案入門套件](/docs/{{version}}/starter-kits)。

<a name="laravel-the-api-backend"></a>
### Laravel - API 後端

也可以將 Laravel 作為 API 後端來提供給 JavaScript SPA 或手機 App 使用。舉例來說，你可以使用 Laravel 作為
[Next.js](https://nextjs.org) App 的 API 後端來使用。在這種情況下，你可以使用 Laravel
來提供[登入驗證](/docs/{{version}}/sanctum)，並為 App 提供資料的儲存、取得功能，同時也能使用到 Laravel
的一些如佇列、E-Mail、通知⋯⋯等強大的功能。

若你打算這樣使用 Laravel，則可以看看有關[路由](/docs/{{version}}/routing)、[Laravel
Sanctum](/docs/{{version}}/sanctum)、以及 [Eloquent
ORM](/docs/{{version}}/eloquent) 的說明文件。

> {tip} 需要使用 Laravel 後端與 Next.js 前端的入門 Scaffolding 嗎？Laravel Breeze 提供了 [API Stack](/docs/{{version}}/starter-kits#breeze-and-next) 以及一個 [Next.js 的前端實作](https://github.com/laravel/breeze-next)，能讓你快速上手。

