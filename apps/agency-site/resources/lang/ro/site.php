<?php

$base = require __DIR__.'/../en/site.php';

return array_replace_recursive($base, [
    'language' => 'Limbă',
    'translation_notice_title' => 'Notă privind traducerea',
    'translation_disclaimer' => 'Această pagină tradusă este oferită doar pentru informare. Dacă există erori, omisiuni, neclarități sau diferențe, versiunea în limba engleză a anunțului prevalează. Nu ne asumăm răspunderea pentru utilizarea conținutului tradus.',
    'nav' => ['properties' => 'Proprietăți', 'about' => 'Despre noi', 'valuation' => 'Evaluare', 'contact' => 'Contact', 'admin' => 'Admin'],
    'actions' => ['view_properties' => 'Vezi proprietăți', 'request_valuation' => 'Cere evaluare', 'search' => 'Caută', 'send_enquiry' => 'Trimite solicitarea', 'submit_offer' => 'Trimite oferta', 'view_on_agency_site' => 'Vezi pe site-ul agenției'],
    'home' => ['latest_local_listing' => 'Cea mai nouă ofertă locală', 'latest_properties' => 'Cele mai noi proprietăți', 'empty_properties' => 'Nu există proprietăți publicate.'],
    'portal' => ['badge' => 'Căutare proprietăți în Irlanda', 'latest_listings' => 'Cele mai noi oferte', 'listed_by' => 'Listată de :agency', 'online_offers_enabled' => 'Ofertele online sunt activate pe site-ul agenției.', 'footer' => 'Oferte sincronizate de la agenții imobiliare independente.'],
    'properties' => ['title' => 'Proprietăți', 'all_types' => 'Toate tipurile de proprietăți', 'price_on_application' => 'Preț la cerere', 'description' => 'Descriere', 'features' => 'Caracteristici', 'enquire' => 'Solicită informații', 'make_offer' => 'Fă o ofertă', 'listing_details' => 'Detalii anunț', 'empty_search' => 'Nicio proprietate nu corespunde căutării.'],
    'labels' => ['name' => 'Nume', 'email' => 'Email', 'phone' => 'Telefon', 'message' => 'Mesaj', 'bedrooms' => 'dormitoare', 'bathrooms' => 'băi', 'beds' => 'dorm.', 'baths' => 'băi', 'offer_amount' => 'Valoarea ofertei', 'financing' => 'Finanțare', 'mortgage_approval' => 'Aprobare ipotecară', 'buyer_position' => 'Situația cumpărătorului', 'proof_document' => 'Document justificativ', 'conditions' => 'Condiții', 'select' => 'Selectează'],
    'offer_terms' => 'Înțeleg că oferta este supusă verificării agentului și contractului.',
    'messages' => ['enquiry_sent' => 'Solicitarea a fost trimisă.', 'offer_sent' => 'Oferta a fost trimisă pentru verificarea agentului.', 'contact_sent' => 'Mulțumim, vă vom contacta în curând.', 'valuation_sent' => 'Cererea de evaluare a fost trimisă.'],
]);
