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

    /** @var string */
    private static string $sFile;

    /** @var string */
    private static string $sVal;

    /** @var bool */
    private static bool $bIsEmail = false;

    public static function isWord(string $sVal): bool
    {
        self::$sFile = self::WORD_FILE;
        self::$sVal = $sVal;

        return self::isInSentence();
    }

    public static function isUsername(string $sVal): bool
    {
        self::$sFile = self::USERNAME_FILE;
        self::$sVal = $sVal;

        return self::is();
    }

    public static function isEmail(string $sVal): bool
    {
        self::$sFile = self::EMAIL_FILE;
        self::$sVal = $sVal;
        self::$bIsEmail = true;

        return self::is();
    }

    public static function isBankAccount(string $sVal): bool
    {
        self::$sFile = self::BANK_ACCOUNT_FILE;
        self::$sVal = $sVal;

        return self::is();
    }

    public static function isIp(string $sVal): bool
    {
        self::$sFile = self::IP_FILE;
        self::$sVal = $sVal;

        return self::is();
    }

    private static function is(): bool
    {
        self::setCaseInsensitive();

        if (self::$bIsEmail && strrchr(self::$sVal, '@')) {
            if (self::check(strrchr(self::$sVal, '@'))) {
                return true;
            }
        }

        return self::check(self::$sVal);
    }

    private static function isInSentence(): bool
    {
        $aBannedContents = self::readFile();

        foreach ($aBannedContents as $sBan) {
            $sBan = trim($sBan);

            if (!empty($sBan) && !self::isCommentFound($sBan) && stripos(self::$sVal, $sBan) !== false) {
                return true;
            }
        }

        return false;
    }

    private static function check(string $sVal): bool
    {
        $aBannedContents = self::readFile();

        return in_array($sVal, array_map('trim', $aBannedContents), true);
    }

    private static function setCaseInsensitive(): void
    {
        self::$sVal = strtolower(self::$sVal);
    }

    private static function isCommentFound($sBan): bool
    {
        return strpos($sBan, self::COMMENT_SIGN) === 0;
    }

    private static function readFile(): array
    {
        if (is_null(static::$cache[static::$sFile]))
            static::$cache[static::$sFile] = (array)file(__DIR__ . self::DATA_DIR . static::$sFile, FILE_SKIP_EMPTY_LINES);

        return static::$cache[static::$sFile];
    }

    /**
     * Private Constructor & Cloning to prevent direct creation of object and blocking cloning.
     */
    private function __construct() {}
    private function __clone() {}
}
