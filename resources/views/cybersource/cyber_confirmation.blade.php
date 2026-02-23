@php

if (!function_exists('sign')) {
    function sign($params) {
        $secret_key = env('CYBERSOURCE_SECRET_KEY');
        return signData(buildDataToSign($params), $secret_key);
    }
}

if (!function_exists('signData')) {
    function signData($data, $secretKey) {
        return base64_encode(hash_hmac('sha256', $data, $secretKey, true));
    }
}

if (!function_exists('buildDataToSign')) {
    function buildDataToSign($params) {
        $signedFieldNames = explode(",", $params["signed_field_names"]);
        foreach ($signedFieldNames as $field) {
            $dataToSign[] = $field . "=" . $params[$field];
        }
        return commaSeparate($dataToSign);
    }
}

if (!function_exists('commaSeparate')) {
    function commaSeparate($dataToSign) {
        return implode(",", $dataToSign);
    }
}
@endphp


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cybersource</title>
    <link rel="stylesheet" type="text/css" href="payment.css"/>
</head>
<body>
{{-- <form id="payment_confirmation" action="https://testsecureacceptance.cybersource.com/pay" method="post">  --}}
<form id="payment_confirmation" action="{{env('CYBERSOURCE_URL')}}" method="post" style="display: none"> 
    <?php
        foreach($data as $name => $value) {
            $params[$name] = $value;
        }
    ?>
    <fieldset id="confirmation">
        <legend>Review Payment Details</legend>
        <div>
        <?php
            foreach($params as $name => $value) {
                echo "<div>";
                echo "<span class=\"fieldName\">" . $name . "</span><span class=\"fieldValue\">" . $value . "</span>";
                echo "</div>\n";
            }
        ?>
        </div>
    </fieldset>
    <?php
        foreach($params as $name => $value) {
            echo "<input type=\"text\" id=\"" . $name . "\" name=\"" . $name . "\" value=\"" . $value . "\"/>\n";
        echo '<br/>';
        }
        echo '<br/><br/>';
        echo "<input type=\"text\" id=\"signature\" name=\"signature\" value=\"" . sign($params) . "\"/>\n";
    ?>
    {{-- <input type="submit" id="submit" value="Confirm "/> --}}
    </form>



<script>
        document.addEventListener("DOMContentLoaded", function() {
         document.getElementById("payment_confirmation").submit();
     });
 </script>
</body>
</html>