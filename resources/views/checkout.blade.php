<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Redirecting to PayHere…</title>
</head>
<body>
    <p>Redirecting to PayHere, please wait…</p>

    <form id="payhere-form" method="POST" action="{{ $checkoutUrl }}">
        @foreach ($checkoutFields as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
    </form>

    <script>
        document.getElementById('payhere-form').submit();
    </script>
</body>
</html>
