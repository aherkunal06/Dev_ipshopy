<?php
// Heading
$_['heading_title']                = 'Playful Sparkle - Google Sitemap';
$_['heading_robotstxt']            = 'Robots.txt';
$_['heading_product']              = 'Produkty';
$_['heading_category']             = 'Kategórie';
$_['heading_manufacturer']         = 'Výrobcovia';
$_['heading_information']          = 'Informácie';
$_['heading_getting_started']      = 'Začíname';
$_['heading_setup']                = 'Nastavenie Google Sitemap';
$_['heading_troubleshot']          = 'Bežné problémy';
$_['heading_faq']                  = 'Často kladené otázky';
$_['heading_contact']              = 'Kontaktujte podporu';

// Text
$_['text_extension']               = 'Rozšírenia';
$_['text_success']                 = 'Úspech: Zmenili ste Google Sitemap feed!';
$_['text_htaccess_update_success'] = 'Úspech: Súbor .htaccess bol úspešne aktualizovaný.';
$_['text_edit']                    = 'Upraviť Google Sitemap';
$_['text_clear']                   = 'Vyčistiť databázu';
$_['text_getting_started']         = '<p><strong>Prehľad:</strong> Rozšírenie Google Sitemap pre OpenCart 3.x pomáha zvýšiť viditeľnosť vášho obchodu generovaním optimalizovaných XML sitemap. Tieto sitemap pomáhajú vyhľadávačom, ako je Google, indexovať kľúčové stránky vášho webu, čo vedie k lepším pozíciám vo vyhľadávačoch a zvýšenej online prítomnosti.</p><p><strong>Požiadavky:</strong> OpenCart 3.x+, PHP 7.3 alebo vyšší a prístup do <a href="https://search.google.com/search-console/about?hl=sk" target="_blank" rel="external noopener noreferrer">Google Search Console</a> pre odoslanie sitemap.</p>';
$_['text_setup']                   = '<p><strong>Nastavenie Google Sitemap:</strong> Nakonfigurujte svoju sitemap, aby obsahovala stránky Produktov, Kategórií, Výrobcov a Informácií podľa potreby. Prepnite možnosti na povolenie alebo zakázanie týchto typov stránok v výstupe sitemap, prispôsobte obsah sitemap potrebám a publiku vášho obchodu.</p>';
$_['text_troubleshot']             = '<ul><li><strong>Rozšírenie:</strong> Uistite sa, že je rozšírenie Google Sitemap povolené v nastaveniach OpenCart. Ak je rozšírenie zakázané, výstup sitemap nebude generovaný.</li><li><strong>Produkt:</strong> Ak chýbajú stránky Produktov vo vašej sitemap, uistite sa, že sú povolené v nastaveniach rozšírenia a že príslušné produkty majú nastavený stav na "Povolené".</li><li><strong>Kategória:</strong> Ak sa stránky Kategórií nezobrazujú, skontrolujte, či sú kategórie povolené v nastaveniach rozšírenia a že ich stav je tiež nastavený na "Povolené".</li><li><strong>Výrobca:</strong> Pre stránky Výrobcov overte, či sú povolené v nastaveniach rozšírenia a že výrobcovia majú nastavený stav na "Povolené".</li><li><strong>Informácie:</strong> Ak sa stránky Informácií nezobrazujú v sitemap, uistite sa, že sú povolené v nastaveniach rozšírenia a že ich stav je nastavený na "Povolené".</li></ul>';
$_['text_faq']                     = '<details><summary>Ako odoslať svoju sitemap do Google Search Console?</summary>V Google Search Console prejdite do <em>Sitemaps</em> v menu, zadajte URL sitemap (typicky /sitemap.xml) a kliknite na <em>Odoslať</em>. Týmto upozorníte Google, aby začal prehľadávať vašu stránku.</details><details><summary>Prečo je sitemap dôležitá pre SEO?</summary>Sitemap usmerňuje vyhľadávače k najdôležitejším stránkam vášho webu, čo uľahčuje presné indexovanie obsahu a môže pozitívne ovplyvniť umiestnenie vo vyhľadávačoch.</details><details><summary>Či sú obrázky zahrnuté v sitemap?</summary>Ano, obrázky sú zahrnuté v generovanej sitemap pomocou tohto rozšírenia, čím sa zabezpečuje, že vyhľadávače môžu indexovať váš vizuálny obsah spolu s URL.</details><details><summary>Prečo sitemap používa <em>lastmod</em> namiesto <em>priority</em> a <em>changefreq</em>?</summary>Google teraz ignoruje hodnoty <priority> a <changefreq>, pričom sa zameriava na <lastmod> pre čerstvosť obsahu. Používanie <lastmod> pomáha prioritizovať nedávne aktualizácie.</details>';
$_['text_contact']                 = '<p>Pre ďalšiu pomoc sa, prosím, obráťte na náš tím podpory:</p><ul><li><strong>Kontakt:</strong> <a href="mailto:%s">%s</a></li><li><strong>Dokumentácia:</strong> <a href="%s" target="_blank" rel="noopener noreferrer">Dokumentácia pre používateľov</a></li></ul>';
$_['text_user_agent_any']          = 'Ľubovoľný používateľský agent';
$_['text_allowed']                 = 'Povolené: %s';
$_['text_disallowed']              = 'Zakázané: %s';

