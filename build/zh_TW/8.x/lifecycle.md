---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/93/en-zhtw'
updatedAt: '2023-02-11T10:27:00Z'
contributors: {  }
progress: 67.24
---

# Request 的生命週期

- [簡介](#introduction)
- [生命週期概覽](#lifecycle-overview)
  - [第一步](#first-steps)
  - [HTTP Kernel 與 Console Kernel](#http-console-kernels)
  - [Service Provider](#service-providers)
  - [路由](#routing)
  - [結束](#finishing-up)
  
- [專注於 Service Provider](#focus-on-service-providers)

<a name="introduction"></a>

## 簡介

在使用任何「真實世界」的工具時，如果我們能瞭解這個工具是怎麼運作的，通常用起來也會比較有自信。程式開發也是一樣。瞭解開發工具怎麼運作後，我們使用起來便更自在、更有自信。

本片說明文件的目標在於提供讀者對 Laravel 框架如何運作的一個好的、高階的概覽。在更加瞭解 Laravel 之後，便不再會覺得所有東西都是「Magic (魔術)」，我們也能在撰寫程式時更有自信。讀者在閱讀時若遇到不了解的名詞，請先別放棄！請先試著對發生什麼事情有初步的了解，之後在閱讀本說明文件的其他部分時，讀者的知識也會持續增加。

<a name="lifecycle-overview"></a>

## 生命週期概覽

<a name="first-steps"></a>

### 第一步

所有 Request 進入 Laravel 程式的起點都是 `public/index.php` 檔案。所有的 Request 都會通過你的網頁伺服器 (Apache / Nginx) 設定檔被導向到這個檔案內。`index.php` 檔案內沒有包含太多的程式碼。裡面只是用來載入 Laravel 中其他部分的起始點。

`index.php` 檔案會載入由 Composer 產生的 Autoloader 定義，然後接著我們會從 `bootstrap/app.php` 中取得 Laravel 應用程式的實體。真正由 Laravel 自己做的第一個步驟是建立應用程式 / [Service Container](/docs/{{version}}/container) 的實體。

<a name="http-console-kernels"></a>

### HTTP 與 Console 的 Kernel

接著，根據進入應用程式的 Request 種類，所有連入的 Request 不是被送到 HTTP Kernel 就是 Console Kernel。這兩種 Kernel (核心) 是提供所有 Request 流向的中心點。現在，我們先只看 HTTP Kernel。該檔案位在 `app/Http/Kernel.php`。

HTTP Kernel 繼承了 `Illuminate\Foundation\Http\Kernel` 類別，該類別定義了一個 `bootstrappers` 的陣列。這個陣列會在 Request 被執行前回傳。這些 Bootstrappers 分別設定了錯誤處理常式、設定 Log、[偵測應用程式執行的環境](/docs/{{version}}/configuration#environment-configuration)、然後進行一些我們在實際處理 Request 前要進行的其他任務。一般來說，這些類別負責處理一些你不需要擔心的 Laravel 內部設定。

HTTP Kernel 還定義了一個 HTTP [Middleware (中介軟體)](/docs/{{version}}/middleware) 列表。Request 必須要通過這些 Middleware，然後才會被應用程式處理。這些 Middleware 會負責處理讀寫 [HTTP Session](/docs/{{version}}/session)、判斷應用程式是否在維護模式下、[認證 CSRF Token (權杖)](/docs/{{version}}/csrf)⋯⋯等。我們稍後會再討論這些。

HTTP Kernel 中 `handle` 方法的簽名 (Signature) 很簡單：handle 方法接受一個 `Request` 然後回傳 `Response`。可以把這個 Kernel 想像成一個大大的黑盒子，這個黑盒子就代表了你的整個應用程式。我們把 HTTP Request 扔給這個黑盒子，黑盒子就會回傳 HTTP Response 給我們。

<a name="service-providers"></a>

### Service Provider

Kernel 啟動過程中最重要的一部分就是載入應用程式的 [Service Provider](/docs/{{version}}/providers)。應用程式中，所有的 Service Provider 都設定在 `config/app.php` 設定檔的 `providers` 陣列中。

Laravel 會迭代這個 Service Provider 列表，然後將逐一初始化這些 Provider。初始化好 Provider 後，就會呼叫所有 Provider 的 `register` (註冊) 方法。接著，註冊好所有 Provider 後，就會呼叫每個 Provider 的 `boot` (啟動) 方法。這樣一來，在執行 `boot` 方法時，Service Provider 就能相依所有註冊好的 Container Binding (容器繫結)。

Service Provider 還負責啟動 Laravel 中各種框架元件，如資料庫、佇列、認證、路由⋯⋯等。Laravel 提供的所有主要功能都由 Service Provider 進行設定。由於這些 Service Provider 負責啟動與設定 Laravel 框架中很多的功能，因此在 Laravel 啟動過程中，Service Provider 時最重要的一環。

<a name="routing"></a>

### 路由

你的程式中，最重要的一個 Service Provider 就是 `App\Providers\RouteServiceProvider` 了。這個 Service Provider 負責載入專案 `routes` 目錄下的路由檔案。現在就去打開 `RouteServiceProvider` 的程式碼然後看看這個檔案是怎麼運作的吧！

啟動好應用程式且註冊好所有 Service Provider 後，`Request` 就會接著被傳給 Router 來分派 (Dispatch)。Router 會將 Request 分派給一個 Route (路由) 或 Controller (控制器)，並執行任何由 Route 指定的 Middleware。

若要對進入應用程式的 HTTP Request 進行過濾或檢驗，Middleware 提供了一個方便的機制。舉例來說，Laravel 中提供了一個用來認證應用程式使用者是否已登入的 Middleware。若使用者未登入，該 Middleware 會把使用者重新導向到登入畫面。不過，若使用者未登入，則該 Middleware 就會讓 Request 繼續進入應用程式。有的 Middleware 被指派給應用程式內的所有 Route，例如定義在 HTTP Kernel 中 `$middleware` 屬性內的那些 Middleware；有的 Middleware 則只被指派給特定的 Route 或 Route 群組。你可以閱讀完整的 [Middleware 說明文件](/docs/{{version}}/middleware)來瞭解更多有關 Middleware 的資訊。

若 Request 通過了所有 Route 指定的 Middleware，則會執行 Route 或 Controller，並將 Route 或 Controller 回傳的 Response 送回給 Route 的 MIddleware 鏈。

<a name="finishing-up"></a>

### 結束

Route 或 Controller 方法回傳 Response 之後，這個 Response 會再反過來通過 Route 的 Middleware，能讓應用程式有機會對要輸出的 Response 作修改或檢驗。

最後，當 Response 回頭走過所有 Middleware 後，HTTP Kernel 的 `handle` 方法就會回傳 Response 物件，然後 `index.php` 檔案則會呼叫這個 Response 物件上的 `send` 方法。`send` 方法會將 Response 的內容送到使用者的網頁瀏覽器上。到這裡，我們走完整個 Laravel Request 生命週期的旅程了！

<a name="focus-on-service-providers"></a>

## 專注於 Service Provider

Service Provider 真的是啟動 Laravel 應用程式的關鍵。建立應用程式實體，然後註冊 Service Provider、最後再將 Request 交給已啟動的應用程式。真的就只有這麼簡單！

稍微瞭解 Laravel 應用程式是怎麼通過 Service Provider 製作與啟動的非常重要。你的應用程式預設的 Service Provider 就放在 `app/Providers` 目錄下。

預設情況下，`AppServiceProvider` 很空。這個 Provider 是為你的應用程式加上啟動程式以及 Service Container 繫結中的一個絕佳地點。對於大型的應用程式，則可能會想建立多個 Service Provider，每個 Service Provider 負責啟動應用程式中的一個特定服務。
