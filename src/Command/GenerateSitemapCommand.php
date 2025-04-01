<?php

namespace App\Command;

use App\Entity\Courses;
use App\Entity\Program;
use App\Entity\Sections;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(
    name: 'app:generate-sitemap',
    description: 'Génère un fichier sitemap.xml à partir des URLs de l\'application.',
)]
class GenerateSitemapCommand extends Command
{
    private EntityManagerInterface $em;
    private UrlGeneratorInterface $urlGenerator;
    private string $hostname;

    public function __construct(EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, string $hostname)
    {
        parent::__construct();
        $this->em = $em;
        $this->urlGenerator = $urlGenerator;
        $this->hostname = rtrim($hostname, '/'); // Supprime un éventuel "/" à la fin
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Génère un fichier sitemap.xml à partir des URLs de l\'application.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $urls = [];

        // 1. URLs statiques (pages fixes)
        $staticUrls = [
            ['route' => 'home_index', 'changefreq' => 'monthly', 'priority' => '1.0'],
            ['route' => 'app_testimonial', 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['route' => 'app_courses_list', 'changefreq' => 'yearly', 'priority' => '0.5'],
            ['route' => 'app_about', 'changefreq' => 'yearly', 'priority' => '0.7'],
            ['route' => 'app_contact', 'changefreq' => 'yearly', 'priority' => '0.7'],
            ['route' => 'app_legal', 'changefreq' => 'yearly', 'priority' => '0.5'],
            ['route' => 'app_privacy', 'changefreq' => 'yearly', 'priority' => '0.5'],
            ['route' => 'app_terms', 'changefreq' => 'yearly', 'priority' => '0.5'],
            ['route' => 'app_faq', 'changefreq' => 'yearly', 'priority' => '0.6'],
            ['route' => 'app_newsletter', 'changefreq' => 'yearly', 'priority' => '0.5'],
            ['route' => 'app_register', 'changefreq' => 'yearly', 'priority' => '0.3'],
            ['route' => 'app_login', 'changefreq' => 'yearly', 'priority' => '0.3'],
            ['route' => 'app_forgot_pw', 'changefreq' => 'yearly', 'priority' => '0.3'],
        ];
        foreach ($staticUrls as $staticUrl) {
            $urls[] = [
                'loc' => $this->hostname . $this->urlGenerator->generate($staticUrl['route']),
                'lastmod' => (new \DateTime())->format('Y-m-d'),
                'changefreq' => $staticUrl['changefreq'],
                'priority' => $staticUrl['priority'],
            ];
        }

        // 1. URLs dynamiques
        $programs = $this->em->getRepository(Program::class)->findAll();
        foreach ($programs as $program) {
            $urls[] = [
                'loc' => $this->hostname . $this->urlGenerator->generate('app_program', ['slug' => $program->getSlug()]),
                'lastmod' => (new \DateTime())->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }

        $freeCourses = $this->em->getRepository(Program::class)->findAll();
        foreach ($freeCourses as $freeCourse) {
            $urls[] = [
                'loc' => $this->hostname . $this->urlGenerator->generate('app_free_course', ['slug' => $freeCourse->getSlug()]),
                'lastmod' => (new \DateTime())->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }

        $sections = $this->em->getRepository(Program::class)->findAll();
        foreach ($sections as $section) {
            $urls[] = [
                'loc' => $this->hostname . $this->urlGenerator->generate('app_sections', ['slug' => $section->getSlug()]),
                'lastmod' => (new \DateTime())->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        $coursesSections = $this->em->getRepository(Sections::class)->findAll();
        foreach ($coursesSections as $coursesSection) {
            $urls[] = [
                'loc' => $this->hostname . $this->urlGenerator->generate('courses_section', ['program_slug' => $coursesSection->getProgram()->getSlug(), 'slug' => $coursesSection->getSlug()]),
                'lastmod' => (new \DateTime())->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }

        $courses = $this->em->getRepository(Courses::class)->findAll();
        foreach ($courses as $course) {
            $courseVideoUrl = null;
            if ($course->getVideoName()) {
                $courseVideoUrl = [
                    'content_loc' => $this->hostname . '/videos/courses/' . $course->getVideoName(),
                    'title' => $course->getName(),
                    'description' => $course->getShortDescription() ?? 'Vidéo du cours ' . $course->getName(),
                ];
            }

            $urls[] = [
                'loc' => $this->hostname . $this->urlGenerator->generate('courses_show', ['program_slug' => $course->getProgram()->getSlug(), 'section_slug' => $course->getSection()->getSlug(), 'slug' => $course->getSlug()]),
                'lastmod' => (new \DateTime())->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.8',
                'video' => $courseVideoUrl
            ];

            // Ajout du fichier Twig uploadé s'il existe
            if ($course->getPartialFileName()) {
                $urls[] = [
                    'loc' => $this->hostname . '/uploads/courses/' . $course->getPartialFileName(),
                    'lastmod' => (new \DateTime())->format('Y-m-d'),
                    'changefreq' => 'monthly',
                    'priority' => '0.7',
                ];
            }
        }

        // 3. Générer et sauvegarder le fichier sitemap.xml
        $sitemapContent = $this->generateXml($urls);
        file_put_contents('public/sitemap.xml', $sitemapContent);

        $output->writeln('<info>Siremap généré avec succès : public/sitemap.xml</info>');

        return Command::SUCCESS;
    }

    private function generateXml(array $urls): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"></urlset>');

        foreach ($urls as $url) {
            $urlElement = $xml->addChild('url');
            $urlElement->addChild('loc', htmlspecialchars($url['loc'], ENT_XML1, 'UTF-8'));
            if ($url['lastmod']) {
                $urlElement->addChild('lastmod', $url['lastmod']);
            }
            $urlElement->addChild('changefreq', $url['changefreq']);
            $urlElement->addChild('priority', $url['priority']);
    
            if (isset($url['video']) && $url['video']) {
                $videoElement = $urlElement->addChild('video:video', '', 'http://www.google.com/schemas/sitemap-video/1.1');
                $videoElement->addChild('video:content_loc', htmlspecialchars($url['video']['content_loc'], ENT_XML1, 'UTF-8'), 'http://www.google.com/schemas/sitemap-video/1.1');
                $videoElement->addChild('video:title', htmlspecialchars($url['video']['title'], ENT_XML1, 'UTF-8'), 'http://www.google.com/schemas/sitemap-video/1.1');
                $videoElement->addChild('video:description', htmlspecialchars($url['video']['description'], ENT_XML1, 'UTF-8'), 'http://www.google.com/schemas/sitemap-video/1.1');
            }
        }

        return $xml->asXML();
    }
}
