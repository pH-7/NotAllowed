<?php
/**
 * @author           Pierre-Henry Soria <hi@ph7.me>
 * @copyright        (c) 2019-2023, Pierre-Henry Soria. All Rights Reserved.
 * @license          MIT License; <https://opensource.org/licenses/MIT>
 */

declare(strict_types=1);

namespace PH7\NotAllowed\Tests;

use PH7\NotAllowed\Ban;
use PH7\NotAllowed\BannedType;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class BanTest extends TestCase
{
    private const REQUIRED_PHP_VERSION = '8.1.0';

    protected function setUp(): void
    {
        if (version_compare(PHP_VERSION, self::REQUIRED_PHP_VERSION, '<')) {
            $this->fail(
                sprintf(
                    'To run the unit tests, you need at least PHP %s or newer. Your current version is %s.',
                    self::REQUIRED_PHP_VERSION,
                    PHP_VERSION
                )
            );
        }

        $class = new ReflectionClass(Ban::class);

        // To work with private props, needs to be run with PHP >=8.1
        $cache = $class->getStaticPropertyValue('cache');
        $clean_cache = [];

        foreach (array_keys($cache) as $key) {
            $clean_cache[$key] = null;
        }

        $class->setStaticPropertyValue('cache', $clean_cache);
    }

    /**
     * @dataProvider bannedWordsProvider
     */
    public function testIsBannedWord(string $sWord): void
    {
        $this->assertTrue(Ban::isWord($sWord));
    }

    public function testIsBannedWords(): void
    {
        $as_array = array_merge(...$this->bannedWordsProvider());
        $this->assertNotEmpty($as_array);
        $this->assertTrue(Ban::isWord($as_array));
    }

    public function testIsNotBannedWord(): void
    {
        $this->assertFalse(Ban::isWord('hello world'));
        $this->assertFalse(Ban::isWord(['hello world', 'goodbye world']));
    }

    /**
     * @dataProvider bannedUsernamesProvider
     */
    public function testIsBannedUsername(string $sUsername): void
    {
        $this->assertTrue(Ban::isUsername($sUsername));
    }

    public function testIsBannedUsernames(): void
    {
        $as_array = array_merge(...$this->bannedUsernamesProvider());
        $this->assertNotEmpty($as_array);
        $this->assertTrue(Ban::isUsername($as_array));
    }

    public function testIsNotBannedUsername(): void
    {
        $this->assertFalse(Ban::isUsername('berylde66'));
        $this->assertFalse(Ban::isUsername(['berylde66', 'rickastley1987']));
    }

    /**
     * @dataProvider bannedEmailsProvider
     */
    public function testIsBannedEmail(string $sEmail): void
    {
        $this->assertTrue(Ban::isEmail($sEmail));
    }

    public function testIsBannedEmails(): void
    {
        $bannedEmails = array_merge(...$this->bannedEmailsProvider());
        $this->assertNotEmpty($bannedEmails);
        $this->assertTrue(Ban::isEmail($bannedEmails));
    }

    public function testIsNotBannedEmail(): void
    {
        $this->assertFalse(Ban::isEmail('pierre@henry.name'));
        $this->assertFalse(Ban::isEmail(['pierre@henry.name', 'rick@astley.co']));
    }

    /**
     * @dataProvider bannedBankAccountsProvider
     */
    public function testIsBannedBankAccount(string $sBankAccount): void
    {
        $this->assertTrue(Ban::isBankAccount($sBankAccount));
    }

    public function testIsBannedBankAccounts(): void
    {
        $as_array = array_merge(...$this->bannedBankAccountsProvider());
        $this->assertNotEmpty($as_array);
        $this->assertTrue(Ban::isBankAccount($as_array));
    }

    public function testIsNotBannedBankAccount(): void
    {
        $this->assertFalse(Ban::isBankAccount('12161216121612161216'));
        $this->assertFalse(Ban::isBankAccount(['12161216121612161216', '12161216121612161217']));
    }

    /**
     * @dataProvider bannedIpsProvider
     */
    public function testIsBannedIp(string $sIp): void
    {
        $this->assertTrue(Ban::isIp($sIp));
    }

    public function testIsBannedIps(): void
    {
        $bannedIps = array_merge(...$this->bannedIpsProvider());
        $this->assertNotEmpty($bannedIps);
        $this->assertTrue(Ban::isIp($bannedIps));
    }

    public function testIsNotBannedIp(): void
    {
        $this->assertFalse(Ban::isIp('127.0.0.1'));
        $this->assertFalse(Ban::isIp(['127.0.0.1', '127.0.0.2']));
    }

    public function testIsAny(): void
    {
        $this->assertTrue(Ban::isAny('admin', false, true, true));
        $this->assertTrue(Ban::isAny(['admin'], false, true, true));
        $this->assertFalse(Ban::isAny(['admin'], false, true));
        $this->assertTrue(Ban::isAny(['good', 'good2', 'bitch', 'good3', 'a@tafmail.COM'], true, true));
    }

    public function testIsAll(): void
    {
        //email not selected as path
        $this->assertFalse(Ban::isAll(['a@tafmail.COM'], false, true, true, true, true));
        //not all are banned
        $this->assertFalse(
            Ban::isAll(['admin', 'goodusername', 'a@tafmail.COM', 'bitch'], true, true, true, true, true)
        );
        //all are banned
        $this->assertTrue(Ban::isAny(['admin', 'a@tafmail.COM', 'bitch'], true, true, true, true, true));

        $this->assertFalse(Ban::isAll(['admin', 'retard'], true, true, true, true, true));

        Ban::merge(BannedType::USERNAME, ['retard']);

        $this->assertTrue(Ban::isAll(['admin', 'retard'], true, true, true, true, true));
    }

    public function testExtendedValueIsMerged(): void
    {
        Ban::merge(BannedType::USERNAME, 'rickastley1987');
        Ban::merge(BannedType::WORD, 'foobar');
        Ban::merge(BannedType::EMAIL, 'foobar@example.com');
        Ban::merge(BannedType::IP, '127.0.0.1');
        Ban::merge(BannedType::BANK_ACCOUNT, '4539791001744107');

        $this->assertTrue(Ban::isUsername("rickastley1987"));
        $this->assertTrue(Ban::isWord("My favorite foobar is bazz"));
        $this->assertTrue(Ban::isEmail("foobar@example.com"));
        $this->assertTrue(Ban::isIp("127.0.0.1"));
        $this->assertTrue(Ban::isBankAccount("4539791001744107"));
    }

    public function testExtendedValuesIsMerged(): void
    {
        Ban::merge(BannedType::USERNAME, ['rickastley1987', 'rickastley123']);
        Ban::merge(BannedType::WORD, ['foobar', 'buzz', 'bizz', 'bozz']);
        Ban::merge(BannedType::EMAIL, ['foobar@example.com', 'noreply@mail.me']);
        Ban::merge(BannedType::IP, ['127.0.0.1', '127.0.0.2']);
        Ban::merge(BannedType::BANK_ACCOUNT, ['4539791001744107', '4539791001744108']);

        $this->assertTrue(Ban::isUsername("rickastley1987"));
        $this->assertTrue(Ban::isUsername("rickastley123"));
        $this->assertTrue(Ban::isWord("My favorite foobar is bazz"));
        $this->assertTrue(Ban::isWord("My favorite bazz is buzz"));
        $this->assertTrue(Ban::isEmail("foobar@example.com"));
        $this->assertTrue(Ban::isEmail("noreply@mail.me"));
        $this->assertTrue(Ban::isIp("127.0.0.1"));
        $this->assertTrue(Ban::isIp("127.0.0.2"));
        $this->assertTrue(Ban::isBankAccount("4539791001744107"));
        $this->assertTrue(Ban::isBankAccount("4539791001744108"));
    }

    public function testExtendedFileIsMerged(): void
    {
        Ban::mergeFile(BannedType::USERNAME, './tests/fixtures/extended_usernames.txt');
        Ban::mergeFile(BannedType::WORD, './tests/fixtures/extended_words.txt');

        $this->assertTrue(Ban::isUsername('jtevesobs'));
        $this->assertTrue(Ban::isWord("Nice FUPA"));
    }

    public function bannedWordsProvider(): array
    {
        return [
            ['bitch'],
            ['flipping the bird'],
            ['cocksucker'],
            ['dickhead'],
            ['dickflipper'],
            ['shitass'],
            ['sonofbitch'],
            ['son-of-a-bitch'],
            ['he is an asshole'],
            ['fucking'],
            ['motherfucker'],
            ['ASShole']
        ];
    }

    public function bannedUsernamesProvider(): array
    {
        return [
            ['AdmiNistraTOR'],
            ['admin'],
            ['root'],
            ['wtf'],
            ['WTF']
        ];
    }

    public function bannedEmailsProvider(): array
    {
        return [
            ['GAYAN@getairmail.com'],
            ['mememem@GuerrillamaiL.biz'],
            ['a@tafmail.COM'],
            ['0@zp.ua'],
            ['DDDDDDDD@THRAML.COM']
        ];
    }

    public function bannedBankAccountsProvider(): array
    {
        return [
            ['6304936989767381455']
        ];
    }

    public function bannedIpsProvider(): array
    {
        return [
            ['1.186.192.242'],
            ['1.34.1.60']
        ];
    }
}
