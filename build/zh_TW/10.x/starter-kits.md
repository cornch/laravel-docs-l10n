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
  - [Breeze and Blade](#breeze-and-blade)
  - [Breeze and Livewire](#breeze-and-livewire)
  - [Breeze and React / Vue](#breeze-and-inertia)
  - [Breeze and Next.js / API](#breeze-and-next)
  
- [Laravel Jetstream](#laravel-jetstream)

<a name="introduction"></a>

## 簡介

為了讓你在撰寫 Laravel 專案時有個好的開始，我們很高興能為你介紹「登入入門套件」與「專案入門套件」。這些套件包含了讓使用者註冊或登入的路由、Controller、與 View，會幫你自動進行 Scaffold。

雖然我們歡迎你使用這些入門套件，不過這些套件並不是必要的。你可以從全新的 Laravel 安裝開始製作自己的專案。無論如何，我們都知道你會做出很棒的東西！

<a name="laravel-breeze"></a>

## Laravel Breeze

[Laravel Breeze](https://github.com/laravel/breeze) 是一個簡單且最小化實作出所有 Laravel [認證功能](/docs/{{version}}/authentication)的套件，包含登入、註冊、密碼重設、電子郵件認證、以及密碼確認。此外，Breeze 中也包含了一個簡單的「個人檔案」頁面，在該頁面中，使用者可以更新其名稱、E-Mail 位址、以及密碼。

Laravel Breeze's default view layer is made up of simple [Blade templates](/docs/{{version}}/blade) styled with [Tailwind CSS](https://tailwindcss.com). Additionally, Breeze provides scaffolding options based on [Livewire](https://livewire.laravel.com) or [Inertia](https://inertiajs.com), with the choice of using Vue or React for the Inertia-based scaffolding.

<img src="https://laravel.com/img/docs/breeze-register.png">
#### Laravel Bootcamp

如果你第一次接觸 Laravel，歡迎參考 [Laravel Bootcamp (英語)](https://bootcamp.laravel.com)。Laravel Bootcamp 會帶領你使用 Breeze 來建立你的第一個 Laravel 專案。Laravel Bootcamp 是學習各種有關 Laravel 與 Breeze 相關技術的好地方。

<a name="laravel-breeze-installation"></a>

### 安裝

首先，請先[建立一個新的 Laravel 專案](/docs/{{version}}/installation)，設定資料庫，執行[資料庫 Migration](/docs/{{version}}/migrations)。建立好新的 Laravel 專案後，可以使用 Composer 來安裝 Laravel Breeze：

```shell
composer require laravel/breeze --dev
```
安裝好 Laravel Breeze 套件後，執行 `breeze:install` Artisan 指令。這個指令會將登入用 View、路由、Controller、以及其他一些資源安裝到專案中。Laravel Breeze 會將其所有程式碼安裝到專案中，因此對於 Breeze 的功能與實作你擁有完整的控制權與可見性。

The `breeze:install` command will prompt you for your preferred frontend stack and testing framework:

```shell
php artisan breeze:install

php artisan migrate
npm install
npm run dev
```
<a name="breeze-and-blade"></a>

### Breeze and Blade

The default Breeze "stack" is the Blade stack, which utilizes simple [Blade templates](/docs/{{version}}/blade) to render your application's frontend. The Blade stack may be installed by invoking the `breeze:install` command with no other additional arguments and selecting the Blade frontend stack. After Breeze's scaffolding is installed, you should also compile your application's frontend assets:

```shell
php artisan breeze:install

php artisan migrate
npm install
npm run dev
```
接著，在瀏覽器中打開網站的 `/login` 或 `/register` 網址。Breeze 中所有的路由都定義在 `routes/auth.php` 中。

> [!NOTE]  
> 要瞭解更多有關如何編譯網站的 CSS 與 JavaScript 的資訊，請參考 Laravel 的 [Vite 說明文件](/docs/{{version}}/mix#running-mix)。

<a name="breeze-and-livewire"></a>

### Breeze and Livewire

Laravel Breeze also offers [Livewire](https://livewire.laravel.com) scaffolding. Livewire is a powerful way of building dynamic, reactive, front-end UIs using just PHP.

Livewire is a great fit for teams that primarily use Blade templates and are looking for a simpler alternative to JavaScript-driven SPA frameworks like Vue and React.

To use the Livewire stack, you may select the Livewire frontend stack when executing the `breeze:install` Artisan command. After Breeze's scaffolding is installed, you should run your database migrations:

```shell
php artisan breeze:install

php artisan migrate
```
<a name="breeze-and-inertia"></a>

### Breeze and React / Vue

Laravel Breeze 也提供了使用 [Inertia](https://inertiajs.com) 前端實作的 React 與 Vue Scaffolding。使用 Inertia，我們就能使用傳統的 Server-Side Routing 與 Controller 來製作現代、單一頁面的 React 或 Vue 網站。

Inertia lets you enjoy the frontend power of React and Vue combined with the incredible backend productivity of Laravel and lightning-fast [Vite](https://vitejs.dev) compilation. To use an Inertia stack, you may select the Vue or React frontend stacks when executing the `breeze:install` Artisan command.

When selecting the Vue or React frontend stack, the Breeze installer will also prompt you to determine if you would like [Inertia SSR](https://inertiajs.com/server-side-rendering) or TypeScript support. After Breeze's scaffolding is installed, you should also compile your application's frontend assets:

```shell
php artisan breeze:install

php artisan migrate
npm install
npm run dev
```
接著，在瀏覽器中打開網站的 `/login` 或 `/register` 網址。Breeze 中所有的路由都定義在 `routes/auth.php` 中。

<a name="breeze-and-next"></a>

### Breeze and Next.js / API

Laravel Breeze can also scaffold an authentication API that is ready to authenticate modern JavaScript applications such as those powered by [Next](https://nextjs.org), [Nuxt](https://nuxt.com), and others. To get started, select the API stack as your desired stack when executing the `breeze:install` Artisan command:

```shell
php artisan breeze:install

php artisan migrate
```
在安裝過程中，Breeze 會在專案的 `.env` 檔中新增一個 `FRONTEND_URL` 環境變數。這個 URL 應為 JavaScript App 的 URL。在開發時，通常為 `http://localhost:3000`。此外，也應確認一下 `APP_URL` 是否為 `http://localhost:8000`，該網址就是 `serve` Artisan 指令的預設 URL。

<a name="next-reference-implementation"></a>

#### Next.js 參考實作

最後，我們已經準備好可以將這個後端與你的前端組合起來了。我們[在 GitHub 上提供了](https://github.com/laravel/breeze-next)一個作為 Breeze 前端的 Next 參考實作。這個前端是由 Laravel 維護的，其中包含了與 Breeze 提供的傳統 Blade 與 Inertia Stack 中相同的使用者界面。

<a name="laravel-jetstream"></a>

## Laravel Jetstream

雖然 Laravel Breeze 提供了一個簡單起始點能讓你開始製作 Laravel 專案，但 Jetstream 提供了更多的功能，其中包含了強健的功能與額外的前端技術 Stack。**對於剛開始使用 Laravel 的新手，我們建議先了解一下 Laravel Breeze 的使用方式，再來學習 Laravel Jetstream。**

Jetstream provides a beautifully designed application scaffolding for Laravel and includes login, registration, email verification, two-factor authentication, session management, API support via Laravel Sanctum, and optional team management. Jetstream is designed using [Tailwind CSS](https://tailwindcss.com) and offers your choice of [Livewire](https://livewire.laravel.com) or [Inertia](https://inertiajs.com) driven frontend scaffolding.

Complete documentation for installing Laravel Jetstream can be found within the [official Jetstream documentation](https://jetstream.laravel.com).
