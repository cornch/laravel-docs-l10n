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
  
- [Focus on Service Providers](#focus-on-service-providers)

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

Next, the incoming request is sent to either the HTTP kernel or the console kernel, using the `handleRequest` or `handleCommand` methods of the application instance, depending on the type of request entering the application. These two kernels serve as the central location through which all requests flow. For now, let's just focus on the HTTP kernel, which is an instance of `Illuminate\Foundation\Http\Kernel`.

The HTTP kernel defines an array of `bootstrappers` that will be run before the request is executed. These bootstrappers configure error handling, configure logging, [detect the application environment](/docs/{{version}}/configuration#environment-configuration), and perform other tasks that need to be done before the request is actually handled. Typically, these classes handle internal Laravel configuration that you do not need to worry about.

The HTTP kernel is also responsible for passing the request through the application's middleware stack. These middleware handle reading and writing the [HTTP session](/docs/{{version}}/session), determining if the application is in maintenance mode, [verifying the CSRF token](/docs/{{version}}/csrf), and more. We'll talk more about these soon.

HTTP Kernel 中 `handle` 方法的簽名 (Signature) 很簡單：handle 方法接受一個 `Request` 然後回傳 `Response`。可以把這個 Kernel 想像成一個大大的黑盒子，這個黑盒子就代表了你的整個應用程式。我們把 HTTP Request 扔給這個黑盒子，黑盒子就會回傳 HTTP Response 給我們。

<a name="service-providers"></a>

### Service Provider

One of the most important kernel bootstrapping actions is loading the [service providers](/docs/{{version}}/providers) for your application. Service providers are responsible for bootstrapping all of the framework's various components, such as the database, queue, validation, and routing components.

Laravel 會迭代這個 Service Provider 列表，然後將逐一初始化這些 Provider。初始化好 Provider 後，就會呼叫所有 Provider 的 `register` (註冊) 方法。接著，註冊好所有 Provider 後，就會呼叫每個 Provider 的 `boot` (啟動) 方法。這樣一來，在執行 `boot` 方法時，Service Provider 就能相依所有註冊好的 Container Binding (容器繫結)。

基本上來說，Laravel 所提供的所有主要功能都是使用 Service Provider 來啟動並設定的。由於 Service Provider 需要啟動並設定框架中許多的功能，因此 Service Provider 是整個 Laravel 啟動過程中最重要的一個部分。

While the framework internally uses dozens of service providers, you also have the option to create your own. You can find a list of the user-defined or third-party service providers that your application is using in the `bootstrap/providers.php` file.

<a name="routing"></a>

### 路由

啟動好應用程式且註冊好所有 Service Provider 後，`Request` 就會接著被傳給 Router 來分派 (Dispatch)。Router 會將 Request 分派給一個 Route (路由) 或 Controller (控制器)，並執行任何由 Route 指定的 Middleware。

Middleware provide a convenient mechanism for filtering or examining HTTP requests entering your application. For example, Laravel includes a middleware that verifies if the user of your application is authenticated. If the user is not authenticated, the middleware will redirect the user to the login screen. However, if the user is authenticated, the middleware will allow the request to proceed further into the application. Some middleware are assigned to all routes within the application, like `PreventRequestsDuringMaintenance`, while some are only assigned to specific routes or route groups. You can learn more about middleware by reading the complete [middleware documentation](/docs/{{version}}/middleware).

若 Request 通過了所有 Route 指定的 Middleware，則會執行 Route 或 Controller，並將 Route 或 Controller 回傳的 Response 送回給 Route 的 MIddleware 鏈。

<a name="finishing-up"></a>

### 結束

Route 或 Controller 方法回傳 Response 之後，這個 Response 會再反過來通過 Route 的 Middleware，能讓應用程式有機會對要輸出的 Response 作修改或檢驗。

Finally, once the response travels back through the middleware, the HTTP kernel's `handle` method returns the response object to the `handleRequest` of the application instance, and this method calls the `send` method on the returned response. The `send` method sends the response content to the user's web browser. We've now completed our journey through the entire Laravel request lifecycle!

<a name="focus-on-service-providers"></a>

## Focus on Service Providers

Service Provider 真的是啟動 Laravel 應用程式的關鍵。建立應用程式實體，然後註冊 Service Provider、最後再將 Request 交給已啟動的應用程式。真的就只有這麼簡單！

Having a firm grasp of how a Laravel application is built and bootstrapped via service providers is very valuable. Your application's user-defined service providers are stored in the `app/Providers` directory.

預設情況下，`AppServiceProvider` 很空。這個 Provider 是為你的應用程式加上啟動程式以及 Service Container 繫結中的一個絕佳地點。對於大型的應用程式，則可能會想建立多個 Service Provider，每個 Service Provider 負責啟動應用程式中的一個特定服務。
