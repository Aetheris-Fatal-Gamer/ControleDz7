<?php

namespace Dz7\Commands;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Dz7\Meta;

class ViewMeta {

    public function run(array $args, Message $message, Discord $discord): void {
        if ($message->channel->id !== $_ENV['CHANNEL_ALLOW_COMMAND_META']) {
            $message->member->sendMessage('⁉️ Você não pode executar esse comando no canal **' . $message->channel->name . '**');
            $message->delete();
            return;
        }

        if (empty($_ENV['CATEGORY_META_RECORD'])) {
            $message->reply('⁉️ Não consegui encontrar a categoria de metas.');
            return;
        }

        $passports = [];
        foreach ($message->guild->channels as $channel) {
            if ($channel->parent_id !== $_ENV['CATEGORY_META_RECORD']) {
                continue;
            }
            $passport = str_replace('・ᴍᴇᴛᴀ-', '', $channel->name);
            if (!is_numeric($passport)) {
                continue;
            }
            $passports[] = $passport;
        }
        
        if (empty($passports)) {
            $message->reply('⁉️ Não consegui encontrar nenhuma sala de meta.');
            return;
        }
        sort($passports);

        $replyMessage = "```";
        $replyMessage .= "📊 Metas:\n";
        $metas = (new Meta)->searchMetaPassports($passports);
        foreach ($passports as $passport) {
            $total = $metas[$passport] ?? 0;
            $replyMessage .= "\n- $passport: $total";
        }
        $replyMessage .= "```";
        $message->reply($replyMessage)->done(function() use ($message) {
            $message->react('✅');
        });
    }
}