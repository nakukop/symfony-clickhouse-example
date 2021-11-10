<?php

declare(strict_types=1);

namespace App\Service\Report\Gambling;

use App\DTO\Request\RequestFilterField;
use App\DTO\Response\ReportResponse;
use App\DTO\Response\ReportResponseItem;
use App\Exception\ReportNoFoundGeneralException;
use App\Service\Report\AbstractReport;
use App\Service\Report\ReportInterface;
use App\Service\ReportQueryBuilder;
use App\Service\RequestFilter\Gambling\GamblingSessionsRequestFilter;

final class GamblingSessionsReport extends AbstractReport implements ReportInterface
{
    private const CLOSE_SESSION_INTERVAL = 3_600;

    private const SESSION_STATUS_OPEN = 'open';

    private const SESSION_STATUS_CLOSE = 'close';

    private const GAME_MODE_REAL = 'real';

    private const GAME_MODE_DEMO = 'demo';

    private const DEVICE_TYPE_MOBILE = 'mobile';

    private const DEVICE_TYPE_TABLET = 'tablet';

    private const DEVICE_TYPE_DESKTOP = 'desktop';

    private const DEVICE_TYPE_OTHER = 'other';

    private const DEVICES_ENUM_MAP = [
        self::DEVICE_TYPE_MOBILE => 'DEVICE_MOBILE',
        self::DEVICE_TYPE_TABLET => 'DEVICE_TABLET',
        self::DEVICE_TYPE_DESKTOP => 'DEVICE_DESKTOP',
        self::DEVICE_TYPE_OTHER => 'DEVICE_OTHER',
    ];

