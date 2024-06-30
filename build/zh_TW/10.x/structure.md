---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/159/en-zhtw
progress: 100
updatedAt: '2023-02-11T10:28:00Z'
---

# 目錄結構

- [簡介](#introduction)
- [Root 目錄](#the-root-directory)
   - [`app` 目錄](#the-root-app-directory)
   - [`bootstrap` 目錄](#the-bootstrap-directory)
   - [`config` 目錄](#the-config-directory)
   - [`database` 目錄](#the-database-directory)
   - [`public` 目錄](#the-public-directory)
   - [`resources` 目錄](#the-resources-directory)
   - [`routes` 目錄](#the-routes-directory)
   - [`storage` 目錄](#the-storage-directory)
   - [`tests` 目錄](#the-tests-directory)
   - [`vendor` 目錄](#the-vendor-directory)
- [App 目錄](#the-app-directory)
   - [`Broadcasting` 目錄](#the-broadcasting-directory)
   - [`Console` 目錄](#the-console-directory)
   - [`Events` 目錄](#the-events-directory)
   - [`Exceptions` 目錄](#the-exceptions-directory)
   - [`Http` 目錄](#the-http-directory)
   - [`Jobs` 目錄](#the-jobs-directory)
   - [`Listeners` 目錄](#the-listeners-directory)
   - [`Mail` 目錄](#the-mail-directory)
   - [`Models` 目錄](#the-models-directory)
   - [`Notifications` 目錄](#the-notifications-directory)
   - [`Policies` 目錄](#the-policies-directory)
   - [`Providers` 目錄](#the-providers-directory)
   - [`Rules` 目錄](#the-rules-directory)

<a name="introduction"></a>

## 簡介

不論專案大小，Laravel 預設的目錄結構都可提供一個不錯的起始點。不過，你也可以隨意調整要如何整理你的專案。Laravel 幾乎不限制 Class 要放在哪裡 —— 只要 Composer 可以自動載入 (Autoload) 該 Class 即可。

> **Note** 是 Laravel 新手嗎？請參考 [Laravel Bootcamp (英語)](https://bootcamp.laravel.com) 來瞭解 Laravel 框架，同時我們也會帶領你建立你的第一個 Laravel 專案。

<a name="the-root-directory"></a>

## 根目錄

<a name="the-root-app-directory"></a>

#### App 目錄

`app` 目錄包含了專案的核心程式碼。我們稍後會更詳細瞭解這個資料夾。不過，專案中幾乎所有的類別都會被放在這個目錄中。

<a name="the-bootstrap-directory"></a>

#### Bootstrap 目錄

`bootstrap` 目錄中包含了啟動框架所需要的 `app.php` 檔案。這個資料夾中也會有一個 `cache` 目錄，該目錄中會包含 Laravel 為了最佳化效能而產生的檔案，如路由與服務的快取檔案。通常來說，不應該去修改這個資料夾中的任何檔案。

<a name="the-config-directory"></a>

#### Config 目錄

`config` 目錄，如同目錄名字所示，包含了專案的所有設定檔。最好可以閱讀一下這些檔案，並熟悉一下有什麼選項可以設定。

<a name="the-database-directory"></a>

#### Database 目錄

`database` 目錄中包含了所有的資料庫遷移檔 (Migration)、Model Factory、以及 Seed。若你想的話，也可以使用這個目錄來存放 SQLite 資料庫。

<a name="the-public-directory"></a>

#### Public 目錄

`public` 目錄包含了 `index.php` 檔案。該檔案是所有請求進入專案時的進入點，也設定了 Autoload。該目錄中也用來放置所有素材，如圖片、JavaScript、與 CSS。

<a name="the-resources-directory"></a>

#### Resources 目錄

`resources` 目錄中包含了 [View](/docs/{{version}}/views) 以及原始未編譯的素材，如 CSS 或 JavaScript。

<a name="the-routes-directory"></a>

#### Routes 目錄

`routes` 目錄包含了專案的所有路由定義。預設情況下，Laravel 包含了幾個路由檔：`web.php`, `api.php`, `console.php`, 與 `channels.php`。

`RouteServiceProvider` 會將 `web.php` 中的路由放在 `web` Middleware 群組中。`web` Middleware 群組提供了如 Session 狀態、CSRF 保護、以及 Cookie 加密等功能。若你的專案不使用 stateless (無狀態) 的 RESTful API，則一般來說所有的路由應該都會放在 `web.php` 檔案中。

`RouteServiceProvider` 會將 `api.php` 中的路由放在 `api` Middleware 群組中。這些路由是給 Stateless 的請求用的，所以通過這些路由進入網站的請求應[使用 Token](/docs/{{version}}/sanctum)來進行登入認證，且將無法存取 Session 狀態。

`console.php` 檔案用來定義所有基於閉包的主控台指令。所有的閉包都有繫結一個指令實體，使用這個指令實體就能輕鬆地與各個指令的 IO 方法互動。雖然這個檔案並不是定義 HTTP 路由，不過它定義了以主控台作為進入點進入專案的路由。

`channels.php` 檔案用來註冊所有專案要使用的[事件廣播](/docs/{{version}}/broadcasting)頻道。

<a name="the-storage-directory"></a>

#### Storage 目錄

`storage` 目錄包含了日誌檔、編譯過的 Blade 樣板、基於檔案的 Session、檔案快取、以及其他由 Laravel 所產生的檔案。該目錄還進一步地分成 `app`, `framework`, 以及 `logs` 目錄。`app` 目錄可用來保存所有由網站產生的檔案。`framework` 目錄用來儲存由 Laravel 產生的檔案與快取檔。最後，`logs` 目錄包含了專案的所有日誌檔。

`storage/app/public` 目錄可用來儲存使用者產生的檔案，如個人資料頭像等應可公開存取的檔案。開發人員應建立一個 `public/storage` 符號連結 (Symbolic Link) 連結到這個目錄。可以使用 `php artisan storage:link` Artisan 指令來建立這個連結。

<a name="the-tests-directory"></a>

#### Tests 目錄

`tests` 目錄包含了自動化測試。Laravel 預設包含了範例的 [PHPUnit](https://phpunit.de/) 單元測試與功能測試。每個測試類別都應以 `Test` 結尾。可以使用 `phpunit` 或 `php vendor/bin/phpunit` 指令來執行測試。或者，若想使用更詳細且好看的測試結果輸出，可以使用 `php artisan test` Artisan 指令。

<a name="the-vendor-directory"></a>

#### Vendor 目錄

`vendor` 目錄包含了 [Composer](https://getcomposer.org) 相依性套件。

<a name="the-app-directory"></a>

## App 目錄

在你的專案中，大多數的程式碼都會放在 `app` 資料夾中。預設情況下，該資料夾是放在 `App` Namespace 下的，且會由 Composer 依照 [PSR-4 Autoloading 標準] Autoload。

`app` 目錄中包含了多個額外的目錄，如 `Console`, `Http`, 與 `Providers`。可以將 `Console` 與 `Http` 目錄想成是用來進入專案核心的 API。HTTP 協定與 CLI 是兩種與專案互動的機制，而這種機制本身則不包含應用上的邏輯。換句話說，你的專案有兩種操作方法。`Console` 目錄包含了所有 Artisan 指令，而 `Http` 目錄則包含了 Controller、Middleware、以及 Request。

其他的資料夾則會在使用 `make` Artisan 指令產生類別時產生在 `app` 目錄下。舉例來說，執行了 `make:job` Artisan 指令來產生 Job 類別後，才會產生 `app/Jobs` 目錄。

> **Note** `app` 目錄內的許多類別都可以通過指令來由 Artisan 產生。若要檢視所有可用的指令，請在終端機中執行 `php artisan list make` 指令。

<a name="the-broadcasting-directory"></a>

#### Broadcasting 目錄

`Broadcasting` 目錄包含了所有專案使用的廣播頻道類別。這些類別是使用 `make:channel` 指令產生的。預設情況下不會有這個目錄，不過在你產生第一個頻道後會自動建立。要瞭解更多有關頻道的資訊，請參考有關[事件廣播](/docs/{{version}}/broadcasting)的說明文件。

<a name="the-console-directory"></a>

#### Console 目錄

`Console` 目錄包含所有專案使用的自訂 Artisan 指令。這些指令可以通過 `make:command` 指令來產生。這個資料夾中也包含了主控台核心 (Kernel)，主控台核心可用來註冊自訂 Artisan指令，並定義[排程任務](/docs/{{version}}/scheduling)。

<a name="the-events-directory"></a>

#### Events 目錄

預設情況下不會有這個資料夾，不過在使用 `event:generate` 與 `make:event` Artisan 指令就會幫你建立。`Events` 目錄內存放了 [Event 類別](/docs/{{version}}/events)。可使用 Event 來告知專案中的其他部分：「某個動作發生了」；使用 Event 也提供了不錯的延展性與解耦合。

<a name="the-exceptions-directory"></a>

#### Exceptions 目錄

`Exceptions` 目錄包含了專案的例外處理常式 (Exception Handler)，這個目錄也適合用來放置所有專案中會擲回的例外。若想客製化例外被紀錄在日誌中的方式、或是客製化例外轉譯的方式，可修改該目錄中的 `Handler` 類別。

<a name="the-http-directory"></a>

#### Http 目錄

`Http` 目錄包含了 Controller, Middleware, 以及 Form Request。幾乎所有用來處理請求進入專案的邏輯都會放在這個資料夾內。

<a name="the-jobs-directory"></a>

#### Jobs 目錄

預設情況下不會有這個目錄，不會在執行 `make:job` Artisan 指令後會自動建立。`Jobs` 目錄用來存放所有專案的[可佇列任務](/docs/{{version}}/queues)。「任務 (Job)」可以由專案放入佇列，或是在目前的請求生命週期中同步執行。在目前請求中同步執行的任務有時候也稱為「指令 (Command)」，因為這種作法是一種[命令模式](https://zh.wikipedia.org/zh-tw/%E5%91%BD%E4%BB%A4%E6%A8%A1%E5%BC%8F)的實作。

<a name="the-listeners-directory"></a>

#### Listeners 目錄

預設情況下不會有這個目錄，不過會在執行 `event:generate` 或 `make:listener` Artisan 指令後自動建立。`Listeners` 目錄中包含了用來處理[事件](/docs/{{version}}/events)的類別。事件監聽程式 (Event Listener) 會接收事件實體並執行一段邏輯，以回應發出的事件。舉例來說，`UserRegistered` 事件可能會由一個 `SendWelcomeEmail` 監聽程式進行處理。

<a name="the-mail-directory"></a>

#### Mail 目錄

預設情況下沒有這個目錄，不過，在執行 `make:mail` Artisan 指令後會自動建立。`Mail` 目錄包含了所有[代表了 E-Mail 的類別](/docs/{{version}}/mail)，專案可以寄出這些 E-Mail。使用 Mail 物件就可以將所有建立 E-Mail 的邏輯封裝到單一、簡單的類別，然後用 `Mail::send` 方法就可以寄出。

<a name="the-models-directory"></a>

#### Models 目錄

`Models` 目錄包含了所有的 [Eloquent Model 類別](/docs/{{version}}/eloquent)。Laravel 中內建的 Eloquent ORM 提供了一個美觀、簡易的 ActiveRecord 實作，可用來操作資料庫。資料庫中每個資料表都有一個對應的「Model」，可用來與這個資料表互動。使用 Model 就可以從資料表中取得資料，以及可將新資料插入到資料表中。

<a name="the-notifications-directory"></a>

#### Notifications 目錄

預設情況下沒有這個目錄，不過會在執行 `make:notification` Artisan 指令後自動建立。`Notifications` 目錄包含了所有由專案寄出的「交易式 (Transactional)」[通知](/docs/{{version}}/notifications)，如：在專案中發生事件的簡易通知。Laravel 的通知功能抽象化了各種不同後端的寄送功能，如：E-Mail、Slack、簡訊、或是儲存在資料庫中。

<a name="the-policies-directory"></a>

#### Policies 目錄

預設情況下不會有這個目錄，不過會在執行 `make:policy` Artisan 指令後自動建立。`Policies` 目錄包含了用於專案的[授權原則類別](/docs/{{version}}/authorization)。原則 (Policy) 可用來判斷某個使用者是否能對特定資源進行給定的動作。

<a name="the-providers-directory"></a>

#### Providers 目錄

`Providers` 目錄包含了專案中的所有 [Service Providers](/docs/{{version}}/providers)。Service Provider 會向 Service Container 繫結服務、註冊事件、或是進行其他任何行為來為專案準備連入請求⋯⋯等，以啟動專案。

在全新安裝的 Laravel 專案中，這個目錄內已經包含了數個 Provider。你可以依照需求隨意在這個目錄內新增你自己的 Provider。

<a name="the-rules-directory"></a>

#### Rules 目錄

預設情況下不會有這個目錄，不過在執行 `make:rule` Artisan 指令後會自動建立。`Rules` 目錄包含了用於專案的自訂驗證規則。Rule 是用來將複雜的驗證邏輯封裝在一個簡單的物件中的。有關更多資訊，請參考[表單驗證說明文件](/docs/{{version}}/validation)。
