{assign var="id" value=$id|default:"main_login"}

{capture name="login"}
    <form id="delete_verify_code" name="{$id}_form" action="{""|fn_url}" method="post" {if $style == "popup"}class="cm-ajax cm-ajax-full-render"{/if}>
        <input type="hidden" name="result_ids" value="send_new_code" />
        <input type="hidden" name="return_url" value="{$smarty.request.return_url|default:$config.current_url}" />
        <input type="hidden" name="redirect_url" value="{$redirect_url|default:$config.current_url}" />

        <p>{__("sd_two_factor_auth.enter_code_from_email")} <b>{$email}</b></p>
        <div id="time-remainer">
            {__("sd_two_factor_auth.rest_time_for_entering_code")}:
            <b><span id="deadline-timer"></span></b>
        </div>
        <div class="ty-control-group">
            <label for="verify_{$id}" class="ty-login__filed-label ty-control-group__label cm-required cm-trim">{__("sd_two_factor_auth.verification_code")}</label>
            <input type="text" id="verify_{$id}" name="verify_code" size="30" value="{if $stored_user_login}{$stored_user_login}{else}{$config.demo_username}{/if}" class="ty-login__input cm-focus" />
        </div>

        {include file="common/image_verification.tpl" option="login" align="left"}
        <div id="send_new_code">
            {hook name="index:login_buttons"}
                <div class="buttons-container clearfix">
                    <div class="ty-float-left">
                        <a id="resend-email-btn" class="ty-btn ty-btn__secondary">{__("sd_two_factor_auth.send_code_again")}</a>
                    </div>
                    <div class="ty-float-right">
                        {include file="buttons/login.tpl" but_name="dispatch[auth.verify_account]" but_role="submit"}
                    </div>
                    <br/>
                </div>
            {/hook}
            <div class="ty-float-left">
                <span>{__("sd_two_factor_auth.resend_code_text")}</span> {if isset($count)}{$count}{else}3{/if}
            </div>
        <!--send_new_code--></div>
    <!--delete_verify_code--></form>
{/capture}

<div class="ty-login">
    {$smarty.capture.login nofilter}
</div>

{capture name="mainbox_title"}{__("sd_two_factor_auth.confirmation")}{/capture}
{script src="js/addons/sd_two_factor_auth/func.js"}
