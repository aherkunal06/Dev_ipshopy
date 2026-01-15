<?php
/**
 * OpenCart 3.x Backend Ecommerce Feed  application
 * 
 * @package Mega Feed Pro
 * @author Andras Kato <developer@newcms.hu>
 * @link https://www.newcms.hu/
 * @copyright NewCart and NewCMS Software LTD. 2004-2021 (https://www.newcms.hu)
 * @license https://www.www.newcms.hu/oc3/license/
 * @version 1.2.7 Pro
 * 
 * Attention!
 * In case you want to use a nulled software version, we will detect it due to the built-in protection and you will be prosecuted!
 * DO NOT USE NULLED SOFTWARE BETTER USE ORIGINAL LICENSED SOFTWARE! 
 * 
 */

// Heading
$_['heading_title'] = 'Marketplace XML Feed v. 1.2.7';

$_['heading_title_1'] = 'Marketplace XML Feed v.1.2.7 - NewCart Extension';

// Text
$_['text_info'] = 'Marketplace XML Feed v. 1.2.7';
$_['text_extension']   = 'Rozšíření';
$_['text_success']     = 'Úspěšně jste změnili nastavení modulu Mega XML Feed Pro!';
$_['text_edit']        = 'Konfigurace modulu';

// Entry
$_['entry_status']     = 'Stav';
$_['entry_enabled']    = 'Aktivovaný';
$_['entry_disabled']   = 'Deaktivovaný';

$_['btn_on']    = 'On'; 
$_['btn_off']   = 'Off'; 

//labels 
$_['help'] = 'Info'; 

//buttons
$_['run_cron_btn']      = 'Ručně generovat feed';
$_['copy_cron_btn']     = 'Kopírovat cron URL';
$_['copy_feed_btn']     = 'Kopírovat URL feedu';
$_['delete_feed_btn']     = 'Smazat feed';
$_['open_feed_btn']     = 'Zobrazit XML Feed';

//tabs
$_['tab_settings']     = 'Nastavení';
$_['tab_adverts']      = 'Reklamní systémy';
$_['tab_cron']         = 'Úlohy cronu';
$_['tab_xml_feeds']    = 'XML feedy';
$_['tab_news']         = 'Novinky';
$_['tab_support']      = 'Podpora';
$_['tab_license']      = 'Licence';

//top nav bar
$_['mega']          = 'Marketplace XML Feed Pro';
$_['register_btn']     = 'Vytvořit účet';
$_['sign_in_btn']      = 'Přihlásit se';
$_['login_url']        = '';
$_['register_url']     = '';
$_['mega_url']      = '';
$_['mega_feed_pro_url'] = '';

//home section
$_['home_heading']     = 'PŘIPOJ SVŮJ ESHOP DO ONLINE SVĚTA'; 
$_['home_desc1']       = 'Napojte se na největší online marketingové kanály.'; 
$_['home_desc2']       = 'Exportujte svoje produkty a pomocí Mega App je proměňte na stovky dalších XML formátů.'; 

//menu section
$_['menu_heading']    = 'Propojte svůj obchod se světem online marketingu.'; 

//settings section
$_['settings_heading'] = 'Nastavení modulu';
$_['product_feed_heading'] = 'Nastavení produktového XML feedu';
$_['product_feed_text'] = 'Nastavte po kolika produktech se budou generovat jednotlivé dávky exportu. Ponechte textové pole prázdné pro vygenerování celého XML feedu najednou.';
$_['product_feed_limit'] = 'Cron limit'; 
$_['product_feed_limit_help'] = 'Počet produktů na 1 dávku. Je-li pole prázdné, limit se nebere v potaz.'; 
$_['category_feed_heading'] = 'Nastavení XML feedu pro kategorie';
$_['category_feed_text'] ='Nastavte po kolika kategoriích se budou generovat jednotlivé dávky exportu. Ponechte textové pole prázdné pro vygenerování celého XML feedu najednou.';
$_['category_feed_limit'] = 'Cron limit'; 
$_['category_feed_limit_help'] = 'Počet kategorií na 1 dávku. Je-li pole prázdné, limit se nebere v potaz.'; 

