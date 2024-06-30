---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/165/en-zhtw
progress: 92
updatedAt: '2024-06-30T08:27:00Z'
---

# 升級指南

- [Upgrading To 11.0 From 10.x](#upgrade-11.0)

<a name="high-impact-changes"></a>

## 高度影響的更改

<div class="content-list" markdown="1">

- [更新相依性套件](#updating-dependencies)
- [更新 Minimum Stability](#updating-minimum-stability)

</div>

<a name="low-impact-changes"></a>

## 低影響的更改

<div class="content-list" markdown="1">

- [The `Enumerable` Contract](#the-enumerable-contract)

</div>

<a name="upgrade-11.0"></a>

## Upgrading To 11.0 From 10.x

<a name="estimated-upgrade-time-??-minutes"></a>

#### Estimated Upgrade Time: ?? Minutes

> **Note** 雖然我們已經儘可能地在本說明文件中涵蓋所有^[中斷性變更](Breaking Change)。不過，在 Laravel 中，有些中斷性變更存在一些比較不明顯的地方，且這些更改中幾乎不太會影響到你的專案。 想節省時間嗎？可以使用 [Laravel Shift](https://laravelshift.com/) 來協助你快速升級你的專案。

<a name="updating-dependencies"></a>

### 相依性套件更新

**受影響的可能：高**

#### PHP 8.2.0 Required

Laravel now requires PHP 8.2.0 or greater.

#### Composer 相依性套件

請在專案的 `composer.json` 檔案中更新下列相依性套件：

<div class="content-list" markdown="1">

- `laravel/framework` to `^11.0`

</div>

<a name="collections"></a>

### Collections

<a name="the-enumerable-contract"></a>

#### `Enumerable` Contract

**受影響的可能：低**

The `dump` method of the `Illuminate\Support\Enumerable` contract has been updated to accept a variadic `...$args` argument. If you are implementing this interface you should update your implementation accordingly:

```php
public function dump(...$args);
```
