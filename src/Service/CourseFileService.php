<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class CourseFileService
{
    public function __construct(protected ParameterBagInterface $parameterBag, protected UploaderHelper $uploaderHelper)
    {}

    public function getFileContent($course): ?string
    {
        $filePath = $this->uploaderHelper->asset($course, 'partialFile');
        if ($filePath) {
            $fullPath = $this->parameterBag->get('kernel.project_dir') . '/public' . $filePath;
            if (file_exists($fullPath)) {
                return file_get_contents($fullPath);
            }
        }
        return null;
    }
}