//adverts section
$_['adverts_heading']     = 'Reklamní systémy'; 
$_['adverts_info_text'] = 'Usilovně pracujeme na dalších funckcionalitách. V blízké době se můžete těšit na podporu reklamních systémů jako Facebook Pixel, Google Ads měření konverzí, remarketing a mnoho dalších.';
$_['adverts_menu_google'] = "Google";
$_['adverts_menu_facebook'] = "Facebook";
$_['adverts_menu_heureka'] = "Heureka";
$_['adverts_menu_glami'] = "GLAMI";
$_['adverts_menu_seznam'] = "Seznam";
$_['adverts_menu_etarget'] = "Etarget";
$_['adverts_menu_najnakupsk'] = "Najnakup.sk";
$_['adverts_menu_pricemania'] = "Pricemania";
$_['adverts_facebook_heading']  = 'Facebook Pixel';
$_['adverts_facebook_help_text']  = 'Pixel ID naleznete v administraci Business Manager pro Facebook. Přejděte do nastavení Správce událostí > Přidat nový zdroj dat > Facebook pixel a vytvořte pixel. Na stránce Přehled daného pixelu vlevo nahoře je pod názvem zobrazené pixel ID.'; 
$_['adverts_facebook_pixel_id']  = 'Facebook Pixel ID';
$_['adverts_facebook_pixel_fill']  = 'Zadej pixel ID'; 
$_['adverts_gtm_heading']  = 'Google Tag Manager'; 
$_['adverts_gtm_id']  = 'Google Tag Manager ID';
$_['adverts_gtm_fill']  = 'Zadej ID';
$_['adverts_gtm_global_site_tracking_code']  = 'Přidat globální sledovací kód \"gtag.js\"'; 
$_['adverts_gtm_ecommerce_tracking']  = 'Elektronický obchod - měření'; 
$_['adverts_gtm_enhanced_ecommerce_tracking']  = 'Rozšířený elektronický obchod - měření'; 
$_['adverts_gtm_help_text1']  = 'Aktivní by měl být vždy pouze Google Tag Manager (Správce značek Google) nebo Globální značka Google gtag.js.'; 
$_['adverts_gtm_help_text2']  = 'Základní kód pro sledování zobrazení stránky (nezbytné pro sledování elektronického obchodu a rozšířeného elektronického obchodu).'; 
$_['adverts_gtm_help_text3']  = 'Měření transakcí / nákupů elektronického obchodu.'; 
$_['adverts_gtm_help_text4']  = 'Rozšířené komplexní sledování akcí zákazníků.'; 
$_['adverts_ga_heading']  = 'Google Analytics - gtag.js'; 
$_['adverts_ga_id']  = 'Google Analytics ID';
$_['adverts_ga_fill']  = 'Vyplňte ID'; 
$_['adverts_ga_global_site_tracking_code']  = 'Přidat Globální sledovací kód \"gtag.js\"'; 
$_['adverts_ga_ecommerce_tracking']  = 'Elektronický obchod - měření'; 
$_['adverts_ga_enhanced_ecommerce_tracking']  = 'Rozšířený elektronický obchod - měření';
$_['adverts_ga_help_text1']  = 'Only Google Tag Manager or gtag.js should be active at a time'; 
$_['adverts_ga_help_text2']  = 'Základní kód pro sledování zobrazení stránky (nezbytné pro sledování elektronického obchodu a rozšířeného elektronického obchodu).'; 
$_['adverts_ga_help_text3']  = 'Měření transakcí / nákupů elektronického obchodu.'; 
$_['adverts_ga_help_text4']  = 'Rozšířené komplexní sledování akcí zákazníků.';
$_['adverts_gcr_heading']  = 'Zákaznické recenze Google'; 
$_['adverts_gcr_id']  = 'Zákaznické recenze Google ID';
$_['adverts_gcr_fill']  = 'Vyplňte ID'; 
$_['adverts_gcr_order_time']  = 'Čas poslední expedované objednávky tentýž den'; 
$_['adverts_gcr_delivery_days']  = 'Počet dnů dodání'; 
$_['adverts_gcr_position']  = 'Pozice recenze'; 
$_['adverts_gcr_pos_inline']  = 'Inline'; 
$_['adverts_gcr_pos_bottom_left']  = 'Vlevo dole - plovoucí';
$_['adverts_gcr_pos_bottom_right']  = 'Vpravo dole - plovoucí'; 
$_['adverts_gcr_help_text1']  = 'Zadejte nejzazší čas, při kterém bude objednávka odeslána ještě tentýž den. Příklad: Pokud nastavíte čas např. na 10:00 a objednávka přijde do 10:00, počet dnů na doručení se začne počítat ode dne přijetí objednávky. Pokud dostanete objednávku po 10:00, počet dnů na doručení se začne počítat od následujícího dne.'; 
$_['adverts_gcr_help_text2']  = 'Zadejte počet dnů do doručení objednávky ode dne odeslání.'; 
$_['adverts_gcr_help_text3']  = 'Inline - odznak se zobrazí na místě, kam vložíte kód. Vlevo dole - plovoucí - odznak bude plout v levém dolním rohu stránky. Vpravo dole - plovoucí - odznak bude plout v pravém dolním rohu stránky.'; 
$_['adverts_glami_heading']  = 'Glami Pixel';
$_['adverts_glami_pixel_help_text']  = 'Váš piXel naleznete v administraci Glami na stránce Glami piXel > Implementace Glami piXel pro vývojáře > sekce Glami piXel kód pro VÁŠ ESHOP.'; 
$_['adverts_glami_pixel_id']  = 'Glami Pixel ID';
$_['adverts_glami_pixel_fill']  = 'Zadej pixel ID'; 
$_['adverts_glami_pixel_active']  = 'Aktivní'; 
$_['adverts_glami_reviews_heading']  = 'Glami TOP Recenze';
$_['adverts_glami_reviews_help_text']  = 'Váš API klíč pro Glami TOP naleznete v administraci Glami na stránce Glami TOP > Implementace > Průvodce implementace pro vývojáře > sekce Integrace pomocí Javascriptu.'; 
$_['adverts_glami_reviews_merchant_id']  = 'API klíč (ID e-shopu)';
$_['adverts_glami_reviews_merchant_fill']  = 'Zadej Merchant ID'; 
$_['adverts_glami_reviews_country'] = 'Registrován v'; 
$_['adverts_glami_reviews_country_label'] = 'Vyberte web';
$_['adverts_najnakupsk_heading'] = 'Najnakup.sk'; 
$_['adverts_najnakupsk_help_text'] = 'Vaše jedinečné ID obchodu z Najnakup.sk.'; 
$_['adverts_pricemania_heading'] = 'Pricemania'; 
$_['adverts_pricemania_help_text'] = 'Vaše jedinečné ID obchodu z Pricemania.'; 
$_['adverts_google_ads_heading'] = 'Google Ads';
$_['adverts_google_ads_tracking_label'] = 'Konverze'; 
$_['adverts_google_ads_code'] = 'Konverzní kód'; 
$_['adverts_google_ads_label'] = 'Konverzní štítek'; 
$_['adverts_google_ads_remarketing_label'] = 'Remarketing'; 
$_['adverts_google_ads_remarketing'] = 'Remarketing ID'; 
$_['adverts_google_ads_code_fill'] = 'Zadej kód'; 
$_['adverts_google_ads_label_fill'] = 'Zadej štítek'; 
$_['adverts_google_ads_remarketing_fill'] = 'Zadej ID'; 
$_['adverts_google_ads_help_text1'] = 'Konverzní kód získáte v administraci Google Ads účtu > Nástroje a nastavení > Měření – konverze > Přidat konverzi > Webová stránka. Vytvořte novou konverzi a poté klikněte na Nainstalovat značku sami. Kód se nachází v sekci “Globální značka webu” a má tuto podobu AW-123456789.'; 
$_['adverts_google_ads_help_text2'] = 'Konverzní štítek najdete na stejné stránce jako konverzní kód. Štítek se nachází v sekci “Fragment události” v elementu send_to v části za lomítkem. Má například podobu /SqrGHAdS-MerfQC.'; 
$_['adverts_google_ads_help_text3'] = 'Remarketing ID získáte v administraci Google Ads účtu > Nástroje a nastavení > Správce publik > Zdroje publik > Nastavit značku Google Ads. Vytvořte novou značku a poté klikněte na Nainstalovat značku sami. Kód se nachází v sekci “Globální značka webu” a má tuto podobu AW-123456789.'; 
$_['adverts_zbozi_heading'] = 'Zbozi.cz'; 
$_['adverts_zbozi_conversion_tracking'] = 'Standardní měření konverzí';
$_['adverts_zbozi_shop_id'] = 'ID Provozovny';
$_['adverts_zbozi_secret_key'] = 'Tajný klíč'; 
$_['adverts_zbozi_shop_id_fill'] = 'Zadej ID provozovny'; 
$_['adverts_zbozi_secret_key_fill'] = 'Zadej Tajný klíč';
$_['adverts_zbozi_debug'] = 'Debug mode';
$_['adverts_zbozi_dph'] = 'DPH';
$_['adverts_zbozi_help_text1'] = 'Narozdíl od omezeného měření umožní Standardní měření konverzí mít přehled o počtu a hodnotě konverzí, a dále také o konverzním poměru, ceně za konverzi, přímých konverzích, počtu prodaných kusů, apod.'; 
$_['adverts_zbozi_help_text2'] = 'Vaše ID provozovny naleznete v administraci zbozi.cz > Provozovny > ESHOP > Měření konverzí > ID provozovny.'; 
$_['adverts_zbozi_help_text3'] = 'Váš unikátní tajný klíč naleznete v administraci zbozi.cz > Provozovny > ESHOP > Měření konverzí > Váš unikátní tajný klíč.';
$_['adverts_zbozi_help_text4'] = 'Pro ověření správného nasazení konverzního kódu Zboží.cz můžete využít testovacího běhu s pomocí testovacích údajů - ID a tajného klíče. Testovací údaje si můžete vygenerovat na této stránce <a href="https://sandbox.zbozi.cz/" target="_blank" rel="noopener">sandbox.zbozi.cz</a>';
$_['adverts_zbozi_help_text5'] = 'Zvolte si, zda bude hodnota konverze odesílána včetně DPH nebo bez DPH. Pozn.: Zboží.cz ve specifikaci pro měření konverzí uvádí cenu objednávky a dopravy včetně DPH.'; 
$_['adverts_heureka_heading'] = 'Heureka';
$_['adverts_heureka_review_by_customers'] = 'Ověřeno zákazníky'; 
$_['adverts_heureka_conversion_tracking'] = 'Měření konverzí'; 
$_['adverts_heureka_help_text1'] = 'Klíč vašeho obchodu naleznete v administraci Heureka účtu pod záložkou Ověřeno zákazníky > Nastavení a data dotazníků > Tajný klíč pro Ověřeno zákazníky.'; 
$_['adverts_heureka_help_text2'] = 'Klíč měření konverzí vašeho obchodu naleznete v administraci Heureka účtu pod záložkou Statistiky a reporty > Měření konverzí > Veřejný klíč pro kód měření konverzí.';
$_['adverts_heureka_help_text3'] = 'Klíč vašeho widgetu naleznete v administraci Heureka účtu pod záložkou Ověřeno zákazníky > Nastavení a data dotazníků > Ikony certifikátu Ověřeno zákazníky. Číselný kód se nachází v kódu pro vložení na web. Má podobu "...setKey\', \'330BD_VAS_KLIC_WIDGETU_2A80\']);_hwq.push\'..."';
$_['adverts_heureka_help_text4'] = 'Nastavení pro skrytí widgetu pod určitou šířkou obrazovky (v px) je platné pouze pro desktopy. Na mobilních zařízeních je toto nastavení ignorováno.'; 
$_['adverts_heureka_help_text5'] = 'Pokud je zapnuta tato volba, widget se zobrazí na mobilních zařízeních bez ohledu na nastavení šířky pro skrytí widgetu.'; 
$_['adverts_heureka_customer_id'] = 'ID certifikátu'; 
$_['adverts_heureka_customer_widget'] = 'Widget';
$_['adverts_heureka_conversion_id'] = 'Klíč pro měření konverzí'; 
$_['adverts_heureka_customer_id_fill'] = 'Zadejte ID certifikátů'; 
$_['adverts_heureka_conversion_id_fill'] = 'Zadejte klíč pro měření konverzí'; 
$_['adverts_heureka_customer_widget_position'] = 'Pozice widgetu'; 
$_['adverts_heureka_customer_widget_pos_left'] = 'Vlevo'; 
$_['adverts_heureka_customer_widget_pos_right'] = 'Vpravo'; 
$_['adverts_heureka_customer_top_margin'] = "Odsazení widgetu shora"; 
$_['adverts_heureka_customer_widget_top_margin_fill'] = "Zadejte odsazení shora v px"; 
$_['adverts_heureka_customer_widget_mobile'] = "Zobrazit widget na mobilních zařízeních"; 
$_['adverts_heureka_customer_min_screen_width'] = "Skrýt widget na obrazovkách menších než"; 
$_['adverts_heureka_customer_widget_min_screen_width_fill'] = "Zadejte min. šířku obrazovky v px"; 
$_['adverts_heureka_customer_widget_id'] = "Widget ID";
$_['adverts_heureka_customer_widget_id_fill'] = "Zadejte widget ID";
$_['adverts_sklik_heading'] = 'Sklik'; 
$_['adverts_sklik_conversion_status'] = 'Měření konverzí'; 
$_['adverts_sklik_conversion_id'] = 'ID konverze'; 
$_['adverts_sklik_conversion_id_fill'] = 'Zadejte ID konverze'; 
$_['adverts_sklik_conversion_value'] = 'Hodnota konverze'; 
$_['adverts_sklik_conversion_value_fill'] = 'Zadejte hodnotu konverze';
$_['adverts_sklik_dph'] = 'DPH'; 
$_['adverts_sklik_help_text1'] = 'ID konverze nalezenete v administraci Sklik -> Nástroje -> Sledování konverzí -> Detail konverze/Vytvořit novou konverzi. ID neleznete ve vygenerovaném scriptu na řádku var seznam_cId = VAŠE_ID';
$_['adverts_sklik_help_text2'] = 'Nechte pole prázdné pro automatické doplnění hodnoty z objednávky. Hodnota objednávky se počítá bez daní a bez ceny za dopravu a platbu.';
$_['adverts_sklik_help_text4'] = 'Zvolte si, zda bude hodnota konverze odesílána včetně DPH nebo bez DPH. Pozn.: Sklik ve specifikaci pro měření konverzí doporučuje uvádět hodnotu konverze bez DPH.';
$_['adverts_sklik_retargeting_status'] = 'Retargeting';
$_['adverts_sklik_retargeting_id'] = 'Retargeting ID';
$_['adverts_sklik_retargeting_id_fill'] = 'Zadej retargeting ID'; 
$_['adverts_sklik_help_text3'] = 'ID naleznete v administraci Sklik -> Nástroje -> Retargeting -> Zobrazit retargetingový kód. Číselný kód se nachází ve vygenerovaném skriptu za značkou: var seznam_retargeting_id = RETARGETINGOVÝ_KÓD'; 

