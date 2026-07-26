<?php

$base = require __DIR__.'/../en/site.php';

return array_replace_recursive($base, [
    'language' => '语言',
    'translation_notice_title' => '翻译提示',
    'translation_disclaimer' => '本翻译页面仅供参考。如有任何错误、遗漏、歧义，或与英文版不一致之处，一律以英文版房源信息为准。我们不对因依赖翻译内容而产生的任何后果承担责任。',
    'nav' => ['menu' => '菜单', 'properties' => '房源', 'about' => '关于我们', 'valuation' => '估价', 'mortgages' => '贷款', 'contact' => '联系', 'admin' => '后台'],
    'actions' => ['view_properties' => '查看房源', 'request_valuation' => '申请估价', 'search' => '搜索', 'send_enquiry' => '发送咨询', 'submit_offer' => '提交报价', 'view_on_agency_site' => '在中介网站查看'],
    'home' => ['latest_local_listing' => '最新本地房源', 'latest_properties' => '最新房源', 'empty_properties' => '暂无已发布房源。'],
    'portal' => ['badge' => '爱尔兰房产搜索', 'latest_listings' => '最新房源', 'listed_by' => '发布方：:agency', 'online_offers_enabled' => '可在中介网站在线报价。', 'footer' => '来自独立房产中介的同步房源。'],
    'properties' => ['title' => '房源', 'categories' => ['all' => '全部', 'for_sale' => '在售', 'to_rent' => '在租', 'commercial' => '商业', 'other' => '其他'], 'all_types' => '所有房产类型', 'price_on_application' => '价格请咨询', 'description' => '描述', 'features' => '特点', 'enquire' => '咨询', 'make_offer' => '提交报价', 'listing_details' => '房源详情', 'empty_search' => '没有符合搜索条件的房源。'],
    'labels' => ['name' => '姓名', 'email' => '邮箱', 'phone' => '电话', 'message' => '留言', 'bedrooms' => '卧室', 'bathrooms' => '浴室', 'beds' => '卧室', 'baths' => '浴室', 'offer_amount' => '报价金额', 'financing' => '付款方式', 'mortgage_approval' => '贷款批准状态', 'buyer_position' => '买家情况', 'proof_document' => '证明文件', 'conditions' => '条件', 'select' => '请选择'],
    'offer_terms' => '我理解该报价需经中介审核并以合同为准。',
    'messages' => ['enquiry_sent' => '您的咨询已发送。', 'offer_sent' => '您的报价已提交给中介审核。', 'buyer_access_requested' => '您的竞价准入申请已提交给中介审核。', 'contact_sent' => '谢谢，我们会尽快联系您。', 'valuation_sent' => '您的估价申请已发送。'],
]);
