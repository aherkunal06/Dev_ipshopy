<?php
// Heading
$_['heading_title']                = 'Playful Sparkle - Google Oldaltérkép';
$_['heading_robotstxt']            = 'Robots.txt';
$_['heading_product']              = 'Termékek';
$_['heading_category']             = 'Kategóriák';
$_['heading_manufacturer']         = 'Gyártók';
$_['heading_information']          = 'Információk';
$_['heading_getting_started']      = 'Kezdő lépések';
$_['heading_setup']                = 'Google Sitemap beállítása';
$_['heading_troubleshot']          = 'Gyakori hibakeresési lépések';
$_['heading_faq']                  = 'GYIK';
$_['heading_contact']              = 'Terméktámogatás';

// Text
$_['text_extension']               = 'Bővítmények';
$_['text_success']                 = 'Siker: A Google Oldaltérkép feedet módosította!';
$_['text_htaccess_update_success'] = 'Siker: A .htaccess fájl sikeresen frissült.';
$_['text_edit']                    = 'Google Oldaltérkép szerkesztése';
$_['text_clear']                   = 'Adatbázis törlése';
$_['text_getting_started']         = '<p><strong>Áttekintés:</strong> A Google Sitemap kiegészítő az OpenCart 3-hoz segít növelni az üzlet láthatóságát optimalizált XML térképek generálásával. Ezek a térképek segítik a keresőmotorokat, mint például a Google, az Ön webhelyének kulcsfontosságú oldalainak indexelésében, ami jobb keresőoptimalizálási rangsoroláshoz és megnövekedett online jelenléthez vezet.</p><p><strong>Követelmények:</strong> OpenCart 3.x+, PHP 7.3 vagy újabb, valamint hozzáférés a <a href="https://search.google.com/search-console/about?hl=hu" target="_blank" rel="external noopener noreferrer">Google Search Console</a>-hoz a térkép benyújtásához.</p>';
$_['text_setup']                   = '<p><strong>A Google Sitemap beállítása:</strong> Konfigurálja a térképet, hogy szükség szerint tartalmazza a Termék, Kategória, Gyártó és Információs oldalakat. Változtassa meg a beállításokat, hogy engedélyezze vagy letiltsa ezeket az oldal típusokat a térkép kimenetében, testre szabva a térkép tartalmát az üzlet igényeinek és közönségének megfelelően.</p>';
$_['text_troubleshot']             = '<ul><li><strong>Kiegészítő:</strong> Győződjön meg arról, hogy a Google Sitemap kiegészítő engedélyezve van az OpenCart beállításokban. Ha a kiegészítő le van tiltva, a térkép kimenete nem lesz generálva.</li><li><strong>Termék:</strong> Ha a Termék oldalak hiányoznak a térképből, győződjön meg arról, hogy engedélyezve vannak a kiegészítő beállításaiban, és hogy a megfelelő termékek állapota "Engedélyezett".</li><li><strong>Kategória:</strong> Ha a Kategória oldalak nem jelennek meg, ellenőrizze, hogy a kategóriák engedélyezve vannak-e a kiegészítő beállításaiban, és hogy azok állapota is "Engedélyezett".</li><li><strong>Gyártó:</strong> A Gyártó oldalak esetében ellenőrizze, hogy azok engedélyezve vannak a kiegészítő beállításaiban, és hogy a gyártók állapota "Engedélyezett".</li><li><strong>Információ:</strong> Ha az Információs oldalak nem jelennek meg a térképen, győződjön meg arról, hogy engedélyezve vannak a kiegészítő beállításaiban, és hogy az állapotuk "Engedélyezett".</li></ul>';
$_['text_faq']                     = '<details><summary>Hogyan küldhetem el a sitemap-et a Google Search Console-ba?</summary>A Google Search Console-ban lépjen a menü <em>Sitemaps</em> részébe, adja meg a sitemap URL-jét (jellemzően /sitemap.xml), majd kattintson az <em>Elküldés</em> gombra. Ezzel értesíti a Google-t, hogy kezdje el a webhelyének bejárását.</details><details><summary>Miért fontos a sitemap a SEO szempontjából?</summary>A sitemap útmutatja a keresőmotorokat a webhely legfontosabb oldalaihoz, megkönnyítve ezáltal a tartalom pontos indexelését, ami pozitívan befolyásolhatja a keresési rangsorolást.</details><details><summary>Az képek szerepelnek a sitemap-ban?</summary>Igen, a képek szerepelnek a generált sitemap-ban, amelyet ez a bővítmény biztosít, biztosítva ezzel, hogy a keresőmotorok indexálhassák a vizuális tartalmát az URL-el együtt.</details><details><summary>Miért használ a sitemap <em>lastmod</em>-ot <em>priority</em> és <em>changefreq</em> helyett?</summary>A Google mostantól figyelmen kívül hagyja a <priority> és <changefreq> értékeket, ehelyett a tartalom frissességére összpontosítva a <lastmod>-ot. A <lastmod> használata segít prioritizálni a legutóbbi frissítéseket.</details>';
$_['text_contact']                 = '<p>További segítségért kérjük, lépjen kapcsolatba támogatási csapatunkkal:</p><ul><li><strong>Kapcsolat:</strong> <a href="mailto:%s">%s</a></li><li><strong>Dokumentáció:</strong> <a href="%s" target="_blank" rel="noopener noreferrer">Felhasználói dokumentáció</a></li></ul>';
$_['text_user_agent_any']          = 'Bármely felhasználói ügynök';
$_['text_allowed']                 = 'Engedélyezett: %s';
$_['text_disallowed']              = 'Tiltott: %s';

