<?php

declare(strict_types=1);

namespace Systemconf\Blocks;

/**
 * Ana sayfa ve Hizmetlerimiz sayfasındaki hizmet kartlarının içeriği.
 * Elementor Pro flip-box bileşenlerinden birebir taşındı.
 */
final class Services
{
    /** @return array<int, array<string, string>> */
    public static function all(): array
    {
        $uploads = content_url('uploads/2023/02/');

        return [
            [
                'title'       => 'İmplant Tedavisi',
                'description' => 'Diş implantı, titanyum malzemeler kullanılarak hazırlanmış vidaların diş köklerine yerleştirilmesi ve kullanılacak protez dişlerin, bu vidalar yardımıyla sabitlenmesi işlemidir. Gelişen teknolojiyle birlikte diş tedavilerinde öne çıkan implant tedavisi hem dişlerde estetik bir görünüm sağlarken ağız sağlığını da olumlu yönde etkiler.',
                'image'       => $uploads . 'IMPLANT.png',
                'url'         => home_url('/implant/'),
            ],
            [
                'title'       => 'Gülüş Tasarımı ve Estetiği',
                'description' => 'Gülüş tasarımı; çeşitli nedenlerle estetik açıdan görünümü bozulan dişler ve diş etlerinin hastaların yüz şekilleri ve gereksinimleri de göz önünde bulundurularak doğal ve güzel bir görünüme kavuşturulması işlemidir. Multidisipliner bir yaklaşım olan gülüş tasarımı, birçok işlemin bir arada uygulanmasıyla gerçekleştirilmektedir.',
                'image'       => $uploads . 'gulus-tasarimi.png',
                'url'         => home_url('/gulus-tasarimi-2/'),
            ],
            [
                'title'       => "Endodonti\n(Kök Kanal Tedavisi)",
                'description' => 'Endodontik tedavi yani kanal tedavisi, çeşitli nedenlerle iltihaplanan veya mikroorganizmaların yerleşmesiyle enfekte olan pulpa dokusunun çıkartılarak kanal boşluğunun temizlenmesi, şekillendirilmesi ve doku dostu kanal dolgu malzemeleri ile doldurulması işlemidir.',
                'image'       => $uploads . 'KANAL_TEDAVISI.png',
                'url'         => home_url('/endodonti-kok-kanal-tedavisi/'),
            ],
            [
                'title'       => "Ortodonti\n(Diş Teli Tedavisi)",
                'description' => 'Diş teli tedavisi; diş ve çene eklemindeki düzensizliklerde, diş çapraşıklıklarının giderilmesinde, dişler arasındaki boşlukların giderilmesinde ve gerekli diş tedavileri için diş-çene anatomisinin düzenlenmesinde uygulanan bir tedavi yöntemidir.',
                'image'       => $uploads . 'TEL_TEDAVISI.png',
                'url'         => home_url('/ortodonti-saglikli-ve-estetik-bir-gulumseme/'),
            ],
            [
                'title'       => 'Diş Beyazlatma',
                'description' => 'Diş beyazlatma; dişlerin yüzeyindeki gözenekli mine yapısında oluşan renkli, organik ve inorganik maddelerin diş beyazlatma jelleri ile giderilmesi işlemidir. Dişlerde meydana gelen renklenmeleri diş beyazlatma işlemi ile giderilebilir ve daha beyaz dişlere sahip olabilirsiniz.',
                'image'       => $uploads . 'DIS_BEYAZLATMA.png',
                'url'         => home_url('/dis-beyazlatma/'),
            ],
            [
                'title'       => "Protetik Diş Tedavisi\n(Protez Diş)",
                'description' => 'Protetik Diş Tedavisi bölümümüzde; bozulan, eksilen, kaybolan işlev, estetik, rahatlık ve sağlığı yeniden kazandırmak amacıyla, dişlerin eksilen kısımları veya bir ya da daha fazla eksik dişi ve ilgili eksik dokular uygun maddelerle doldurulması işlemidir.',
                'image'       => $uploads . 'PROTEZ-1.png',
                'url'         => home_url('/protetik-dis-tedavisi/'),
            ],
        ];
    }
}