//cron section
$_['cron_text']        = 'Nezapomeňte přidat následující cron odkazy do vašeho plánovače úloh. Plánovač úloh slouží k automatickému spouštění skriptů, v tomto případě k automatickému přegenerování XML feedů. Cron plánovače úloh jsou běžně dostupné například v rámci web hostingu.';
$_['product_cron_heading']     = 'Produktové XML feedy';
$_['category_cron_heading']     = 'XML feedy pro kategorie'; 
$_['heureka_availability_cron_heading'] = 'Heureka dostupnostní XML feed'; 
$_['cron_lang']    = 'Jazyk';
$_['cron_url']    = 'Cron URL';

//feed section
$_['feeds_text']       = ''; 
$_['product_feeds_heading']    = 'Produktové XML feedy';
$_['category_feeds_heading']    = 'XML feedy pro kategorie';
$_['heureka_availability_feeds_heading']    = 'Heureka dostupnostní XML feed'; 
$_['feed_last_change'] = 'Poslední změna';
$_['feed_last_creation'] = 'Datum vytvoření'; 
$_['feed_progress'] = 'Stav generování'; 
$_['feed_status'] = 'Stav';
$_['feed_url'] = 'XML URL'; 
$_['feed_lang']    = 'Jazyk';
$_['ok']            = 'OK';
$_['err']           = 'Chyba';
$_['feed_generator_modal_title'] = 'Generování feedu'; 
$_['warn_modal_title'] = 'Upozornění';
$_['err_modal_title'] = 'Chyba';
$_['feed_succ_msg']  = 'Feed byl úspěšně vygenerován.';
$_['feed_err_msg']  = 'Generování feedu selhalo.';
$_['feed_invalid_token_msg']  = 'Chyba: nevalidní bezpečnostní token.';
$_['delete_feed_title'] = 'Odstranění exportu'; 
$_['really_delete_feed'] = 'Opravdu smazat export '; 
$_['feed_delete_succ_msg'] = 'Odstranění exportu bylo úspěsné.'; 
$_['feed_delete_err_msg'] = 'Odstranění exportu selhalo.'; 
$_['modal_delete_btn'] = 'Smazat'; 

