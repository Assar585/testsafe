<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cybersource</title>
</head>
<body>
    <form id="payment_form" action="{{route('cybersource.process')}}" method="post" style="display: none;"> 
        @csrf
        @method('POST')

    <input type="text" id="access_key" name="access_key" value="{{env('CYBERSOURCE_ACCESS_KEY')}}">
    <input type="text" id="profile_id" name="profile_id" value="{{env('CYBERSOURCE_PROFILE_ID')}}">
    <input type="text" id="signed_field_names" name="signed_field_names" value="access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,amount,currency">
    <input type="text" id="unsigned_field_names" name="unsigned_field_names" value="bill_to_forename,bill_to_surname,bill_to_address_line1,bill_to_address_line2,bill_to_address_city,bill_to_address_country,bill_to_address_postal_code,bill_to_phone,bill_to_email">
    <input type="text" id="locale" name="locale" value="en">    
    <input type="text" id="amount" name="amount" value="{{$amount}}">
    <input type="text" id="currency" name="currency" value="{{$currency}}">
    <input type="text" id="transaction_type" name="transaction_type" value="sale">
    <input type="text" id="reference_number" name="reference_number" value="{{ $reference_number }}">
    <input type="text" id="transaction_uuid" name="transaction_uuid" value="{{ $transactionUuid }}">
    <?php date_default_timezone_set('Indian/Mahe'); ?>
    <input type="text" id="signed_date_time" name="signed_date_time" value="<?php echo gmdate("Y-m-d\TH:i:s\Z"); ?>">
    <?php 
        $t1 = $user->name ?? 'Martin Main';
        $t = explode(' ', $t1);
        $c = count($t);
        $lname = $t[$c-1];
        $fname = str_replace(' '.$lname, '', $t1);
    ?>
    <input type="text" id="bill_to_forename" name="bill_to_forename" value="{{ $fname }}">
    <input type="text" id="bill_to_surname" name="bill_to_surname" value="{{ $lname }}">

    <input type="text" id="bill_to_address_line1" name="bill_to_address_line1" value='{{ str_replace("\r"," ",$user->address) }}'>
    <input type="text" id="bill_to_address_line2" name="bill_to_address_line2" value=" ">
    <input type="text" id="bill_to_address_city" name="bill_to_address_city" value="{{ $user->city }}">
    <input type="text" id="bill_to_address_country" name="bill_to_address_country" value="{{ $user->country }}">
    <input type="text" id="bill_to_address_postal_code" name="bill_to_address_postal_code" value="{{$user->postal_code}}">
    <input type="text" id="bill_to_phone" name="bill_to_phone" value="{{ $user->phone }}">
    <input type="text" id="bill_to_email" name="bill_to_email" value="{{ $user->email }}">

    <button type="submit">Submit</button>
     <script type="text/javascript" src="payment_form.js"></script> 
</form>


<script>
       document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("payment_form").submit();
    });
</script>
</body>
</html>