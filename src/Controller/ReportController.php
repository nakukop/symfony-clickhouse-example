<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\MetaInfo\ReportListMetaInfo;
use App\DTO\MetaInfo\ReportMetaInfoColumns;
use App\DTO\MetaInfo\ReportMetaInfoFilter;
use App\DTO\Request\ReportRequestDto;
use App\DTO\Response\ReportResponse;
use App\Service\ReportBuilderService;
use App\Service\ReportMetaInfo\ReportMetaInfoService;
use App\Service\ReportRequestBuilderService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[Route('reports')]
/**
 * @OA\Tag(name="Reports")
 * @OA\Response(
 *     response="401",
 *     ref="#/components/responses/Unauthorized",
 * )
 * @OA\Response(
 *     response="503",
 *     ref="#/components/responses/ServerUnavailable",
 * )
 * @OA\Response(
 *     response="500",
 *     ref="#/components/responses/GeneralError",
 * )
 * @OA\Response(
 *     response="404",
 *     ref="#/components/responses/NotFound",
 * )
 */
class ReportController
{
    #[Route(
        path: '/{' . ReportRequestBuilderService::REPORTS_IDENTIFIER_PARAM . '}',
        name: 'get_report_by_identifier',
        methods: ['POST'],
    )]
    /**
     * @OA\Post(
     *     operationId="report:build",
     *     description="Provides available filters list for report.",
     *     @OA\Parameter(
     *         name=ReportRequestBuilderService::REPORTS_IDENTIFIER_PARAM,
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", enum=ReportRequestBuilderService::REPORTS_IDENTIFIERS),
     *     ),
     *     @OA\RequestBody(
     *          @OA\JsonContent(ref=@Model(type=ReportRequestDto::class)),
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(ref=@Model(type=ReportResponse::class)),
     *     ),
     * )
     */
    public function getReport(
        Request $request,
        ReportRequestBuilderService $reportRequestBuilderService,
        ReportBuilderService $reportBuilderService,
    ): JsonResponse {
        $reportRequest = $reportRequestBuilderService->buildReportRequest($request);

        return $this->getJsonResponse(
            $reportBuilderService
                ->buildReport($request)
                ->setReportRequest($reportRequest)
                ->prepareStatement()
                ->getReportResponse(),
        );
    }

    #[Route(
        path: '/{' . ReportRequestBuilderService::REPORTS_IDENTIFIER_PARAM . '}/filters',
        name: 'get_report_filter_meta',
        methods: ['GET'],
    )]
    /**
     * @OA\Get(
     *     operationId="report:meta_info:filter",
     *     description="Returns object with info about report filter",
     *     @OA\Parameter(
     *         name=ReportRequestBuilderService::REPORTS_IDENTIFIER_PARAM,
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", enum=ReportRequestBuilderService::REPORTS_IDENTIFIERS),
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(ref=@Model(type=ReportMetaInfoFilter::class)),
     *     ),
     * )
     */
    public function getMetaInfoFilter(Request $request, ReportMetaInfoService $metaInfoService): JsonResponse
    {
        return $this->getJsonResponse($metaInfoService->getMetaInfoFilter($request));
    }

    /**
     * @OA\Get(
     *     operationId="report:meta_info:columns",
     *     description="Returns object with info about report columns",
     *     @OA\Parameter(
     *         name=ReportRequestBuilderService::REPORTS_IDENTIFIER_PARAM,
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", enum=ReportRequestBuilderService::REPORTS_IDENTIFIERS),
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(ref=@Model(type=ReportMetaInfoColumns::class)),
     *     ),
     * )
     */
    #[Route(
        path: '/{' . ReportRequestBuilderService::REPORTS_IDENTIFIER_PARAM . '}/columns',
        name: 'get_report_columns_meta',
        methods: ['GET'],
    )]
    public function getMetaInfoColumns(Request $request, ReportMetaInfoService $metaInfoService): JsonResponse
    {
        return $this->getJsonResponse($metaInfoService->getMetaInfoColumns($request));
    }

    /**
     * @OA\Get(
     *     operationId="report:list",
     *     description="Returns available reports list",
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(ref=@Model(type=ReportListMetaInfo::class)),
     *     ),
     * )
     */
    #[Route(
        path: '/list',
        name: 'get_report_list',
        methods: ['GET'],
    )]
    public function getReportList(ReportMetaInfoService $metaInfoService): JsonResponse
    {
        return $this->getJsonResponse($metaInfoService->getAllReportsInfo());
    }

    private function getJsonResponse(
        ReportResponse|ReportListMetaInfo|ReportMetaInfoColumns|ReportMetaInfoFilter $object,
    ): JsonResponse {
        return (new JsonResponse(
            (new Serializer(
                [new ObjectNormalizer(), new ArrayDenormalizer()],
                [new JsonEncoder()],
            ))->serialize($object, 'json'),
            JsonResponse::HTTP_OK,
            [],
            true,
        ));
    }
}
