<?php

$base = require __DIR__.'/../en/site.php';

return array_replace_recursive($base, [
    'language' => 'Język',
    'translation_notice_title' => 'Informacja o tłumaczeniu',
    'translation_disclaimer' => 'Ta przetłumaczona strona ma wyłącznie charakter informacyjny. W razie błędów, braków, niejasności lub rozbieżności wiążąca jest angielska wersja ogłoszenia. Nie ponosimy odpowiedzialności za poleganie na treści tłumaczenia.',
    'nav' => ['properties' => 'Nieruchomości', 'valuation' => 'Wycena', 'contact' => 'Kontakt', 'admin' => 'Admin'],
    'actions' => ['view_properties' => 'Zobacz nieruchomości', 'request_valuation' => 'Poproś o wycenę', 'search' => 'Szukaj', 'send_enquiry' => 'Wyślij zapytanie', 'submit_offer' => 'Złóż ofertę', 'view_on_agency_site' => 'Zobacz na stronie agencji'],
    'home' => ['latest_local_listing' => 'Najnowsza lokalna oferta', 'latest_properties' => 'Najnowsze nieruchomości', 'empty_properties' => 'Brak opublikowanych nieruchomości.'],
    'portal' => ['badge' => 'Wyszukiwarka nieruchomości w Irlandii', 'latest_listings' => 'Najnowsze oferty', 'listed_by' => 'Oferta od :agency', 'online_offers_enabled' => 'Oferty online są dostępne na stronie agencji.', 'footer' => 'Zsynchronizowane oferty od niezależnych agentów nieruchomości.'],
    'properties' => ['title' => 'Nieruchomości', 'all_types' => 'Wszystkie typy nieruchomości', 'price_on_application' => 'Cena na zapytanie', 'description' => 'Opis', 'features' => 'Cechy', 'enquire' => 'Zapytaj', 'make_offer' => 'Złóż ofertę', 'listing_details' => 'Szczegóły oferty', 'empty_search' => 'Brak nieruchomości pasujących do wyszukiwania.'],
    'labels' => ['name' => 'Imię i nazwisko', 'email' => 'Email', 'phone' => 'Telefon', 'message' => 'Wiadomość', 'bedrooms' => 'sypialnie', 'bathrooms' => 'łazienki', 'beds' => 'syp.', 'baths' => 'łaz.', 'offer_amount' => 'Kwota oferty', 'financing' => 'Finansowanie', 'mortgage_approval' => 'Zgoda kredytowa', 'buyer_position' => 'Sytuacja kupującego', 'proof_document' => 'Dokument potwierdzający', 'conditions' => 'Warunki', 'select' => 'Wybierz'],
    'offer_terms' => 'Rozumiem, że oferta podlega weryfikacji agenta i umowie.',
    'messages' => ['enquiry_sent' => 'Zapytanie zostało wysłane.', 'offer_sent' => 'Oferta została wysłana do weryfikacji agenta.', 'contact_sent' => 'Dziękujemy, wkrótce się skontaktujemy.', 'valuation_sent' => 'Prośba o wycenę została wysłana.'],
]);
