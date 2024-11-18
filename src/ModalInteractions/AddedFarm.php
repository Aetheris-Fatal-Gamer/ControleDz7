<?php

namespace Dz7\ModalInteractions;

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use Discord\Parts\Interactions\Interaction;
use Dz7\ButtonsInteractions\AddedFarmTotal;
use Dz7\Meta;
use Dz7\Util;

class AddedFarm {

    public function handle(Interaction $interaction, Discord $discord, $components): void
    {
        foreach ($components as $actionRow) {
            foreach ($actionRow->components as $component) {
                $customId = $component->custom_id;
                $$customId = $component->value;
            }
        }

        $passport = $passport ?? Util::extractPassport($interaction);

        // gunTrigger, gunPart, ironIngot, aluminumPlate, copperPlate
        if (empty($gunTrigger) && empty($gunPart) && empty($ironIngot) && empty($aluminumPlate) && empty($copperPlate)) {
            return;
        }

        if (
            !empty($gunTrigger) && !is_numeric($gunTrigger) ||
            !empty($gunPart) && !is_numeric($gunPart) ||
            !empty($ironIngot) && !is_numeric($ironIngot) ||
            !empty($aluminumPlate) && !is_numeric($aluminumPlate) ||
            !empty($copperPlate) && !is_numeric($copperPlate)
        ) {
            return;
        }

        if (!is_numeric($passport)) {
            return;
        }
        $member = $interaction->member;
        Util::setPassportInName($member, $passport);

        $interaction->acknowledge()->then(function () use ($interaction, $discord) {
            $addedFarmTotal = new AddedFarmTotal;
            $addedFarmTotal->handle($interaction, $discord);
        });
    }
}