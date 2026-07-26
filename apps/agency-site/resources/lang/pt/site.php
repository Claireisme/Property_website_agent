<?php

$base = require __DIR__.'/../en/site.php';

return array_replace_recursive($base, [
    'language' => 'Idioma',
    'translation_notice_title' => 'Aviso de tradução',
    'translation_disclaimer' => 'Esta página traduzida é fornecida apenas para referência. Em caso de erro, omissão, ambiguidade ou divergência, prevalece a versão em inglês do anúncio. Não assumimos responsabilidade pelo uso do conteúdo traduzido.',
    'nav' => ['properties' => 'Imóveis', 'about' => 'Sobre nós', 'valuation' => 'Avaliação', 'contact' => 'Contacto', 'admin' => 'Admin'],
    'actions' => ['view_properties' => 'Ver imóveis', 'request_valuation' => 'Pedir avaliação', 'search' => 'Pesquisar', 'send_enquiry' => 'Enviar pedido', 'submit_offer' => 'Enviar oferta', 'view_on_agency_site' => 'Ver no site da agência'],
    'home' => ['latest_local_listing' => 'Anúncio local mais recente', 'latest_properties' => 'Imóveis recentes', 'empty_properties' => 'Ainda não há imóveis publicados.'],
    'portal' => ['badge' => 'Pesquisa de imóveis na Irlanda', 'latest_listings' => 'Anúncios recentes', 'listed_by' => 'Anunciado por :agency', 'online_offers_enabled' => 'As ofertas online estão disponíveis no site da agência.', 'footer' => 'Anúncios sincronizados de agências imobiliárias independentes.'],
    'properties' => ['title' => 'Imóveis', 'all_types' => 'Todos os tipos de imóvel', 'price_on_application' => 'Preço sob consulta', 'description' => 'Descrição', 'features' => 'Características', 'enquire' => 'Pedir informações', 'make_offer' => 'Fazer uma oferta', 'listing_details' => 'Detalhes do anúncio', 'empty_search' => 'Nenhum imóvel corresponde à pesquisa.'],
    'labels' => ['name' => 'Nome', 'email' => 'Email', 'phone' => 'Telefone', 'message' => 'Mensagem', 'bedrooms' => 'quartos', 'bathrooms' => 'casas de banho', 'beds' => 'quartos', 'baths' => 'banhos', 'offer_amount' => 'Valor da oferta', 'financing' => 'Financiamento', 'mortgage_approval' => 'Aprovação de hipoteca', 'buyer_position' => 'Situação do comprador', 'proof_document' => 'Documento comprovativo', 'conditions' => 'Condições', 'select' => 'Selecionar'],
    'offer_terms' => 'Compreendo que esta oferta está sujeita à revisão do agente e ao contrato.',
    'messages' => ['enquiry_sent' => 'O seu pedido foi enviado.', 'offer_sent' => 'A sua oferta foi enviada para revisão do agente.', 'contact_sent' => 'Obrigado, entraremos em contacto em breve.', 'valuation_sent' => 'O seu pedido de avaliação foi enviado.'],
]);
