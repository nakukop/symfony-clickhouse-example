<?php

declare(strict_types=1);

namespace App\Service\RequestFilter\Gambling;

use App\DTO\Request\ReportRequestDto;
use App\DTO\Request\RequestFilterField;
use App\Service\ReportMetaInfo\Attribute\MetaInfo as MI;
use App\Service\ReportMetaInfo\DictionaryDataProvider as Dictionary;
use App\Service\RequestFilter\RequestFilterMapperInterface;
use App\Validator\GamblingSessionsFilterConstraint;
use App\Validator\RequestFilterFieldConstraint;
use Symfony\Component\Validator\Constraints as Assert;

#[GamblingSessionsFilterConstraint]
final class GamblingSessionsRequestFilter implements RequestFilterMapperInterface
{
    public const DEVICES = [
        'mobile',
        'tablet',
        'desktop',
        'other',
    ];

    public const GAME_MODES = ['demo', 'real'];

    public const SESSION_STATUSES = ['open', 'close'];

    private const ALL_COMPARE_TYPES = [
        RequestFilterField::FIELD_EQUAL,
        //RequestFilterField::FIELD_NOT_EQUAL,
        //RequestFilterField::FIELD_GREAT,
        RequestFilterField::FIELD_GREAT_OR_EQUAL,
        //RequestFilterField::FIELD_LESS,
        RequestFilterField::FIELD_LESS_OR_EQUAL,
    ];

