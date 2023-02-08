(function (_, $) {
    $('#send_new_code').on('click', '#resend-email-btn', fn_send_new_verify_code);
    let count = 3;

    function fn_send_new_verify_code() {
        if (count == 0) {
            window.location.href = 'index.php?dispatch=auth.login_form';
        }
        count -= 1;

        $.ceAjax('request', fn_url('auth.send_new_code'), {
            data: {
                count: count,
                result_ids: 'send_new_code',
            },
        });
    }

    function fn_delete_verify_code() {
        $.ceAjax('request', fn_url('auth.delete_verify_code'), {
            data: {
                result_ids: 'delete_verify_code',
            },
        });

        window.location.href = 'index.php?dispatch=auth.login_form';
    }

    let currentTime = new Date();
    let deadlineTime = currentTime.setMinutes(currentTime.getMinutes() + 5)
    let countdownTimer = setInterval(function() {
        let now = new Date().getTime();
        let rest = deadlineTime - now;
        let minutes = Math.floor( (rest % (1000 * 60 * 60)) / (1000 * 60) );
        let seconds = Math.floor( (rest % (1000 * 60)) / 1000 );
        minutes = minutes < 10 ? '0' + minutes : minutes;
        seconds = seconds < 10 ? '0' + seconds : seconds;
        document.getElementById('deadline-timer').innerHTML = minutes + ':' + seconds;
        if (rest < 0) {
            clearInterval(countdownTimer);
            document.getElementById('time-remainer').innerHTML = '<h2>Время истекло!</h2>';
            setTimeout(fn_delete_verify_code, 5000);
        }
    }, 1000);
})(Tygh, Tygh.$);