// Tab
$_['tab_general']                  = 'Všeobecné';
$_['tab_help_and_support']         = 'Pomoc a podpora';
$_['tab_data_feed_url']            = 'URL dátového feedu';
$_['tab_data_feed_seo_url']        = 'SEO-priateľská URL dátového feedu';

// Entry
$_['entry_status']                 = 'Stav';
$_['entry_product']                = 'Produkt';
$_['entry_product_images']         = 'Exportovať obrázky produktov';
$_['entry_max_product_images']     = 'Max. obrázkov produktov na výstup';
$_['entry_category']               = 'Kategória';
$_['entry_category_images']        = 'Exportovať obrázky kategórií';
$_['entry_manufacturer']           = 'Výrobca';
$_['entry_manufacturer_images']    = 'Exportovať obrázky výrobcov';
$_['entry_information']            = 'Informácie';
$_['entry_data_feed_url']          = 'URL dátového feedu';
$_['entry_active_store']           = 'Aktívny obchod';
$_['entry_htaccess_mod']           = 'Úprava .htaccess';
$_['entry_validation_results']     = 'Výsledky overenia';
$_['entry_user_agent']             = 'User-Agent';

// Button
$_['button_patch_htaccess']        = 'Použiť úpravy .htaccess';
$_['button_validate_robotstxt']    = 'Overiť pravidlá Robots.txt';

// Help
$_['help_copy']                    = 'Skopírovať URL';
$_['help_open']                    = 'Otvoriť URL';
$_['help_product_images']          = 'Exportovanie obrázkov produktov môže na začiatku zvýšiť čas spracovania (iba pri prvom spracovaní obrázkov), a veľkosť súboru XML mapy stránky sa tým zväčší.';
$_['help_htaccess_mod']            = 'SEO-priateľská URL dátového feedu vyžaduje úpravu súboru .htaccess. Kód môžete pridať manuálne skopírovaním a vložením do súboru .htaccess, alebo jednoducho kliknite na oranžové tlačidlo „Patch .htaccess“ pre automatické vykonanie zmien.';

// Error
$_['error_permission']             = 'Upozornenie: Nemáte oprávnenie na úpravu Google Sitemap feedu!';
$_['error_htaccess_update']        = 'Upozornenie: Došlo k chybe pri aktualizácii súboru .htaccess. Skontrolujte prosím povolenia k súboru a skúste to znova.';
$_['error_store_id']               = 'Upozornenie: Formulár neobsahuje identifikátor obchodu!';
$_['error_max_product_images_min'] = 'Hodnota maximálneho počtu obrázkov produktov nemôže byť menšia ako nula.';
