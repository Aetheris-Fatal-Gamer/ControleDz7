<?php

namespace Dz7\ButtonsInteractions;

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\TextInput;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Dz7\Util;

class PersonalAdded {

    private array $actionsRows = [];

    public function handle(Interaction $interaction, Discord $discord): void {
        $passport = Util::extractPassport($interaction);
        if (empty($passport)) {
            $interaction->acknowledge();
            return;
        }
        
        $this->buildActionsRows();
        $interaction->showModal('Adicionar no pessoal - ' . $passport, 'personal_added', $this->actionsRows);
    }

    private function buildActionsRows(): void {
        $itemInput = TextInput::new('Item adicionado:', TextInput::STYLE_SHORT, 'item')
            ->setRequired(true)
            ->setPlaceholder('Ex: Dinheiro Sujo')
            ->setMaxLength(300);
        $itemRow = ActionRow::new()->addComponent($itemInput);
        $this->actionsRows[] = $itemRow;

        $quantityInput = TextInput::new('Quantidade:', TextInput::STYLE_SHORT, 'quantity')
            ->setRequired(true)
            ->setPlaceholder('Ex: 11398');
        $quantityRow = ActionRow::new()->addComponent($quantityInput);
        $this->actionsRows[] = $quantityRow;
    }
}