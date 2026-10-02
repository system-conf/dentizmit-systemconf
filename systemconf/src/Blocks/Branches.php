<?php

declare(strict_types=1);

namespace Systemconf\Blocks;

/**
 * Şube verisi. Telefon alanı boş bırakılırsa merkez telefonu kullanılır.
 */
final class Branches
{
    public const HOURS = 'Her gün 09:00 - 00:00';

    /** @return array<int, array<string, string>> */
    public static function all(): array
    {
        $uploads = 'https://dentizmit.com/wp-content/uploads/2026/04/';

        return [
            [
                'name'    => 'İzmit Polikliniği',
                'address' => 'Ömerağa Mah. Ankara Karayolu Cd. No:59/2 İzmit/KOCAELİ',
                'phone'   => '0(262) 333 90 90',
                'hours'   => self::HOURS,
                'image'   => $uploads . 'izmit-1.jpg',
            ],
            [
                'name'    => 'Gölcük Polikliniği',
                'address' => 'Şehitler, Atatürk Blv. No:158, 41030 Gölcük/Kocaeli',
                'phone'   => '0(262) 333 90 90',
                'hours'   => self::HOURS,
                'image'   => $uploads . 'golcuk-2.jpg',
            ],
            [
                'name'    => 'Başiskele Polikliniği',
                'address' => 'Yeşilyurt Mah., Yuvacık 17 Ağustos Blv., 152A Başiskele/Kocaeli',
                'phone'   => '0(262) 333 90 90',
                'hours'   => self::HOURS,
                'image'   => $uploads . 'basikele-sube-1024x1024.jpeg',
            ],
        ];
    }

    public static function mapEmbedUrl(string $address): string
    {
        return 'https://www.google.com/maps?q=' . rawurlencode($address) . '&output=embed';
    }

    public static function directionsUrl(string $address): string
    {
        return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($address);
    }

    /** "0(262) 333 90 90" -> "02623339090" */
    public static function telHref(string $phone): string
    {
        return 'tel:' . preg_replace('/[^0-9+]/', '', $phone);
    }
}
