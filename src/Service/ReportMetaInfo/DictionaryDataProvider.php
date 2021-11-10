<?php

declare(strict_types=1);

namespace App\Service\ReportMetaInfo;

use App\DB\Connection;
use App\DTO\MetaInfo\ReportMetaInfoDictionaryItem;
use App\Exception\MetaInfoException;
use App\Service\ReportQueryBuilder;
use App\Service\RequestFilter\Gambling\GamblingSessionsRequestFilter;

class DictionaryDataProvider
{
    public const DICTIONARY_GAMBLING_HALLS = 'gambling_halls';

    public const DICTIONARY_GAMBLING_COUNTRIES = 'gambling_countries';

    public const DICTIONARY_GAMBLING_CURRENCIES = 'gambling_currencies';

    public const DICTIONARY_GAMBLING_DEVICES = 'gambling_devices';

    public const DICTIONARY_GAMBLING_PROVIDERS = 'gambling_providers';

    public const DICTIONARY_GAMBLING_GAME_TYPES = 'gambling_game_types';

    public const DICTIONARY_GAMBLING_GAMES = 'gambling_game_list';

    public const DICTIONARY_GAMBLING_SESSION_STATUSES = 'gambling_session_statuses';

    public const DICTIONARY_GAMBLING_GAME_MODES = 'gambling_game_modes';

    private const LABEL = 'label';

    private const VALUE = 'value';

    private const GAME_DEVICES = GamblingSessionsRequestFilter::DEVICES;

    private const GAME_SESSION_STATUSES = GamblingSessionsRequestFilter::SESSION_STATUSES;

    private const GAME_MODES = GamblingSessionsRequestFilter::GAME_MODES;

    public function __construct(private Connection $connection)
    {
    }

    /**
     * @return array<ReportMetaInfoDictionaryItem>
     */
    public function getDictionary(string $dictionary): array
    {
        $map = [
            self::DICTIONARY_GAMBLING_HALLS => fn () => $this->getGamblingHalls(),
            self::DICTIONARY_GAMBLING_COUNTRIES => fn () => $this->getGamblingCountries(),
            self::DICTIONARY_GAMBLING_CURRENCIES => fn () => $this->getGamblingCurrencies(),
            self::DICTIONARY_GAMBLING_DEVICES => fn () => $this->getListByArray(self::GAME_DEVICES),
            self::DICTIONARY_GAMBLING_PROVIDERS => fn () => $this->getGamblingProviders(),
            self::DICTIONARY_GAMBLING_GAME_TYPES => fn () => $this->getGamblingGameTypes(),
            self::DICTIONARY_GAMBLING_GAMES => fn () => $this->getGamblingGameList(),
            self::DICTIONARY_GAMBLING_GAME_MODES => fn () => $this->getListByArray(self::GAME_MODES),
            self::DICTIONARY_GAMBLING_SESSION_STATUSES => fn () => $this->getListByArray(self::GAME_SESSION_STATUSES),
        ];

        if (!array_key_exists($dictionary, $map) || !is_callable($map[$dictionary])) {
            throw new MetaInfoException('Meta-information dictionary does not exist: ' . $dictionary);
        }

        return $map[$dictionary]();
    }

    /**
     * @return array<ReportMetaInfoDictionaryItem>
     */
    private function getGamblingHalls(): array
    {
        $data = [];
        $queryBuilder = new ReportQueryBuilder();
        $queryBuilder->setSelectList([
            'DISTINCT h.external_id AS ' . self::VALUE,
            'h.name AS ' . self::LABEL,
        ]);
        $queryBuilder->setFrom('gambling_sessions AS gs');
        $queryBuilder->setJoinList(['INNER JOIN hall AS h ON gs.hall_id=h.id']);

        foreach ($this->connection->getConnection()->select($queryBuilder->buildQuery(), [])->rows() as $rowData) {
            $item = new ReportMetaInfoDictionaryItem();
            $item->setValue($rowData[self::VALUE]);
            $item->setLabel($rowData[self::LABEL]);
            $data[] = $item;
        }

        return $data;
    }

