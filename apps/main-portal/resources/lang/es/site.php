<?php

$base = require __DIR__.'/../en/site.php';

return array_replace_recursive($base, [
    'language' => 'Idioma',
    'translation_notice_title' => 'Aviso de traducción',
    'translation_disclaimer' => 'Esta página traducida se ofrece solo como referencia. Si hay errores, omisiones, ambigüedades o diferencias, prevalece la versión en inglés del anuncio. No aceptamos responsabilidad por el uso del contenido traducido.',
    'nav' => ['properties' => 'Propiedades', 'valuation' => 'Valoración', 'contact' => 'Contacto', 'admin' => 'Admin'],
    'actions' => ['view_properties' => 'Ver propiedades', 'request_valuation' => 'Solicitar valoración', 'search' => 'Buscar', 'send_enquiry' => 'Enviar consulta', 'submit_offer' => 'Enviar oferta', 'view_on_agency_site' => 'Ver en la web de la agencia'],
    'home' => ['latest_local_listing' => 'Último anuncio local', 'latest_properties' => 'Últimas propiedades', 'empty_properties' => 'Aún no hay propiedades publicadas.'],
    'portal' => ['badge' => 'Búsqueda de propiedades en Irlanda', 'latest_listings' => 'Últimos anuncios', 'listed_by' => 'Publicado por :agency', 'online_offers_enabled' => 'Las ofertas online están disponibles en la web de la agencia.', 'footer' => 'Anuncios sincronizados de agencias inmobiliarias independientes.'],
    'properties' => ['title' => 'Propiedades', 'all_types' => 'Todos los tipos de propiedad', 'price_on_application' => 'Precio a consultar', 'description' => 'Descripción', 'features' => 'Características', 'enquire' => 'Consultar', 'make_offer' => 'Hacer una oferta', 'listing_details' => 'Detalles del anuncio', 'empty_search' => 'No hay propiedades que coincidan con tu búsqueda.'],
    'labels' => ['name' => 'Nombre', 'email' => 'Email', 'phone' => 'Teléfono', 'message' => 'Mensaje', 'bedrooms' => 'habitaciones', 'bathrooms' => 'baños', 'beds' => 'hab.', 'baths' => 'baños', 'offer_amount' => 'Importe de la oferta', 'financing' => 'Financiación', 'mortgage_approval' => 'Aprobación hipotecaria', 'buyer_position' => 'Situación del comprador', 'proof_document' => 'Documento justificativo', 'conditions' => 'Condiciones', 'select' => 'Seleccionar'],
    'offer_terms' => 'Entiendo que esta oferta está sujeta a revisión del agente y contrato.',
    'messages' => ['enquiry_sent' => 'Tu consulta ha sido enviada.', 'offer_sent' => 'Tu oferta ha sido enviada para revisión del agente.', 'contact_sent' => 'Gracias, nos pondremos en contacto pronto.', 'valuation_sent' => 'Tu solicitud de valoración ha sido enviada.'],
]);
