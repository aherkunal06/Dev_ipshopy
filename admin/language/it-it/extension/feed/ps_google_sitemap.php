<?php
// Heading
$_['heading_title']                = 'Playful Sparkle - Google Sitemap';
$_['heading_robotstxt']            = 'Robots.txt';
$_['heading_product']              = 'Prodotti';
$_['heading_category']             = 'Categorie';
$_['heading_manufacturer']         = 'Produttori';
$_['heading_information']          = 'Informazioni';
$_['heading_getting_started']      = 'Inizio';
$_['heading_setup']                = 'Impostazione di Google Sitemap';
$_['heading_troubleshot']          = 'Risoluzione dei problemi comuni';
$_['heading_faq']                  = 'Domande frequenti';
$_['heading_contact']              = 'Contatta il supporto';

// Text
$_['text_extension']               = 'Estensioni';
$_['text_success']                 = 'Successo: Hai modificato il feed di Google Sitemap!';
$_['text_htaccess_update_success'] = 'Successo: Il file .htaccess è stato aggiornato con successo.';
$_['text_edit']                    = 'Modifica Google Sitemap';
$_['text_clear']                   = 'Pulisci il database';
$_['text_getting_started']         = '<p><strong>Panoramica:</strong> Google Sitemap per OpenCart 3.x aiuta a migliorare la visibilità del tuo negozio generando sitemap XML ottimizzate. Queste sitemap aiutano i motori di ricerca come Google a indicizzare le pagine principali del tuo sito, portando a un miglior posizionamento nei motori di ricerca e aumentando la presenza online.</p><p><strong>Requisiti:</strong> OpenCart 3.x+, PHP 7.3 o superiore, e accesso alla tua <a href="https://search.google.com/search-console/about?hl=en" target="_blank" rel="external noopener noreferrer">Google Search Console</a> per l\'invio della sitemap.</p>';
$_['text_setup']                   = '<p><strong>Impostazione di Google Sitemap:</strong> Configura la tua sitemap per includere le pagine di prodotto, categoria, produttore e informazioni come necessario. Attiva o disattiva le opzioni per includere questi tipi di pagina nell\'output della sitemap, personalizzando il contenuto della sitemap in base alle esigenze del tuo negozio e del tuo pubblico.</p>';
$_['text_troubleshot']             = '<ul><li><strong>Estensione:</strong> Assicurati che l\'estensione Google Sitemap sia abilitata nelle impostazioni di OpenCart. Se l\'estensione è disabilitata, l\'output della sitemap non verrà generato.</li><li><strong>Prodotto:</strong> Se le pagine dei prodotti mancano dalla tua sitemap, assicurati che siano abilitate nelle impostazioni dell\'estensione e che i prodotti rilevanti abbiano lo stato impostato su "Abilitato".</li><li><strong>Categoria:</strong> Se le pagine delle categorie non appaiono, controlla che le categorie siano abilitate nelle impostazioni dell\'estensione e che il loro stato sia impostato su "Abilitato".</li><li><strong>Produttore:</strong> Per le pagine dei produttori, verifica che siano abilitate nelle impostazioni dell\'estensione e che i produttori abbiano lo stato impostato su "Abilitato".</li><li><strong>Informazioni:</strong> Se le pagine di informazioni non vengono visualizzate nella sitemap, assicurati che siano abilitate nelle impostazioni dell\'estensione e che il loro stato sia impostato su "Abilitato".</li></ul>';
$_['text_faq']                     = '<details><summary>Come invio la mia sitemap a Google Search Console?</summary>In Google Search Console, vai su <em>Sitemaps</em> nel menu, inserisci l\'URL della sitemap (tipicamente /sitemap.xml) e fai clic su <em>Invia</em>. Questo notificherà a Google di iniziare a scansionare il tuo sito.</details><details><summary>Perché una sitemap è importante per la SEO?</summary>Una sitemap guida i motori di ricerca alle pagine più importanti del tuo sito, facilitando l\'indicizzazione accurata del contenuto, il che può avere un impatto positivo sul posizionamento nei motori di ricerca.</details><details><summary>Le immagini sono incluse nella sitemap?</summary>Sì, le immagini sono incluse nella sitemap generata da questa estensione, garantendo che i motori di ricerca possano indicizzare il tuo contenuto visivo insieme all\'URL.</details><details><summary>Perché la sitemap usa <em>lastmod</em> invece di <em>priority</em> e <em>changefreq</em>?</summary>Google ora ignora i valori di <priority> e <changefreq>, concentrandosi invece su <lastmod> per la freschezza del contenuto. Utilizzare <lastmod> aiuta a dare priorità agli aggiornamenti recenti.</details>';
$_['text_contact']                 = '<p>Per ulteriore assistenza, contatta il nostro team di supporto:</p><ul><li><strong>Contatto:</strong> <a href="mailto:%s">%s</a></li><li><strong>Documentazione:</strong> <a href="%s" target="_blank" rel="noopener noreferrer">Documentazione utente</a></li></ul>';
$_['text_user_agent_any']          = 'Qualsiasi agente utente';
$_['text_allowed']                 = 'Consentito: %s';
$_['text_disallowed']              = 'Non consentito: %s';

// Tab
$_['tab_general']                  = 'Generale';
$_['tab_help_and_support']         = 'Aiuto &amp; Supporto';
$_['tab_data_feed_url']            = 'URL del feed dati';
$_['tab_data_feed_seo_url']        = 'URL del feed dati SEO-friendly';

// Entry
$_['entry_status']                 = 'Stato';
$_['entry_product']                = 'Prodotto';
$_['entry_product_images']         = 'Esporta immagini dei prodotti';
$_['entry_max_product_images']     = 'Max. immagini prodotto';
$_['entry_category']               = 'Categoria';
$_['entry_category_images']        = 'Esporta immagini delle categorie';
$_['entry_manufacturer']           = 'Produttore';
$_['entry_manufacturer_images']    = 'Esporta immagini dei produttoria';
$_['entry_information']            = 'Informazioni';
$_['entry_data_feed_url']          = 'URL feed dati';
$_['entry_active_store']           = 'Negozio attivo';
$_['entry_htaccess_mod']           = 'Modifica .htaccess';
$_['entry_validation_results']     = 'Risultati della convalida';
$_['entry_user_agent']             = 'User-Agent';

// Button
$_['button_patch_htaccess']        = 'Applicare la modifica a .htaccess';
$_['button_validate_robotstxt']    = 'Convalida le regole di Robots.txt';

// Help
$_['help_copy']                    = 'Copia URL';
$_['help_open']                    = 'Apri URL';
$_['help_product_images']          = 'L’esportazione delle immagini dei prodotti può aumentare inizialmente il tempo di elaborazione (solo al primo processo delle immagini), e la dimensione del file della sitemap XML sarà maggiore di conseguenza.';
$_['help_htaccess_mod']            = 'L\'URL del feed dati SEO-friendly richiede la modifica del file .htaccess. Puoi aggiungere manualmente il codice richiesto copiandolo e incollandolo nel file .htaccess, oppure fare semplicemente clic sul pulsante arancione „Patch .htaccess” per applicare automaticamente le modifiche.';

// Error
$_['error_permission']             = 'Attenzione: Non hai il permesso di modificare il feed di Google Sitemap!';
$_['error_htaccess_update']        = 'Attenzione: Si è verificato un errore durante l’aggiornamento del file .htaccess. Verifica i permessi del file e riprova.';
$_['error_store_id']               = 'Attenzione: Il modulo non contiene store_id!';
$_['error_max_product_images_min'] = 'Il valore delle immagini massime del prodotto non può essere inferiore a zero.';
