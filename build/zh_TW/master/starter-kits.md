---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/157/en-zhtw
progress: 79
updatedAt: '2024-06-30T08:27:00Z'
---

# 入門套件

- [簡介](#introduction)
- [Laravel Breeze](#laravel-breeze)
   - [安裝](#laravel-breeze-installation)
   - [Breeze 與 Blade](#breeze-and-blade)
   - [Breeze & React / Vue](#breeze-and-inertia)
   - [Breeze & Next.js / API](#breeze-and-next)
- [Laravel Jetstream](#laravel-jetstream)

<a name="introduction"></a>

## 簡介

為了讓你在撰寫 Laravel 專案時有個好的開始，我們很高興能為你介紹「登入入門套件」與「專案入門套件」。這些套件包含了讓使用者註冊或登入的路由、Controller、與 View，會幫你自動進行 Scaffold。

雖然我們歡迎你使用這些入門套件，不過這些套件並不是必要的。你可以從全新的 Laravel 安裝開始製作自己的專案。無論如何，我們都知道你會做出很棒的東西！

<a name="laravel-breeze"></a>

## Laravel Breeze

[Laravel Breeze](https://github.com/laravel/breeze) 是一個簡單且最小化實作出所有 Laravel [認證功能](/docs/{{version}}/authentication)的套件，包含登入、註冊、密碼重設、電子郵件認證、以及密碼確認。此外，Breeze 中也包含了一個簡單的「個人檔案」頁面，在該頁面中，使用者可以更新其名稱、E-Mail 位址、以及密碼。

Laravel Breeze 的預設 View 層是由 [Blade 樣板](/docs/{{version}}/blade)構成的，並使用 [Tailwind CSS](https://tailwindcss.com)。或者，Breeze 也可以使用 Vue 或 React 以及 [Inertia](https://inertiajs.com) 來 Scaffold 你的專案。

對於從頭開始撰寫 Laravel 專案來說，Breeze 提供了一個絕佳的起始點。而且，對於打算通過 [Laravel Livewire](https://laravel-livewire.com) 來提升 Blade 樣板功能的專案來說，Breeze 也是個不錯的選項。

<img src="https://laravel.com/img/docs/breeze-register.png">

#### Laravel Bootcamp

如果你第一次接觸 Laravel，歡迎參考 [Laravel Bootcamp (英語)](https://bootcamp.laravel.com)。Laravel Bootcamp 會帶領你使用 Breeze 來建立你的第一個 Laravel 專案。Laravel Bootcamp 是學習各種有關 Laravel 與 Breeze 相關技術的好地方。

<a name="laravel-breeze-installation"></a>

### 安裝

首先，請先[建立一個新的 Laravel 專案](/docs/{{version}}/installation)，設定資料庫，執行[資料庫 Migration](/docs/{{version}}/migrations)。建立好新的 Laravel 專案後，可以使用 Composer 來安裝 Laravel Breeze：

```shell
composer require laravel/breeze --dev
```

安裝好 Breeze 後，就可以使用本文件下方討論的其中一個 Breeze「Stack」來 Scaffold 你的專案。

<a name="breeze-and-blade"></a>

### Breeze 與 Blade

安裝好 Laravel Breeze 套件後，執行 `breeze:install` Artisan 指令。這個指令會將登入用 View、路由、Controller、以及其他一些資源安裝到專案中。Laravel Breeze 會將其所有程式碼安裝到專案中，因此對於 Breeze 的功能與實作你擁有完整的控制權與可見性。

預設的 Breeze「Stack」是 Blade Stack。Blade Stack 使用了簡單的 [Blade 樣板](/docs/{{version}}/blade) 來轉譯專案的前端。只要不加任何參數地叫用 `breeze:install`，就可以安裝 Blade Stack。Breeze 的 Scaffolding 安裝好後，就可以編譯專案的前端資源：

```shell
php artisan breeze:install

php artisan migrate
npm install
npm run dev
```

接著，在瀏覽器中打開網站的 `/login` 或 `/register` 網址。Breeze 中所有的路由都定義在 `routes/auth.php` 中。

<a name="dark-mode"></a>

#### 深色模式

若想在 Scaffold 專案前端時讓 Breeze 包含「深色模式」的支援，只要在執行 `breeze:install` 指令時加上 `--dark` 指示詞即可：

```shell
php artisan breeze:install --dark
```

> **Note** 要瞭解更多有關如何編譯網站的 CSS 與 JavaScript 的資訊，請參考 Laravel 的 [Vite 說明文件](/docs/{{version}}/mix#running-mix)。

<a name="breeze-and-inertia"></a>

### Breeze & React / Vue

Laravel Breeze 也提供了使用 [Inertia](https://inertiajs.com) 前端實作的 React 與 Vue Scaffolding。使用 Inertia，我們就能使用傳統的 Server-Side Routing 與 Controller 來製作現代、單一頁面的 React 或 Vue 網站。

使用 Inertia，我們就可以享受 React 與 Vue 所提供的強大前端，並搭配使用生產效率高的 Laravel 後端，以及超快速的 [Vite](https://vitejs.dev) 編譯。若要使用 Inertia Stack，請在執行 `breeze:install` Artisan 指令時將 `vue` 或 `react` 指定為要使用的 Stack。安裝好 Breeze 的 Scaffolding 後，也請編譯專案的前端資源：

```shell
php artisan breeze:install vue

# 或是...

php artisan breeze:install react

php artisan migrate
npm install
npm run dev
```

接著，在瀏覽器中打開網站的 `/login` 或 `/register` 網址。Breeze 中所有的路由都定義在 `routes/auth.php` 中。

<a name="server-side-rendering"></a>

#### 伺服器端轉譯

若想讓 Breeze 在 Scaffold 時加上對 [Inertia SSR](https://inertiajs.com/server-side-rendering) 的支援，可在叫用 `breeze:install` 指令時提供 `ssr` 選項：

```shell
php artisan breeze:install vue --ssr
php artisan breeze:install react --ssr
```

<a name="breeze-and-next"></a>

### Breeze & Next.js / API

Breeze 也可以用來 Scaffold 登入 API，以用於讓如 [Next](https://nextjs.org)、[Nuxt](https://nuxtjs.org)、或其他框架驅動的現代 JavaScript 網站進行登入認證。若要開始使用登入 API，請在執行 `breeze:install` Artisan 指令時指定 `api` 作為你想要的 Stack：

```shell
php artisan breeze:install api

php artisan migrate
```

在安裝過程中，Breeze 會在專案的 `.env` 檔中新增一個 `FRONTEND_URL` 環境變數。這個 URL 應為 JavaScript App 的 URL。在開發時，通常為 `http://localhost:3000`。此外，也應確認一下 `APP_URL` 是否為 `http://localhost:8000`，該網址就是 `serve` Artisan 指令的預設 URL。

<a name="next-reference-implementation"></a>

#### Next.js 參考實作

最後，我們已經準備好可以將這個後端與你的前端組合起來了。我們[在 GitHub 上提供了](https://github.com/laravel/breeze-next)一個作為 Breeze 前端的 Next 參考實作。這個前端是由 Laravel 維護的，其中包含了與 Breeze 提供的傳統 Blade 與 Inertia Stack 中相同的使用者界面。

<a name="laravel-jetstream"></a>

## Laravel Jetstream

雖然 Laravel Breeze 提供了一個簡單起始點能讓你開始製作 Laravel 專案，但 Jetstream 提供了更多的功能，其中包含了強健的功能與額外的前端技術 Stack。**對於剛開始使用 Laravel 的新手，我們建議先了解一下 Laravel Breeze 的使用方式，再來學習 Laravel Jetstream。**

Jetstream 為 Laravel 提供了一個設計的很好看的網站 Scaffolding，並包含了登入、註冊、E-Mail 認證、二步驟認證、工作階段管理、通過 Laravel Sanctum 提供的 API 支援、以及一個可選的團隊管理功能。Jetstream 使用 [Tailwind CSS](https://tailwindcss.com) 設計，並提供了 [Livewire](https://laravel-livewire.com) 與 [Inertia](https://inertiajs.com) 作為前端 Scaffolding 的選項。

Complete documentation for installing Laravel Jetstream can be found within the [official Jetstream documentation](https://jetstream.laravel.com).
