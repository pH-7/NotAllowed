<?php
/**
 * @author           Pierre-Henry Soria <hi@ph7.me>
 * @copyright        (c) 2012-2022, Pierre-Henry Soria. All Rights Reserved.
 * @license          MIT License; <https://opensource.org/licenses/MIT>
 */

declare(strict_types=1);

namespace PH7\NotAllowed;

use Exception;

class Ban
{
    private const DATA_DIR = '/banned-data/';
    private const USERNAME_FILE = 'usernames.txt';
    private const EMAIL_FILE = 'emails.txt';
    private const WORD_FILE = 'words.txt';
    private const BANK_ACCOUNT_FILE = 'bank_accounts.txt';
    private const IP_FILE = 'ips.txt';
    private const COMMENT_SIGN = '#';
    private static array $cache = [
        self::IP_FILE => null,
        self::USERNAME_FILE => null,
        self::BANK_ACCOUNT_FILE => null,
        self::WORD_FILE => null,
        self::EMAIL_FILE => null
    ];

    /** @var bool */
    private static bool $bIsEmail = false;

    /**
     * @param string $scope Possible values are: usernames, words, ips, emails, bank_accounts
     * @param string | array $value phrases to ban
     */
    public static function merge(string $scope, string | array $value) : void {
        self::setCaseInsensitive($scope);

        switch ($scope) {
            case 'usernames':
                $target_scope = self::USERNAME_FILE;
                break;
            case 'ips':
                $target_scope = self::IP_FILE;
                break;
            case 'emails':
                $target_scope = self::EMAIL_FILE;
                break;
            case 'bank_accounts':
                $target_scope = self::BANK_ACCOUNT_FILE;
                break;
            case 'words':
                $target_scope = self::WORD_FILE;
                break;
            default:
                throw new Exception("Unsupported value $scope");
        }

        static::getContents($target_scope);

        $value = is_array($value) ? $value : [$value];
        array_push(static::$cache[$target_scope], ...$value);
    }

    public static function mergeFile(string $scope, string $path) : void {
        static::merge($scope, static::readFile(realpath($path)));
    }

    /**
     * Pick and choose validation paths for provided value[s].
     *
     * For example, if you want to validate a value is either a banned word or banned username call the method by way of:
     * `Ban::isAny(false, true, true);`
     *
     * For PHP 8 you can use named parameters:
     * `Ban::isAny(username: true, word: true);`
     *
     * @return bool true if the value, or any of array values, are banned based on chosen validation paths
     */
    public static function isAny(string | array $value,
                                 bool $email = false,
                                 bool $word = false,
                                 bool $username = false,
                                 bool $ip = false,
                                 bool $bank_accounts = false) : bool {

        if ($email && static::isEmail($value)) return true;
        if ($word && static::isWord($value)) return true;
        if ($username && static::isUsername($value)) return true;
        if ($ip && static::isIp($value)) return true;
        if ($bank_accounts && static::isBankAccount($value)) return true;

        return false;
    }

    /**
     * Pick and choose validation paths for provided values.
     *
     * For example, if you want to validate a value is either a banned word or banned username call the method by way of:
     * `Ban::isAll(false, true, true);`
     *
     * For PHP 8 you can use named parameters:
     * `Ban::isAll(username: true, word: true);`
     *
     * @return bool true only if _ALL_ of the provided values are banned across all the paths selected
     */
    public static function isAll(array $value,
                                 bool $email = false,
                                 bool $word = false,
                                 bool $username = false,
                                 bool $ip = false,
                                 bool $bank_accounts = false) : bool {

        foreach ($value as $v) {
            if (!static::isAny($v, $email, $word, $username, $ip, $bank_accounts))
                return false;
        }

        return true;
    }

    public static function isWord(string | array $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $v)
                if (static::isInSentence($v)) return true;

            return false;
        }

        return self::isInSentence($value);
    }

    public static function isUsername(string | array $value): bool
    {
        return static::is_facade(self::USERNAME_FILE, $value);
    }

    public static function isEmail(string | array $value): bool
    {
        self::$bIsEmail = true;
        $isEmail = static::is_facade(self::EMAIL_FILE, $value);
        self::$bIsEmail = false;

        return $isEmail;
    }

    public static function isBankAccount(string | array $value): bool
    {
        return static::is_facade(self::BANK_ACCOUNT_FILE, $value);
    }

    public static function isIp(string | array $value): bool
    {
        return static::is_facade(self::IP_FILE, $value);
    }

    private static function is_facade(string $scope, string | array $value) : bool {
        return is_array($value)
            ? static::isIn($scope, $value)
            : static::is($scope, $value);
    }

    private static function isIn(string $scope, array $value) : bool {
        foreach ($value as $v)
            if (static::is($scope, $v)) return true;

        return false;
    }

    private static function is(string $scope, string $value): bool
    {
        self::setCaseInsensitive($value);

        if (self::$bIsEmail && strrchr($value, '@')) {
            if (self::check($scope, strrchr($value, '@'))) {
                return true;
            }
        }

        return self::check($scope, $value);
    }

    private static function isInSentence(string $value): bool
    {
        $aBannedContents = self::getContents(self::WORD_FILE);

        foreach ($aBannedContents as $sBan) {
            $sBan = trim($sBan);

            if (!empty($sBan) && !self::isCommentFound($sBan) && stripos($value, $sBan) !== false) {
                return true;
            }
        }

        return false;
    }

    private static function check(string $scope, string $value): bool
    {
        $aBannedContents = static::getContents($scope);

        return in_array($value, array_map('trim', $aBannedContents), true);
    }

    private static function setCaseInsensitive(string &$value): void
    {
        $value = strtolower($value);
    }

    private static function isCommentFound($sBan): bool
    {
        return strpos($sBan, self::COMMENT_SIGN) === 0;
    }

    private static function getContents(string $scope): array
    {
        if (is_null(static::$cache[$scope]))
            static::$cache[$scope] = static::readFile(__DIR__ . self::DATA_DIR . $scope);

        return static::$cache[$scope];
    }

    private static function readFile(string $path) {
        return (array)file($path, FILE_SKIP_EMPTY_LINES);
    }

    /**
     * Private Constructor & Cloning to prevent direct creation of object and blocking cloning.
     */
    private function __construct() {}
    private function __clone() {}
}
