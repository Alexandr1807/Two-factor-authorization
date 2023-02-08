{include file="common/letter_header.tpl"}
{__("sd_two_factor_auth.verify_code", ["[storefront_url]" => $storefront_url, "[password]" => $password]) nofilter}
{include file="common/letter_footer.tpl"}