    /**
     * @return array<ReportMetaInfoDictionaryItem>
     */
    private function getGamblingProviders(): array
    {
        $data = [];
        $queryBuilder = new ReportQueryBuilder();
        $queryBuilder->setSelectList([
            'DISTINCT gp.guid AS ' . self::VALUE,
            'gp.name AS ' . self::LABEL,
        ]);
        $queryBuilder->setFrom('gambling_sessions AS gs');
        $queryBuilder->setJoinList(['INNER JOIN game_provider AS gp ON gs.provider_external_id=gp.id']);

        foreach ($this->connection->getConnection()->select($queryBuilder->buildQuery(), [])->rows() as $rowData) {
            $item = new ReportMetaInfoDictionaryItem();
            $item->setValue($rowData[self::VALUE]);
            $item->setLabel($rowData[self::LABEL]);
            $data[] = $item;
        }

        return $data;
    }

    /**
     * @return array<ReportMetaInfoDictionaryItem>
     */
    private function getGamblingGameTypes(): array
    {
        $data = [];
        $queryBuilder = new ReportQueryBuilder();
        $queryBuilder->setSelectList(['DISTINCT gs.game_type_name AS ' . self::VALUE]);
        $queryBuilder->setFrom('gambling_sessions AS gs');

        foreach ($this->connection->getConnection()->select($queryBuilder->buildQuery(), [])->rows() as $rowData) {
            $item = new ReportMetaInfoDictionaryItem();
            $item->setValue($rowData[self::VALUE]);
            $item->setLabel($rowData[self::VALUE]);
            $data[] = $item;
        }

        return $data;
    }

    /**
     * @return array<ReportMetaInfoDictionaryItem>
     */
    private function getGamblingCountries(): array
    {
        $data = [];
        $queryBuilder = new ReportQueryBuilder();
        $queryBuilder->setSelectList(['DISTINCT gs.country_code AS ' . self::VALUE]);
        $queryBuilder->setFrom('gambling_sessions AS gs');

        foreach ($this->connection->getConnection()->select($queryBuilder->buildQuery(), [])->rows() as $rowData) {
            $item = new ReportMetaInfoDictionaryItem();
            $item->setValue($rowData[self::VALUE]);
            $item->setLabel($rowData[self::VALUE]);
            $data[] = $item;
        }

        return $data;
    }

    /**
     * @return array<ReportMetaInfoDictionaryItem>
     */
    private function getGamblingGameList(): array
    {
        $data = [];
        $queryBuilder = new ReportQueryBuilder();
        $queryBuilder->setSelectList([
            'DISTINCT g.guid AS ' . self::VALUE,
            'g.original_name AS ' . self::LABEL,
        ]);
        $queryBuilder->setFrom('gambling_sessions AS gs');
        $queryBuilder->setJoinList(['INNER JOIN game AS g ON gs.game_external_id=g.id']);

        foreach ($this->connection->getConnection()->select($queryBuilder->buildQuery(), [])->rows() as $rowData) {
            $item = new ReportMetaInfoDictionaryItem();
            $item->setValue($rowData[self::VALUE]);
            $item->setLabel($rowData[self::LABEL]);
            $data[] = $item;
        }

        return $data;
    }

    /**
     * @return array<ReportMetaInfoDictionaryItem>
     */
    private function getGamblingCurrencies(): array
    {
        $data = [];
        $queryBuilder = new ReportQueryBuilder();
        $queryBuilder->setSelectList(['DISTINCT gs.currency_code AS ' . self::VALUE]);
        $queryBuilder->setFrom('gambling_sessions AS gs');

        foreach ($this->connection->getConnection()->select($queryBuilder->buildQuery(), [])->rows() as $rowData) {
            $item = new ReportMetaInfoDictionaryItem();
            $item->setValue($rowData[self::VALUE]);
            $item->setLabel($rowData[self::VALUE]);
            $data[] = $item;
        }

        return $data;
    }

    /**
     * @param array<string> $values
     * @return array<ReportMetaInfoDictionaryItem>
     */
    private function getListByArray(array $values): array
    {
        $data = [];

        foreach ($values as $listItem) {
            $item = new ReportMetaInfoDictionaryItem();
            $item->setValue($listItem);
            $item->setLabel(ucfirst($listItem));
            $data[] = $item;
        }

        return $data;
    }
}
