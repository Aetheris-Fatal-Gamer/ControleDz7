<?php

namespace Dz7\Tasks;

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use Dz7\Database\Create;
use Dz7\Database\Read;
use Dz7\Database\Update;

class UpdateBoxQueue {

    public static function handle(Discord &$discord): void {
        try {
            $table = 'control_update_chest';

            $read = new Read;
            $controlUpdateChest = $read->run("SELECT id, needs_to_update FROM $table WHERE needs_to_update = :needs_to_update", [
                'needs_to_update' => 1
            ])->getResult();

            if (empty($controlUpdateChest)) {
                return;
            }

            $update = new Update;
            $update->run('control_update_chest', ['needs_to_update' => 0], 'WHERE id = :id', ['id' => 1]);

            $guild = $discord->guilds->get('id', '969755019073159228');
            if (empty($guild)) return;
            $channel = $guild->channels->get('id', '1065788969532542987');
            if (empty($channel)) return;

            $channel->getMessageHistory(['limit' => 100])->done(function($messages) {
                foreach ($messages as $messageHistory) {
                    if ($messageHistory->id != 1065792566261461092) {
                        continue;
                    }

                    $read = new Read;
                    $items = $read->run("SELECT item_name, item_quantity FROM stock_items WHERE item_quantity != 0 ORDER BY CAST(item_quantity AS INT) DESC")->getResult();
                    if (empty($items)) {
                        break;
                    }

                    $itensArrayFarm = [];
                    $itemsArrayNormal = [];
                    foreach($items as $item) {
                        if (in_array($item->item_name, ['Part. Arma', 'Ferro', 'Gatilho', 'Alumínio', 'Cobre', 'Lata de Metal', 'Pilhas'])) {
                            $itensArrayFarm[] = "- **$item->item_name:** $item->item_quantity";
                        } else {
                            $itemsArrayNormal[] = "- **$item->item_name:** $item->item_quantity";
                        }
                    }
                    $itemsNormalMessage = implode("\n", $itemsArrayNormal);
                    $itensFarmMessage = implode("\n", $itensArrayFarm);

                    $content = "**• ɪᴛᴇɴꜱ ᴅᴏ ʙᴀᴜ:**\n$itemsNormalMessage\n\n**• ɪᴛᴇɴꜱ ᴅᴇ ꜰᴀʀᴍ:**\n$itensFarmMessage";
                    $messageBuilder = MessageBuilder::new()->setContent($content);
                    $messageHistory->edit($messageBuilder);
                }
            });
        } catch (\Throwable $e) {
            echo PHP_EOL . PHP_EOL .
                 'ATENÇÃO! Problemas em fazer a atualização do báu: ' . $e->getMessage() .
                 PHP_EOL . PHP_EOL;
        }
    }
}