    #[MI(
        id: 'dateStart',
        type: MI::TYPE_COMPARE,
        title: 'Date Start',
        isRequired: false,
        isMulti: false,
        valueType: MI::VALUE_TYPE_DATE_TIME,
        compareModes: MI::COMPARE_MODES_ALL,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_INTEGER],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => self::ALL_COMPARE_TYPES,
    ])]
    private ?RequestFilterField $dateStart = null;

    #[MI(
        id: 'dateEnd',
        type: MI::TYPE_COMPARE,
        title: 'Date End',
        isRequired: false,
        isMulti: false,
        valueType: MI::VALUE_TYPE_DATE_TIME,
        compareModes: MI::COMPARE_MODES_ALL,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_INTEGER],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => self::ALL_COMPARE_TYPES,
    ])]
    private ?RequestFilterField $dateEnd = null;

    #[MI(
        id: 'dateActive',
        type: MI::TYPE_COMPARE,
        title: 'Date Active',
        isRequired: false,
        isMulti: false,
        valueType: MI::VALUE_TYPE_DATE_TIME,
        compareModes: MI::COMPARE_MODES_ALL,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_INTEGER],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => self::ALL_COMPARE_TYPES,
    ])]
    private ?RequestFilterField $dateActive = null;

    #[MI(
        id: 'duration',
        type: MI::TYPE_COMPARE,
        title: 'Duration',
        isRequired: false,
        isMulti: false,
        valueType: MI::VALUE_TYPE_INT,
        compareModes: MI::COMPARE_MODES_ALL,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_INTEGER],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => self::ALL_COMPARE_TYPES,
    ])]
    private ?RequestFilterField $duration = null;

    #[MI(
        id: 'hallId',
        type: MI::TYPE_SELECT,
        title: 'Hall Id',
        isRequired: false,
        isMulti: true,
        valueType: MI::VALUE_TYPE_UUID,
        dictionary: Dictionary::DICTIONARY_GAMBLING_HALLS,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_UUID],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => [RequestFilterField::FIELD_VALUES],
    ])]
    private ?RequestFilterField $hallId = null;

    #[MI(
        id: 'countryCode',
        type: MI::TYPE_SELECT,
        title: 'Country Code',
        isRequired: false,
        isMulti: true,
        valueType: MI::VALUE_TYPE_STRING,
        dictionary: Dictionary::DICTIONARY_GAMBLING_COUNTRIES,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_STRING],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => [RequestFilterField::FIELD_VALUES],
    ])]
    private ?RequestFilterField $countryCode = null;

    #[MI(
        id: 'accountId',
        type: MI::TYPE_INPUT,
        title: 'Account Id',
        isRequired: false,
        isMulti: false,
        valueType: MI::VALUE_TYPE_STRING,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_STRING],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => [RequestFilterField::FIELD_VALUE],
    ])]
    private ?RequestFilterField $accountId = null;

    #[MI(
        id: 'ip',
        type: MI::TYPE_INPUT,
        title: 'IP Address',
        isRequired: false,
        isMulti: false,
        valueType: MI::VALUE_TYPE_STRING,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_STRING],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => [RequestFilterField::FIELD_VALUE],
    ])]
    private ?RequestFilterField $ip = null;

    #[MI(
        id: 'device',
        type: MI::TYPE_SELECT,
        title: 'Device',
        isRequired: false,
        isMulti: true,
        valueType: MI::VALUE_TYPE_STRING,
        dictionary: Dictionary::DICTIONARY_GAMBLING_DEVICES,
    )]
    #[Assert\Expression(expression: "this.isDeviceInList()", message: 'Device type is invalid.')]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_STRING],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => [RequestFilterField::FIELD_VALUES],
    ])]
    private ?RequestFilterField $device = null;

    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_STRING],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => [RequestFilterField::FIELD_VALUES],
    ])]
    private ?RequestFilterField $source = null;

    #[MI(
        id: 'providerId',
        type: MI::TYPE_SELECT,
        title: 'Provider Id',
        isRequired: false,
        isMulti: true,
        valueType: MI::VALUE_TYPE_UUID,
        dictionary: Dictionary::DICTIONARY_GAMBLING_PROVIDERS,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_UUID],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => [RequestFilterField::FIELD_VALUES],
    ])]
    private ?RequestFilterField $providerId = null;

    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_STRING],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => [RequestFilterField::FIELD_VALUES],
    ])]
    private ?RequestFilterField $product = null;

    #[MI(
        id: 'gameType',
        type: MI::TYPE_SELECT,
        title: 'Game Type',
        isRequired: false,
        isMulti: true,
        valueType: MI::VALUE_TYPE_STRING,
        dictionary: Dictionary::DICTIONARY_GAMBLING_GAME_TYPES,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_STRING],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => [RequestFilterField::FIELD_VALUES],
    ])]
    private ?RequestFilterField $gameType = null;

    #[MI(
        id: 'gameId',
        type: MI::TYPE_SELECT,
        title: 'Game Id',
        isRequired: false,
        isMulti: true,
        valueType: MI::VALUE_TYPE_UUID,
        dictionary: Dictionary::DICTIONARY_GAMBLING_GAMES,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_UUID],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => [RequestFilterField::FIELD_VALUES],
    ])]
    private ?RequestFilterField $gameId = null;

    #[MI(
        id: 'gameMode',
        type: MI::TYPE_SELECT,
        title: 'Game Mode',
        isRequired: false,
        isMulti: false,
        valueType: MI::VALUE_TYPE_STRING,
        dictionary: Dictionary::DICTIONARY_GAMBLING_GAME_MODES,
    )]
    #[Assert\Expression(expression: 'this.isGameModeInList()', message: 'Game mode is invalid.')]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_STRING],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => [RequestFilterField::FIELD_VALUE],
    ])]
    private ?RequestFilterField $gameMode = null;

    #[MI(
        id: 'sessionStatus',
        type: MI::TYPE_SELECT,
        title: 'Game Session Status',
        isRequired: false,
        isMulti: false,
        valueType: MI::VALUE_TYPE_STRING,
        dictionary: Dictionary::DICTIONARY_GAMBLING_SESSION_STATUSES,
    )]
    #[Assert\Expression(expression: 'this.isSessionStatusInList()', message: 'Session status is invalid.')]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_STRING],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => [RequestFilterField::FIELD_VALUE],
    ])]
    private ?RequestFilterField $sessionStatus = null;

    #[MI(
        id: 'gameSessionId',
        type: MI::TYPE_INPUT,
        title: 'Game Session Id',
        isRequired: false,
        isMulti: false,
        valueType: MI::VALUE_TYPE_INT,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_INTEGER],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => [RequestFilterField::FIELD_VALUE],
    ])]
    private ?RequestFilterField $gameSessionId = null;

    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_STRING],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => [RequestFilterField::FIELD_VALUE],
    ])]
    private ?RequestFilterField $gameSessionProviderId = null;

    #[MI(
        id: 'currencyCode',
        type: MI::TYPE_SELECT,
        title: 'Currency Code',
        isRequired: false,
        isMulti: true,
        valueType: MI::VALUE_TYPE_STRING,
        dictionary: Dictionary::DICTIONARY_GAMBLING_CURRENCIES,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_STRING],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => [RequestFilterField::FIELD_VALUES],
    ])]
    private ?RequestFilterField $currencyCode = null;

    #[MI(
        id: 'betCount',
        type: MI::TYPE_COMPARE,
        title: 'Bet Count',
        isRequired: false,
        isMulti: false,
        valueType: MI::VALUE_TYPE_INT,
        compareModes: MI::COMPARE_MODES_ALL,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_INTEGER],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => self::ALL_COMPARE_TYPES,
    ])]
    private ?RequestFilterField $betCount = null;

    #[MI(
        id: 'betAmount',
        type: MI::TYPE_COMPARE,
        title: 'Bet Amount',
        isRequired: false,
        isMulti: false,
        valueType: MI::VALUE_TYPE_DECIMAL,
        compareModes: MI::COMPARE_MODES_ALL,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_DECIMAL],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => self::ALL_COMPARE_TYPES,
    ])]
    private ?RequestFilterField $betAmount = null;

    #[MI(
        id: 'winCount',
        type: MI::TYPE_COMPARE,
        title: 'Win Count',
        isRequired: false,
        isMulti: false,
        valueType: MI::VALUE_TYPE_INT,
        compareModes: MI::COMPARE_MODES_ALL,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_INTEGER],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => self::ALL_COMPARE_TYPES,
    ])]
    private ?RequestFilterField $winCount = null;

    #[MI(
        id: 'winAmount',
        type: MI::TYPE_COMPARE,
        title: 'Win Amount',
        isRequired: false,
        isMulti: false,
        valueType: MI::VALUE_TYPE_DECIMAL,
        compareModes: MI::COMPARE_MODES_ALL,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_DECIMAL],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => self::ALL_COMPARE_TYPES,
    ])]
    private ?RequestFilterField $winAmount = null;

    #[MI(
        id: 'freespinCount',
        type: MI::TYPE_COMPARE,
        title: 'Freespin Count',
        isRequired: false,
        isMulti: false,
        valueType: MI::VALUE_TYPE_INT,
        compareModes: MI::COMPARE_MODES_ALL,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_INTEGER],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => self::ALL_COMPARE_TYPES,
    ])]
    private ?RequestFilterField $freespinCount = null;

    #[MI(
        id: 'jackpotAmount',
        type: MI::TYPE_COMPARE,
        title: 'Jackpot Amount',
        isRequired: false,
        isMulti: false,
        valueType: MI::VALUE_TYPE_DECIMAL,
        compareModes: MI::COMPARE_MODES_ALL,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_DECIMAL],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => self::ALL_COMPARE_TYPES,
    ])]
    private ?RequestFilterField $jackpotAmount = null;

    #[MI(
        id: 'refundCount',
        type: MI::TYPE_COMPARE,
        title: 'Refund Count',
        isRequired: false,
        isMulti: false,
        valueType: MI::VALUE_TYPE_INT,
        compareModes: MI::COMPARE_MODES_ALL,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_INTEGER],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => self::ALL_COMPARE_TYPES,
    ])]
    private ?RequestFilterField $refundCount = null;

    #[MI(
        id: 'refundAmount',
        type: MI::TYPE_COMPARE,
        title: 'Refund Amount',
        isRequired: false,
        isMulti: false,
        valueType: MI::VALUE_TYPE_DECIMAL,
        compareModes: MI::COMPARE_MODES_ALL,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_DECIMAL],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => self::ALL_COMPARE_TYPES,
    ])]
    private ?RequestFilterField $refundAmount = null;

    #[MI(
        id: 'rollbackCount',
        type: MI::TYPE_COMPARE,
        title: 'Rollback Count',
        isRequired: false,
        isMulti: false,
        valueType: MI::VALUE_TYPE_INT,
        compareModes: MI::COMPARE_MODES_ALL,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_INTEGER],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => self::ALL_COMPARE_TYPES,
    ])]
    private ?RequestFilterField $rollbackCount = null;

    #[MI(
        id: 'profit',
        type: MI::TYPE_COMPARE,
        title: 'Profit',
        isRequired: false,
        isMulti: false,
        valueType: MI::VALUE_TYPE_DECIMAL,
        compareModes: MI::COMPARE_MODES_ALL,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_DECIMAL],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => self::ALL_COMPARE_TYPES,
    ])]
    private ?RequestFilterField $profit = null;

    #[MI(
        id: 'margin',
        type: MI::TYPE_COMPARE,
        title: 'Margin',
        isRequired: false,
        isMulti: false,
        valueType: MI::VALUE_TYPE_DECIMAL,
        compareModes: MI::COMPARE_MODES_ALL,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_DECIMAL],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => self::ALL_COMPARE_TYPES,
    ])]
    private ?RequestFilterField $margin = null;

    #[MI(
        id: 'rtp',
        type: MI::TYPE_COMPARE,
        title: 'Rtp',
        isRequired: false,
        isMulti: false,
        valueType: MI::VALUE_TYPE_DECIMAL,
        compareModes: MI::COMPARE_MODES_ALL,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_DECIMAL],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => self::ALL_COMPARE_TYPES,
    ])]
    private ?RequestFilterField $rtp = null;

    #[MI(
        id: 'avgBet',
        type: MI::TYPE_COMPARE,
        title: 'Avg Bet',
        isRequired: false,
        isMulti: false,
        valueType: MI::VALUE_TYPE_DECIMAL,
        compareModes: MI::COMPARE_MODES_ALL,
    )]
    #[RequestFilterFieldConstraint(options: [
        RequestFilterFieldConstraint::KEY_ALLOWED_VALUES_TYPES => [RequestFilterFieldConstraint::VALUES_TYPE_DECIMAL],
        RequestFilterFieldConstraint::KEY_ALLOWED_FIELDS_TYPES => self::ALL_COMPARE_TYPES,
    ])]
    private ?RequestFilterField $avgBet = null;

    public function getDateStart(): ?RequestFilterField
    {
        return $this->dateStart;
    }

    public function setDateStart(?RequestFilterField $dateStart): void
    {
        $this->dateStart = $dateStart;
    }

    public function getDateEnd(): ?RequestFilterField
    {
        return $this->dateEnd;
    }

    public function setDateEnd(?RequestFilterField $dateEnd): void
    {
        $this->dateEnd = $dateEnd;
    }

    public function getDateActive(): ?RequestFilterField
    {
        return $this->dateActive;
    }

    public function setDateActive(?RequestFilterField $dateActive): void
    {
        $this->dateActive = $dateActive;
    }

    public function getDuration(): ?RequestFilterField
    {
        return $this->duration;
    }

    public function setDuration(?RequestFilterField $duration): void
    {
        $this->duration = $duration;
    }

    public function getHallId(): ?RequestFilterField
    {
        return $this->hallId;
    }

    public function setHallId(?RequestFilterField $hallId): void
    {
        $this->hallId = $hallId;
    }

    public function getCountryCode(): ?RequestFilterField
    {
        return $this->countryCode;
    }

    public function setCountryCode(?RequestFilterField $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    public function getAccountId(): ?RequestFilterField
    {
        return $this->accountId;
    }

    public function setAccountId(?RequestFilterField $accountId): void
    {
        $this->accountId = $accountId;
    }

    public function getIp(): ?RequestFilterField
    {
        return $this->ip;
    }

    public function setIp(?RequestFilterField $ip): void
    {
        $this->ip = $ip;
    }

    public function getDevice(): ?RequestFilterField
    {
        return $this->device;
    }

    public function setDevice(?RequestFilterField $device): void
    {
        $this->device = $device;
    }

    public function getSource(): ?RequestFilterField
    {
        return $this->source;
    }

    public function setSource(?RequestFilterField $source): void
    {
        $this->source = $source;
    }

    public function getProviderId(): ?RequestFilterField
    {
        return $this->providerId;
    }

    public function setProviderId(?RequestFilterField $providerId): void
    {
        $this->providerId = $providerId;
    }

    public function getProduct(): ?RequestFilterField
    {
        return $this->product;
    }

    public function setProduct(?RequestFilterField $product): void
    {
        $this->product = $product;
    }

    public function getGameType(): ?RequestFilterField
    {
        return $this->gameType;
    }

    public function setGameType(?RequestFilterField $gameType): void
    {
        $this->gameType = $gameType;
    }

    public function getGameId(): ?RequestFilterField
    {
        return $this->gameId;
    }

    public function setGameId(?RequestFilterField $gameId): void
    {
        $this->gameId = $gameId;
    }

    public function getGameMode(): ?RequestFilterField
    {
        return $this->gameMode;
    }

    public function setGameMode(?RequestFilterField $gameMode): void
    {
        $this->gameMode = $gameMode;
    }

    public function getSessionStatus(): ?RequestFilterField
    {
        return $this->sessionStatus;
    }

    public function setSessionStatus(?RequestFilterField $sessionStatus): void
    {
        $this->sessionStatus = $sessionStatus;
    }

    public function getGameSessionId(): ?RequestFilterField
    {
        return $this->gameSessionId;
    }

    public function setGameSessionId(?RequestFilterField $gameSessionId): void
    {
        $this->gameSessionId = $gameSessionId;
    }

    public function getGameSessionProviderId(): ?RequestFilterField
    {
        return $this->gameSessionProviderId;
    }

    public function setGameSessionProviderId(?RequestFilterField $gameSessionProviderId): void
    {
        $this->gameSessionProviderId = $gameSessionProviderId;
    }

    public function getCurrencyCode(): ?RequestFilterField
    {
        return $this->currencyCode;
    }

    public function setCurrencyCode(?RequestFilterField $currencyCode): void
    {
        $this->currencyCode = $currencyCode;
    }

    public function getBetCount(): ?RequestFilterField
    {
        return $this->betCount;
    }

    public function setBetCount(?RequestFilterField $betCount): void
    {
        $this->betCount = $betCount;
    }

    public function getBetAmount(): ?RequestFilterField
    {
        return $this->betAmount;
    }

    public function setBetAmount(?RequestFilterField $betAmount): void
    {
        $this->betAmount = $betAmount;
    }

    public function getWinCount(): ?RequestFilterField
    {
        return $this->winCount;
    }

    public function setWinCount(?RequestFilterField $winCount): void
    {
        $this->winCount = $winCount;
    }

    public function getWinAmount(): ?RequestFilterField
    {
        return $this->winAmount;
    }

    public function setWinAmount(?RequestFilterField $winAmount): void
    {
        $this->winAmount = $winAmount;
    }

    public function getFreespinCount(): ?RequestFilterField
    {
        return $this->freespinCount;
    }

    public function setFreespinCount(?RequestFilterField $freespinCount): void
    {
        $this->freespinCount = $freespinCount;
    }

    public function getJackpotAmount(): ?RequestFilterField
    {
        return $this->jackpotAmount;
    }

    public function setJackpotAmount(?RequestFilterField $jackpotAmount): void
    {
        $this->jackpotAmount = $jackpotAmount;
    }

    public function getRefundCount(): ?RequestFilterField
    {
        return $this->refundCount;
    }

    public function setRefundCount(?RequestFilterField $refundCount): void
    {
        $this->refundCount = $refundCount;
    }

    public function getRefundAmount(): ?RequestFilterField
    {
        return $this->refundAmount;
    }

    public function setRefundAmount(?RequestFilterField $refundAmount): void
    {
        $this->refundAmount = $refundAmount;
    }

    public function getRollbackCount(): ?RequestFilterField
    {
        return $this->rollbackCount;
    }

    public function setRollbackCount(?RequestFilterField $rollbackCount): void
    {
        $this->rollbackCount = $rollbackCount;
    }

    public function getProfit(): ?RequestFilterField
    {
        return $this->profit;
    }

    public function setProfit(?RequestFilterField $profit): void
    {
        $this->profit = $profit;
    }

    public function getMargin(): ?RequestFilterField
    {
        return $this->margin;
    }

    public function setMargin(?RequestFilterField $margin): void
    {
        $this->margin = $margin;
    }

    public function getRtp(): ?RequestFilterField
    {
        return $this->rtp;
    }

    public function setRtp(?RequestFilterField $rtp): void
    {
        $this->rtp = $rtp;
    }

    public function getAvgBet(): ?RequestFilterField
    {
        return $this->avgBet;
    }

    public function setAvgBet(?RequestFilterField $avgBet): void
    {
        $this->avgBet = $avgBet;
    }

    /**
     * @inheritDoc
     */
    // phpcs:ignore
    public function getMap(ReportRequestDto $requestDto): array
    {
        return [];
    }

    public function isDeviceInList(): bool
    {
        if ($this->getDevice() === null) {
            return true;
        }

        $isValueInList = $this->getDevice()->getValue() === ''
            || in_array($this->getDevice()->getValue(), self::DEVICES, true);

        $isValuesInList = true;

        foreach ($this->getDevice()->getValues() as $device) {
            if (!in_array($device, self::DEVICES, true)) {
                $isValuesInList = false;

                break;
            }
        }

        return $isValueInList && $isValuesInList;
    }

    public function isGameModeInList(): bool
    {
        if ($this->getGameMode() === null) {
            return true;
        }

        return $this->getGameMode()->getValue() === ''
            || in_array($this->getGameMode()->getValue(), self::GAME_MODES, true);
    }

    public function isSessionStatusInList(): bool
    {
        if ($this->getSessionStatus() === null) {
            return true;
        }

        return $this->getSessionStatus()->getValue() === ''
            || in_array($this->getSessionStatus()->getValue(), self::SESSION_STATUSES, true);
    }
}
