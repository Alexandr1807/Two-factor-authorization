{if $addons.yandex_delivery.autocomplete == "Y"}
    {script src="js/addons/yandex_delivery/autocomplete.js"}
{/if}

{if $addons.yandex_delivery.autopostcode == "Y"}
    {script src="js/addons/yandex_delivery/postcode.js"}
{/if}

<script>
    (function (_, $) {
        _.tr({
            "yandex_delivery.yandex_delivery_cookie_title": '{__("yandex_delivery.yandex_delivery_cookie_title", ['skip_live_editor' => true])|escape:"javascript"}',
            "yandex_delivery.yandex_delivery_cookie_description": '{__("yandex_delivery.yandex_delivery_cookie_description", ['skip_live_editor' => true])|escape:"javascript"}',
        });
    })(Tygh, Tygh.$);
</script>