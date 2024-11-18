<?php

namespace Dz7\ButtonsInteractions;

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\TextInput;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Dz7\Util;

class NewOrder {

    private array $actionsRows = [];

    public function handle(Interaction $interaction, Discord $discord): void {
        $this->buildActionsRows($interaction);

        $passport = Util::extractPassport($interaction);
        $passport = $passport ? ' - ' . $passport : '';
        $interaction->showModal('ᴄᴀᴅᴀꜱᴛʀᴀʀ ɴᴏᴠᴀ ᴇɴᴄᴏᴍᴇɴᴅᴀ' . $passport, 'new_order', $this->actionsRows);
    }

    private function buildActionsRows(Interaction $interaction): void {
        $orderPersonNameInput = TextInput::new('ᴠᴜʟɢᴏ ᴅᴇ Qᴜᴇᴍ ᴇɴᴄᴏᴍᴇɴᴅᴏᴜ:', TextInput::STYLE_SHORT, 'orderPersonName')
            ->setRequired(true)
            ->setPlaceholder('ᴇx: ᴘɪꜱᴛᴀ')
            ->setMaxLength(300);
        $itemRow = ActionRow::new()->addComponent($orderPersonNameInput);
        $this->actionsRows[] = $itemRow;

        $orderPersonContactInput = TextInput::new('ᴄᴏɴᴛᴀᴛᴏ ᴅᴇ Qᴜᴇᴍ ᴇɴᴄᴏᴍᴇɴᴅᴏᴜ:', TextInput::STYLE_SHORT, 'orderPersonContact')
            ->setRequired(false)
            ->setPlaceholder('ᴇx: ᴄᴏɴᴛᴀᴛᴏ ᴘᴇꜱꜱᴏᴀʟ ᴏᴜ ᴅᴏ ᴛʜᴏʀ (ᴏᴘᴄɪᴏɴᴀʟ)')
            ->setMaxLength(300);
        $itemRow = ActionRow::new()->addComponent($orderPersonContactInput);
        $this->actionsRows[] = $itemRow;

        $orderItemsInput = TextInput::new('ɪᴛᴇɴꜱ ᴅᴀ ᴇɴᴄᴏᴍᴇɴᴅᴀ:', TextInput::STYLE_SHORT, 'orderItems')
            ->setRequired(true)
            ->setPlaceholder('ᴇx: 5 ꜰɪᴠᴇ, 10 ꜱɪɢ');
        $quantityRow = ActionRow::new()->addComponent($orderItemsInput);
        $this->actionsRows[] = $quantityRow;

        $deliveryDateInput = TextInput::new('ᴅᴀᴛᴀ ᴅᴀ ᴇɴᴛʀᴇɢᴀ:', TextInput::STYLE_SHORT, 'deliveryDate')
            ->setRequired(true)
            ->setPlaceholder('ᴇx: ᴀ ɴᴏɪᴛᴇ ɴᴏ ᴅɪᴀ 20/01/2023');
        $currentTotalRow = ActionRow::new()->addComponent($deliveryDateInput);
        $this->actionsRows[] = $currentTotalRow;

        $saleValueInput = TextInput::new('ᴠᴀʟᴏʀ ᴅᴀ ᴇɴᴄᴏᴍᴇɴᴅᴀ:', TextInput::STYLE_SHORT, 'saleValue')
            ->setRequired(true)
            ->setPlaceholder('ᴇx: 356000 (ᴏ ᴠᴀʟᴏʀ ᴘʀᴇᴄɪꜱᴀ ᴇꜱᴛᴀʀ ᴇᴍ ɴᴜᴍᴇʀᴀʟ)');
        $currentTotalRow = ActionRow::new()->addComponent($saleValueInput);
        $this->actionsRows[] = $currentTotalRow;
    }
}