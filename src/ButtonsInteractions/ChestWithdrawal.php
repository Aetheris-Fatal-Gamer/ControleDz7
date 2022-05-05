<?php

namespace Dz7\ButtonsInteractions;

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\TextInput;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Dz7\Util;

class ChestWithdrawal {

    private $actionsRows = [];

    public function handle(Interaction $interaction, Discord $discord): void {
        $this->buildActionsRows($interaction);

        $passport = Util::extractPassport($interaction);
        $passport = $passport ? ' - ' . $passport : '';
        $interaction->showModal('Retirado do baú' . $passport, 'chest_withdrawal', $this->actionsRows);
    }

    private function buildActionsRows(Interaction $interaction): void {
        $passport = Util::extractPassport($interaction);

        if (empty($passport)) {
            $passportInput = TextInput::new('Digite o seu passaporte:', TextInput::STYLE_SHORT, 'passport')
                ->setRequired(true)
                ->setValue($passport);
            $passportRow = ActionRow::new()->addComponent($passportInput);
            $this->actionsRows[] = $passportRow;
        }

        $itemInput = TextInput::new('Item retirado:', TextInput::STYLE_SHORT, 'item')
            ->setRequired(true)
            ->setPlaceholder('Ex: Ergolina');
        $itemRow = ActionRow::new()->addComponent($itemInput);
        $this->actionsRows[] = $itemRow;

        $quantityInput = TextInput::new('Quantidade:', TextInput::STYLE_SHORT, 'quantity')
            ->setRequired(true)
            ->setPlaceholder('Ex: 105');
        $quantityRow = ActionRow::new()->addComponent($quantityInput);
        $this->actionsRows[] = $quantityRow;

        $remainingTotalInput = TextInput::new('Restante no baú:', TextInput::STYLE_SHORT, 'remainingTotal')
            ->setRequired(true)
            ->setPlaceholder('Ex: 100');
        $remainingTotalRow = ActionRow::new()->addComponent($remainingTotalInput);
        $this->actionsRows[] = $remainingTotalRow;
    }
}