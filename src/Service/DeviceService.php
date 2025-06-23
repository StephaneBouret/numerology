<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use UAParser\Parser;

class DeviceService
{
    public function generateDeviceFingerprint(Request $request): string
    {
        return hash(
            'sha256',
            $request->headers->get('User-Agent') . 
            $request->headers->get('Sec-Ch-Ua-Platform') // Plateforme (Windows, macOs, Android)
        );
    }

    public function getDeviceType(Request $request): string
    {
        $userAgent = $request->headers->get('User-Agent');
        if (preg_match('/ipad|android(?!.*mobile)|tablet|kindle|silk/i', $userAgent)) {
            return 'tablet';
        }
        return preg_match('/mobile|iphone|android/i', $userAgent) ? 'mobile' : 'desktop';
    }

    public function getBrowserAndPlatform(Request $request): array
    {
        $userAgent = $request->headers->get('User-Agent');
        $parser = Parser::create();
        $ua = $parser->parse($userAgent);

        return [
            'browser' => $ua->ua->family,   // Exemple: Chrome, Firefox
            'platform' => $ua->os->family, // Exemple: Windows, macOS, Android 
        ];
    }
}