<?php

namespace App\EventListener;

use App\Entity\Courses;
use getID3;
use Vich\UploaderBundle\Event\Event;

class VideoDurationListener
{
    public function onVichUploaderPostUpload(Event $event): void
    {
        $object = $event->getObject();

        if (!$object instanceof Courses) {
            return;
        }

        // Récupérer le chemin absolu de la vidéo après upload
        $videoPath = $object->getVideoFile();

        if (!$videoPath || !file_exists($videoPath)) {
            return;
        }

        // Analyser la vidéo avec getID3
        $getID3 = new getID3();
        $fileInfo = $getID3->analyze($videoPath);

        if (isset($fileInfo['playtime_seconds'])) {
            $object->setDuration((int) round($fileInfo['playtime_seconds']));
        }
    }
}