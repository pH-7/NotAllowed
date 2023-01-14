<?php
/**
 * @author           Pierre-Henry Soria <hi@ph7.me>
 * @copyright        (c) 2012-2023, Pierre-Henry Soria. All Rights Reserved.
 * @license          MIT License; <https://opensource.org/licenses/MIT>
 */

declare(strict_types=1);

namespace PH7\NotAllowed;

enum BannedType: string
{
    case USERNAME = 'usernames';
    case EMAIL = 'emails';
    case WORD = 'words';
    case BANK_ACCOUNT = 'bank_accounts';
    case IP = 'ips';

    public function fileName(): string
    {
        return match ($this) {
            BannedType::USERNAME => Ban::USERNAME_FILE,
            BannedType::EMAIL => Ban::EMAIL_FILE,
            BannedType::WORD => Ban::WORD_FILE,
            BannedType::BANK_ACCOUNT => Ban::BANK_ACCOUNT_FILE,
            BannedType::IP => Ban::IP_FILE,
        };
    }
}

class Ban
{
    private const DATA_DIR = '/banned-data/';
    private const COMMENT_SIGN = '#';

    public const USERNAME_FILE = 'usernames.txt';
    public const EMAIL_FILE = 'emails.txt';
    public const WORD_FILE = 'words.txt';
    public const BANK_ACCOUNT_FILE = 'bank_accounts.txt';
    public const IP_FILE = 'ips.txt';

    private static array $cache = [
        self::USERNAME_FILE => null,
        self::EMAIL_FILE => null,
        self::WORD_FILE => null,
        self::BANK_ACCOUNT_FILE => null,
        self::IP_FILE => null,
    ];

    /**
     * @param BannedType $bannedType
     * @param string|array $value phrases to ban.
     */
    public static function merge(BannedType $bannedType, string|array $value): void
    {
        $targetScope = $bannedType->fileName();

        static::loadContents($targetScope);

        $value = is_array($value) ? $value : [$value];
        array_push(static::$cache[$targetScope], ...$value);
    }

    /**
     * @param BannedType $bannedType
     * @param string $path Full path of the file.
     */
    public static function mergeFile(BannedType $bannedType, string $path): void
    {
        static::merge($bannedType, static::readFile(realpath($path)));
    }

    /**
     * Pick and choose validation paths for provided value[s].
     *
     * For example, if you want to validate a value is either a banned word or banned username call the method by way of:
     * `Ban::isAny(false, true, true);`
     *
     * For PHP 8 you can use named parameters:
     * `Ban::isAny(username: true, word: true);`
     * @return bool TRUE if the value, or any of array values, are banned based on chosen validation paths
     */
    public static function isAny(
        string|array $value,
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

    public static function isWord(string|array $value): bool
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

    public static function isUsername(string|array $value): bool
    {
        return static::isFacade(self::USERNAME_FILE, $value);
    }

    public static function isEmail(string|array $value): bool
    {
        return static::isFacade(self::EMAIL_FILE, $value);
    }

    public static function isBankAccount(string|array $value): bool
    {
        return static::isFacade(self::BANK_ACCOUNT_FILE, $value);
    }

    public static function isIp(string|array $value): bool
    {
        return static::isFacade(self::IP_FILE, $value);
    }

    private static function isFacade(string $scope, string|array $value): bool
    {
        return is_array($value)
            ? static::doesExist($scope, $value)
            : static::is($scope, $value);
    }

    private static function doesExist(string $scope, array $value): bool
    {
        foreach ($value as $val) {
            if (static::is($scope, $val)) {
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
        static::loadContents($scope);

        return static::$cache[$scope];
    }

    private static function loadContents(string $scope): void
    {
        if (static::$cache[$scope] === null) {
            static::$cache[$scope] = static::readFile(__DIR__ . self::DATA_DIR . $scope);
        }
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
