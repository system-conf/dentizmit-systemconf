# systemconf dağıtım akışı

1. `systemconf/systemconf.php` içinde **iki yerde** sürümü yükselt (başlık `Version:` ve `SYSTEMCONF_VERSION`).
2. Commit at: NE ve NEDEN yazılı mesajla.
3. Etiketle ve gönder: `git tag vX.Y.Z && git push origin master && git push origin vX.Y.Z`
4. GitHub Actions paketi hazırlar ve sürümü yayınlar (yaklaşık 1 dakika). Kontrol: `gh run list --repo system-conf/dentizmit-systemconf --limit 1`
5. Sitede **Systemconf > Güncelleme > "Şimdi kontrol et"**, sonra **Eklentiler** ekranında "güncelle".
   Tarayıcıdan tek komutla: Eklentiler sayfasında `wp.updates.updatePlugin({plugin:'systemconf/systemconf.php', slug:'systemconf'})`.
6. Kurulu sürümü doğrula: `curl -K .wpapi.curlrc "https://dentizmit.com/wp-json/wp/v2/plugins/systemconf/systemconf?_fields=version"`

Notlar
- Etiket ile eklenti sürümü uyuşmazsa iş akışı bilerek hata verir.
- `.wpapi.curlrc` git'e girmez; WordPress uygulama parolası orada durur.
- Elle zip yükleme artık gerekmiyor; acil durumda Eklentiler > Yeni ekle > Yükle hâlâ çalışır.
