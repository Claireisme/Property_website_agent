<?php

$base = require __DIR__.'/../en/site.php';

return array_replace_recursive($base, [
    'language' => 'Kalba',
    'translation_notice_title' => 'Vertimo pranešimas',
    'translation_disclaimer' => 'Šis išverstas puslapis pateikiamas tik informaciniais tikslais. Jei yra klaidų, praleidimų, neaiškumų ar neatitikimų, galioja angliška skelbimo versija. Neprisiimame atsakomybės už rėmimąsi išverstu turiniu.',
    'nav' => ['properties' => 'Būstai', 'about' => 'Apie mus', 'valuation' => 'Vertinimas', 'contact' => 'Kontaktai', 'admin' => 'Admin'],
    'actions' => ['view_properties' => 'Žiūrėti būstus', 'request_valuation' => 'Prašyti vertinimo', 'search' => 'Ieškoti', 'send_enquiry' => 'Siųsti užklausą', 'submit_offer' => 'Pateikti pasiūlymą', 'view_on_agency_site' => 'Žiūrėti agentūros svetainėje'],
    'home' => ['latest_local_listing' => 'Naujausias vietinis skelbimas', 'latest_properties' => 'Naujausi būstai', 'empty_properties' => 'Kol kas nėra paskelbtų būstų.'],
    'portal' => ['badge' => 'Būstų paieška Airijoje', 'latest_listings' => 'Naujausi skelbimai', 'listed_by' => 'Paskelbė :agency', 'online_offers_enabled' => 'Internetiniai pasiūlymai galimi agentūros svetainėje.', 'footer' => 'Sinchronizuoti nepriklausomų nekilnojamojo turto agentų skelbimai.'],
    'properties' => ['title' => 'Būstai', 'all_types' => 'Visi būstų tipai', 'price_on_application' => 'Kaina pagal užklausą', 'description' => 'Aprašymas', 'features' => 'Ypatybės', 'enquire' => 'Teirautis', 'make_offer' => 'Pateikti pasiūlymą', 'listing_details' => 'Skelbimo informacija', 'empty_search' => 'Pagal paiešką būstų nerasta.'],
    'labels' => ['name' => 'Vardas', 'email' => 'El. paštas', 'phone' => 'Telefonas', 'message' => 'Žinutė', 'bedrooms' => 'miegamieji', 'bathrooms' => 'vonios', 'beds' => 'mieg.', 'baths' => 'vonios', 'offer_amount' => 'Pasiūlymo suma', 'financing' => 'Finansavimas', 'mortgage_approval' => 'Hipotekos patvirtinimas', 'buyer_position' => 'Pirkėjo padėtis', 'proof_document' => 'Patvirtinantis dokumentas', 'conditions' => 'Sąlygos', 'select' => 'Pasirinkti'],
    'offer_terms' => 'Suprantu, kad pasiūlymą turi peržiūrėti agentas ir jis priklauso nuo sutarties.',
    'messages' => ['enquiry_sent' => 'Jūsų užklausa išsiųsta.', 'offer_sent' => 'Jūsų pasiūlymas išsiųstas agento peržiūrai.', 'contact_sent' => 'Ačiū, netrukus susisieksime.', 'valuation_sent' => 'Jūsų vertinimo užklausa išsiųsta.'],
]);