// Tab
$_['tab_general']                  = 'Általános';
$_['tab_help_and_support']         = 'Segítség &amp; támogatás';
$_['tab_data_feed_url']            = 'Adatfolyam URL';
$_['tab_data_feed_seo_url']        = 'SEO-barát adatfolyam URL';

// Entry
$_['entry_status']                 = 'Állapot';
$_['entry_product']                = 'Termék';
$_['entry_product_images']         = 'Termékképek exportálása';
$_['entry_max_product_images']     = 'Maximális termék képek';
$_['entry_category']               = 'Kategória';
$_['entry_category_images']        = 'Kategóriaképek exportálása';
$_['entry_manufacturer']           = 'Gyártó';
$_['entry_manufacturer_images']    = 'Gyártóképek exportálása';
$_['entry_information']            = 'Információ';
$_['entry_data_feed_url']          = 'Adatfolyam URL';
$_['entry_active_store']           = 'Aktív áruház';
$_['entry_htaccess_mod']           = '.htaccess módosítása';
$_['entry_validation_results']     = 'Érvényesítés eredményei';
$_['entry_user_agent']             = 'User-Agent';

// Button
$_['button_patch_htaccess']        = '.htaccess módosítása';
$_['button_validate_robotstxt']    = 'Robots.txt szabályok érvényesítése';

// Help
$_['help_copy']                    = 'URL másolása';
$_['help_open']                    = 'URL megnyitása';
$_['help_product_images']          = 'A termékképek exportálása kezdetben megnövelheti a folyamat idejét (csak az első képfeldolgozásnál), és ennek eredményeként az XML webhelytérkép fájlmérete is nagyobb lesz.';
$_['help_htaccess_mod']            = 'A SEO-barát adatfolyam URL-je módosítást igényel a .htaccess fájlban. A szükséges kódot manuálisan is hozzáadhatja a .htaccess fájlhoz másolással és beillesztéssel, vagy egyszerűen kattintson a narancssárga „Patch .htaccess” gombra a módosítások automatikus alkalmazásához.';

// Error
$_['error_permission']             = 'Figyelmeztetés: Nincs jogosultsága a Google Oldaltérkép feed módosításához!';
$_['error_htaccess_update']        = 'Figyelem: Hiba történt a .htaccess fájl frissítése során. Kérjük, ellenőrizze a fájl jogosultságait, majd próbálja újra.';
$_['error_store_id']               = 'Figyelem: A űrlap nem tartalmazza a áruház azonosítóját!';
$_['error_max_product_images_min'] = 'A maximális termék képek értéke nem lehet kisebb mint nulla.';
