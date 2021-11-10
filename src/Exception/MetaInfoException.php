<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class MetaInfoException extends NotFoundHttpException implements Throwable
{

}
