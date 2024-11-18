<?php

namespace Dz7;

use Dz7\Database\Create;
use Dz7\Database\Read;
use Dz7\Database\Update;
use stdClass;

class Meta
{

    public function searchMetaPassports(array $passports): array
    {
        $table = $_ENV['TABLE_META_PASSPORT'];
        $read = new Read;

        $metas = [];
        foreach ($passports as $passport) {
            $meta = $read->run("SELECT passport, SUM(quantity) AS total FROM $table WHERE close_id IS NULL AND passport = :passport", ['passport' => $passport])->getResult();

            $meta = $meta[0] ?? null;
            if (empty($meta)) {
                continue;
            }
            $passport = $meta?->passport ?? 0;
            $total = $meta?->total ?? 0;
            $total = $total ?: 0;

            $metas[$passport] = $total;
        }
        unset($read);
        return $metas;
    }

    public function closeMetaPassports(array $passports, int $percentage, int $unitaryValue, int $qtdMeta): void
    {
        $tableMetaPassport = $_ENV['TABLE_META_PASSPORT'];
        $tableMetaClose = $_ENV['TABLE_META_CLOSE'];

        $create = new Create;
        $closeId = $create->run($tableMetaClose, [
            'percentage' => $percentage,
            'unitary_value' => $unitaryValue,
            'qtd_meta' => $qtdMeta,
            'date' => Util::now(),
        ])->getResult();
        if (!empty($closeId)) {
            $update = new Update;
            $update->run($tableMetaPassport, ['close_id' => $closeId], 'WHERE passport IN ("' . implode('", "', $passports) . '") AND close_id IS NULL', []);
        }
    }

    public function setMetaPassport(string $passport, int $gunTrigger, int $gunPart, int $ironIngot, int $copperPlate, int $aluminumPlate): void
    {
        $create = new Create;
        $create->run($_ENV['TABLE_META_PASSPORT'], [
            'passport' => $passport,
            'gunTrigger' => $gunTrigger,
            'gunPart' => $gunPart,
            'ironIngot' => $ironIngot,
            'copperPlate' => $copperPlate,
            'aluminumPlate' => $aluminumPlate,
            'date' => Util::now(),
        ]);
        unset($create);
    }

    public function getMetaPassport(string $passport): array
    {
        $table = $_ENV['TABLE_META_PASSPORT'];
        $read = new Read;
        $meta = $read->run("SELECT SUM(gunTrigger) AS gunTrigger, SUM(gunPart) AS gunPart, SUM(ironIngot) AS ironIngot, SUM(copperPlate) AS copperPlate, SUM(aluminumPlate) AS aluminumPlate FROM $table WHERE close_id IS NULL AND passport = :passport", ['passport' => $passport])->getResult();
        return (array) ($meta[0] ?? []);
    }
}