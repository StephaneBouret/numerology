<?php

namespace App\Service;

use Nucleos\DompdfBundle\Wrapper\DompdfWrapperInterface;
use Symfony\Component\HttpFoundation\Response;

class PdfGeneratorService
{
    public function __construct(protected DompdfWrapperInterface $wrapper)
    {}

    public function getStreamResponse(string $html, string $filename): Response
    {
        return $this->wrapper->getStreamResponse($html, $filename);
    }
}