//support section
$_['support_heading']  = 'Podpora';
$_['support_text']     = 'V případě jakýchkoliv otázek nás neváhejte kontaktovat na <a href="mailto:support.oc3@newcms.hu">support.oc3@newcms.hu</a>.';
$_['logs_status']     = 'Logování';
$_['debug_mode']     = 'Režim ladění';
$_['logs_enabled']    = 'Zapnuté';
$_['logs_disabled']   = 'Vypnuté';

//logs section
$_['logs_heading']     = 'Logy';
$_['logs_settings_heading'] = 'Nastavení';
$_['logs_text']        = 'Každý úkon je zalogovaný do logů. V případě problémů nám prosím pošlete výpis logů ze dne a času, kdy problém nastal.<br/><br/>Všechny logy můžete smazat na stránce logů. Tyto logy jsou dostupné pouze pro uživatele s administrátorskými právy.<br/><br/><strong>Pokud kontaktujete podporu, nezapomeňte připojit tyto údaje:</strong>';
$_['logs_table_item']  = 'Položka';
$_['logs_table_value'] = 'Hodnota';
$_['ext_ver_label']    = 'Verze modulu';
$_['php_ver_label']    = 'Verze PHP';
$_['oc_ver_label']     = 'Verze OpenCart';
$_['website_url']      = 'URL obchodu';
$_['product_cron_url'] = 'URL produktového cronu';
$_['product_feed_url'] = 'URL produktové feedu';
$_['category_cron_url'] = 'URL cronu pro kategorie'; 
$_['category_feed_url'] = 'URL feedu pro kategorie';
$_['heureka_availability_cron_url'] = 'URL cronu pro Heureka dostupnostní feed'; 
$_['heureka_availability_feed_url'] = 'URL feedu pro Heureka dostupnostní feed'; 
$_['log_url']          = 'URL logu';
$_['mg_token']         = 'Mega token';
$_['s_id']             = 'ID obchodu';
$_['show_logs_btn']    = 'TXT export';
$_['download_logs_btn']= 'CSV export';
$_['clear_logs_btn']   = 'Vymazat logy';
$_['logs_clear_succ_msg']  = 'Logy byly úspěšně smazány.';
$_['logs_clear_err_msg']   = 'Při mazání logů se vyskytla chyba. Zkuste to znovu.';

