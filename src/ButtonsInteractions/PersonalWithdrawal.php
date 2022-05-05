<?php

namespace Dz7\ButtonsInteractions;

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\TextInput;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Dz7\Util;

class PersonalWithdrawal {

    private array $actionsRows = [];

    public function handle(Interaction $interaction, Discord $discord): void {
        $passport = Util::extractPassport($interaction);
        if (empty($passport)) {
            $interaction->acknowledge();
            return;
        }
        
        $this->buildActionsRows();
        $interaction->showModal('Retirado do pessoal - ' . $passport, 'personal_withdrawal', $this->actionsRows);
    }

    private function buildActionsRows(): void {
        $itemInput = TextInput::new('Item retirado:', TextInput::STYLE_SHORT, 'item')
            ->setRequired(true)
            ->setPlaceholder('Ex: Dinheiro Sujo')
            ->setMaxLength(300);
        $itemRow = ActionRow::new()->addComponent($itemInput);
        $this->actionsRows[] = $itemRow;

        $quantityInput = TextInput::new('Quantidade:', TextInput::STYLE_SHORT, 'quantity')
            ->setRequired(true)
            ->setPlaceholder('Ex: 10298');
        $quantityRow = ActionRow::new()->addComponent($quantityInput);
        $this->actionsRows[] = $quantityRow;
    }
}