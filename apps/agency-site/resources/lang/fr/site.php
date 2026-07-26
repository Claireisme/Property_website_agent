<?php

$base = require __DIR__.'/../en/site.php';

return array_replace_recursive($base, [
    'language' => 'Langue',
    'translation_notice_title' => 'Avis de traduction',
    'translation_disclaimer' => 'Cette page traduite est fournie uniquement à titre indicatif. En cas d’erreur, d’omission, d’ambiguïté ou de divergence, la version anglaise de l’annonce prévaut. Nous déclinons toute responsabilité liée à l’utilisation du contenu traduit.',
    'nav' => ['properties' => 'Biens', 'about' => 'À propos', 'valuation' => 'Estimation', 'contact' => 'Contact', 'admin' => 'Admin'],
    'actions' => ['view_properties' => 'Voir les biens', 'request_valuation' => 'Demander une estimation', 'search' => 'Rechercher', 'send_enquiry' => 'Envoyer la demande', 'submit_offer' => 'Envoyer l’offre', 'view_on_agency_site' => 'Voir sur le site de l’agence'],
    'home' => ['latest_local_listing' => 'Dernière annonce locale', 'latest_properties' => 'Derniers biens', 'empty_properties' => 'Aucun bien publié pour le moment.'],
    'portal' => ['badge' => 'Recherche immobilière en Irlande', 'latest_listings' => 'Dernières annonces', 'listed_by' => 'Annonce publiée par :agency', 'online_offers_enabled' => 'Les offres en ligne sont disponibles sur le site de l’agence.', 'footer' => 'Annonces synchronisées depuis des agences immobilières indépendantes.'],
    'properties' => ['title' => 'Biens', 'all_types' => 'Tous les types de biens', 'price_on_application' => 'Prix sur demande', 'description' => 'Description', 'features' => 'Caractéristiques', 'enquire' => 'Demander des informations', 'make_offer' => 'Faire une offre', 'listing_details' => 'Détails de l’annonce', 'empty_search' => 'Aucun bien ne correspond à votre recherche.'],
    'labels' => ['name' => 'Nom', 'email' => 'Email', 'phone' => 'Téléphone', 'message' => 'Message', 'bedrooms' => 'chambres', 'bathrooms' => 'salles de bain', 'beds' => 'ch.', 'baths' => 'sdb', 'offer_amount' => 'Montant de l’offre', 'financing' => 'Financement', 'mortgage_approval' => 'Accord de prêt', 'buyer_position' => 'Situation de l’acheteur', 'proof_document' => 'Justificatif', 'conditions' => 'Conditions', 'select' => 'Sélectionner'],
    'offer_terms' => 'Je comprends que cette offre est soumise à l’examen de l’agent et au contrat.',
    'messages' => ['enquiry_sent' => 'Votre demande a été envoyée.', 'offer_sent' => 'Votre offre a été envoyée pour examen par l’agent.', 'contact_sent' => 'Merci, nous vous contacterons bientôt.', 'valuation_sent' => 'Votre demande d’estimation a été envoyée.'],
]);
