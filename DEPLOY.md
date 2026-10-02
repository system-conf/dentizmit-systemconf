# systemconf dağıtım akışı

1. `systemconf/systemconf.php` içinde **iki yerde** sürümü yükselt (başlık `Version:` ve `SYSTEMCONF_VERSION`).
2. Commit at: NE ve NEDEN yazılı mesajla.
3. Etiketle ve gönder: `git tag vX.Y.Z && git push origin master && git push origin vX.Y.Z`
4. GitHub Actions paketi hazırlar ve sürümü yayınlar (yaklaşık 1 dakika). Kontrol: `gh run list --repo system-conf/dentizmit-systemconf --limit 1`
5. Sürüm yayınlandıktan ~20 sn sonra siteye kurdur (tarayıcı gerekmez):
   `curl -K .wpapi.curlrc -X POST -m 120 https://dentizmit.com/wp-json/systemconf/v1/run-update`
   Cevapta `"updated":true` ve `"installed":"X.Y.Z"` görünmeli. "Yeni sürüm yok" derse GitHub API'si henüz tazelenmemiştir; 20 sn sonra tekrar dene.
   (Yedek yol: panelde Systemconf > Güncelleme > "Şimdi kontrol et", sonra Eklentiler > güncelle.)
6. Kurulu sürümü doğrula: `curl -K .wpapi.curlrc "https://dentizmit.com/wp-json/wp/v2/plugins/systemconf/systemconf?_fields=version"`

Ayar değiştirme (tarayıcısız): `curl -K .wpapi.curlrc -X POST -H "Content-Type: application/json" -d '{"merge":true,"value":{"enabled":false}}' https://dentizmit.com/wp-json/systemconf/v1/option/systemconf_layout`
Okuma: `GET .../option/systemconf_layout` (şifre alanları maskelenir). E-posta testi: `POST .../mail-test` gövde `{"to":"adres"}`.

Notlar
- Etiket ile eklenti sürümü uyuşmazsa iş akışı bilerek hata verir.
- `.wpapi.curlrc` git'e girmez; WordPress uygulama parolası orada durur.
- Elle zip yükleme artık gerekmiyor; acil durumda Eklentiler > Yeni ekle > Yükle hâlâ çalışır.
