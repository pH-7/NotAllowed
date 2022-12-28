<?php
/**
 * @author           Pierre-Henry Soria <hi@ph7.me>
 * @copyright        (c) 2012-2022, Pierre-Henry Soria. All Rights Reserved.
 * @license          MIT License; <https://opensource.org/licenses/MIT>
 */

declare(strict_types=1);

namespace PH7\NotAllowed;

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

        return static::is_facade(self::EMAIL_FILE, $value);
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
            static::$cache[$scope] = (array)file(__DIR__ . self::DATA_DIR . $scope, FILE_SKIP_EMPTY_LINES);

        return static::$cache[$scope];
    }

    /**
     * Private Constructor & Cloning to prevent direct creation of object and blocking cloning.
     */
    private function __construct() {}
    private function __clone() {}
}
