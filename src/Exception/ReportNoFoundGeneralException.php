<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ReportNoFoundGeneralException extends NotFoundHttpException implements Throwable
{
}