//license section
$_['license_heading']  = 'Licence';
$_['license_text']     = 'Použití modulu Mega Feed Pro je pouze na vlastní riziko. Tvůrce modulu, společnost Mega Feed Protechnologies, LTD., nenese odpovědnost za případné ztráty či vzniklé škody v jakékoliv podobě. Instalací modulu do vašeho e-shopu s tímto souhlasíte.<br/><br/>Modul není možné měnit zásahem do zdrojových kódů či upravovat jinak než uživatelským nastavením v administraci OpenCart.<br/></br>Používání modulu Mega Feed Pro pro OpenCart je zdarma. Podporované verze jsou od 3.0.0.0. výš.';

//news section
$_['news_heading']  = 'Novinky'; 
$_['news_empty_text'] = 'Žádné novinky.';
$_['update_heading']  = 'Aktualizace'; 
$_['update_empty_text'] = 'Žádné nové aktualizace.'; 
$_['category_news']     = 'Novinky'; 
$_['category_update']     = 'Aktualizace';

//sidebar section
$_['sidebar_recommend_heading']  = 'Mega Feed Pro doporučuje'; 

//modal
$_['modal_close_btn']  = 'Zavřít';

//exit modal
$_['exit_title']  = 'Výstraha'; 
$_['exit_msg']  = 'Skutečně chcete opustit stránku? Změny, které jste provedli, nebudou uloženy.';
$_['modal_leave_btn']  = 'Opustit stránku'; 
$_['modal_stay_btn']  = 'Zůstat na stránce'; 

//rating modal
$_['rating_popup_heading'] = 'Jak hodnotit'; 
$_['rating_popup_text1'] = '<span class="number">1.</span> Musíte se <strong>Přihlásit</strong> (Log in v pravém horním rohu)'; 
$_['rating_popup_text2'] = '<span class="number">2.</span> Ve Vašem účtu: Přejděte na <strong>Rate your downloads</strong>'; 
$_['rating_popup_text3'] = '<span class="number">3.</span> <strong>Rate Mega Feed Pro</strong>'; 
$_['rating_popup_rate_btn'] = 'Ok, ohodnotit nyní'; 

?>
