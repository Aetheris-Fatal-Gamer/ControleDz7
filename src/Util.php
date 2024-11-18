<?php

namespace Dz7;

use DateTime;
use DateTimeZone;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\User\Member;

class Util { 
    
    public static function formatCustomId(string $customId): string {
        $words = explode('_', $customId);
        $words = array_map('ucfirst', $words);
        return implode('', $words);
    }

    public static function setPassportInName(Member $member, string $passport): void {
        $passportExists = self::extractPassport(null, $member);
        $userName = $member?->nick ?? $member?->user?->username ?? '';
        if (empty($passportExists) && !empty($userName) && !empty($passport)) {
            $member->setNickname($passport . ' - ' . $userName, 'Set do passaporte');
        }
    }

    public static function extractPassport(?Interaction $interaction, ?Member $member = null): ?string {
        $nick = $member?->nick ?? $interaction?->member?->nick ?? '';
        $passport = explode(' - ', $nick)[0];
        if (!is_numeric($passport)) {
            return null;
        }
        return $passport;
    }

    public static function now(): string {
        $timezone = new DateTimeZone('America/Sao_Paulo');
        $datetime = new DateTime('now', $timezone);
        return $datetime->format('Y-m-d H:i:s');
    }
}