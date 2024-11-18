<?php

namespace Dz7;

use \Discord\Discord;
use \Discord\Parts\Interactions\Interaction;

class ButtonInteraction {

    public static function handle(Interaction $interaction, Discord $discord): void {
        $customId = $interaction->data->custom_id;
        $params = explode('-', $customId);

        $customId = array_shift($params);
        $customId = Util::formatCustomId($customId);

        $params = empty($params) ? [] : $params;

        if (!file_exists(__DIR__ . "/ButtonsInteractions/$customId.php")) {
            return;
        }
        $class = "Dz7\\ButtonsInteractions\\" . $customId;
        $instaceClass = new $class;
        $instaceClass->handle($interaction, $discord, $params);
    }
}