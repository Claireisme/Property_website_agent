<?php

$base = require __DIR__.'/../en/site.php';

return array_replace_recursive($base, [
    'language' => 'Sprache',
    'translation_notice_title' => 'Übersetzungshinweis',
    'translation_disclaimer' => 'Diese übersetzte Seite dient nur zur Orientierung. Bei Fehlern, Auslassungen, Unklarheiten oder Abweichungen gilt die englische Version des Inserats. Wir übernehmen keine Haftung für die Nutzung der übersetzten Inhalte.',
    'nav' => ['properties' => 'Immobilien', 'about' => 'Über uns', 'valuation' => 'Bewertung', 'contact' => 'Kontakt', 'admin' => 'Admin'],
    'actions' => ['view_properties' => 'Immobilien ansehen', 'request_valuation' => 'Bewertung anfragen', 'search' => 'Suchen', 'send_enquiry' => 'Anfrage senden', 'submit_offer' => 'Angebot senden', 'view_on_agency_site' => 'Auf Agenturwebsite ansehen'],
    'home' => ['latest_local_listing' => 'Neueste lokale Anzeige', 'latest_properties' => 'Neueste Immobilien', 'empty_properties' => 'Noch keine Immobilien veröffentlicht.'],
    'portal' => ['badge' => 'Immobiliensuche in Irland', 'latest_listings' => 'Neueste Anzeigen', 'listed_by' => 'Angeboten von :agency', 'online_offers_enabled' => 'Online-Angebote sind auf der Agenturwebsite verfügbar.', 'footer' => 'Synchronisierte Anzeigen unabhängiger Immobilienagenturen.'],
    'properties' => ['title' => 'Immobilien', 'all_types' => 'Alle Immobilientypen', 'price_on_application' => 'Preis auf Anfrage', 'description' => 'Beschreibung', 'features' => 'Merkmale', 'enquire' => 'Anfragen', 'make_offer' => 'Angebot abgeben', 'listing_details' => 'Anzeigendetails', 'empty_search' => 'Keine Immobilien entsprechen Ihrer Suche.'],
    'labels' => ['name' => 'Name', 'email' => 'E-Mail', 'phone' => 'Telefon', 'message' => 'Nachricht', 'bedrooms' => 'Schlafzimmer', 'bathrooms' => 'Badezimmer', 'beds' => 'Schlafz.', 'baths' => 'Bäder', 'offer_amount' => 'Angebotsbetrag', 'financing' => 'Finanzierung', 'mortgage_approval' => 'Hypothekenzusage', 'buyer_position' => 'Käufersituation', 'proof_document' => 'Nachweisdokument', 'conditions' => 'Bedingungen', 'select' => 'Auswählen'],
    'offer_terms' => 'Ich verstehe, dass dieses Angebot der Prüfung durch den Agenten und dem Vertrag unterliegt.',
    'messages' => ['enquiry_sent' => 'Ihre Anfrage wurde gesendet.', 'offer_sent' => 'Ihr Angebot wurde zur Prüfung an den Agenten gesendet.', 'contact_sent' => 'Danke, wir melden uns in Kürze.', 'valuation_sent' => 'Ihre Bewertungsanfrage wurde gesendet.'],
]);
