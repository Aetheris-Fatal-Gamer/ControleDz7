<?php

namespace Dz7\Services;

use Discord\Parts\Channel\Message;
use Dz7\Database\Create;
use Dz7\Util;

class BoxService {

    public static function register(Message $message): void {
        $embeds = $message?->embeds;
        if (empty($embeds)) return;
        $fields = $embeds[0]?->fields;
        if (empty($fields)) return;

        $currentField = null;
        foreach ($fields as $field) {
            $currentField = $field;
            break;
        }
        if (empty($currentField)) return;

        $passportId = str_replace('USER_ID: ', '', $currentField['name']);
        $value = str_replace("\n", '', $currentField['value']);
        if (str_contains($value, 'BAU DO LIDER')) return;

        $itemOperation = str_contains($value, 'GUARDOU') ? 'GUARDOU' : 'RETIROU';
        $value = str_replace($itemOperation . ' ITEM DO BAU', '', $value);
        $value = explode(' ', $value);
        $itemQuantity = array_pop($value);
        $itemQuantity = str_replace('x', '', $itemQuantity);
        $itemName = implode(' ', $value ?? []);
        if (empty($itemQuantity) || !is_numeric($itemQuantity) || empty($itemName)) return;

        $userId = null;
        $userAvatar = null;
        $userNick = null;
        $userPassport = $passportId;

        $guildId = $message->guild->id;

        foreach ($message->guild->members as $member) {
            $userNick = $member->nick;
            if (empty($userNick)) {
                continue;
            }

            $memberPassport = explode(' ', $member->nick)[0] ?? null;
            if (empty($memberPassport) || !is_numeric($memberPassport)) {
                continue;
            }
            if ($memberPassport != $userPassport) {
                continue;
            }

            $memberAvatar = $member?->user?->avatar;
            if (!empty($memberAvatar) && $memberAvatar != '') {
                $userAvatar = $memberAvatar;
            }
            if (empty($userNick) || $userNick == 'null') {
                $userNick = $userPassport;
            }

            $userId = $member->user->id ?? null;
            break;
        }

        $userAvatar = $userAvatar ?? 'https://homeosapiens.com.br/wp-content/uploads/2015/02/emptyuserphoto.png';

        $create = new Create;
        // $create->run($_ENV['TABLE_META_PASSPORT'], [
        $create->run('chest_processing_queue', [
            'guild_id' => $guildId,
            'user_id' => $userId,
            'user_passport' => $userPassport,
            'user_nick' => $userNick,
            'user_avatar' => $userAvatar,
            'item_operation' => $itemOperation,
            'item_name' => $itemName,
            'item_quantity' => $itemQuantity,
            'processed' => 0,
            'date' => Util::now(),
        ]);
        unset($create);
    }

    public static function registerLeader(Message $message): void {
        $embeds = $message?->embeds;
        if (empty($embeds)) return;
        $fields = $embeds[0]?->fields;
        if (empty($fields)) return;

        $currentField = null;
        foreach ($fields as $field) {
            $currentField = $field;
            break;
        }
        if (empty($currentField)) return;

        $passportId = str_replace('USER_ID: ', '', $currentField['name']);
        $value = str_replace("\n", '', $currentField['value']);
        if (!str_contains($value, 'BAU DO LIDER')) return;

        $itemOperation = str_contains($value, 'GUARDOU') ? 'GUARDOU' : 'RETIROU';
        $value = str_replace($itemOperation . ' ITEM DO BAU DO LIDER', '', $value);
        $value = explode(' ', $value);
        $itemQuantity = array_pop($value);
        $itemQuantity = str_replace('x', '', $itemQuantity);
        $itemName = implode(' ', $value ?? []);
        if (empty($itemQuantity) || !is_numeric($itemQuantity) || empty($itemName)) return;

        $userId = null;
        $userAvatar = null;
        $userNick = null;
        $userPassport = $passportId;

        $guildId = $message->guild->id;

        foreach ($message->guild->members as $member) {
            $userNick = $member->nick;
            if (empty($userNick)) {
                continue;
            }

            $memberPassport = explode(' ', $member->nick)[0] ?? null;
            if (empty($memberPassport) || !is_numeric($memberPassport)) {
                continue;
            }
            if ($memberPassport != $userPassport) {
                continue;
            }

            $memberAvatar = $member?->user?->avatar;
            if (!empty($memberAvatar) && $memberAvatar != '') {
                $userAvatar = $memberAvatar;
            }
            if (empty($userNick) || $userNick == 'null') {
                $userNick = $userPassport;
            }

            $userId = $member->user->id ?? null;
            break;
        }

        $userAvatar = $userAvatar ?? 'https://homeosapiens.com.br/wp-content/uploads/2015/02/emptyuserphoto.png';

        $create = new Create;
        $create->run('leader_chest_processing_queue', [
            'guild_id' => $guildId,
            'user_id' => $userId,
            'user_passport' => $userPassport,
            'user_nick' => $userNick,
            'user_avatar' => $userAvatar,
            'item_operation' => $itemOperation,
            'item_name' => $itemName,
            'item_quantity' => $itemQuantity,
            'processed' => 0,
            'date' => Util::now(),
        ]);
        unset($create);
    }
}