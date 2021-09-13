# 參與貢獻指南

- [Bug 回報](#bug-reports)
- [支援提問](#support-questions)
- [核心開發討論](#core-development-discussion)
- [要用哪個分支？](#which-branch)
- [編譯素材](#compiled-assets)
- [安全性漏洞](#security-vulnerabilities)
- [編碼樣式](#coding-style)
    - [PHPDoc](#phpdoc)
    - [StyleCI](#styleci)
- [行為準則 Code of Conduct](#code-of-conduct)

<a name="bug-reports"></a>
## Bug 回報

To encourage active collaboration, Laravel strongly encourages pull
requests, not just bug reports. "Bug reports" may also be sent in the form
of a pull request containing a failing test.

不過，若要提出 Bug 回報，則 Issue 內應包含標題以及對該問題的清楚敘述。也應儘可能提供相關資訊、以及能展示此問題的範例程式碼。Bug
回報的目標就是要能讓你自己——以及其他人——能重現此 Bug 並著手修正。

請記得，進行 Bug 回報的目的是為了能讓其他有相同問題的人能與你一起協作來解決此問題。請不要期待回報了 Bug 後，該 Bug
就會自動吸引到人，或是就有人自動來修正此 Bug。回報 Bug 是為了幫助你自己以及其他人能作為修正問題的出發點。若你想參與，可以協助修正[任何列在我們
Issue Tracker 上的
Bug](https://github.com/issues?q=is%3Aopen+is%3Aissue+label%3Abug+user%3Alaravel)。你必須先登入
GitHub 才能檢視所有的 Laravel Issue。

Laravel 的原始碼託管於 GitHub，而各個 Laravel 專案都有各自的儲存庫：

<div class="content-list" markdown="1">
- [Laravel 應用程式](https://github.com/laravel/laravel)
- [Laravel Art](https://github.com/laravel/art)
- [Laravel 說明文件](https://github.com/laravel/docs)
- [Laravel Dusk](https://github.com/laravel/dusk)
- [Laravel Cashier Stripe](https://github.com/laravel/cashier)
- [Laravel Cashier Paddle](https://github.com/laravel/cashier-paddle)
- [Laravel Echo](https://github.com/laravel/echo)
- [Laravel Envoy](https://github.com/laravel/envoy)
- [Laravel Framework](https://github.com/laravel/framework)
- [Laravel Homestead](https://github.com/laravel/homestead)
- [Laravel Homestead 建制指令碼](https://github.com/laravel/settler)
- [Laravel Horizon](https://github.com/laravel/horizon)
- [Laravel Jetstream](https://github.com/laravel/jetstream)
- [Laravel Passport](https://github.com/laravel/passport)
- [Laravel Sail](https://github.com/laravel/sail)
- [Laravel Sanctum](https://github.com/laravel/sanctum)
- [Laravel Scout](https://github.com/laravel/scout)
- [Laravel Socialite](https://github.com/laravel/socialite)
- [Laravel Telescope](https://github.com/laravel/telescope)
- [Laravel 網站](https://github.com/laravel/laravel.com-next)
</div>

<a name="support-questions"></a>
## 支援提問

Laravel 的 GitHub Issue Tracker 並不是用來提供 Laravel 說明或協助的。請改用下列其中一種管道：

<div class="content-list" markdown="1">
- [GitHub Discussions](https://github.com/laravel/framework/discussions)
- [Laracasts Forums](https://laracasts.com/discuss)
- [Laravel.io Forums](https://laravel.io/forum)
- [StackOverflow](https://stackoverflow.com/questions/tagged/laravel)
- [Discord](https://discordapp.com/invite/KxwQuKb)
- [Larachat](https://larachat.co)
- [IRC](https://webchat.freenode.net/?nick=artisan&channels=%23laravel&prompt=1)
</div>

<a name="core-development-discussion"></a>
## 核心開發討論

You may propose new features or improvements of existing Laravel behavior in
the Laravel Ideas [issue board](https://github.com/laravel/ideas/issues). If
you propose a new feature, please be willing to implement at least some of
the code that would be needed to complete the feature.

有關 Bug、新功能、以及現有功能實作的一些比較不正式的討論，會在 [Laravel Discord
伺服器](https://discordapp.com/invite/mPZNm7A)上的 `#internals` 頻道內進行。Taylor
Otwell（Laravel 的維護人員），通常會在工作日的上午 8 時至下午 5 時（UTC-06:00 或
America/Chicago）或是其他不定期時間出現在此頻道內。

<a name="which-branch"></a>
## 要用哪個分支？

**所有** 的 Bug 修正都應送交至穩定版分支或是[目前的 LTS 分支](/docs/{{version}}/releases#support-policy)。Bug 修正**不該**送交至 `master` 分支，除非要修正的功能只出現在未來的版本中。

對於目前版本有**完整向下相容性**的**次要**功能，可以送交至最新的穩定分支。

**主要**新功能則應送交至 `master` 分支，這個分支包含了未來的版本。

若不確定你的功能是主要功能還是次要功能，請在 [Laravel Discord
伺服器](https://discordapp.com/invite/mPZNm7A)的 `#internals` 頻道內詢問 Taylor
Otwell。

<a name="compiled-assets"></a>
## 編譯素材

若要送交的修改會影響到經過編譯的檔案（通常是 `laravel/laravel` 儲存庫的 `resources/css` 或
`resources/js`），請不要簽入 (Commit) 這些編譯後的檔案。由於這些檔案的大小很大，因此無法實際被維護人員審閱，進而造成能向
Laravel 插入惡意程式碼的機會。為了避免此一問題，所有經過編譯的檔案都應由 Laravel 維護人員產生並簽入。

<a name="security-vulnerabilities"></a>
## 安全性漏洞

若在 Laravel 內發現了安全性漏洞，請傳送電子郵件給 Taylor Otwell，<a href="mailto:taylor@laravel.com">taylor@laravel.com</a>。所有的安全性漏洞都會被即時處理。

<a name="coding-style"></a>
## 編碼樣式

Laravel 遵守
[PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
編碼標準以及
[PSR-4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
Autoload 標準。

<a name="phpdoc"></a>
### PHPDoc

下列為一個有效的 Laravel 文件區塊範例。請注意，`@param` 屬性後有兩個空格，然後是引數型別，然後是兩個空格，最後才是變數名稱：

    /**
     * Register a binding with the container.
     *
     * @param  string|array  $abstract
     * @param  \Closure|string|null  $concrete
     * @param  bool  $shared
     * @return void
     *
     * @throws \Exception
     */
    public function bind($abstract, $concrete = null, $shared = false)
    {
        //
    }

<a name="styleci"></a>
### StyleCI

不用擔心你的編碼風格不正確！[StyleCI](https://styleci.io/) 會在 Pull Request 被 Merge
後自動合併任何的樣式修正。這樣一來我們就能專注在參與貢獻的內容而非程式碼風格。

<a name="code-of-conduct"></a>
## 行為準則 Code of Conduct

Laravel 的行為準則改編自 Ruby 的 Code of Conduct。有任何違反 Code of Conduct 的行為，可以回報給
Taylor Otwell (taylor@laravel.com)：

<div class="content-list" markdown="1">
- 參與者應容忍相反的意見。
   Participants will be tolerant of opposing views.
- 參與者必須確保所使用的話語與行為不包含人身攻擊以及詆譭個人的言論。
   Participants must ensure that their language and actions are free of personal attacks and disparaging personal remarks.
- 在理解他人的文字或行為時，參與者應總是假設其為善意的。
   When interpreting the words and actions of others, participants should always assume good intentions.
- 可被合理視為騷擾的行為不會被容忍。
   Behavior that can be reasonably considered harassment will not be tolerated.
</div>
