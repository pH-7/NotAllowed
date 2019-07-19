<?php
/**
 * @author           Pierre-Henry Soria <hi@ph7.me>
 * @copyright        (c) 2019, Pierre-Henry Soria. All Rights Reserved.
 * @license          MIT License; <https://opensource.org/licenses/MIT>
 */

declare(strict_types=1);

namespace PH7\NotAllowed\Tests;

use PH7\NotAllowed\Ban;
use PHPUnit\Framework\TestCase;

class BanTest extends TestCase
{
    /**
     * @dataProvider bannedWordsProvider
     */
    public function testIsBannedWord(string $sWord): void
    {
        $this->assertTrue(Ban::isWord($sWord));
    }

    public function testIsNotBannedWord(): void
    {
        $this->assertFalse(Ban::isWord('hello world'));
    }

    /**
     * @dataProvider bannedUsernamesProvider
     */
    public function testIsBannedUsername(string $sUsername): void
    {
        $this->assertTrue(Ban::isUsername($sUsername));
    }

    public function testIsNotBannedUsername(): void
    {
        $this->assertFalse(Ban::isUsername('berylde66'));
    }

    /**
     * @dataProvider bannedEmailsProvider
     */
    public function testIsBannedEmail(string $sEmail): void
    {
        $this->assertTrue(Ban::isEmail($sEmail));
    }

    public function testIsNotBannedEmail(): void
    {
        $this->assertFalse(Ban::isEmail('pierre@henry.name'));
    }

    /**
     * @dataProvider bannedBankAccountsProvider
     */
    public function testIsBannedBankAccount(string $sBankAccount): void
    {
        $this->assertTrue(Ban::isBankAccount($sBankAccount));
    }

    public function testIsNotBannedBankAccount(): void
    {
        $this->assertFalse(Ban::isBankAccount('12161216121612161216'));
    }

    /**
     * @dataProvider bannedIpsProvider
     */
    public function testIsBannedIp(string $sIp): void
    {
        $this->assertTrue(Ban::isIp($sIp));
    }

    public function testIsNotBannedIp(): void
    {
        $this->assertFalse(Ban::isIp('127.0.0.1'));
    }

    /**
     * @return array
     */
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
            ['125.123.208.182']
        ];
    }
}
