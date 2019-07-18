# Not Allowed

## ⚙ Server Requirement

[PHP 7.2](https://php.net/releases/7_2_0.php) or higher.


## 📓 Installation (with Composer)

```bash
composer require ph-7/phonedetector
```

## 🎮  Usage

Simple example of what you can do with it :)

```php
use PierreHenry\PhoneDetector\PhoneDetector;

$phoneDetector = new PhoneDetector();
if ($phoneDetector->isAndroid()) {
    echo 'Your mobile phone is Android!';
}

if ($phoneDetector->isIos()) {
    echo 'Your mobile phone runs with iOS';
}
```


## 🚀 Author

[![Pierre-Henry Soria](https://avatars0.githubusercontent.com/u/1325411?s=200)](https://pierrehenry.be "My personal website :-)")

[![@phenrysay][twitter-image]][twitter-url]

**[Pierre-Henry Soria][author-url]**, a Highly-Passionate, Zen&Cool Software Engineer.


## 😄 Used By...

**[pH7Builder][ph7cms-url]**, a social dating webapp builder. Used here: [https://github.com/pH7Software/pH7-Social-Dating-CMS/blob/master/_protected/framework/Security/Ban/Ban.class.php](https://github.com/pH7Software/pH7-Social-Dating-CMS/blob/master/_protected/framework/Security/Ban/Ban.class.php).


## ⚖ License

Generously distributed under [MIT License][license-url]!


<!-- GitHub's Markdown reference links -->
[author-url]: https://pierrehenry.be
[ph7cms-url]: https://ph7cms.com
[license-url]: https://opensource.org/licenses/MIT
[twitter-image]: https://img.shields.io/twitter/url/https/shields.io.svg?style=social
[twitter-url]: https://twitter.com/phenrysay


