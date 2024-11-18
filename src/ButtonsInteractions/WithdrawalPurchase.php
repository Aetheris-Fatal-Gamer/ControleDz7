<?php

namespace Dz7\ButtonsInteractions;

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use Discord\Parts\Interactions\Interaction;
use Dz7\Database\Update;
use Dz7\Services\OrderService;

class WithdrawalPurchase {

    public function handle(Interaction $interaction, Discord $discord): void {
        $this->buildActionsRows($interaction);
    }

    private function buildActionsRows(Interaction $interaction): void {
        $messageReply = MessageBuilder::new()->setContent('・ᴇꜱꜱᴀ ꜰᴜɴᴄɪᴏɴᴀʟɪᴅᴀᴅᴇ ᴅᴇ ʀᴇɢɪꜱᴛʀᴀʀ ɴᴏᴠᴀ ᴄᴏᴍᴘʀᴀ, ᴇꜱᴛᴀ ᴇᴍ ᴅᴇꜱᴇɴᴠᴏʟᴠɪᴍᴇɴᴛᴏ!
        ');
        $interaction->member->user->sendMessage($messageReply, false, null, null, $interaction->message)->then(function() use ($interaction) {
            $interaction->acknowledge();
        });
    }
}
