<?php

declare(strict_types=1);

namespace App\Enums;

enum SourceType: string
{
    case Homepage = 'homepage';
    case About = 'about';
    case Services = 'services';
    case Contact = 'contact';
    case Careers = 'careers';

    public function label(): string
    {
        return match ($this) {
            self::Homepage => 'Homepage',
            self::About => 'About',
            self::Services => 'Services',
            self::Contact => 'Contact',
            self::Careers => 'Careers',
        };
    }

    public function defaultPath(): string
    {
        return match ($this) {
            self::Homepage => '/',
            self::About => '/about',
            self::Services => '/services',
            self::Contact => '/contact',
            self::Careers => '/careers',
        };
    }

    /**
     * @return array<string>
     */
    public function alternativePaths(): array
    {
        return match ($this) {
            self::Homepage => ['/'],
            self::About => ['/about', '/about-us', '/who-we-are', '/company'],
            self::Services => ['/services', '/what-we-do', '/solutions', '/offerings'],
            self::Contact => ['/contact', '/contact-us', '/get-in-touch'],
            self::Careers => ['/careers', '/jobs', '/work-with-us', '/join-us'],
        };
    }
}
