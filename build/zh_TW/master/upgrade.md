---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/165/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 45.51
---

# 升級指南

- [Upgrading To 12.0 From 11.x](#upgrade-12.0)

<a name="high-impact-changes"></a>

## 高度影響的更改

<div class="content-list" markdown="1">
- TBD

</div>
<a name="medium-impact-changes"></a>

## 中度影響的更改

<div class="content-list" markdown="1">
- TBD

</div>
<a name="low-impact-changes"></a>

## 低影響的更改

<div class="content-list" markdown="1">
- TBD

</div>
<a name="upgrade-12.0"></a>

## Upgrading To 12.0 From 11.x

<a name="estimated-upgrade-time-??-minutes"></a>

#### Estimated Upgrade Time: ?? Minutes

> [!NOTE]  
> 雖然我們已經儘可能地在本說明文件中涵蓋所有^[中斷性變更](Breaking Change)。不過，在 Laravel 中，有些中斷性變更存在一些比較不明顯的地方，且這些更改中幾乎不太會影響到你的專案。 想節省時間嗎？可以使用 [Laravel Shift](https://laravelshift.com/) 來協助你快速升級你的專案。

<a name="updating-dependencies"></a>

### 相依性套件更新

**受影響的可能：高**

#### Composer 相依性套件

請在專案的 `composer.json` 檔案中更新下列相依性套件：

<div class="content-list" markdown="1">
- `laravel/framework` to `^12.0`

</div>
<a name="miscellaneous"></a>

### 其他

We also encourage you to view the changes in the `laravel/laravel` [GitHub repository](https://github.com/laravel/laravel). While many of these changes are not required, you may wish to keep these files in sync with your application. Some of these changes will be covered in this upgrade guide, but others, such as changes to configuration files or comments, will not be. You can easily view the changes with the [GitHub comparison tool](https://github.com/laravel/laravel/compare/11.x...master) and choose which updates are important to you.
