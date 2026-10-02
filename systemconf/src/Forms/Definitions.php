<?php

declare(strict_types=1);

namespace Systemconf\Forms;

/**
 * Sitedeki formların alan tanımları. Elementor Pro formlarından birebir taşındı.
 *
 * Alan şeması: id, label, type (text|tel|email|date|time|select|textarea|checkbox|file),
 * required, placeholder, options (select/checkbox için), width (100|50), rows.
 */
final class Definitions
{
    /** @return array<string, array<string, mixed>>|null */
    public static function get(string $formId): ?array
    {
        $all = self::all();

        return $all[$formId] ?? null;
    }

    /** @return array<string, array<string, mixed>> */
    public static function all(): array
    {
        return [
            'kariyer' => [
                'title'       => 'Kariyer Başvurusu',
                'subject'     => 'Yeni kariyer başvurusu',
                'button'      => 'Gönder',
                'show_labels' => false,
                'fields'      => [
                    ['id' => 'ad_soyad', 'label' => 'İsim Soyisim', 'type' => 'text', 'required' => true, 'placeholder' => 'İsim Soyisim', 'width' => 50],
                    ['id' => 'telefon', 'label' => 'Telefon', 'type' => 'tel', 'required' => true, 'placeholder' => 'Telefon', 'width' => 50],
                    ['id' => 'eposta', 'label' => 'E-Mail', 'type' => 'email', 'required' => true, 'placeholder' => 'E-Mail', 'width' => 100],
                    ['id' => 'konu', 'label' => 'Konu', 'type' => 'text', 'required' => false, 'placeholder' => 'Konu', 'width' => 100],
                    ['id' => 'mesaj', 'label' => 'Mesaj', 'type' => 'textarea', 'required' => false, 'placeholder' => 'Mesaj', 'width' => 100, 'rows' => 4],
                    ['id' => 'dosya', 'label' => 'Özgeçmiş (PDF/Word)', 'type' => 'file', 'required' => true, 'width' => 100],
                ],
            ],
            'randevu' => [
                'title'       => 'Online Randevu',
                'subject'     => 'Yeni online randevu talebi',
                'button'      => 'Gönder',
                'show_labels' => true,
                'fields'      => [
                    ['id' => 'ad_soyad', 'label' => 'İsim Soyisim', 'type' => 'text', 'required' => true, 'width' => 100],
                    ['id' => 'gun', 'label' => 'Gün', 'type' => 'date', 'required' => true, 'width' => 50],
                    ['id' => 'saat', 'label' => 'Saat', 'type' => 'time', 'required' => true, 'width' => 50],
                    ['id' => 'telefon', 'label' => 'Telefon Numaranız', 'type' => 'tel', 'required' => true, 'width' => 100],
                    ['id' => 'eposta', 'label' => 'E-Mail Adresiniz', 'type' => 'email', 'required' => true, 'width' => 100],
                    [
                        'id' => 'hekim', 'label' => 'Hekim Seçiniz *', 'type' => 'select', 'required' => true, 'width' => 50,
                        'options' => [
                            'Uzm. Dr. KEREM ÇAĞLAR GÜMÜŞ', 'AYLİN GÜMÜŞ', 'ALİ FURKAN AKKUŞ', 'BEYZANUR TUNÇ',
                            'BERKAY YILMAZ', 'HARUN YÜCEDAĞ', 'BAYBARS DENİZ ŞEREMETLİOGLU', 'SELVİNUR ÇALIK', 'SÜMEYYE EYİ',
                        ],
                    ],
                    [
                        'id' => 'hizmet', 'label' => 'Hizmet Seçiniz *', 'type' => 'select', 'required' => true, 'width' => 50,
                        'options' => [
                            'İMPLANT TEDAVİSİ', 'GÜLÜŞ TASARIMI VE ESTETİGİ', 'ENDODONTİ (KÖK KANAL TEDAVİSİ)',
                            'ORTODONTİ(DİS TELİ TEDAVİSİ)', 'DİS BEYAZLATMA', 'PROTETİK DİS TEDAVİSİ (PROTEZ DİS)',
                        ],
                    ],
                    ['id' => 'mesaj', 'label' => 'Mesajınız *', 'type' => 'textarea', 'required' => false, 'width' => 100, 'rows' => 4],
                    [
                        'id' => 'poliklinik', 'label' => 'Poliklinik Seçiniz', 'type' => 'select', 'required' => false, 'width' => 100,
                        'options' => ['Merkez Polikliniği', 'Gölcük Polikliniği'],
                    ],
                    [
                        'id' => 'kvkk', 'label' => '6998 sayılı KVKK hakkında bilgilendirmeyi okudum ve anladım.',
                        'type' => 'checkbox', 'required' => true, 'width' => 100,
                    ],
                ],
            ],
        ];
    }
}
