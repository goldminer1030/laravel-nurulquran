<html>
    <head>
        <title>{{ $settings->get('branding.site_name') }}</title>

        <base href="{{ $htmlBaseUri }}">

        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <link href='https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,400italic' rel='stylesheet' type='text/css'>
        <link rel="icon" type="image/x-icon" href="favicon.ico">

        {{--custom theme begin--}}
        @if ($settings->get('branding.use_custom_theme'))
            <link rel="stylesheet" href="{{asset('storage/appearance/theme.css')}}">
        @endif
        {{--custom theme end--}}

        {{--angular styles begin--}}
		<link href="styles.ec5639b3df4224e48472.bundle.css" rel="stylesheet">
		{{--angular styles end--}}

        @if ($settings->has('custom_code.css'))
            <style>{!! $settings->get('custom_code.css') !!}</style>
        @endif
	</head>

    <body>
        <app-root></app-root>

        <script>
            window.bootstrapData = "{!! $bootstrapData !!}";
        </script>

        {{--angular scripts begin--}}
		<script type="text/javascript" src="inline.541d56f60bac5882ef51.bundle.js"></script>
		<script type="text/javascript" src="polyfills.8736bd7b7e2705fb4847.bundle.js"></script>
		<script type="text/javascript" src="scripts.46e3ef7978a8bc26d72e.bundle.js"></script>
		<script type="text/javascript" src="vendor.be5398de0244bffc89be.bundle.js"></script>
		<script type="text/javascript" src="main.4bd6d9689d002c449b46.bundle.js"></script>
		{{--angular scripts end--}}

        @if ($settings->has('custom_code.js'))
            <script>{!! $settings->get('custom_code.js') !!}</script>
        @endif
	</body>
</html>