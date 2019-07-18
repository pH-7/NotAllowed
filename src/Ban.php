<?php
/**
 * @author           Pierre-Henry Soria <hi@ph7.me>
 * @copyright        (c) 2012-2019, Pierre-Henry Soria. All Rights Reserved.
 * @license          MIT License; <https://opensource.org/licenses/MIT>
 */

namespace PH7\NotAllowed;

class Ban
{
    const DATA_DIR = '/banned-data/';
    const USERNAME_FILE = 'usernames.txt';
    const EMAIL_FILE = 'emails.txt';
    const WORD_FILE = 'words.txt';
    const BANK_ACCOUNT_FILE = 'bank_accounts.txt';
    const IP_FILE = 'ips.txt';

    /** @var string */
    private static $sFile;

    /** @var string */
    private static $sVal;

    /** @var bool */
    private static $bIsEmail = false;

    /**
     * Check if a word is not banned.
     *
     * @param string $sVal
     *
     * @return bool
     */
    public static function isWord($sVal)
    {
        self::$sFile = self::WORD_FILE;
        self::$sVal = $sVal;

        return self::is();
    }

    /**
     * Checks if the username is not a banned username.
     *
     * @param string $sVal
     *
     * @return bool
     */
    public static function isUsername($sVal)
    {
        self::$sFile = self::USERNAME_FILE;
        self::$sVal = $sVal;

        return self::is();
    }

    /**
     * @param string $sVal
     *
     * @return bool
     */
    public static function isEmail($sVal)
    {
        self::$sFile = self::EMAIL_FILE;
        self::$sVal = $sVal;
        self::$bIsEmail = true;

        return self::is();
    }

    /**
     * @param string $sVal
     *
     * @return bool
     */
    public static function isBankAccount($sVal)
    {
        self::$sFile = self::BANK_ACCOUNT_FILE;
        self::$sVal = $sVal;
        self::$bIsEmail = true;

        return self::is();
    }

    /**
     * @param string $sVal
     *
     * @return bool
     */
    public static function isIp($sVal)
    {
        self::$sFile = self::IP_FILE;
        self::$sVal = $sVal;

        return self::is();
    }

    /**
     * Generic method that checks if there.
     *
     * @return bool Returns TRUE if the text is banned, FALSE otherwise.
     */
    private static function is()
    {
        self::setCaseInsensitive();

        if (self::$bIsEmail) {
            if (self::check(strrchr(self::$sVal, '@'))) {
                return true;
            }
        }

        return self::check(self::$sVal);
    }

    /**
     * @param string $sVal
     *
     * @return bool Returns TRUE if the value is banned, FALSE otherwise.
     */
    private static function check($sVal)
    {
        $aBans = file(__DIR__ . self::DATA_DIR . self::$sFile);

        return in_array($sVal, array_map('trim', $aBans), true);
    }

    private static function setCaseInsensitive(): void
    {
        self::$sVal = strtolower(self::$sVal);
    }

    /**
     * Private Constructor & Cloning to prevent direct creation of object and blocking cloning.
     */
    final private function __construct()
    {
    }

    final private function __clone()
    {
    }
}
