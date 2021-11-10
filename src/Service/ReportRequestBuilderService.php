<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Request\ReportRequest;
use App\DTO\Request\ReportRequestDto;
use App\DTO\Request\RequestFilterField;
use App\Exception\ReportFilterException;
use App\Exception\ReportNoFoundGeneralException;
use App\Service\RequestFilter\Gambling\GamblingSessionsRequestFilter;
use App\Service\RequestFilter\RequestFilterMapperInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReportRequestBuilderService
{
    public const REPORT_CATEGORY_GAMBLING = 'gambling';

    public const REPORTS_IDENTIFIER_PARAM = 'identifier';

    public const REPORT_GAMBLING_SESSION_LOG = 'gambling-session-log';

    public const REPORTS_IDENTIFIERS = [
        self::REPORT_GAMBLING_SESSION_LOG,
    ];

    private const MAP = [
        self::REPORT_GAMBLING_SESSION_LOG => GamblingSessionsRequestFilter::class,
    ];

    private SerializerInterface $serializer;

    public function __construct(
        private ReportRequestFilterFactory $requestFilterFactory,
        private ReportRequestFilterMapper $filterMapper,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @throws \App\Exception\ReportFilterException
     * @throws \JsonException
     */
    public function buildReportRequest(Request $request): ReportRequest
    {
        $requestDto = $this->buildReportRequestDto($request);
        $filter = $this->getFilter($request);
        $this->throwIfMapperException(
            $this->filterMapper->mapRequestDto($filter, $requestDto),
        );

        $this->validateFilter($filter);

        return new ReportRequest($filter, $requestDto);
    }

    public function getFilter(Request $request): RequestFilterMapperInterface
    {
        return $this->requestFilterFactory->getFilter(
            $this->getReportFilterClassName($request),
        );
    }

    /**
     * @return array<string, string>
     */
    public function getReportIdentifiersWithCategories(): array
    {
        return [
            self::REPORT_GAMBLING_SESSION_LOG => self::REPORT_CATEGORY_GAMBLING,
        ];
    }

    private function buildReportRequestDto(Request $request): ReportRequestDto
    {
        $serializerFormat = 'json';

        $requestDto = new ReportRequestDto(json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR));
        $this->validateRequestDtoRawData($requestDto);
        $this->createReportRequestDtoSerializer()->deserialize(
            $request->getContent(),
            ReportRequestDto::class,
            $serializerFormat,
            [AbstractNormalizer::OBJECT_TO_POPULATE => $requestDto],
        );

        $filterFieldSerializer = $this->createRequestFilterFieldSerializer();
        $filters = [];

        foreach ($requestDto->getFilters() as $filter) {
            $filters[] = $filterFieldSerializer->deserialize(
                $filterFieldSerializer->serialize($filter, $serializerFormat),
                RequestFilterField::class,
                $serializerFormat,
            );
        }

        $requestDto->setFilters($filters);

        return $requestDto;
    }

    private function createRequestFilterFieldSerializer(): SerializerInterface
    {
        $setMethodNormalizer = new GetSetMethodNormalizer();
        $objectNormalizer = new ObjectNormalizer(null, null, null, new ReflectionExtractor());
        $arrayNormalizer = new ArrayDenormalizer();
        $encoder = new JsonEncoder();

        return new Serializer([$setMethodNormalizer, $objectNormalizer, $arrayNormalizer], [$encoder]);
    }

    private function createReportRequestDtoSerializer(): SerializerInterface
    {
        $encoder = new JsonEncoder();
        $objectNormalizer = new ObjectNormalizer(null, null, null, new ReflectionExtractor());
        $arrayDenormalizer = new ArrayDenormalizer();
        $setMethodNormalizer = new GetSetMethodNormalizer();

        return new Serializer([$objectNormalizer, $setMethodNormalizer, $arrayDenormalizer], [$encoder]);
    }

    private function getReportFilterClassName(Request $request): string
    {
        $type = $request->get(self::REPORTS_IDENTIFIER_PARAM);

        if (!array_key_exists($type, self::MAP)) {
            throw new ReportNoFoundGeneralException('Report `' . $type . '` is not found.');
        }

        return self::MAP[$type];
    }

    /**
     * @throws \App\Exception\ReportFilterException
     */
    private function validateFilter(RequestFilterMapperInterface $filter): void
    {
        $errors = $this->validator->validate($filter);

        if (count($errors) > 0) {
            $data = [];

            foreach ($errors as $error) {
                assert($error instanceof ConstraintViolation);

                if (
                    array_key_exists($error->getPropertyPath(), $data)
                    && in_array($error->getMessage(), $data[$error->getPropertyPath()], true)
                ) {
                    continue;
                }

                $data[ReportRequestDto::REPORT_REQUEST_KEY_FILTERS][] = (string) $error->getMessage();
            }

            throw new ReportFilterException($data);
        }
    }

    /**
     * @param array<string> $errors
     * @throws \App\Exception\ReportFilterException
     */
    private function throwIfMapperException(array $errors): void
    {
        if ($errors === []) {
            return;
        }

        throw new ReportFilterException([
            ReportRequestDto::REPORT_REQUEST_KEY_FILTERS => ['Invalid id for filter: ' . implode(', ', $errors)],
        ]);
    }

    private function validateRequestDtoRawData(ReportRequestDto $requestDto): void
    {
        $errors = $this->validator->validate($requestDto);

        if (count($errors) > 0) {
            $data = [];

            foreach ($errors as $error) {
                assert($error instanceof ConstraintViolation);

                if (
                    array_key_exists($error->getPropertyPath(), $data)
                    && in_array($error->getMessage(), $data[$error->getPropertyPath()], true)
                ) {
                    continue;
                }

                $data[$error->getPropertyPath()][] = (string) $error->getMessage();
            }

            throw new ReportFilterException($data);
        }
    }
}
