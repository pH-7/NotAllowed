# 🚫 Not Allowed 🚫

**NotAllowed** is a simple lightweight PHP 7.2 library that helps you to censor words, profane words, blacklisted IPs, forbidden user names, banned bank card numbers, etc.

You can add easily new specific keywords (such as IPs, usernames, bank accounts, words, etc) to ban in `~/src/banned-data/*` folder.


## 🛠 Server Requirement

- [PHP 8.0](https://www.php.net/releases/8.0/en.php) or higher.


## 📓 Installation (with Composer)

```bash
composer require ph-7/notallowed
```


## 🎮 Usage

Simple example of what you can do with it :)

```php
use PH7\NotAllowed\Ban;

if (Ban::isUsername('admin')) {
    echo '"admin" is not allowed as username.';
}

if (Ban::isEmail('james@spamhole.com')) {
    echo '"@spamhole.com" domain is considered as a email spam host.';
}

if (Ban::isWord('He is an asshole')) {
    echo 'Please watch your mouth :-)';
}

if(Ban::isIp('1.170.36.229')) {
    echo 'This IP address is blacklisted';
}

$userinput = 'admin';
if (Ban::isUsername($userinput, ['root', 'sudo', 'admin'])) {
    echo "$userinput is not allowed";
}

// Validate of the userinput is a banned word _OR_ a banned username
if (Ban::isAny($userinput, email: false, word: true, username: true)) {
    echo "$userinput is not allowed";
}
```

### Extending Banned Phrases

You can supply your own values to be merged with the out-of-box banned-data 2 ways

1. `Ban::merge(string $scope, string | array $value)`
2. `Ban::mergeFile(string $scope, string $path)`

`$scope` refers to the category of data. Possible values are currently:

- usernames
- words
- ips
- emails
- bank_accounts

<details>
<summary>Example</summary>

```php
Ban::merge('usernames', ['pooter', 'hitler', '690']);
Ban::merge('words', ['cuck', 'bomb']);
Ban::mergeFile('emails', './my_banned_emails.txt');
```
</details>



Now simply validate per normal conventions

## 🚀 Author

**[Pierre-Henry Soria][author-url]**, a highly passionate, zen &amp; cool software engineer 😊

[![@phenrysay][twitter-image]][twitter-url]

[![Pierre-Henry Soria](https://avatars0.githubusercontent.com/u/1325411?s=220)](https://pierrehenry.be "Pierre-Henry - Software Developer Website :-)")

## 👩🏻‍💻 Helpers

**[soulshined](https://github.com/soulshined)** - just a coder

## 🧐 Used By...

**[pH7Builder][ph7cms-url]**, a social dating webapp builder. Used here: [https://github.com/pH7Software/pH7-Social-Dating-CMS/blob/master/_protected/framework/Security/Ban/Ban.class.php](https://github.com/pH7Software/pH7-Social-Dating-CMS/blob/master/_protected/framework/Security/Ban/Ban.class.php).


## ⚖️ License

Generously distributed under [MIT License][license-url]! 🎈


<!-- GitHub's Markdown reference links -->
[author-url]: https://pierrehenry.be
[ph7cms-url]: https://ph7cms.com
[license-url]: https://opensource.org/licenses/MIT
[twitter-image]: https://img.shields.io/twitter/url/https/shields.io.svg?style=social
[twitter-url]: https://twitter.com/phenrysay