    public function prepareStatement(): ReportInterface
    {
        $filter = $this->getReportRequest()->getFilter();

        if (!$filter instanceof GamblingSessionsRequestFilter) {
            throw new ReportNoFoundGeneralException('Report not found by filter type.');
        }

        $queryBuilder = $this->getQueryBuilder();

        $queryBuilder->setFrom('gambling_sessions AS gs');

        // hallId
        if (
            $this->isNeedShowColumn('hallId')
            || $this->isNeedSortColumn('hallId')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getHallId())
        ) {
            $queryBuilder->addToJoinList('LEFT JOIN hall AS h ON h.id=gs.hall_id');
            $queryBuilder->addToSelectList('h.external_id AS hallId');
            $queryBuilder->addToGroupList('h.external_id');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getHallId())) {
            $whereCondition = $queryBuilder->prepareCondition('h.external_id', $filter->getHallId());
            $queryBuilder->addToWhereList(
                sprintf(
                    "h.id IN (
                        SELECT arrayJoin(dictGetDescendants(hall, parent_id)) FROM hall AS h
                    WHERE %s)",
                    $whereCondition->getAssignExpressionsAsString(),
                ),
            );
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        // hallName
        if (
            $this->isNeedShowColumn('hallName')
            || $this->isNeedSortColumn('hallName')
        ) {
            $queryBuilder->addToJoinList('LEFT JOIN hall AS h ON h.id=gs.hall_id');
            $queryBuilder->addToSelectList('h.name AS hallName');
        }

        // accountId
        if (
            $this->isNeedShowColumn('accountId')
            || $this->isNeedSortColumn('accountId')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getAccountId())
        ) {
            $queryBuilder->addToSelectList('gs.player_account_id AS accountId');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getAccountId())) {
            $whereCondition = $queryBuilder->prepareCondition('gs.player_account_id', $filter->getAccountId());
            $queryBuilder->addToWhereList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        // countryCode
        if (
            $this->isNeedShowColumn('countryCode')
            || $this->isNeedSortColumn('countryCode')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getCountryCode())
        ) {
            $queryBuilder->addToSelectList('gs.country_code AS countryCode');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getCountryCode())) {
            $whereCondition = $queryBuilder->prepareCondition('gs.country_code', $filter->getCountryCode());
            $queryBuilder->addToWhereList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        // ip
        if (
            $this->isNeedShowColumn('ip')
            || $this->isNeedSortColumn('ip')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getIp())
        ) {
            $queryBuilder->addToSelectList('gs.ip AS ip');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getIp())) {
            $whereCondition = $queryBuilder->prepareCondition('gs.ip', $filter->getIp(), true);
            $queryBuilder->addToWhereList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        // device
        if (
            $this->isNeedShowColumn('device')
            || $this->isNeedSortColumn('device')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getDevice())
        ) {
            $queryBuilder->addToSelectList('gs.device AS device');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getDevice())) {
            $whereCondition = $queryBuilder->prepareCondition(
                'gs.device',
                $this->prepareFilterByDeviceEnum($filter)->getDevice(),
            );
            $queryBuilder->addToWhereList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        // source
        if (
            $this->isNeedShowColumn('source')
            || $this->isNeedSortColumn('source')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getSource())
        ) {
            $queryBuilder->addToSelectList('gs.source AS source');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getSource())) {
            $whereCondition = $queryBuilder->prepareCondition('gs.source', $filter->getDevice());
            $queryBuilder->addToWhereList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        // providerId
        if (
            $this->isNeedShowColumn('providerId')
            || $this->isNeedSortColumn('providerId')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getProviderId())
        ) {
            $queryBuilder->addToJoinList('LEFT JOIN game_provider AS gp ON gp.id=gs.provider_external_id');
            $queryBuilder->addToSelectList('gp.guid AS providerId');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getProviderId())) {
            $whereCondition = $queryBuilder->prepareCondition('gp.guid', $filter->getProviderId());
            $queryBuilder->addToWhereList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        // providerName
        if (
            $this->isNeedShowColumn('providerName')
            || $this->isNeedSortColumn('providerName')
        ) {
            $queryBuilder->addToJoinList('LEFT JOIN game_provider AS gp ON gp.id=gs.provider_external_id');
            $queryBuilder->addToSelectList('gp.name AS providerName');
        }

        // product
        if (
            $this->isNeedShowColumn('product')
            || $this->isNeedSortColumn('product')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getProduct())
        ) {
            $queryBuilder->addToSelectList('gs.product AS product');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getProduct())) {
            $whereCondition = $queryBuilder->prepareCondition('gs.product', $filter->getProduct());
            $queryBuilder->addToWhereList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        // gameType
        if (
            $this->isNeedShowColumn('gameType')
            || $this->isNeedSortColumn('gameType')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getGameType())
        ) {
            $queryBuilder->addToSelectList('gs.game_type_name AS gameType');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getGameType())) {
            $whereCondition = $queryBuilder->prepareCondition('gs.game_type_name', $filter->getGameType());
            $queryBuilder->addToWhereList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        // gameId
        if (
            $this->isNeedShowColumn('gameId')
            || $this->isNeedSortColumn('gameId')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getGameId())
        ) {
            $queryBuilder->addToJoinList('LEFT JOIN game AS g ON g.id=gs.game_external_id');
            $queryBuilder->addToSelectList('g.guid AS gameId');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getGameId())) {
            $whereCondition = $queryBuilder->prepareCondition('g.guid', $filter->getGameId());
            $queryBuilder->addToWhereList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        // gameName
        if (
            $this->isNeedShowColumn('gameName')
            || $this->isNeedSortColumn('gameName')
        ) {
            $queryBuilder->addToJoinList('LEFT JOIN game AS g ON g.id=gs.game_external_id');
            $queryBuilder->addToSelectList('g.original_name AS gameName');
        }

        // gameMode
        if (
            $this->isNeedShowColumn('gameMode')
            || $this->isNeedSortColumn('gameMode')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getGameMode())
        ) {
            $queryBuilder->addToSelectList(
                sprintf(
                    "
                    CASE
                        WHEN gs.game_mode = 'GAME_MODE_REAL' THEN '%s'
                        ELSE '%s'
                    END AS gameMode
                ",
                    self::GAME_MODE_REAL,
                    self::GAME_MODE_DEMO,
                ),
            );
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getGameMode())) {
            if ($filter->getGameMode()->getValue() === self::GAME_MODE_REAL) {
                $queryBuilder->addToWhereList("gs.game_mode = 'GAME_MODE_REAL'");
            } elseif ($filter->getGameMode()->getValue() === self::GAME_MODE_DEMO) {
                $queryBuilder->addToWhereList("gs.game_mode = 'GAME_MODE_DEMO'");
            }
        }

        // sessionStatus
        if (
            $this->isNeedShowColumn('sessionStatus')
            || $this->isNeedSortColumn('sessionStatus')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getSessionStatus())
        ) {
            $queryBuilder->addToJoinList('LEFT JOIN gambling_transactions AS gt ON gt.game_session_id=gs.id');
            $queryBuilder->addToGroupList('gs.id');
            $queryBuilder->addToSelectList(sprintf(
                "CASE
                    WHEN (toInt64(now()) - max(gt.created_at)) <= %s THEN '%s'
                    ELSE '%s'
                END AS sessionStatus",
                self::CLOSE_SESSION_INTERVAL,
                self::SESSION_STATUS_OPEN,
                self::SESSION_STATUS_CLOSE,
            ));

            $queryBuilder->addToGroupExclusionList('sessionStatus');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getSessionStatus())) {
            $queryBuilder->addToJoinList('LEFT JOIN gambling_transactions AS gt ON gt.game_session_id=gs.id');

            if ($filter->getSessionStatus()->getValue() === self::SESSION_STATUS_OPEN) {
                $queryBuilder->addToHavingList(
                    sprintf("(toInt64(now()) - max(gt.created_at)) <= %s", self::CLOSE_SESSION_INTERVAL),
                );
            } elseif ($filter->getSessionStatus()->getValue() === self::SESSION_STATUS_CLOSE) {
                $queryBuilder->addToHavingList(
                    sprintf("(toInt64(now()) - max(gt.created_at)) > %s", self::CLOSE_SESSION_INTERVAL),
                );
            }
        }

        // gameSessionId
        if (
            $this->isNeedShowColumn('gameSessionId')
            || $this->isNeedSortColumn('gameSessionId')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getGameSessionId())
        ) {
            $queryBuilder->addToSelectList('gs.id AS gameSessionId');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getGameSessionId())) {
            $whereCondition = $queryBuilder->prepareCondition('gs.id', $filter->getGameSessionId());
            $queryBuilder->addToWhereList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        // currencyCode
        if (
            $this->isNeedShowColumn('currencyCode')
            || $this->isNeedSortColumn('currencyCode')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getCurrencyCode())
        ) {
            $queryBuilder->addToSelectList('gs.currency_code AS currencyCode');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getCurrencyCode())) {
            $whereCondition = $queryBuilder->prepareCondition('gs.currency_code', $filter->getGameSessionId());
            $queryBuilder->addToWhereList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        // sessionStartDate
        if (
            $this->isNeedShowColumn('sessionStartDate')
            || $this->isNeedSortColumn('sessionStartDate')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getDateStart())
        ) {
            $queryBuilder->addToSelectList('max(gs.started_at) AS sessionStartDate');
            $queryBuilder->addToGroupExclusionList('sessionStartDate');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getDateStart())) {
            $whereCondition = $queryBuilder->prepareCondition('max(gs.started_at)', $filter->getDateStart());
            $queryBuilder->addToHavingList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
            $queryBuilder->addToGroupExclusionList('sessionStartDate');
        }

        // sessionEndDate
        if (
            $this->isNeedShowColumn('sessionEndDate')
            || $this->isNeedSortColumn('sessionEndDate')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getDateEnd())
        ) {
            $queryBuilder->addToJoinList('LEFT JOIN gambling_transactions AS gt ON gt.game_session_id=gs.id');
            $queryBuilder->addToGroupList('gs.id');
            $queryBuilder->addToSelectList(sprintf(
                "CASE
                    WHEN (toInt64(now()) - max(gt.created_at)) > %s THEN max(gt.created_at)
                    ELSE NULL
                END AS sessionEndDate",
                self::CLOSE_SESSION_INTERVAL,
            ));

            $queryBuilder->addToGroupExclusionList('sessionEndDate');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getDateEnd())) {
            $whereCondition = $queryBuilder->prepareCondition('max(gt.created_at)', $filter->getDateEnd());
            $queryBuilder->addToHavingList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());

            $queryBuilder->addToHavingList(sprintf(
                '(toInt64(now()) - max(gt.created_at)) > %s',
                self::CLOSE_SESSION_INTERVAL,
            ));
        }

        //sessionDuration
        if (
            $this->isNeedShowColumn('sessionDuration')
            || $this->isNeedSortColumn('sessionDuration')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getDuration())
        ) {
            $queryBuilder->addToJoinList('LEFT JOIN gambling_transactions AS gt ON gt.game_session_id=gs.id');
            $queryBuilder->addToGroupList('gs.id');
            $queryBuilder->addToSelectList('max(gt.created_at) - max(gs.started_at) AS sessionDuration');
            $queryBuilder->addToGroupExclusionList('sessionDuration');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getDuration())) {
            $whereCondition = $queryBuilder->prepareCondition(
                'max(gt.created_at) - max(gs.started_at)',
                $filter->getDuration(),
            );
            $queryBuilder->addToHavingList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        //dateActive
        if ($queryBuilder->isFilterFieldNotEmpty($filter->getDateActive())) {
            $this->updateQueryBuilderByDateActive($queryBuilder, $filter->getDateActive());
        }

        //betCount
        $subQuery = "SUM(
                CASE
                    WHEN gt.transaction_type = 'TYPE_BET' THEN 1
                    WHEN gt.transaction_type = 'TYPE_REFUND' THEN -1
                    ELSE 0
                END
            )";

        if (
            $this->isNeedShowColumn('betCount')
            || $this->isNeedSortColumn('betCount')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getBetCount())
        ) {
            $queryBuilder->addToJoinList('LEFT JOIN gambling_transactions AS gt ON gt.game_session_id=gs.id');
            $queryBuilder->addToGroupList('gs.id');
            $queryBuilder->addToSelectList($subQuery . ' AS betCount');
            $queryBuilder->addToGroupExclusionList('betCount');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getBetCount())) {
            $whereCondition = $queryBuilder->prepareCondition($subQuery, $filter->getBetCount());
            $queryBuilder->addToHavingList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        //betAmount
        $subQuery = "SUM(
                CASE
                    WHEN gt.transaction_type = 'TYPE_BET' THEN gt.amount
                    WHEN gt.transaction_type = 'TYPE_REFUND' THEN -gt.amount
                    ELSE 0
                END
            )";

        if (
            $this->isNeedShowColumn('betAmount')
            || $this->isNeedSortColumn('betAmount')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getBetAmount())
        ) {
            $queryBuilder->addToJoinList('LEFT JOIN gambling_transactions AS gt ON gt.game_session_id=gs.id');
            $queryBuilder->addToGroupList('gs.id');
            $queryBuilder->addToSelectList($subQuery . ' AS betAmount');
            $queryBuilder->addToGroupExclusionList('betAmount');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getBetAmount())) {
            $whereCondition = $queryBuilder->prepareCondition($subQuery, $filter->getBetAmount());
            $queryBuilder->addToHavingList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        //winCount
        $subQuery = "SUM(gt.transaction_type = 'TYPE_WIN' ? 1 : 0)";

        if (
            $this->isNeedShowColumn('winCount')
            || $this->isNeedSortColumn('winCount')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getWinCount())
        ) {
            $queryBuilder->addToJoinList('LEFT JOIN gambling_transactions AS gt ON gt.game_session_id=gs.id');
            $queryBuilder->addToGroupList('gs.id');
            $queryBuilder->addToSelectList($subQuery . ' AS winCount');
            $queryBuilder->addToGroupExclusionList('winCount');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getWinCount())) {
            $whereCondition = $queryBuilder->prepareCondition($subQuery, $filter->getWinCount());
            $queryBuilder->addToHavingList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        //winAmount
        $subQuery = "SUM(gt.transaction_type = 'TYPE_WIN' ? gt.amount : 0)";

        if (
            $this->isNeedShowColumn('winAmount')
            || $this->isNeedSortColumn('winAmount')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getWinAmount())
        ) {
            $queryBuilder->addToJoinList('LEFT JOIN gambling_transactions AS gt ON gt.game_session_id=gs.id');
            $queryBuilder->addToGroupList('gs.id');
            $queryBuilder->addToSelectList($subQuery . ' AS winAmount');
            $queryBuilder->addToGroupExclusionList('winAmount');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getWinAmount())) {
            $whereCondition = $queryBuilder->prepareCondition($subQuery, $filter->getWinAmount());
            $queryBuilder->addToHavingList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        //freespinCount
        $subQuery = "SUM(gt.transaction_type = 'TYPE_FREESPIN' ? 1 : 0)";

        if (
            $this->isNeedShowColumn('freespinCount')
            || $this->isNeedSortColumn('freespinCount')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getFreespinCount())
        ) {
            $queryBuilder->addToJoinList('LEFT JOIN gambling_transactions AS gt ON gt.game_session_id=gs.id');
            $queryBuilder->addToGroupList('gs.id');
            $queryBuilder->addToSelectList($subQuery . ' AS freespinCount');
            $queryBuilder->addToGroupExclusionList('freespinCount');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getFreespinCount())) {
            $whereCondition = $queryBuilder->prepareCondition($subQuery, $filter->getFreespinCount());
            $queryBuilder->addToHavingList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        //jackpotAmount
        $subQuery = "SUM(gt.transaction_type = 'TYPE_JACKPOT' ? gt.amount : 0)";

        if (
            $this->isNeedShowColumn('jackpotAmount')
            || $this->isNeedSortColumn('jackpotAmount')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getJackpotAmount())
        ) {
            $queryBuilder->addToJoinList('LEFT JOIN gambling_transactions AS gt ON gt.game_session_id=gs.id');
            $queryBuilder->addToGroupList('gs.id');
            $queryBuilder->addToSelectList($subQuery . ' AS jackpotAmount');
            $queryBuilder->addToGroupExclusionList('jackpotAmount');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getJackpotAmount())) {
            $whereCondition = $queryBuilder->prepareCondition($subQuery, $filter->getJackpotAmount());
            $queryBuilder->addToHavingList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        //refundCount
        $subQuery = "SUM(gt.transaction_type = 'TYPE_REFUND' ? 1 : 0)";

        if (
            $this->isNeedShowColumn('refundCount')
            || $this->isNeedSortColumn('refundCount')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getRefundCount())
        ) {
            $queryBuilder->addToJoinList('LEFT JOIN gambling_transactions AS gt ON gt.game_session_id=gs.id');
            $queryBuilder->addToGroupList('gs.id');
            $queryBuilder->addToSelectList($subQuery . ' AS refundCount');
            $queryBuilder->addToGroupExclusionList('refundCount');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getRefundCount())) {
            $whereCondition = $queryBuilder->prepareCondition($subQuery, $filter->getRefundCount());
            $queryBuilder->addToHavingList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        //refundAmount
        $subQuery = "SUM(gt.transaction_type = 'TYPE_REFUND' ? gt.amount : 0)";

        if (
            $this->isNeedShowColumn('refundAmount')
            || $this->isNeedSortColumn('refundAmount')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getRefundAmount())
        ) {
            $queryBuilder->addToJoinList('LEFT JOIN gambling_transactions AS gt ON gt.game_session_id=gs.id');
            $queryBuilder->addToGroupList('gs.id');
            $queryBuilder->addToSelectList($subQuery . ' AS refundAmount');
            $queryBuilder->addToGroupExclusionList('refundAmount');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getRefundAmount())) {
            $whereCondition = $queryBuilder->prepareCondition($subQuery, $filter->getRefundAmount());
            $queryBuilder->addToHavingList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        //rollbackCount
        $subQuery = "SUM(gt.transaction_type = 'TYPE_ROLLBACK' ? 1 : 0)";

        if (
            $this->isNeedShowColumn('rollbackCount')
            || $this->isNeedSortColumn('rollbackCount')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getRollbackCount())
        ) {
            $queryBuilder->addToJoinList('LEFT JOIN gambling_transactions AS gt ON gt.game_session_id=gs.id');
            $queryBuilder->addToGroupList('gs.id');
            $queryBuilder->addToSelectList($subQuery . ' AS rollbackCount');
            $queryBuilder->addToGroupExclusionList('rollbackCount');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getRollbackCount())) {
            $whereCondition = $queryBuilder->prepareCondition($subQuery, $filter->getRollbackCount());
            $queryBuilder->addToHavingList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        //profit
        $subQuery = "SUM(
                CASE
                    WHEN gt.transaction_type = 'TYPE_BET' THEN gt.amount
                    WHEN gt.transaction_type = 'TYPE_REFUND' THEN -gt.amount
                    ELSE 0
                END
            ) - (
                SUM(gt.transaction_type = 'TYPE_WIN' ? gt.amount : 0)
                + SUM(gt.transaction_type = 'TYPE_JACKPOT' ? gt.amount : 0)
            )";

        if (
            $this->isNeedShowColumn('profit')
            || $this->isNeedSortColumn('profit')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getProfit())
        ) {
            $queryBuilder->addToJoinList('LEFT JOIN gambling_transactions AS gt ON gt.game_session_id=gs.id');
            $queryBuilder->addToGroupList('gs.id');
            $queryBuilder->addToSelectList($subQuery . ' AS profit');
            $queryBuilder->addToGroupExclusionList('profit');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getProfit())) {
            $whereCondition = $queryBuilder->prepareCondition($subQuery, $filter->getProfit());
            $queryBuilder->addToHavingList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        //margin
        $subQuery = "
        ROUND(
            (
                SUM(
                    CASE
                        WHEN gt.transaction_type = 'TYPE_BET' THEN gt.amount
                        WHEN gt.transaction_type = 'TYPE_REFUND' THEN -gt.amount
                        ELSE 0
                    END
                ) - (
                    SUM(gt.transaction_type = 'TYPE_WIN' ? gt.amount : 0)
                    + SUM(gt.transaction_type = 'TYPE_JACKPOT' ? gt.amount : 0)
                )
            ) / (
                SUM(
                    CASE
                        WHEN gt.transaction_type = 'TYPE_BET' THEN gt.amount
                        WHEN gt.transaction_type = 'TYPE_REFUND' THEN -gt.amount
                        ELSE 0
                    END
                )
            ) * 100
        , 8)";

        if (
            $this->isNeedShowColumn('margin')
            || $this->isNeedSortColumn('margin')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getMargin())
        ) {
            $queryBuilder->addToJoinList('LEFT JOIN gambling_transactions AS gt ON gt.game_session_id=gs.id');
            $queryBuilder->addToGroupList('gs.id');
            $queryBuilder->addToSelectList($subQuery . ' AS margin');
            $queryBuilder->addToGroupExclusionList('margin');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getMargin())) {
            $whereCondition = $queryBuilder->prepareCondition($subQuery, $filter->getMargin());
            $queryBuilder->addToHavingList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        //rtp
        $subQuery = "
        ROUND(
            (
                SUM(gt.transaction_type = 'TYPE_WIN' ? gt.amount : 0)
                + SUM(gt.transaction_type = 'TYPE_JACKPOT' ? gt.amount : 0)
            ) / (
                SUM(
                    CASE
                        WHEN gt.transaction_type = 'TYPE_BET' THEN gt.amount
                        WHEN gt.transaction_type = 'TYPE_REFUND' THEN -gt.amount
                        ELSE 0
                    END
                )
            ) * 100
        , 8)";

        if (
            $this->isNeedShowColumn('rtp')
            || $this->isNeedSortColumn('rtp')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getRtp())
        ) {
            $queryBuilder->addToJoinList('LEFT JOIN gambling_transactions AS gt ON gt.game_session_id=gs.id');
            $queryBuilder->addToGroupList('gs.id');
            $queryBuilder->addToSelectList($subQuery . ' AS rtp');
            $queryBuilder->addToGroupExclusionList('rtp');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getRtp())) {
            $whereCondition = $queryBuilder->prepareCondition($subQuery, $filter->getRtp());
            $queryBuilder->addToHavingList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        //avgBet
        $subQuery = "
        ROUND(
            SUM(
                CASE
                    WHEN gt.transaction_type = 'TYPE_BET' THEN gt.amount
                    WHEN gt.transaction_type = 'TYPE_REFUND' THEN -gt.amount
                    ELSE 0
                END
            ) / SUM(
                CASE
                    WHEN gt.transaction_type = 'TYPE_BET' THEN 1
                    WHEN gt.transaction_type = 'TYPE_REFUND' THEN -1
                    ELSE 0
                END
            )
        , 8)";

        if (
            $this->isNeedShowColumn('avgBet')
            || $this->isNeedSortColumn('avgBet')
            || $queryBuilder->isFilterFieldNotEmpty($filter->getAvgBet())
        ) {
            $queryBuilder->addToJoinList('LEFT JOIN gambling_transactions AS gt ON gt.game_session_id=gs.id');
            $queryBuilder->addToGroupList('gs.id');
            $queryBuilder->addToSelectList($subQuery . ' AS avgBet');
            $queryBuilder->addToGroupExclusionList('avgBet');
        }

        if ($queryBuilder->isFilterFieldNotEmpty($filter->getAvgBet())) {
            $whereCondition = $queryBuilder->prepareCondition($subQuery, $filter->getAvgBet());
            $queryBuilder->addToHavingList($whereCondition->getAssignExpressionsAsString());
            $queryBuilder->addToParameterList($whereCondition->getPlaceholders());
        }

        //sort
        $queryBuilder->setSortList(['gameSessionId DESC']);
        $sortBy = $this->getSortList();

        if ($sortBy !== []) {
            $queryBuilder->setSortList($sortBy);
        }

        //pagination
        [$limit, $offset] = $this->getPaginationParameters();
        $queryBuilder->setLimit($limit);
        $queryBuilder->setOffset($offset);

        return $this;
    }

    public function getReportResponse(): ReportResponse
    {
        $queryStatement = $this->connection->getConnection()->select(
            $this->getQueryBuilder()->buildQuery(),
            $this->getQueryBuilder()->getParameterList(),
        );

        $data = [];

        foreach ($queryStatement->rows() as $rowIndex => $rowData) {
            $rowData = array_filter(
                $rowData,
                fn(string $key) => $this->isNeedShowColumn($key),
                ARRAY_FILTER_USE_KEY,
            );

            $item = new ReportResponseItem();
            $item->setId((string)$rowIndex);
            $item->setAttributes(array_map(
                fn(string|int|bool $value) => (string) $value,
                $rowData,
            ));

            $data[] = $item;
        }

        $reportResponse = new ReportResponse();
        $reportResponse->setTotal($queryStatement->countAll());
        $reportResponse->setData($data);

        return $reportResponse;
    }

    /**
     * @inheritDoc
     */
    public function getRequiredColumns(): array
    {
        return [
            'hallId',
            'providerId',
            'gameId',
            'gameSessionId',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getDefaultColumns(): array
    {
        return [
            'accountId',
            'providerName',
            'gameType',
            'gameName',
            'gameMode',
            'sessionStatus',
            'gameSessionId',
            'currencyCode',
            'sessionStartDate',
            'sessionEndDate',
            'sessionDuration',
            'betAmount',
            'winAmount',
            'jackpotAmount',
            'profit',
            'margin',
            'rtp',
            'avgBet',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getAllColumns(): array
    {
        return [
            'hallId',
            'hallName',
            'accountId',
            'countryCode',
            'ip',
            'device',
            //'source', //core doesn't provide it
            'providerId',
            'providerName',
            //'product', //core doesn't provide it
            'gameType',
            'gameId',
            'gameName',
            'gameMode',
            'sessionStatus',
            'gameSessionId',
            //'gameSessionProviderId', //core doesn't provide it
            'currencyCode',
            'sessionStartDate',
            'sessionEndDate',
            'sessionDuration',
            'betCount',
            'betAmount',
            'winCount',
            'winAmount',
            'freespinCount',
            'jackpotAmount',
            'refundCount',
            'refundAmount',
            'rollbackCount',
            'profit',
            'margin',
            'rtp',
            'avgBet',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSortColumns(): array
    {
        return $this->getAllColumns();
    }

    /**
     * @inheritDoc
     */
    public function getGroupByColumns(): array
    {
        return [];
    }

    private function prepareFilterByDeviceEnum(GamblingSessionsRequestFilter $filter): GamblingSessionsRequestFilter
    {
        $device = $filter->getDevice();
        $device->setValue(self::DEVICES_ENUM_MAP[$device->getValue()] ?? '');

        $values = [];

        foreach ($device->getValues() as $value) {
            if (!isset(self::DEVICES_ENUM_MAP[$value])) {
                continue;
            }

            $values[] = self::DEVICES_ENUM_MAP[$value];
        }

        $device->setValues($values);

        return $filter;
    }

    private function updateQueryBuilderByDateActive(
        ReportQueryBuilder $queryBuilder,
        RequestFilterField $dateActiveField
    ): void {
        $queryBuilder->addToJoinList('LEFT JOIN gambling_transactions AS gt ON gt.game_session_id=gs.id');
        $queryBuilder->addToGroupList('gs.id');
        $queryBuilder->addToSelectList('max(gs.started_at) AS firstSessionTime');
        $queryBuilder->addToSelectList('max(gt.created_at) AS lastSessionTime');
        $queryBuilder->addToGroupExclusionList('firstSessionTime');
        $queryBuilder->addToGroupExclusionList('lastSessionTime');

        if ($dateActiveField->getValue() !== '' || $dateActiveField->getEqual() !== '') {
            $value = $dateActiveField->getValue() !== '' ? $dateActiveField->getValue() : $dateActiveField->getEqual();
            $queryBuilder->addToHavingList(':activeDatePlaceholderVal BETWEEN firstSessionTime AND lastSessionTime');
            $queryBuilder->addToParameterList(['activeDatePlaceholderVal' => (int)$value]);
        }

        if ($dateActiveField->getNotEqual() !== '') {
            $valueNotBetween = $dateActiveField->getNotEqual();
            $queryBuilder->addToHavingList(
                ':activeDatePlaceholderVal NOT BETWEEN firstSessionTime AND lastSessionTime',
            );
            $queryBuilder->addToParameterList(['activeDatePlaceholderVal' => (int)$valueNotBetween]);
        }

        $isMinFilterOnly = (
                $dateActiveField->getGreatOrEqual() !== '' || $dateActiveField->getGreat() !== ''
            ) && (
                $dateActiveField->getLessOrEqual() === '' && $dateActiveField->getLess() === ''
            );
        $isMaxFilterOnly = (
                $dateActiveField->getLessOrEqual() !== '' || $dateActiveField->getLess() !== ''
            ) && (
                $dateActiveField->getGreatOrEqual() === '' && $dateActiveField->getGreat() === ''
            );
        $isBetweenFilter = !$isMinFilterOnly && !$isMaxFilterOnly;

        if ($dateActiveField->getGreatOrEqual() !== '' && $isMinFilterOnly) {
            $queryBuilder->addToHavingList('lastSessionTime >= :activeDatePlaceholderMin');
            $queryBuilder->addToParameterList(
                ['activeDatePlaceholderMin' => (int)$dateActiveField->getGreatOrEqual()],
            );
        } elseif ($dateActiveField->getGreat() !== '' && $isMinFilterOnly) {
            $queryBuilder->addToHavingList('lastSessionTime > :activeDatePlaceholderMin');
            $queryBuilder->addToParameterList(['activeDatePlaceholderMin' => (int)$dateActiveField->getGreat()]);
        }

        if ($dateActiveField->getLessOrEqual() !== '' && $isMaxFilterOnly) {
            $queryBuilder->addToHavingList('firstSessionTime <= :activeDatePlaceholderMax');
            $queryBuilder->addToParameterList(['activeDatePlaceholderMax' => (int)$dateActiveField->getLessOrEqual()]);
        } elseif ($dateActiveField->getLess() !== '' && $isMaxFilterOnly) {
            $queryBuilder->addToHavingList('firstSessionTime < :activeDatePlaceholderMax');
            $queryBuilder->addToParameterList(['activeDatePlaceholderMax' => (int)$dateActiveField->getLess()]);
        }

        if ($isBetweenFilter === true) {
            $queryBuilder->addToHavingList('
                    (:activeDatePlaceholderMin BETWEEN firstSessionTime AND lastSessionTime)
                    OR (:activeDatePlaceholderMax BETWEEN firstSessionTime AND lastSessionTime)
                    OR (firstSessionTime BETWEEN :activeDatePlaceholderMin AND :activeDatePlaceholderMax)
                    OR (lastSessionTime BETWEEN :activeDatePlaceholderMin AND :activeDatePlaceholderMax)
                ');

            $min = $dateActiveField->getGreatOrEqual() === ''
                ? (int) $dateActiveField->getGreat()
                : (int) $dateActiveField->getGreatOrEqual();
            $max = $dateActiveField->getLessOrEqual() === ''
                ? (int) $dateActiveField->getLess()
                : (int) $dateActiveField->getLessOrEqual();

            $queryBuilder->addToParameterList(
                ['activeDatePlaceholderMin' => $min, 'activeDatePlaceholderMax' => $max],
            );
        }
    }
}
