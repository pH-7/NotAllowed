<?php
/**
 * @author           Pierre-Henry Soria <hi@ph7.me>
 * @copyright        (c) 2012-2023, Pierre-Henry Soria. All Rights Reserved.
 * @license          MIT License; <https://opensource.org/licenses/MIT>
 */

declare(strict_types=1);

namespace PH7\NotAllowed;

use Exception;

class Ban
{
    private const DATA_DIR = '/banned-data/';
    private const COMMENT_SIGN = '#';

    private const USERNAME_FILE = 'usernames.txt';
    private const EMAIL_FILE = 'emails.txt';
    private const WORD_FILE = 'words.txt';
    private const BANK_ACCOUNT_FILE = 'bank_accounts.txt';
    private const IP_FILE = 'ips.txt';

    private const USERNAME_TYPE = 'usernames';
    private const EMAIL_TYPE = 'emails';
    private const WORD_TYPE = 'words';
    private const BANK_ACCOUNT_TYPE = 'bank_accounts';
    private const IP_TYPE = 'ips';

    private static array $cache = [
        self::IP_FILE => null,
        self::USERNAME_FILE => null,
        self::BANK_ACCOUNT_FILE => null,
        self::WORD_FILE => null,
        self::EMAIL_FILE => null
    ];

    /**
     * @param string $scope Possible values are: usernames, words, ips, emails, bank_accounts
     * @param string|array $value phrases to ban.
     *
     * @throws Exception When the given scope is invalid.
     */
    public static function merge(string $scope, string|array $value): void
    {
        self::setCaseInsensitive($scope);

        switch ($scope) {
            case self::USERNAME_TYPE:
                $targetScope = self::USERNAME_FILE;
                break;
            case self::EMAIL_TYPE:
                $targetScope = self::EMAIL_FILE;
                break;
            case self::WORD_TYPE:
                $targetScope = self::WORD_FILE;
                break;
            case self::BANK_ACCOUNT_TYPE:
                $targetScope = self::BANK_ACCOUNT_FILE;
                break;
            case self::IP_TYPE:
                $targetScope = self::IP_FILE;
                break;
            default:
                throw new Exception("Unsupported value $scope");
        }

        $value = is_array($value) ? $value : [$value];
        $cachedValue = (array)static::$cache[$targetScope];
        array_push($cachedValue, ...$value);
    }

    /**
     * @param string $scope Possible values are: usernames, words, ips, emails, bank_accounts
     * @param string $path location of file
     */
    public static function mergeFile(string $scope, string $path): void
    {
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
     * @param string | array $value
     * @return bool true if the value, or any of array values, are banned based on chosen validation paths
     */
    public static function isAny(
        $value,
        bool $email = false,
        bool $word = false,
        bool $username = false,
        bool $ip = false,
        bool $bank_accounts = false
    ): bool {
        if ($email && static::isEmail($value)) {
            return true;
        }
        if ($word && static::isWord($value)) {
            return true;
        }
        if ($username && static::isUsername($value)) {
            return true;
        }
        if ($ip && static::isIp($value)) {
            return true;
        }
        if ($bank_accounts && static::isBankAccount($value)) {
            return true;
        }

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
    public static function isAll(
        array $value,
        bool $email = false,
        bool $word = false,
        bool $username = false,
        bool $ip = false,
        bool $bank_accounts = false
    ): bool {
        foreach ($value as $v) {
            if (!static::isAny($v, $email, $word, $username, $ip, $bank_accounts)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string | array $value
     */
    public static function isWord($value): bool
    {
        if (is_array($value)) {
            foreach ($value as $v) {
                if (static::isInSentence($v)) {
                    return true;
                }
            }

            return false;
        }

        return self::isInSentence($value);
    }

    /**
     * @param string | array $value
     */
    public static function isUsername($value): bool
    {
        return static::is_facade(self::USERNAME_FILE, $value);
    }

    /**
     * @param string | array $value
     */
    public static function isEmail($value): bool
    {
        return static::is_facade(self::EMAIL_FILE, $value);
    }

    /**
     * @param string | array $value
     */
    public static function isBankAccount($value): bool
    {
        return static::is_facade(self::BANK_ACCOUNT_FILE, $value);
    }

    /**
     * @param string | array $value
     */
    public static function isIp($value): bool
    {
        return static::is_facade(self::IP_FILE, $value);
    }

    private static function is_facade(string $scope, $value): bool
    {
        return is_array($value)
            ? static::isIn($scope, $value)
            : static::is($scope, $value);
    }

    private static function isIn(string $scope, array $value): bool
    {
        foreach ($value as $v) {
            if (static::is($scope, $v)) {
                return true;
            }
        }

        return false;
    }

    private static function is(string $scope, string $value): bool
    {
        self::setCaseInsensitive($value);

        if ($scope === self::EMAIL_FILE && strrchr($value, '@')) {
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
        return str_starts_with($sBan, self::COMMENT_SIGN);
    }

    private static function getContents(string $scope): array
    {
        if (is_null(static::$cache[$scope])) {
            static::$cache[$scope] = static::readFile(__DIR__ . self::DATA_DIR . $scope);
        }

        return static::$cache[$scope];
    }

    private static function readFile(string $path): array
    {
        return (array)file($path, FILE_SKIP_EMPTY_LINES);
    }

    /**
     * Private Constructor & Cloning to prevent direct creation of object and blocking cloning.
     */
    private function __construct()
    {
    }

    private function __clone()
    {
    }
}
