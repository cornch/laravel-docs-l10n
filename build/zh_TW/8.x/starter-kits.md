---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/157/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 51.04
---

# 入門套件

- [簡介](#introduction)
- [Laravel Breeze](#laravel-breeze)
  - [安裝](#laravel-breeze-installation)
  - [Breeze & Inertia](#breeze-and-inertia)
  - [Breeze & Next.js / API](#breeze-and-next)
  
- [Laravel Jetstream](#laravel-jetstream)

<a name="introduction"></a>

## 簡介

為了讓你在撰寫 Laravel 專案時有個好的開始，我們很高興能為你介紹「登入入門套件」與「專案入門套件」。這些套件包含了讓使用者註冊或登入的路由、Controller、與 View，會幫你自動進行 Scaffold。

雖然我們歡迎你使用這些入門套件，不過這些套件並不是必要的。你可以從全新的 Laravel 安裝開始製作自己的專案。無論如何，我們都知道你會做出很棒的東西！

<a name="laravel-breeze"></a>

## Laravel Breeze

[Laravel Breeze](https://github.com/laravel/breeze) 是一個簡單且最小化實作出所有 Laravel [認證功能](/docs/{{version}}/authentication)的套件，包含登入、註冊、密碼重設、電子郵件認證、以及密碼確認。Laravel Breeze 預設的 View 層是通過簡單的 [Blade 樣板](/docs/{{version}}/blade) 搭配 [Tailwind CSS](https://tailwindcss.com) 提供樣式組合而成的。

對於從頭開始撰寫 Laravel 專案來說，Breeze 提供了一個絕佳的起始點。而且，對於打算通過 [Laravel Livewire](https://laravel-livewire.com) 來提升 Blade 樣板功能的專案來說，Breeze 也是個不錯的選項。

<a name="laravel-breeze-installation"></a>

### 安裝

首先，先[建立一個新的 Laravel 專案](/docs/{{version}}/installation)、設定資料庫、然後執行[資料庫 Migration](/docs/{{version}}/migrations)：

```bash
curl -s https://laravel.build/example-app | bash

cd example-app

php artisan migrate
```
建立好 Laravel 專案後，可以使用 Composer 來安裝 Laravel Breeze：

```bash
composer require laravel/breeze:1.9.2 
```
安裝好 Laravel Breeze 套件後，執行 `breeze:install` Artisan 指令。這個指令會將登入用 View、路由、Controller、以及其他一些資源安裝到專案中。Laravel Breeze 會將其所有程式碼安裝到專案中，因此對於 Breeze 的功能與實作你擁有完整的控制權與可見性。安裝好 Breeze 之後，你需要接著編譯資源，這樣網站才會有 CSS 檔可用：

```nothing
php artisan breeze:install

npm install
npm run dev
php artisan migrate
```
接著，在瀏覽器中打開網站的 `/login` 或 `/register` 網址。Breeze 中所有的路由都定義在 `routes/auth.php` 中。

> [!TIP]  
> To learn more about compiling your application's CSS and JavaScript, check out the [Laravel Mix documentation](/docs/{{version}}/mix#running-mix).

<a name="breeze-and-inertia"></a>

### Breeze & Inertia

Laravel Breeze 也提供了由 Vue 或 React 驅動的 [Inertia.js](https://inertiajs.com) 前端實作。若要使用 Inertia Stack，請在執行 `breeze:install` Artisan 指令時將 `vue` 或 `react` 指定為你想要的 Stack：

```nothing
php artisan breeze:install vue

// Or...

php artisan breeze:install react

npm install
npm run dev
php artisan migrate
```
<a name="breeze-and-next"></a>

### Breeze & Next.js / API

Laravel Breeze can also scaffold an authentication API that is ready to authenticate modern JavaScript applications such as those powered by [Next](https://nextjs.org), [Nuxt](https://nuxt.com), and others. To get started, specify the `api` stack as your desired stack when executing the `breeze:install` Artisan command:

```nothing
php artisan breeze:install api

php artisan migrate
```
在安裝時，Breeze 也會在專案的 `.env` 檔案中新增一個 `FRONTEND_URL` 環境變數。這個網址就是 JavaScript 程式的網址。通常來說，在開發期間，這個網址會是 `http://localhost:3000`。

<a name="next-reference-implementation"></a>

#### Next.js 參考實作

最後，我們已經準備好可以將這個後端與你的前端組合起來了。我們[在 GitHub 上提供了](https://github.com/laravel/breeze-next)一個作為 Breeze 前端的 Next 參考實作。這個前端是由 Laravel 維護的，其中包含了與 Breeze 提供的傳統 Blade 與 Inertia Stack 中相同的使用者界面。

<a name="laravel-jetstream"></a>

## Laravel Jetstream

雖然 Laravel Breeze 提供了一個簡單起始點能讓你開始製作 Laravel 專案，但 Jetstream 提供了更多的功能，其中包含了強健的功能與額外的前端技術 Stack。**對於剛開始使用 Laravel 的新手，我們建議先了解一下 Laravel Breeze 的使用方式，再來學習 Laravel Jetstream。**

Jetstream 為 Laravel 提供了一個設計的很好看的網站 Scaffolding，並包含了登入、註冊、E-Mail 認證、二步驟認證、工作階段管理、通過 Laravel Sanctum 提供的 API 支援、以及一個可選的團隊管理功能。Jetstream 使用 [Tailwind CSS](https://tailwindcss.com) 設計，並提供了 [Livewire](https://laravel-livewire.com) 與 [Inertia.js](https://inertiajs.com) 作為前端 Scaffolding 的選項。

Complete documentation for installing Laravel Jetstream can be found within the [official Jetstream documentation](https://jetstream.laravel.com/introduction.html).
