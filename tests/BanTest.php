<?php
/**
 * @author           Pierre-Henry Soria <hi@ph7.me>
 * @copyright        (c) 2019-2022, Pierre-Henry Soria. All Rights Reserved.
 * @license          MIT License; <https://opensource.org/licenses/MIT>
 */

declare(strict_types=1);

namespace PH7\NotAllowed\Tests;

use PH7\NotAllowed\Ban;
use PHPUnit\Framework\TestCase;

final class BanTest extends TestCase
{
    /**
     * @dataProvider bannedWordsProvider
     */
    public function testIsBannedWord(string $sWord): void
    {
        $this->assertTrue(Ban::isWord($sWord));
    }

    public function testIsBannedWords() : void {
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
        $as_array = array_merge(...$this->bannedEmailsProvider());
        $this->assertNotEmpty($as_array);
        $this->assertTrue(Ban::isEmail($as_array));
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
        $as_array = array_merge(...$this->bannedIpsProvider());
        $this->assertNotEmpty($as_array);
        $this->assertTrue(Ban::isIp($as_array));
    }

    public function testIsNotBannedIp(): void
    {
        $this->assertFalse(Ban::isIp('127.0.0.1'));
        $this->assertFalse(Ban::isIp(['127.0.0.1', '127.0.0.2']));
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
