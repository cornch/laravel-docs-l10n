---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/176/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:27:00Z'
---

# 前端

- [簡介](#introduction)
- [使用 PHP](#using-php)
   - [PHP 與 Blade](#php-and-blade)
   - [Livewire](#livewire)
   - [入門套件](#php-starter-kits)
- [使用 Vue 或 React](#using-vue-react)
   - [Inertia](#inertia)
   - [入門套件](#inertia-starter-kits)
- [打包資源](#bundling-assets)

<a name="introduction"></a>

## 簡介

Laravel 是一個後端框架，提供了用來製作現代網頁 App 所需的所有功能。例如 [路由](/docs/{{version}}/routing)、[表單驗證](/docs/{{version}}/validation)、[快取](/docs/{{version}}/cache)、[佇列](/docs/{{version}}/queues)、[檔案儲存](/docs/{{version}}/filesystem)……等。不過，我們相信，為開發者提供美好的全端開發經驗是很重要的。所謂提供美好的全端開發經驗也包含要提供一種強大的方法讓開發者製作網站前端。

在使用 Laravel 製作網站時，主要有兩種的前端開發方式。至於要選擇哪種方式，則取決於你是要使用 PHP 還是使用像 Vue 或 React 這樣的 JavaScript 框架來製作前端。我們稍後會來討論這幾種方式，好讓你可以決定哪種前端開發方式最適合。

<a name="using-php"></a>

## 使用 PHP

<a name="php-and-blade"></a>

### PHP 與 Blade

以前，大多數的 PHP 網站會在 Request 中使用簡單的 HTML 樣板，並在中間穿插一些 PHP 的 `echo` 陳述式來轉譯從資料庫中取出的資料，最終轉譯出 HTML 給瀏覽器：

```blade
<div>
    <?php foreach ($users as $user): ?>
        Hello, <?php echo $user->name; ?> <br />
    <?php endforeach; ?>
</div>
```

在 Laravel 中，我們還是可以通過 [View] 與 [Blade] 做到這種轉譯 HTML 的方法。Blade 是一種極為輕巧的樣板語言，讓我們能方便地使用簡短的語法來顯示資料、迭代資料……等：

```blade
<div>
    @foreach ($users as $user)
        Hello, {{ $user->name }} <br />
    @endforeach
</div>
```

使用這種方式製作網站時，若進行送出表單或其他的網頁互動，通常會從伺服器端收到一份全新的 HTML 文件，然後瀏覽器會重新轉譯這份 HTML。就算到了今天，使用簡單的 Blade 樣板來製作前端依然適用於許多的網站。

<a name="growing-expectations"></a>

#### 持續增加的期待

不過，使用者對於 Web App 的期待增加了，而許多開發人員也注意到必須製作互動起來更精緻的動態前端。因此，有些開發人員選擇使用如 Vue 或 React 這樣的 JavaScript 框架來製作網頁前端。

而其他想繼續使用慣用後端語言的開發者則開發出了一些解決方案，能讓我們在製作現代化 Web APP 的 UI 時，使用我們想用的後端語言來完成大部分的工作。舉例來說，在 [Rails](https://rubyonrails.org/) 生態圈中，就有如 [Turbo](https://turbo.hotwired.dev/) [Hotwire](https://hotwired.dev/) 或 [Stimulus](https://stimulus.hotwired.dev/) 等的套件。

在 Laravel 的生態圈中，因為想使用 PHP 作為主要語言來製作現代化、動態的前端，因此就誕生了 [Laravel Livewire](https://laravel-livewire.com) 與 [Alpine.js](https://alpinejs.dev/)。

<a name="livewire"></a>

### Livewire

[Laravel Livewire](https://laravel-livewire.com) 是一個框架，可以通過 Laravel 來製作前端，而且用 Livewire 製作出來的前端非常動態、現代化，就像是用 Vue 或 React 等現代 JavaScript 框架製作出來的一樣。

使用 Livewire 時，我們可以建立一個 Livewire 的「元件」來轉譯 UI 中的某個抽象部分，並暴露 (Expose) 出一些網站前端中可叫用或互動的方法或資料。舉例來說，下面是一個簡單的「^[Counter](計數器)」元件：

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Counter extends Component
{
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return view('livewire.counter');
    }
}
```

接著，這個 Counter 對應的樣板可以像這樣寫：

```blade
<div>
    <button wire:click="increment">+</button>
    <h1>{{ $count }}</h1>
</div>
```

就像這樣，使用 Livewire，就可以讓我們使用像 `wire:click` 這樣的新 HTML 屬性來將網站前端與 Laravel 後端連結起來。而且，我們只要使用簡單的 Blade 運算式，就能將元件目前的狀態轉譯出來。

對許多人來說，Livewire 徹底改變了 Laravel 的前端開發，讓我們能待在舒服的 Laravel 內製作現代化的動態 Web App。一般來說，使用 Livewire 的開發人員也會使用 [Alpine.js](https://alpinejs.dev/) 來在一些真的有需要用到 JavaScript 的地方「點綴」上一點 JavaScript，例如要轉譯對話方塊視窗等。

若讀者是 Laravel 初學者，我們建議可以先熟悉 [View](/docs/{{version}}/views) 與 [Blade](/docs/{{version}}/blade) 的基礎用法。然後，請參考官方的 [Laravel Livewire 說明文件](https://laravel-livewire.com/docs)來瞭解如何使用可互動的 Livewire 元件讓你的網站更好用。

<a name="php-starter-kits"></a>

### 入門套件

若想使用 PHP 與 Livewire 來製作前端，可參考我們的 Breeze 與 Jetstream [入門套件](/docs/{{version}}/starter-kits)以快速開始開發網站。這兩個入門套件都使用 [Blade] 與 [Tailwind] 來 Scaffold 網站的前後端登入流程，這樣一來你就能直接開始製作你的 Idea。

<a name="using-vue-react"></a>

## 使用 Vue 或 React

雖然，也是可以使用 Laravel 與 Livewire 來製作現代化的前端，但許多開發者還是偏好使用如 Vue 或 React 等的 JavaScript 框架。這樣，開發者就能享受到 NPM 生態圈上眾多的 JavaScript 套件與工具。

不過，若沒有額外工具，要把 Laravel 跟 Vue 或 React 搭配在一起使用會需要處理各種複雜的問題。如在用戶端進行路由、填入資料 (Hydration)、身份認證等。使用一些如 [Nuxt](https://nuxtjs.org/) 或 [Next](https://nextjs.org/) 等常用的 Vue / React 框架通常可簡化用戶端路由。不過，要讓這些前端框架與 Laravel 這樣的後端框架搭配使用時，要填入資料或是身份認證等的問題還是一樣複雜而且是個棘手的問題。

而且，一些開發者最後還必須維護兩個分開的 Repository，而且還常常需要在兩個 Repository 間互相協調其維護、Release、或開發。但這些問題並不是無解的，我們認為這種開發網站的方式並不是一種有效率且可享受的方法。

<a name="inertia"></a>

### Inertia

幸好，Laravel 為前後端都提供了最好的解決方案。使用 [Inertia](https://inertiajs.com) ，就能將 Laravel 程式與你的現代化 Vue 或 React 前端連結起來，讓我們能在使用供 Laravel 來路由到 Controller、填充資料、進行身份認證等的同時，還能使用 Vue 或 React 來製作成熟的現代化前端。用這種方式的話，我們就能同時享受到 Laravel 與 Vue / React 的完整功能，而不前後端框架的任何功能妥協。

將 Inertia 安裝到 Laravel 專案後，我們就可以像平常一樣寫 Route 與 Controller。不過，在這裡我們不在 Controller 內回傳 Blade 樣板，而是回傳 Inertia 頁面：

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Show the profile for a given user.
     */
    public function show(string $id): Response
    {
        return Inertia::render('Users/Profile', [
            'user' => User::findOrFail($id)
        ]);
    }
}
```

Inertia 頁面對應到 Vue 或 React 元件，這些元件通常存放在專案的 `resources/js/Pages` 目錄下。使用 `Inertia::render` 方法傳給頁面的資料會用來填入該頁面元件中的「^[props](屬性)」：

```vue
<script setup>
import Layout from '@/Layouts/Authenticated.vue';
import { Head } from '@inertiajs/inertia-vue3';

const props = defineProps(['user']);
</script>

<template>
    <Head title="User Profile" />

    <Layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Profile
            </h2>
        </template>

        <div class="py-12">
            Hello, {{ user.name }}
        </div>
    </Layout>
</template>
```

就像這樣，因為 Inertia 在 Laravel 後端與 JavaScript 前端間搭起了一座輕量的橋樑，因此我們在製作前端時就能享受到 Vue 與 React 的完整功能。

#### 伺服器端轉譯

若因為網站需要伺服器端轉譯而擔心無法使用 Inertia 的話，請別擔心。Inertia 有提供[伺服器端轉譯支援](https://inertiajs.com/server-side-rendering)。而且，在使用 [Laravel Forge](https://forge.laravel.com) 部署網站時，要確保 Inertia 的 SSR 處理程序有持續執行就跟呼吸一樣輕鬆。

<a name="inertia-starter-kits"></a>

### 入門套件

若想使用 Vue / React 來製作前端，可參考我們的 Breeze 與 Jetstream [入門套件](/docs/{{version}}/starter-kits#breeze-and-inertia)以快速開始開發網站。這兩個入門套件都使用 Inertia、Vue / React、[Tailwind](https://tailwindcss.com)、與 [Vite](https://vitejs.dev) 來 Scaffold 網站的前後端登入流程，這樣一來你就能直接開始製作你的 Idea。

<a name="bundling-assets"></a>

## 打包資源

無論讀者選擇使用 Blade 與 Livewire 來開發前端，還是使用 Vue / React 與 Inertia，通常都需要將專案的 CSS 打包成可在線上環境使用的素材。當然，若讀者選擇使用 Vue 或 React 來製作網站前端，那麼還需要將這些元件打包成瀏覽器可使用的 JavaScript 素材。

預設情況下，Laravel 使用 [Vite](https://vitejs.dev) 來打包資源。Vite 在開發環境下提供了快速的建置，以及幾乎即時的 HMR (熱模組取代，Hot Module Replacement)。包含使用我們的[入門套件](/docs/{{version}}/starter-kits)在內，所有新安裝的 Laravel 專案中都有個 `vite.config.js` 檔案，其中載入了我們的輕型 Laravel Vite 外掛。Laravel 的 Vite 外掛能讓我們非常容易的將 Vite 搭配 Laravel 專案使用。

要開始使用 Laravel 與 Vite，最快的方法就是使用 [Laravel Breeze](/docs/{{version}}/starter-kits#laravel-breeze) 來開始開發專案。Laravel Breeze 是我們最簡單的入門套件，提供了身份認證前後端 Scaffolding，能讓我們直接開始開發專案。

> **Note** 更多在 Laravel 中使用 Vite 的詳細說明文件，請參考有關[打包與編譯素材的說明文件](/docs/{{version}}/vite)。
